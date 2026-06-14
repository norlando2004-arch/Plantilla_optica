<?php

namespace App\Services;

use App\Models\BloqueContenido;

class GafasPolarizadasPromoContent
{
    public const BLOCK_KEY = 'gafas_polarizadas_page_promo';
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

        $uploadedAssets = $block?->archivos()
            ->where('field_key', self::FIELD_IMAGE)
            ->orderByDesc('orden')
            ->orderByDesc('id')
            ->get() ?? collect();

        $uploadedImageUrls = $uploadedAssets
            ->map(static fn ($asset): string => $asset->publicUrl())
            ->filter(static fn (string $url): bool => trim($url) !== '')
            ->values()
            ->all();

        $uploadedImageNames = $uploadedAssets
            ->map(static fn ($asset): string => trim((string) ($asset->original_name ?? '')))
            ->filter(static fn (string $name): bool => $name !== '')
            ->values()
            ->all();

        $promoAssets = $uploadedAssets
            ->map(static fn ($asset): array => [
                'id' => (int) $asset->id,
                'url' => $asset->publicUrl(),
                'name' => trim((string) ($asset->original_name ?? '')),
            ])
            ->filter(static fn (array $asset): bool => trim((string) ($asset['url'] ?? '')) !== '')
            ->values()
            ->all();

        return [
            'image_url' => $uploadedImageUrls[0] ?? $manualImageUrl,
            'image_urls' => $uploadedImageUrls,
            'manual_image_url' => $manualImageUrl,
            'image_alt' => (string) ($data['image_alt'] ?? $defaults['image_alt']),
            'has_uploaded_image' => count($uploadedImageUrls) > 0,
            'uploaded_name' => $uploadedImageNames[0] ?? null,
            'uploaded_names' => $uploadedImageNames,
            'promo_assets' => $promoAssets,
        ];
    }

    public static function upsert(array $data): BloqueContenido
    {
        return BloqueContenido::query()->updateOrCreate(
            ['clave' => self::BLOCK_KEY],
            [
                'titulo' => 'Gafas polarizadas: promo de cabecera',
                'cuerpo' => null,
                'datos' => $data,
                'esta_activo' => true,
                'orden' => 1,
            ]
        );
    }
}
