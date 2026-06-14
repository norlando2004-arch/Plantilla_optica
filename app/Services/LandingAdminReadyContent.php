<?php

namespace App\Services;

use App\Models\BloqueContenido;

class LandingAdminReadyContent
{
    public const BLOCK_KEY = 'landing_admin_ready';

    public static function defaults(): array
    {
        return [
            'enabled' => true,
            'title' => 'Listo para el panel admin',
            'text' => 'Las imágenes se repiten a propósito: es el placeholder único que luego vas a reemplazar con contenido real.',
            'modules' => [
                ['label' => 'Módulo', 'title' => 'Banners', 'desc' => 'Hero y promos editables.'],
                ['label' => 'Módulo', 'title' => 'Categorías', 'desc' => 'Tarjetas conectadas a colecciones.'],
            ],
            'image_url' => '',
            'image_alt' => 'Imagen de ejemplo',
            'blocks' => [
                ['label' => 'Bloque', 'title' => 'CTA'],
                ['label' => 'Bloque', 'title' => 'Grid'],
                ['label' => 'Bloque', 'title' => 'Footer'],
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
        $data['enabled'] = (bool) ($stored['enabled'] ?? $defaults['enabled']);

        $modules = is_array($stored['modules'] ?? null) ? $stored['modules'] : [];
        $normalizedModules = [];
        for ($i = 0; $i < 2; $i++) {
            $fallback = $defaults['modules'][$i];
            $src = is_array($modules[$i] ?? null) ? $modules[$i] : [];
            $normalizedModules[] = [
                'label' => (string)($src['label'] ?? $fallback['label']),
                'title' => (string)($src['title'] ?? $fallback['title']),
                'desc' => (string)($src['desc'] ?? $fallback['desc']),
            ];
        }
        $data['modules'] = $normalizedModules;

        $blocks = is_array($stored['blocks'] ?? null) ? $stored['blocks'] : [];
        $normalizedBlocks = [];
        for ($i = 0; $i < 3; $i++) {
            $fallback = $defaults['blocks'][$i];
            $src = is_array($blocks[$i] ?? null) ? $blocks[$i] : [];
            $normalizedBlocks[] = [
                'label' => (string)($src['label'] ?? $fallback['label']),
                'title' => (string)($src['title'] ?? $fallback['title']),
            ];
        }
        $data['blocks'] = $normalizedBlocks;

        return $data;
    }

    public static function upsert(array $data): void
    {
        $payload = array_merge(static::defaults(), $data);
        $payload['enabled'] = (bool) ($data['enabled'] ?? $payload['enabled']);

        BloqueContenido::query()->updateOrCreate(
            ['clave' => static::BLOCK_KEY],
            [
                'nombre' => 'Listo para el panel admin',
                'orden' => 9,
                'datos' => $payload,
            ]
        );
    }
}
