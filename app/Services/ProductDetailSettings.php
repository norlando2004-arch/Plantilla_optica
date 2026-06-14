<?php

namespace App\Services;

use App\Models\BloqueContenido;

class ProductDetailSettings
{
    public const BLOCK_KEY = 'gafas_show_settings';

    public static function defaults(): array
    {
        return [
            'hide_love_cta_for_clients' => false,
        ];
    }

    public static function load(): array
    {
        $defaults = self::defaults();

        try {
            $block = BloqueContenido::query()
                ->where('clave', self::BLOCK_KEY)
                ->first();
        } catch (\Throwable $e) {
            $block = null;
        }

        $data = is_array($block?->datos) ? $block->datos : [];

        return array_replace($defaults, array_filter($data, static fn ($value) => $value !== null));
    }

    public static function hideLoveCtaForClients(): bool
    {
        return (bool) (self::load()['hide_love_cta_for_clients'] ?? false);
    }

    public static function upsert(array $data): BloqueContenido
    {
        $payload = array_replace(self::defaults(), $data);

        return BloqueContenido::query()->updateOrCreate(
            ['clave' => self::BLOCK_KEY],
            [
                'titulo' => 'Gafas: ajustes de detalle',
                'cuerpo' => null,
                'datos' => $payload,
                'esta_activo' => true,
                'orden' => 0,
            ]
        );
    }
}
