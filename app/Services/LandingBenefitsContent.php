<?php

namespace App\Services;

use App\Models\BloqueContenido;

class LandingBenefitsContent
{
    public const BLOCK_KEY = 'landing_benefits';

    private const ITEMS_COUNT = 4;

    public static function defaults(): array
    {
        return [
            'items' => [
                ['title' => 'Calidad verificada', 'desc' => 'Materiales y acabados consistentes'],
                ['title' => 'Ajuste cómodo', 'desc' => 'Opciones para diario y trabajo'],
                ['title' => 'Protección UV', 'desc' => 'Opciones para sol y exteriores'],
                ['title' => 'Luz azul', 'desc' => 'Diseñado para pantallas'],
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

        $merged = array_replace($defaults, array_filter($data, fn ($v) => $v !== null));

        $defaultItems = is_array($defaults['items'] ?? null) ? $defaults['items'] : [];
        $items = is_array($merged['items'] ?? null) ? $merged['items'] : [];

        $normalized = [];
        for ($i = 0; $i < self::ITEMS_COUNT; $i++) {
            $base = is_array($defaultItems[$i] ?? null) ? $defaultItems[$i] : ['title' => '', 'desc' => ''];
            $row = is_array($items[$i] ?? null) ? $items[$i] : [];
            $normalized[] = [
                'title' => (string) ($row['title'] ?? $base['title'] ?? ''),
                'desc' => (string) ($row['desc'] ?? $base['desc'] ?? ''),
            ];
        }

        $merged['items'] = $normalized;
        return $merged;
    }

    public static function upsert(array $data): void
    {
        BloqueContenido::query()->updateOrCreate(
            ['clave' => self::BLOCK_KEY],
            [
                'titulo' => 'Landing: beneficios',
                'cuerpo' => null,
                'datos' => $data,
                'esta_activo' => true,
                'orden' => 1,
            ]
        );
    }
}
