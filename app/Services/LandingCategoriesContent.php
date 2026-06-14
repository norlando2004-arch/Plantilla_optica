<?php

namespace App\Services;

use App\Models\BloqueContenido;

class LandingCategoriesContent
{
    public const BLOCK_KEY = 'landing_categories';

    private const ITEMS_COUNT = 4;

    public static function defaults(): array
    {
        return [
            'title' => 'Categorías',
            'subtitle' => 'Secciones listas para conectar con tu inventario y CMS.',
            'top_link_text' => 'Ir a destacados →',
            'top_link_href' => '#destacados',
            'items' => [
                ['title' => 'Gafas formuladas', 'desc' => 'Clásicas, modernas y ligeras', 'href' => '#', 'image_url' => '', 'image_pos_x' => 50, 'image_pos_y' => 50, 'image_zoom' => 1],
                ['title' => 'Gafas de sol', 'desc' => 'Protección UV y estilo', 'href' => '#', 'image_url' => '', 'image_pos_x' => 50, 'image_pos_y' => 50, 'image_zoom' => 1],
                ['title' => 'Luz azul', 'desc' => 'Pantallas sin fatiga', 'href' => '#', 'image_url' => '', 'image_pos_x' => 50, 'image_pos_y' => 50, 'image_zoom' => 1],
                ['title' => 'Accesorios', 'desc' => 'Estuches, paños y más', 'href' => '#', 'image_url' => '', 'image_pos_x' => 50, 'image_pos_y' => 50, 'image_zoom' => 1],
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

        $assetFieldKeys = [];
        for ($i = 0; $i < self::ITEMS_COUNT; $i++) {
            $assetFieldKeys[] = "item_{$i}_image_url";
        }

        $assetsByField = $block
            ? $block->archivos()
                ->whereIn('field_key', $assetFieldKeys)
                ->orderBy('orden')
                ->orderBy('id')
                ->get()
                ->groupBy('field_key')
            : collect();

        $merged = array_replace($defaults, array_filter($data, fn ($v) => $v !== null));

        $defaultItems = is_array($defaults['items'] ?? null) ? $defaults['items'] : [];
        $items = is_array($merged['items'] ?? null) ? $merged['items'] : [];

        $normalized = [];
        for ($i = 0; $i < self::ITEMS_COUNT; $i++) {
            $base = is_array($defaultItems[$i] ?? null) ? $defaultItems[$i] : ['title' => '', 'desc' => '', 'href' => '#', 'image_url' => '', 'image_pos_x' => 50, 'image_pos_y' => 50, 'image_zoom' => 1];
            $row = is_array($items[$i] ?? null) ? $items[$i] : [];

            $posX = is_numeric($row['image_pos_x'] ?? null) ? (float) $row['image_pos_x'] : (float) ($base['image_pos_x'] ?? 50);
            $posY = is_numeric($row['image_pos_y'] ?? null) ? (float) $row['image_pos_y'] : (float) ($base['image_pos_y'] ?? 50);
            $zoom = is_numeric($row['image_zoom'] ?? null) ? (float) $row['image_zoom'] : (float) ($base['image_zoom'] ?? 1);

            $posX = max(0, min(100, $posX));
            $posY = max(0, min(100, $posY));
            $zoom = max(0.5, min(1.5, $zoom));

            $manualImageUrl = trim((string) ($row['image_url'] ?? $base['image_url'] ?? ''));
            $assetFieldKey = "item_{$i}_image_url";
            $uploadedAsset = $assetsByField->get($assetFieldKey)?->last();
            $uploadedImageUrl = $uploadedAsset ? $uploadedAsset->publicUrl() : '';

            $normalized[] = [
                'title' => (string) ($row['title'] ?? $base['title'] ?? ''),
                'desc' => (string) ($row['desc'] ?? $base['desc'] ?? ''),
                'href' => (string) ($row['href'] ?? $base['href'] ?? '#'),
                'image_url' => $uploadedImageUrl !== '' ? $uploadedImageUrl : $manualImageUrl,
                'manual_image_url' => $manualImageUrl,
                'has_uploaded_image' => $uploadedImageUrl !== '',
                'image_pos_x' => $posX,
                'image_pos_y' => $posY,
                'image_zoom' => $zoom,
            ];
        }

        $merged['items'] = $normalized;
        return $merged;
    }

    public static function upsert(array $data): BloqueContenido
    {
        return BloqueContenido::query()->updateOrCreate(
            ['clave' => self::BLOCK_KEY],
            [
                'titulo' => 'Landing: categorías',
                'cuerpo' => null,
                'datos' => $data,
                'esta_activo' => true,
                'orden' => 2,
            ]
        );
    }
}
