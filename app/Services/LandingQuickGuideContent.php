<?php

namespace App\Services;

use App\Models\BloqueContenido;

class LandingQuickGuideContent
{
    public const BLOCK_KEY = 'landing_quick_guide';

    public static function defaults(): array
    {
        return [
            'label' => 'GUÍA RÁPIDA',
            'title' => 'Compra como en una tienda grande',
            'desc' => 'Este banner intercalado funciona como bloque “editorial”: engancha al usuario y lo lleva al flujo.',
            'cta_primary_text' => 'Ver cómo funciona',
            'cta_primary_href' => '#como-funciona',
            'cta_secondary_text' => 'Pedir asesoría',
            'cta_secondary_href' => '#contacto',
            'steps' => [
                ['k' => '1', 'v' => 'Elige'],
                ['k' => '2', 'v' => 'Configura'],
                ['k' => '3', 'v' => 'Cotiza'],
            ],
            'image_url' => '',
            'image_alt' => 'Banner guía',
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
        $merged = array_replace($defaults, array_filter($data, fn ($v) => $v !== null));

        $defaultSteps = is_array($defaults['steps'] ?? null) ? $defaults['steps'] : [];
        $steps = is_array($merged['steps'] ?? null) ? $merged['steps'] : [];

        $normSteps = [];
        for ($i = 0; $i < 3; $i++) {
            $base = is_array($defaultSteps[$i] ?? null) ? $defaultSteps[$i] : ['k' => (string)($i + 1), 'v' => ''];
            $row = is_array($steps[$i] ?? null) ? $steps[$i] : [];
            $normSteps[] = [
                'k' => (string) ($row['k'] ?? $base['k'] ?? (string)($i + 1)),
                'v' => (string) ($row['v'] ?? $base['v'] ?? ''),
            ];
        }

        $merged['steps'] = $normSteps;
        $merged['label'] = (string) ($merged['label'] ?? $defaults['label']);
        $merged['title'] = (string) ($merged['title'] ?? $defaults['title']);
        $merged['desc'] = (string) ($merged['desc'] ?? $defaults['desc']);
        $merged['cta_primary_text'] = (string) ($merged['cta_primary_text'] ?? $defaults['cta_primary_text']);
        $merged['cta_primary_href'] = (string) ($merged['cta_primary_href'] ?? $defaults['cta_primary_href']);
        $merged['cta_secondary_text'] = (string) ($merged['cta_secondary_text'] ?? $defaults['cta_secondary_text']);
        $merged['cta_secondary_href'] = (string) ($merged['cta_secondary_href'] ?? $defaults['cta_secondary_href']);
        $merged['image_url'] = (string) ($merged['image_url'] ?? $defaults['image_url']);
        $merged['image_alt'] = (string) ($merged['image_alt'] ?? $defaults['image_alt']);

        return $merged;
    }

    public static function upsert(array $data): void
    {
        BloqueContenido::query()->updateOrCreate(
            ['clave' => self::BLOCK_KEY],
            [
                'titulo' => 'Landing: guía rápida',
                'cuerpo' => null,
                'datos' => $data,
                'esta_activo' => true,
                'orden' => 5,
            ]
        );
    }
}
