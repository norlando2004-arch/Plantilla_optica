<?php

namespace App\Services;

use App\Models\BloqueContenido;

class LandingWhatsappContent
{
    public const BLOCK_KEY = 'landing_whatsapp';

    public static function defaults(): array
    {
        return [
            'phone_number' => '',
            'bubble_message' => '',
            'icon_url' => '/images/whatsapp.png',
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

        return [
            'phone_number' => trim((string) ($merged['phone_number'] ?? '')),
            'bubble_message' => trim((string) ($merged['bubble_message'] ?? '')),
            'icon_url' => trim((string) ($merged['icon_url'] ?? '')),
        ];
    }

    public static function upsert(array $data): void
    {
        BloqueContenido::query()->updateOrCreate(
            ['clave' => self::BLOCK_KEY],
            [
                'titulo' => 'Landing: WhatsApp',
                'cuerpo' => null,
                'datos' => $data,
                'esta_activo' => true,
                'orden' => 50,
            ]
        );
    }
}
