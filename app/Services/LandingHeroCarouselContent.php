<?php

namespace App\Services;

use App\Models\BloqueContenido;

class LandingHeroCarouselContent
{
    public const BLOCK_KEY = 'landing_hero_carousel';

    public static function defaults(): array
    {
        return [
            'seconds_per_slide' => 5,
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
        $seconds = (int) ($data['seconds_per_slide'] ?? $defaults['seconds_per_slide']);
        $seconds = max(2, min(30, $seconds));

        return [
            'seconds_per_slide' => $seconds,
        ];
    }

    public static function upsert(array $data): void
    {
        $seconds = (int) ($data['seconds_per_slide'] ?? self::defaults()['seconds_per_slide']);
        $seconds = max(2, min(30, $seconds));

        BloqueContenido::query()->updateOrCreate(
            ['clave' => self::BLOCK_KEY],
            [
                'titulo' => 'Landing: carrusel hero',
                'cuerpo' => null,
                'datos' => ['seconds_per_slide' => $seconds],
                'esta_activo' => true,
                'orden' => 1,
            ]
        );
    }
}
