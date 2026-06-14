<?php

namespace App\Services;

use App\Models\BloqueContenido;

class LandingHowItWorksContent
{
    public const BLOCK_KEY = 'landing_how_it_works';
    public const IMAGE_FIELD_KEY = 'image_url';

    public static function defaults(): array
    {
        return [
            'title' => 'Cómo funciona',
            'subtitle' => 'Un flujo claro que luego puedes automatizar con tu panel admin.',
            'steps' => [
                ['title' => 'Elige una categoría', 'desc' => 'Formuladas, sol, luz azul o accesorios.'],
                ['title' => 'Define tu necesidad', 'desc' => 'Uso diario, pantalla, conducción o estilo.'],
                ['title' => 'Cotiza o agenda', 'desc' => 'Capturamos datos y conectamos con admin.'],
                ['title' => 'Entrega y soporte', 'desc' => 'Seguimiento, cambios y garantía.'],
            ],
            'cta_primary_text' => 'Empezar',
            'cta_primary_href' => '#categorias',
            'cta_secondary_text' => 'Hablar con asesor',
            'cta_secondary_href' => '#contacto',
            'image_url' => '',
            'image_alt' => 'Flujo de compra',
            'stat_0_label' => 'Tiempo estimado',
            'stat_0_value' => '3–5 minutos',
            'stat_1_label' => 'Resultado',
            'stat_1_value' => 'Cotización',
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

        $uploadedImageUrl = '';
        if ($row) {
            $uploadedAsset = $row->archivos()
                ->where('field_key', self::IMAGE_FIELD_KEY)
                ->orderBy('orden')
                ->orderBy('id')
                ->get()
                ->last();

            if ($uploadedAsset) {
                $uploadedImageUrl = (string) $uploadedAsset->publicUrl();
            }
        }

        $data = array_merge($defaults, array_intersect_key($stored, $defaults));

        $steps = is_array($stored['steps'] ?? null) ? $stored['steps'] : [];
        $normalized = [];

        for ($i = 0; $i < 4; $i++) {
            $fallback = $defaults['steps'][$i];
            $src = is_array($steps[$i] ?? null) ? $steps[$i] : [];

            $normalized[] = [
                'title' => (string)($src['title'] ?? $fallback['title']),
                'desc' => (string)($src['desc'] ?? $fallback['desc']),
            ];
        }

        $data['steps'] = $normalized;

        $manualImageUrl = trim((string) ($data['image_url'] ?? ''));
        $data['image_url'] = $uploadedImageUrl !== '' ? $uploadedImageUrl : $manualImageUrl;
        $data['manual_image_url'] = $manualImageUrl;
        $data['has_uploaded_image'] = $uploadedImageUrl !== '';

        return $data;
    }

    public static function upsert(array $data): BloqueContenido
    {
        $payload = array_merge(static::defaults(), $data);

        return BloqueContenido::query()->updateOrCreate(
            ['clave' => static::BLOCK_KEY],
            [
                'titulo' => 'Cómo funciona (Landing)',
                'cuerpo' => null,
                'esta_activo' => true,
                'orden' => 7,
                'datos' => $payload,
            ]
        );
    }
}
