<?php

namespace App\Services;

use App\Models\BloqueContenido;

class LandingServicesContent
{
    public const BLOCK_KEY = 'landing_services';

    private const ITEMS_COUNT = 4;

    public static function defaults(): array
    {
        return [
            'title' => 'Servicios',
            'subtitle' => 'Módulos típicos de una óptica: listos para parametrizar.',
            'note_label' => 'Nota',
            'note_text' => 'Aquí no hay lógica: es UI. Luego lo conectamos a rutas, modelos y admin.',
            'items' => [
                [
                    'title' => 'Asesoría de montura',
                    'desc' => 'Recomendación por forma de rostro y uso',
                    'href' => '#contacto',
                    'image_url' => '',
                    'image_alt' => 'Asesoría de montura',
                    'image_pos_x' => 50.0,
                    'image_pos_y' => 50.0,
                    'image_zoom' => 1.0,
                ],
                [
                    'title' => 'Lentes formulados',
                    'desc' => 'Opciones estándar, antirreflejo, fotocromático',
                    'href' => '#contacto',
                    'image_url' => '',
                    'image_alt' => 'Lentes formulados',
                    'image_pos_x' => 50.0,
                    'image_pos_y' => 50.0,
                    'image_zoom' => 1.0,
                ],
                [
                    'title' => 'Filtro luz azul',
                    'desc' => 'Para estudio, oficina y pantallas',
                    'href' => '#contacto',
                    'image_url' => '',
                    'image_alt' => 'Filtro luz azul',
                    'image_pos_x' => 50.0,
                    'image_pos_y' => 50.0,
                    'image_zoom' => 1.0,
                ],
                [
                    'title' => 'Ajuste y mantenimiento',
                    'desc' => 'Cambio de plaquetas, ajuste, limpieza',
                    'href' => '#contacto',
                    'image_url' => '',
                    'image_alt' => 'Ajuste y mantenimiento',
                    'image_pos_x' => 50.0,
                    'image_pos_y' => 50.0,
                    'image_zoom' => 1.0,
                ],
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
            $base = is_array($defaultItems[$i] ?? null)
                ? $defaultItems[$i]
                : ['title' => '', 'desc' => '', 'href' => '#contacto', 'image_url' => '', 'image_alt' => ''];
            $row = is_array($items[$i] ?? null) ? $items[$i] : [];

            $title = (string) ($row['title'] ?? $base['title'] ?? '');
            $manualImageUrl = trim((string) ($row['image_url'] ?? $base['image_url'] ?? ''));
            $assetFieldKey = "item_{$i}_image_url";
            $uploadedAsset = $assetsByField->get($assetFieldKey)?->last();
            $uploadedImageUrl = $uploadedAsset ? $uploadedAsset->publicUrl() : '';

            $posX = (float) ($row['image_pos_x'] ?? $base['image_pos_x'] ?? 50.0);
            $posY = (float) ($row['image_pos_y'] ?? $base['image_pos_y'] ?? 50.0);
            $zoom = (float) ($row['image_zoom'] ?? $base['image_zoom'] ?? 1.0);

            $posX = max(0.0, min(100.0, $posX));
            $posY = max(0.0, min(100.0, $posY));
            $zoom = max(0.5, min(1.5, $zoom));

            $normalized[] = [
                'title' => $title,
                'desc' => (string) ($row['desc'] ?? $base['desc'] ?? ''),
                'href' => (string) ($row['href'] ?? $base['href'] ?? '#contacto'),
                'image_url' => $uploadedImageUrl !== '' ? $uploadedImageUrl : $manualImageUrl,
                'manual_image_url' => $manualImageUrl,
                'has_uploaded_image' => $uploadedImageUrl !== '',
                'image_alt' => (string) ($row['image_alt'] ?? ($base['image_alt'] ?? $title)),
                'image_pos_x' => $posX,
                'image_pos_y' => $posY,
                'image_zoom' => $zoom,
            ];
        }

        $merged['items'] = $normalized;
        $merged['title'] = (string) ($merged['title'] ?? $defaults['title']);
        $merged['subtitle'] = (string) ($merged['subtitle'] ?? $defaults['subtitle']);
        $merged['note_label'] = (string) ($merged['note_label'] ?? $defaults['note_label']);
        $merged['note_text'] = (string) ($merged['note_text'] ?? $defaults['note_text']);

        return $merged;
    }

    public static function upsert(array $data): BloqueContenido
    {
        return BloqueContenido::query()->updateOrCreate(
            ['clave' => self::BLOCK_KEY],
            [
                'titulo' => 'Landing: servicios',
                'cuerpo' => null,
                'datos' => $data,
                'esta_activo' => true,
                'orden' => 4,
            ]
        );
    }
}
