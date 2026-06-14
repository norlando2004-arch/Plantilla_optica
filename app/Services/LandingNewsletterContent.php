<?php

namespace App\Services;

use App\Models\BloqueContenido;

class LandingNewsletterContent
{
    public const BLOCK_KEY = 'landing_newsletter';

    public static function defaults(): array
    {
        return [
            'title' => 'Recibe novedades y promociones',
            'subtitle' => 'Formulario visual. Luego lo conectas a tu proveedor de email o a tu admin.',
            'email_label' => 'Email',
            'email_placeholder' => 'tuemail@correo.com',
            'button_text' => 'Suscribirme',
            'note' => 'Placeholder: sin envío real todavía.',
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

        return [
            'title' => (string) ($data['title'] ?? $defaults['title']),
            'subtitle' => (string) ($data['subtitle'] ?? $defaults['subtitle']),
            'email_label' => (string) ($data['email_label'] ?? $defaults['email_label']),
            'email_placeholder' => (string) ($data['email_placeholder'] ?? $defaults['email_placeholder']),
            'button_text' => (string) ($data['button_text'] ?? $defaults['button_text']),
            'note' => (string) ($data['note'] ?? $defaults['note']),
        ];
    }

    public static function upsert(array $data): void
    {
        $payload = array_merge(static::defaults(), $data);

        BloqueContenido::query()->updateOrCreate(
            ['clave' => static::BLOCK_KEY],
            [
                'nombre' => 'Newsletter (Landing)',
                'orden' => 12,
                'datos' => $payload,
            ]
        );
    }
}
