<?php

namespace App\Services;

use App\Models\BloqueContenido;

class LandingPromoBannersContent
{
    public const BLOCK_KEY = 'landing_promo_banners';
    public const PROMO_IMAGE_FIELD_KEY = 'promo_image_url';
    public const RECOMMENDED_IMAGE_FIELD_KEY = 'rec_image_url';

    public static function defaults(): array
    {
        return [
            'promo' => [
                'label' => 'OFERTA',
                'title' => '2x1 en monturas seleccionadas',
                'desc' => 'Banner listo para que lo administres desde tu panel.',
                'cta_text' => 'Ver ofertas',
                'href' => '#destacados',
                'image_url' => '',
                'image_alt' => 'Banner promo',
            ],
            'recommended' => [
                'label' => 'RECOMENDADO',
                'title' => 'Luz azul para pantallas',
                'desc' => 'Arma tu sección “luz azul” como en una tienda grande.',
                'cta_text' => 'Ver servicio',
                'href' => '#servicios',
                'image_url' => '',
                'image_alt' => 'Banner luz azul',
            ],
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

        $assetsByField = $block
            ? $block->archivos()
                ->whereIn('field_key', [self::PROMO_IMAGE_FIELD_KEY, self::RECOMMENDED_IMAGE_FIELD_KEY])
                ->orderBy('orden')
                ->orderBy('id')
                ->get()
                ->groupBy('field_key')
            : collect();
        $merged = array_replace($defaults, array_filter($data, fn ($v) => $v !== null));

        foreach (['promo', 'recommended'] as $key) {
            $base = is_array($defaults[$key] ?? null) ? $defaults[$key] : [];
            $row = is_array($merged[$key] ?? null) ? $merged[$key] : [];

            $manualImageUrl = trim((string) ($row['image_url'] ?? $base['image_url'] ?? ''));
            $fieldKey = $key === 'promo' ? self::PROMO_IMAGE_FIELD_KEY : self::RECOMMENDED_IMAGE_FIELD_KEY;
            $uploadedAsset = $assetsByField->get($fieldKey)?->last();
            $uploadedImageUrl = $uploadedAsset ? $uploadedAsset->publicUrl() : '';

            $merged[$key] = [
                'label' => (string) ($row['label'] ?? $base['label'] ?? ''),
                'title' => (string) ($row['title'] ?? $base['title'] ?? ''),
                'desc' => (string) ($row['desc'] ?? $base['desc'] ?? ''),
                'cta_text' => (string) ($row['cta_text'] ?? $base['cta_text'] ?? ''),
                'href' => (string) ($row['href'] ?? $base['href'] ?? '#'),
                'image_url' => $uploadedImageUrl !== '' ? $uploadedImageUrl : $manualImageUrl,
                'manual_image_url' => $manualImageUrl,
                'has_uploaded_image' => $uploadedImageUrl !== '',
                'image_alt' => (string) ($row['image_alt'] ?? $base['image_alt'] ?? ''),
            ];
        }

        return $merged;
    }

    public static function upsert(array $data): BloqueContenido
    {
        return BloqueContenido::query()->updateOrCreate(
            ['clave' => self::BLOCK_KEY],
            [
                'titulo' => 'Landing: banners promo',
                'cuerpo' => null,
                'datos' => $data,
                'esta_activo' => true,
                'orden' => 3,
            ]
        );
    }
}
