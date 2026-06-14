<?php

namespace App\Services;

use App\Models\BloqueContenido;

class GafasPromoContent
{
    public const BLOCK_KEY = 'gafas_page_promo';
    public const FIELD_IMAGE = 'promo_image';

    public static function defaults(): array
    {
        return [
            'image_url' => '/images/borrardespues.png',
            'image_alt' => 'Banner principal',
        ];
    }

    public static function load(): array
    {
        $defaults = self::defaults();

        try {
            $block = BloqueContenido::query()
                ->where('clave', self::BLOCK_KEY)
                ->where('esta_activo', true)
                ->first();
        } catch (\Throwable $e) {
            $block = null;
        }

        $data = is_array($block?->datos) ? $block->datos : [];
        $manualImageUrl = trim((string) ($data['image_url'] ?? $defaults['image_url']));

        $uploadedAsset = $block?->archivos()
            ->where('field_key', self::FIELD_IMAGE)
            ->orderByDesc('orden')
            ->orderByDesc('id')
            ->first();

        return [
            'image_url' => $uploadedAsset ? $uploadedAsset->publicUrl() : $manualImageUrl,
            'manual_image_url' => $manualImageUrl,
            'image_alt' => (string) ($data['image_alt'] ?? $defaults['image_alt']),
            'has_uploaded_image' => !is_null($uploadedAsset),
            'uploaded_name' => $uploadedAsset?->original_name,
        ];
    }
}