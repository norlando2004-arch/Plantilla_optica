<?php

namespace App\Services;

use App\Models\BloqueContenido;

class LandingHighlightsContent
{
    public const BLOCK_KEY = 'landing_highlights';

    public static function defaults(): array
    {
        return [
            'title' => 'Destacados',
            'subtitle' => 'Bloques tipo promo para que luego los administres desde tu panel.',
            'tip_label' => 'Tip',
            'tip_text' => 'Puedes mapear estos “cards” a productos, colecciones o banners.',
            'items' => [
                [
                    'tag' => '2x1',
                    'title' => 'Monturas seleccionadas',
                    'copy' => 'Promoción temporal (editable)',
                    'href' => '#',
                    'image_url' => '',
                    'image_alt' => 'Monturas seleccionadas',
                ],
                [
                    'tag' => 'Nuevo',
                    'title' => 'Colección minimal',
                    'copy' => 'Líneas limpias y livianas',
                    'href' => '#',
                    'image_url' => '',
                    'image_alt' => 'Colección minimal',
                ],
                [
                    'tag' => 'Top',
                    'title' => 'Para manejar',
                    'copy' => 'Enfoque y comodidad',
                    'href' => '#',
                    'image_url' => '',
                    'image_alt' => 'Para manejar',
                ],
                [
                    'tag' => 'Plus',
                    'title' => 'Progresivos',
                    'copy' => 'Para todas las distancias',
                    'href' => '#',
                    'image_url' => '',
                    'image_alt' => 'Progresivos',
                ],
            ],
        ];
    }

    public static function load(): array
    {
        $defaults = static::defaults();

        try {
            $row = BloqueContenido::query()->where('clave', static::BLOCK_KEY)->first();
        } catch (\Throwable $e) {
            $row = null;
        }
        $stored = is_array($row?->datos) ? $row->datos : [];

        $data = array_merge($defaults, array_intersect_key($stored, $defaults));

        $items = is_array($stored['items'] ?? null) ? $stored['items'] : [];
        $normalized = [];

        for ($i = 0; $i < 4; $i++) {
            $fallback = $defaults['items'][$i];
            $src = is_array($items[$i] ?? null) ? $items[$i] : [];

            $normalized[] = [
                'tag' => (string)($src['tag'] ?? $fallback['tag']),
                'title' => (string)($src['title'] ?? $fallback['title']),
                'copy' => (string)($src['copy'] ?? $fallback['copy']),
                'href' => (string)($src['href'] ?? $fallback['href']),
                'image_url' => (string)($src['image_url'] ?? $fallback['image_url']),
                'image_alt' => (string)($src['image_alt'] ?? ($src['title'] ?? $fallback['image_alt'])),
            ];
        }

        $data['items'] = $normalized;

        return $data;
    }

    public static function upsert(array $data): void
    {
        $payload = array_merge(static::defaults(), $data);

        BloqueContenido::query()->updateOrCreate(
            ['clave' => static::BLOCK_KEY],
            [
                'nombre' => 'Destacados (Landing)',
                'orden' => 6,
                'datos' => $payload,
            ]
        );
    }
}
