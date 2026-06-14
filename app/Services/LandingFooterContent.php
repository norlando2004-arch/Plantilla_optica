<?php

namespace App\Services;

use App\Models\BloqueContenido;

class LandingFooterContent
{
    public const BLOCK_KEY = 'landing_footer';
    public const IMAGE_FIELD_KEY = 'image_url';
    public const ABOUT_PDF_FIELD_PREFIX = 'about_pdf_';

    public static function defaults(): array
    {
        return [
            'company_name' => 'Óptica',
            'tagline' => 'Landing profesional en Laravel + Tailwind, lista para conectar con panel admin.',
            'image_url' => '',
            'image_alt' => 'Imagen footer',
            'notice_title' => 'Aviso',
            'notice_text' => 'Este landing es UI de referencia. El contenido real se administra desde tu panel.',
            'services_title' => 'Servicios',
            'services_links' => [
                ['text' => 'Asesoría de montura', 'href' => '#servicios'],
                ['text' => 'Lentes formulados', 'href' => '#servicios'],
                ['text' => 'Luz azul', 'href' => '#servicios'],
                ['text' => 'Mantenimiento', 'href' => '#servicios'],
            ],
            'help_title' => 'Ayuda',
            'help_links' => [
                ['text' => 'Preguntas frecuentes', 'href' => '#faq'],
                ['text' => 'Contacto', 'href' => '#contacto'],
                ['text' => 'Cómo funciona', 'href' => '#como-funciona'],
                ['text' => 'Ubicación', 'href' => '#ubicacion'],
                ['text' => 'Newsletter', 'href' => '#newsletter'],
            ],
            'legal_title' => 'Legal',
            'legal_links' => [
                ['text' => 'Términos', 'href' => '#'],
                ['text' => 'Privacidad', 'href' => '#'],
                ['text' => 'Garantías', 'href' => '#'],
                ['text' => 'Envíos', 'href' => '#'],
            ],
            'contact_label' => 'Atención:',
            'contact_phone' => '+57 300 000 0000',
            'contact_email' => 'contacto@optica.com',
            'about_links' => [
                ['text' => 'Quiénes somos', 'href' => '#'],
                ['text' => 'Términos y condiciones', 'href' => '#'],
                ['text' => 'Política de privacidad', 'href' => '#'],
            ],
            'social_links' => [
                ['platform' => 'Facebook', 'icon' => 'f', 'href' => '', 'color' => 'bg-[#3b82f6]'],
                ['platform' => 'Instagram', 'icon' => 'ig', 'href' => '', 'color' => 'bg-gradient-to-br from-[#f59e0b] via-[#ec4899] to-[#8b5cf6]'],
                ['platform' => 'TikTok', 'icon' => '♪', 'href' => '', 'color' => 'bg-[#111111]'],
            ],
            'faq' => [
                ['question' => 'Cómo hacer un pedido?', 'answer' => ''],
                ['question' => '¿Cómo encuentro la montura de la talla perfecta?', 'answer' => ''],
                ['question' => 'Cómo leer una formula?', 'answer' => ''],
                ['question' => 'Qué es DP (Distancia Pupilar)?', 'answer' => ''],
                ['question' => 'Cómo elegir lentes?', 'answer' => ''],
                ['question' => 'Envío y seguimiento', 'answer' => ''],
                ['question' => 'Cuál es el tiempo de entrega?', 'answer' => ''],
                ['question' => 'Devolución o cambio', 'answer' => ''],
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

        $uploadedImageUrl = '';
        $aboutPdfUrls = [];
        $aboutPdfNames = [];
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

            for ($i = 0; $i < 3; $i++) {
                $fieldKey = self::ABOUT_PDF_FIELD_PREFIX . $i;
                $pdfAsset = $row->archivos()
                    ->where('field_key', $fieldKey)
                    ->orderBy('orden')
                    ->orderBy('id')
                    ->get()
                    ->last();

                $aboutPdfUrls[$i] = $pdfAsset ? (string) $pdfAsset->publicUrl() : '';
                $aboutPdfNames[$i] = $pdfAsset ? (string) ($pdfAsset->original_name ?? '') : '';
            }
        }

        $data = array_merge($defaults, array_intersect_key($stored, $defaults));

        $normalizeLinks = function (array $links, array $fallbackLinks): array {
            $out = [];
            $count = count($fallbackLinks);
            for ($i = 0; $i < $count; $i++) {
                $fallback = $fallbackLinks[$i];
                $it = is_array($links[$i] ?? null) ? $links[$i] : [];
                $out[] = [
                    'text' => (string) ($it['text'] ?? $fallback['text'] ?? ''),
                    'href' => (string) ($it['href'] ?? $fallback['href'] ?? '#'),
                ];
            }
            return $out;
        };

        $manualImageUrl = trim((string) ($data['image_url'] ?? $defaults['image_url']));

        $socialDefaults = is_array($defaults['social_links'] ?? null) ? $defaults['social_links'] : [];
        $socialStored = is_array($data['social_links'] ?? null) ? $data['social_links'] : [];
        $socialLinks = [];
        $socialCount = count($socialDefaults);
        for ($i = 0; $i < $socialCount; $i++) {
            $fallback = is_array($socialDefaults[$i] ?? null) ? $socialDefaults[$i] : [];
            $item = is_array($socialStored[$i] ?? null) ? $socialStored[$i] : [];
            $socialLinks[] = [
                'platform' => (string) ($item['platform'] ?? $fallback['platform'] ?? ''),
                'icon' => (string) ($item['icon'] ?? $fallback['icon'] ?? ''),
                'color' => (string) ($item['color'] ?? $fallback['color'] ?? ''),
                'href' => static::normalizeSocialUrl((string) ($item['href'] ?? $fallback['href'] ?? '#')),
            ];
        }

        return [
            'company_name' => (string) ($data['company_name'] ?? $defaults['company_name']),
            'tagline' => (string) ($data['tagline'] ?? $defaults['tagline']),
            'image_url' => $uploadedImageUrl !== '' ? $uploadedImageUrl : $manualImageUrl,
            'manual_image_url' => $manualImageUrl,
            'has_uploaded_image' => $uploadedImageUrl !== '',
            'image_alt' => (string) ($data['image_alt'] ?? $defaults['image_alt']),
            'notice_title' => (string) ($data['notice_title'] ?? $defaults['notice_title']),
            'notice_text' => (string) ($data['notice_text'] ?? $defaults['notice_text']),
            'services_title' => (string) ($data['services_title'] ?? $defaults['services_title']),
            'services_links' => $normalizeLinks(
                is_array($data['services_links'] ?? null) ? $data['services_links'] : [],
                $defaults['services_links']
            ),
            'help_title' => (string) ($data['help_title'] ?? $defaults['help_title']),
            'help_links' => $normalizeLinks(
                is_array($data['help_links'] ?? null) ? $data['help_links'] : [],
                $defaults['help_links']
            ),
            'legal_title' => (string) ($data['legal_title'] ?? $defaults['legal_title']),
            'legal_links' => $normalizeLinks(
                is_array($data['legal_links'] ?? null) ? $data['legal_links'] : [],
                $defaults['legal_links']
            ),
            'contact_label' => (string) ($data['contact_label'] ?? $defaults['contact_label']),
            'contact_phone' => (string) ($data['contact_phone'] ?? $defaults['contact_phone']),
            'contact_email' => (string) ($data['contact_email'] ?? $defaults['contact_email']),
            'about_links' => $normalizeLinks(
                is_array($data['about_links'] ?? null) ? $data['about_links'] : [],
                $defaults['about_links']
            ),
            'about_pdf_urls' => $aboutPdfUrls,
            'about_pdf_names' => $aboutPdfNames,
            'social_links' => $socialLinks,
            'faq' => is_array($data['faq'] ?? null) ? $data['faq'] : $defaults['faq'],
        ];
    }

    private static function normalizeSocialUrl(string $href): string
    {
        $href = trim($href);
        if ($href === '' || $href === '#') {
            return '';
        }

        $lower = mb_strtolower($href);
        if (str_starts_with($lower, 'javascript:') || str_starts_with($lower, 'data:')) {
            return '#';
        }

        if (str_starts_with($href, '#') || str_starts_with($href, '/')) {
            return $href;
        }

        if (preg_match('/^[a-z][a-z0-9+\-.]*:\/\//i', $href) === 1) {
            return $href;
        }

        return 'https://' . ltrim($href, '/');
    }

    public static function upsert(array $data): BloqueContenido
    {
        $payload = array_merge(static::defaults(), $data);

        return BloqueContenido::query()->updateOrCreate(
            ['clave' => static::BLOCK_KEY],
            [
                'titulo' => 'Footer (Landing)',
                'cuerpo' => null,
                'esta_activo' => true,
                'orden' => 14,
                'datos' => $payload,
            ]
        );
    }
}
