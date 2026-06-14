<?php

namespace App\Services;

use App\Models\BloqueContenido;

class LandingLocationContent
{
    public const BLOCK_KEY = 'landing_location';

    public static function defaults(): array
    {
        return [
            'title' => 'Ubicación',
            'subtitle' => 'Configura cada sede usando coordenadas para mostrar su mapa.',

            'locations' => [
                [
                    'venue_name' => 'Sede Bogotá',
                    'address' => 'Bogotá',
                    'description' => '',
                    'hours' => '',
                    'cta_primary_text' => 'Agendar',
                    'cta_primary_href' => '#contacto',
                    'cta_secondary_text' => 'Cómo llegar',
                    'lat' => 4.7110,
                    'lng' => -74.0721,
                    'zoom' => 15,
                    'map_title' => 'Mapa Bogotá',
                    'map_caption_title' => '',
                    'map_caption_subtitle' => '',
                    'image_url' => '/images/naratodo.png',
                ],
                [
                    'venue_name' => 'Sede Medellin',
                    'address' => 'Medellin',
                    'description' => '',
                    'hours' => '',
                    'cta_primary_text' => 'Agendar',
                    'cta_primary_href' => '#contacto',
                    'cta_secondary_text' => 'Cómo llegar',
                    'lat' => 6.2442,
                    'lng' => -75.5812,
                    'zoom' => 15,
                    'map_title' => 'Mapa Medellin',
                    'map_caption_title' => '',
                    'map_caption_subtitle' => '',
                    'image_url' => '/images/naratodo.png',
                ],
                [
                    'venue_name' => 'Sede Cali',
                    'address' => 'Cali',
                    'description' => '',
                    'hours' => '',
                    'cta_primary_text' => 'Agendar',
                    'cta_primary_href' => '#contacto',
                    'cta_secondary_text' => 'Cómo llegar',
                    'lat' => 3.4516,
                    'lng' => -76.5320,
                    'zoom' => 15,
                    'map_title' => 'Mapa Cali',
                    'map_caption_title' => '',
                    'map_caption_subtitle' => '',
                    'image_url' => '/images/naratodo.png',
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

        $data = [
            'title' => (string) ($stored['title'] ?? $defaults['title']),
            'subtitle' => (string) ($stored['subtitle'] ?? $defaults['subtitle']),
            'locations' => [],
        ];

        $rawLocations = $stored['locations'] ?? null;
        if (is_array($rawLocations) && count($rawLocations) > 0) {
            $data['locations'] = $rawLocations;
        } else {
            // Compatibilidad con el esquema antiguo (una sola sede en el root).
            $data['locations'] = [[
                'venue_name' => (string) ($stored['venue_name'] ?? $defaults['locations'][0]['venue_name']),
                'address' => (string) ($stored['address'] ?? $defaults['locations'][0]['address']),
                'description' => (string) ($stored['description'] ?? $defaults['locations'][0]['description']),
                'hours' => (string) ($stored['hours'] ?? $defaults['locations'][0]['hours']),
                'cta_primary_text' => (string) ($stored['cta_primary_text'] ?? $defaults['locations'][0]['cta_primary_text']),
                'cta_primary_href' => (string) ($stored['cta_primary_href'] ?? $defaults['locations'][0]['cta_primary_href']),
                'cta_secondary_text' => (string) ($stored['cta_secondary_text'] ?? $defaults['locations'][0]['cta_secondary_text']),
                'lat' => is_numeric($stored['lat'] ?? null) ? (float) $stored['lat'] : (float) $defaults['locations'][0]['lat'],
                'lng' => is_numeric($stored['lng'] ?? null) ? (float) $stored['lng'] : (float) $defaults['locations'][0]['lng'],
                'zoom' => is_numeric($stored['zoom'] ?? null) ? (int) $stored['zoom'] : (int) $defaults['locations'][0]['zoom'],
                'map_title' => (string) ($stored['map_title'] ?? $defaults['locations'][0]['map_title']),
                'map_caption_title' => (string) ($stored['map_caption_title'] ?? $defaults['locations'][0]['map_caption_title']),
                'map_caption_subtitle' => (string) ($stored['map_caption_subtitle'] ?? $defaults['locations'][0]['map_caption_subtitle']),
            ]];
        }

        $normalized = [];
        foreach ($data['locations'] as $loc) {
            if (!is_array($loc)) continue;

            $lat = is_numeric($loc['lat'] ?? null) ? (float) $loc['lat'] : (float) $defaults['locations'][0]['lat'];
            $lng = is_numeric($loc['lng'] ?? null) ? (float) $loc['lng'] : (float) $defaults['locations'][0]['lng'];
            $zoom = is_numeric($loc['zoom'] ?? null) ? (int) $loc['zoom'] : (int) $defaults['locations'][0]['zoom'];

            $lat = max(-90, min(90, $lat));
            $lng = max(-180, min(180, $lng));
            $zoom = max(1, min(20, $zoom));

            $normalized[] = [
                'venue_name' => (string) ($loc['venue_name'] ?? $defaults['locations'][0]['venue_name']),
                'address' => (string) ($loc['address'] ?? $defaults['locations'][0]['address']),
                'description' => (string) ($loc['description'] ?? $defaults['locations'][0]['description']),
                'hours' => (string) ($loc['hours'] ?? $defaults['locations'][0]['hours']),
                'cta_primary_text' => (string) ($loc['cta_primary_text'] ?? $defaults['locations'][0]['cta_primary_text']),
                'cta_primary_href' => (string) ($loc['cta_primary_href'] ?? $defaults['locations'][0]['cta_primary_href']),
                'cta_secondary_text' => (string) ($loc['cta_secondary_text'] ?? $defaults['locations'][0]['cta_secondary_text']),
                'lat' => $lat,
                'lng' => $lng,
                'zoom' => $zoom,
                'map_title' => (string) ($loc['map_title'] ?? $defaults['locations'][0]['map_title']),
                'map_caption_title' => (string) ($loc['map_caption_title'] ?? $defaults['locations'][0]['map_caption_title']),
                'map_caption_subtitle' => (string) ($loc['map_caption_subtitle'] ?? $defaults['locations'][0]['map_caption_subtitle']),
                'image_url' => (string) ($loc['image_url'] ?? $defaults['locations'][0]['image_url']),
            ];
        }

        if (count($normalized) === 0) {
            $normalized = $defaults['locations'];
        }

        // Garantiza espacios mínimos para Bogotá, Medellin y Cali en configuración.
        while (count($normalized) < count($defaults['locations'])) {
            $normalized[] = $defaults['locations'][count($normalized)];
        }

        if ($row) {
            foreach ($normalized as $idx => $loc) {
                $uploaded = $row->archivos()
                    ->where('field_key', 'location_image_'.$idx)
                    ->orderByDesc('orden')
                    ->orderByDesc('id')
                    ->first();

                if ($uploaded) {
                    $normalized[$idx]['image_url'] = $uploaded->publicUrl();
                }
            }
        }

        $data['locations'] = $normalized;
        return $data;
    }

    public static function upsert(array $data): void
    {
        $defaults = static::defaults();

        $payload = [
            'title' => (string) ($data['title'] ?? $defaults['title']),
            'subtitle' => (string) ($data['subtitle'] ?? $defaults['subtitle']),
            'locations' => is_array($data['locations'] ?? null) ? $data['locations'] : $defaults['locations'],
        ];

        BloqueContenido::query()->updateOrCreate(
            ['clave' => static::BLOCK_KEY],
            [
                'nombre' => 'Ubicación (Mapa)',
                'orden' => 11,
                'datos' => $payload,
            ]
        );
    }
}
