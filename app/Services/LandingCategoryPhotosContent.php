<?php

namespace App\Services;

use App\Models\BloqueContenido;

class LandingCategoryPhotosContent
{
    public const BLOCK_KEY = 'landing_category_photos';
    public const FIELD_NINOS = 'ninos_image';
    public const FIELD_MUJERES = 'mujeres_image';
    public const FIELD_HOMBRES = 'hombres_image';

    public static function defaults(): array
    {
        return [
            'ninos_image' => '/images/Niños.png',
            'mujeres_image' => '/images/Mujer.png',
            'hombres_image' => '/images/Hombre.png',
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

        $uploadedNinos = $block?->archivos()
            ->where('field_key', self::FIELD_NINOS)
            ->orderByDesc('orden')
            ->orderByDesc('id')
            ->first();
        $uploadedMujeres = $block?->archivos()
            ->where('field_key', self::FIELD_MUJERES)
            ->orderByDesc('orden')
            ->orderByDesc('id')
            ->first();
        $uploadedHombres = $block?->archivos()
            ->where('field_key', self::FIELD_HOMBRES)
            ->orderByDesc('orden')
            ->orderByDesc('id')
            ->first();

        $merged['ninos_image'] = $uploadedNinos ? $uploadedNinos->publicUrl() : (string) ($merged['ninos_image'] ?? $defaults['ninos_image']);
        $merged['mujeres_image'] = $uploadedMujeres ? $uploadedMujeres->publicUrl() : (string) ($merged['mujeres_image'] ?? $defaults['mujeres_image']);
        $merged['hombres_image'] = $uploadedHombres ? $uploadedHombres->publicUrl() : (string) ($merged['hombres_image'] ?? $defaults['hombres_image']);

        $merged['ninos_uploaded_name'] = $uploadedNinos?->original_name;
        $merged['mujeres_uploaded_name'] = $uploadedMujeres?->original_name;
        $merged['hombres_uploaded_name'] = $uploadedHombres?->original_name;
        $merged['has_uploaded_ninos'] = !is_null($uploadedNinos);
        $merged['has_uploaded_mujeres'] = !is_null($uploadedMujeres);
        $merged['has_uploaded_hombres'] = !is_null($uploadedHombres);

        return $merged;
    }

    public static function upsert(array $data): void
    {
        BloqueContenido::query()->updateOrCreate(
            ['clave' => self::BLOCK_KEY],
            [
                'titulo' => 'Landing: fotos de categorias',
                'cuerpo' => null,
                'datos' => [],
                'esta_activo' => true,
                'orden' => 3,
            ]
        );
    }
}
