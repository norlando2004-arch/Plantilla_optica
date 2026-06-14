<?php

namespace App\Services;

use App\Models\BloqueContenido;

class LandingEssentialBenefitsContent
{
    public const BLOCK_KEY = 'landing_essential_benefits';

    public static function defaults(): array
    {
        return [
            'title' => 'Beneficios',
            'subtitle' => 'Lo esencial: confianza, claridad y soporte.',
            'items' => [
                ['title' => 'Garantía y cambios', 'desc' => 'Política simple y transparente'],
                ['title' => 'Asesoría', 'desc' => 'Ayuda para elegir talla y estilo'],
                ['title' => 'Calidad', 'desc' => 'Materiales pensados para durar'],
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

        for ($i = 0; $i < 3; $i++) {
            $fallback = $defaults['items'][$i];
            $src = is_array($items[$i] ?? null) ? $items[$i] : [];

            $normalized[] = [
                'title' => (string)($src['title'] ?? $fallback['title']),
                'desc' => (string)($src['desc'] ?? $fallback['desc']),
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
                'nombre' => 'Beneficios (Sección)',
                'orden' => 8,
                'datos' => $payload,
            ]
        );
    }
}
