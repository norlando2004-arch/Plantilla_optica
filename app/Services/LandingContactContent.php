<?php

namespace App\Services;

use App\Models\BloqueContenido;

class LandingContactContent
{
    public const BLOCK_KEY = 'landing_contact';
    public const IMAGE_FIELD_KEY = 'image_url';

    public static function defaults(): array
    {
        return [
            'title' => 'Contacto',
            'subtitle' => 'Este bloque es visual (sin backend). Cuando estés listo, lo conectamos a tu panel y a tus rutas.',

            'whatsapp_label' => 'WhatsApp:',
            'whatsapp_value' => '+57 300 000 0000',
            'email_label' => 'Email:',
            'email_value' => 'contacto@optica.com',
            'hours_label' => 'Horario:',
            'hours_value' => 'Lun–Sáb 9:00–18:00',

            'channels' => [
                ['label' => 'Canal', 'title' => 'Asesoría', 'desc' => 'Ideal para elegir talla, estilo y tipo de lente.'],
                ['label' => 'Canal', 'title' => 'Cotización', 'desc' => 'Recibe un resumen claro de opciones y precio.'],
            ],

            'form_label' => '¿Qué necesitas?',
            'form_placeholder' => 'Ej: gafas para luz azul',
            'form_button_text' => 'Enviar',
            'form_note' => 'Sin envío real todavía: es un placeholder visual.',

            'image_url' => '',
            'image_alt' => 'Contacto',
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

        $channelsStored = is_array($stored['channels'] ?? null) ? $stored['channels'] : [];
        $channelsNormalized = [];
        for ($i = 0; $i < 2; $i++) {
            $fallback = $defaults['channels'][$i];
            $src = is_array($channelsStored[$i] ?? null) ? $channelsStored[$i] : [];
            $channelsNormalized[] = [
                'label' => (string)($src['label'] ?? $fallback['label']),
                'title' => (string)($src['title'] ?? $fallback['title']),
                'desc' => (string)($src['desc'] ?? $fallback['desc']),
            ];
        }

        $manualImageUrl = trim((string)($data['image_url'] ?? ''));
        $imageUrl = $uploadedImageUrl !== '' ? $uploadedImageUrl : $manualImageUrl;

        return [
            'title' => (string)($data['title'] ?? $defaults['title']),
            'subtitle' => (string)($data['subtitle'] ?? $defaults['subtitle']),

            'whatsapp_label' => (string)($data['whatsapp_label'] ?? $defaults['whatsapp_label']),
            'whatsapp_value' => (string)($data['whatsapp_value'] ?? $defaults['whatsapp_value']),
            'email_label' => (string)($data['email_label'] ?? $defaults['email_label']),
            'email_value' => (string)($data['email_value'] ?? $defaults['email_value']),
            'hours_label' => (string)($data['hours_label'] ?? $defaults['hours_label']),
            'hours_value' => (string)($data['hours_value'] ?? $defaults['hours_value']),

            'channels' => $channelsNormalized,

            'form_label' => (string)($data['form_label'] ?? $defaults['form_label']),
            'form_placeholder' => (string)($data['form_placeholder'] ?? $defaults['form_placeholder']),
            'form_button_text' => (string)($data['form_button_text'] ?? $defaults['form_button_text']),
            'form_note' => (string)($data['form_note'] ?? $defaults['form_note']),

            'image_url' => $imageUrl,
            'manual_image_url' => $manualImageUrl,
            'has_uploaded_image' => $uploadedImageUrl !== '',
            'image_alt' => (string)($data['image_alt'] ?? $defaults['image_alt']),
        ];
    }

    public static function upsert(array $data): BloqueContenido
    {
        $payload = array_merge(static::defaults(), $data);

        return BloqueContenido::query()->updateOrCreate(
            ['clave' => static::BLOCK_KEY],
            [
                'titulo' => 'Contacto (Landing)',
                'cuerpo' => null,
                'esta_activo' => true,
                'orden' => 13,
                'datos' => $payload,
            ]
        );
    }
}
