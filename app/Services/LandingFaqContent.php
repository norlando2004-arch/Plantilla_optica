<?php

namespace App\Services;

use App\Models\BloqueContenido;

class LandingFaqContent
{
    public const BLOCK_KEY = 'landing_faq';

    public static function defaults(): array
    {
        return [
            'title' => 'Preguntas frecuentes',
            'subtitle' => 'FAQ accesible usando <details> (sin JS).',
            'items' => [
                [
                    'question' => '¿Puedo cambiar mi montura?',
                    'answer' => 'Sí. Escríbenos con tu número de pedido y te guiamos según nuestras políticas de cambio.',
                ],
                [
                    'question' => '¿Qué incluye la garantía?',
                    'answer' => 'Incluye revisión de fábrica y soporte ante defectos. El alcance exacto depende del producto.',
                ],
                [
                    'question' => '¿Hacen lentes con filtro luz azul?',
                    'answer' => 'Sí. Podemos cotizarte lentes con filtro de luz azul según tu receta y uso.',
                ],
                [
                    'question' => '¿Cómo se realiza la cotización?',
                    'answer' => 'Compartes tu receta (si aplica) y tus preferencias. Te enviamos una propuesta con opciones y precios.',
                ],
                [
                    'question' => '¿Tienen asesoría para elegir talla?',
                    'answer' => 'Sí. Te ayudamos a elegir talla y estilo con medidas y recomendaciones.',
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

        for ($i = 0; $i < 5; $i++) {
            $fallback = $defaults['items'][$i];
            $src = is_array($items[$i] ?? null) ? $items[$i] : [];

            $normalized[] = [
                'question' => (string)($src['question'] ?? $fallback['question']),
                'answer' => (string)($src['answer'] ?? $fallback['answer']),
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
                'nombre' => 'Preguntas frecuentes (FAQ)',
                'orden' => 10,
                'datos' => $payload,
            ]
        );
    }
}
