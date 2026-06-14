<?php

namespace App\Services;

use App\Models\BloqueContenido;

class LandingBenefitStripContent
{
    public const BLOCK_KEY = 'landing_benefit_strip';

    public static function defaults(): array
    {
        return [
            'items' => [
                'Exámen visual computarizado',
                'Garantía de lentes de 3 meses',
                'Kit de limpieza',
                'Estuche metálico',
                'Gafas formuladas',
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
        $items = is_array($data['items'] ?? null) ? $data['items'] : [];

        $normalized = [];
        foreach ($items as $item) {
            $line = trim((string) $item);
            if ($line === '') {
                continue;
            }
            $normalized[] = mb_substr($line, 0, 120);
            if (count($normalized) >= 12) {
                break;
            }
        }

        if (count($normalized) === 0) {
            $normalized = $defaults['items'];
        }

        return [
            'items' => $normalized,
        ];
    }

    public static function upsert(array $data): void
    {
        $items = is_array($data['items'] ?? null) ? $data['items'] : [];

        $normalized = [];
        foreach ($items as $item) {
            $line = trim((string) $item);
            if ($line === '') {
                continue;
            }
            $normalized[] = mb_substr($line, 0, 120);
            if (count($normalized) >= 12) {
                break;
            }
        }

        if (count($normalized) === 0) {
            $normalized = self::defaults()['items'];
        }

        BloqueContenido::query()->updateOrCreate(
            ['clave' => self::BLOCK_KEY],
            [
                'titulo' => 'Landing: franja de beneficios',
                'cuerpo' => null,
                'datos' => ['items' => $normalized],
                'esta_activo' => true,
                'orden' => 2,
            ]
        );
    }
}
