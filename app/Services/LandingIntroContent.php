<?php

namespace App\Services;

use App\Models\BloqueContenido;

class LandingIntroContent
{
    public const BLOCK_KEY = 'landing_intro';
    public const IMAGE_FIELD_KEY = 'image_urls';

    public static function defaults(): array
    {
        return [
            'badge' => 'Monturas + Lentes • Estilo y comodidad',
            'title_prefix' => 'Encuentra tus',
            'title_highlight' => 'gafas perfectas',
            'title_suffix' => 'sin complicarte',
            'description' => 'Una experiencia clara: elige tu estilo, define tu necesidad y deja listo todo para conectar con tu panel admin.',
            'cta_primary_text' => 'Explorar categorías',
            'cta_primary_href' => '#categorias',
            'cta_secondary_text' => 'Ver beneficios',
            'cta_secondary_href' => '#beneficios',
            'stats' => [
                ['label' => 'Entrega', 'value' => 'Rápida'],
                ['label' => 'Garantía', 'value' => 'Incluida'],
                ['label' => 'Soporte', 'value' => 'Asesoría'],
            ],
            'cards' => [
                [
                    'eyebrow' => 'Recomendado',
                    'title' => 'Compra guiada',
                    'desc' => 'Flujo claro: categoría → elección → contacto.',
                ],
                [
                    'eyebrow' => 'Para tu admin',
                    'title' => 'Contenido editable',
                    'desc' => 'Banners, cards y textos listos para mapear.',
                ],
            ],
            'image_alt' => 'Imagen principal de ejemplo',
            'image_urls' => [],
            'mini_cards' => [
                ['eyebrow' => 'Top venta', 'title' => 'Gafas para diario'],
                ['eyebrow' => 'Novedad', 'title' => 'Filtro luz azul'],
            ],
            'image_rotate_ms' => 4200,
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

        $manualImageUrls = collect(is_array($data['image_urls'] ?? null) ? $data['image_urls'] : [])
            ->map(fn ($url) => trim((string) $url))
            ->filter(fn ($url) => $url !== '')
            ->values()
            ->all();

        $uploadedImageUrls = $block
            ? $block->archivos()
                ->where('field_key', self::IMAGE_FIELD_KEY)
                ->orderBy('orden')
                ->orderBy('id')
                ->get()
                ->map(fn ($asset) => $asset->publicUrl())
                ->all()
            : [];

        // Shallow merge is enough because we store full arrays for stats/cards.
        $merged = array_replace($defaults, array_filter($data, fn ($v) => $v !== null));
        $merged['manual_image_urls'] = $manualImageUrls;
        $merged['image_urls'] = array_values(array_filter([
            ...$uploadedImageUrls,
            ...$manualImageUrls,
        ], fn ($url) => trim((string) $url) !== ''));
        $merged['uploaded_images_count'] = count($uploadedImageUrls);

        return $merged;
    }

    public static function upsert(array $data): BloqueContenido
    {
        return BloqueContenido::query()->updateOrCreate(
            ['clave' => self::BLOCK_KEY],
            [
                'titulo' => 'Landing: bloque principal',
                'cuerpo' => null,
                'datos' => $data,
                'esta_activo' => true,
                'orden' => 0,
            ]
        );
    }
}
