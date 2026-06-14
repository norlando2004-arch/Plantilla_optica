<?php

namespace App\Services;

use App\Models\BloqueContenido;

class GafasFormulasContent
{
    public const BLOCK_KEY = 'gafas_formulas_images';

    public const DATA_MONO_DESCRIPTION = 'mono_description';
    public const DATA_BIFOCAL_DESCRIPTION = 'bifocal_description';
    public const DATA_OCUPACIONAL_DESCRIPTION = 'ocupacional_description';
    public const DATA_PROGRESIVO_DESCRIPTION = 'progresivo_description';
    public const DATA_BIFOCAL_BLANCO_DESCRIPTION = 'bifocal_blanco_description';
    public const DATA_BIFOCAL_AR_AZUL_DESCRIPTION = 'bifocal_ar_azul_description';
    public const DATA_BIFOCAL_AR_VERDE_DESCRIPTION = 'bifocal_ar_verde_description';
    public const DATA_BIFOCAL_AR_AZUL_FOTO_BLUE_DESCRIPTION = 'bifocal_ar_azul_foto_blue_description';
    public const DATA_BIFOCAL_AR_VERDE_FOTO_BLUE_DESCRIPTION = 'bifocal_ar_verde_foto_blue_description';
    public const DATA_BIFOCAL_159_TRANSITIONS_DESCRIPTION = 'bifocal_159_transitions_description';
    public const DATA_BIFOCAL_159_BLANCO_DESCRIPTION = 'bifocal_159_blanco_description';
    public const DATA_BIFOCAL_159_AR_VERDE_DESCRIPTION = 'bifocal_159_ar_verde_description';
    public const DATA_BIFOCAL_159_BLUE_BLOCK_DESCRIPTION = 'bifocal_159_blue_block_description';
    public const DATA_BIFOCAL_159_FOTO_AR_BLUE_DESCRIPTION = 'bifocal_159_foto_ar_blue_description';
    public const DATA_BIFOCAL_159_AR_VERDE_FOTO_BLUE_DESCRIPTION = 'bifocal_159_ar_verde_foto_blue_description';

    public const FIELD_MONO = 'mono';
    public const FIELD_BIFOCAL = 'bifocal';
    public const FIELD_PROGRESIVO_BASIC = 'progresivo_basic';
    public const FIELD_PROGRESIVO_PREMIUM = 'progresivo_premium';
    public const FIELD_OCUPACIONAL = 'ocupacional';
    public const FIELD_DEFAULT_LENTE = 'default_lente';
    public const FIELD_NARA_BASICA = 'nara_basica';
    public const FIELD_NARA_MEDIA = 'nara_media';
    public const FIELD_NARA_ALTA = 'nara_alta';
    public const FIELD_NARA_PREMIUM = 'nara_premium';

    // small icon per module
    public const FIELD_MONO_ICON = 'mono_icon';
    public const FIELD_BIFOCAL_ICON = 'bifocal_icon';
    public const FIELD_OCUPACIONAL_ICON = 'ocupacional_icon';
    public const FIELD_PROGRESIVO_ICON = 'progresivo_icon';

    // bifocal variant icons
    public const FIELD_BIFOCAL_BLANCO = 'bifocal_blanco';
    public const FIELD_BIFOCAL_AR_AZUL = 'bifocal_ar_azul';
    public const FIELD_BIFOCAL_AR_VERDE = 'bifocal_ar_verde';
    public const FIELD_BIFOCAL_AR_AZUL_FOTO_BLUE = 'bifocal_ar_azul_foto_blue';
    public const FIELD_BIFOCAL_AR_VERDE_FOTO_BLUE = 'bifocal_ar_verde_foto_blue';
    public const FIELD_BIFOCAL_159_TRANSITIONS = 'bifocal_159_transitions';
    public const FIELD_BIFOCAL_159_BLANCO = 'bifocal_159_blanco';
    public const FIELD_BIFOCAL_159_AR_VERDE = 'bifocal_159_ar_verde';
    public const FIELD_BIFOCAL_159_BLUE_BLOCK = 'bifocal_159_blue_block';
    public const FIELD_BIFOCAL_159_FOTO_AR_BLUE = 'bifocal_159_foto_ar_blue';
    public const FIELD_BIFOCAL_159_AR_VERDE_FOTO_BLUE = 'bifocal_159_ar_verde_foto_blue';

    public static function defaults(): array
    {
        return [
            'mono_url' => '/images/lente.png',
            'bifocal_url' => '/images/lente.png',
            'progresivo_basic_url' => '/image/narabasico.png',
            'progresivo_premium_url' => '/image/naraPREMIUM.png',
            'nara_basica' => '/image/narabasico.png',
            'nara_media' => '/image/naramedia.png',
            'nara_alta' => '/image/naraAlta.png',
            'nara_premium' => '/image/naraPREMIUM.png',
            'ocupacional_url' => '/images/lente.png',
            'default_lente_url' => '/images/lente.png',
            'mono_icon_url' => '/images/lente.png',
            'bifocal_icon_url' => '/images/lente.png',
            'ocupacional_icon_url' => '/images/lente.png',
            'progresivo_icon_url' => '/images/lente.png',
            'default_lente_uploaded_name' => null,

            // bifocal defaults (icons)
            'bifocal_blanco' => '/images/lente.png',
            'bifocal_ar_azul' => '/images/lente.png',
            'bifocal_ar_verde' => '/images/lente.png',
            'bifocal_ar_azul_foto_blue' => '/images/lente.png',
            'bifocal_ar_verde_foto_blue' => '/images/lente.png',
            'bifocal_159_transitions' => '/images/lente.png',
            'bifocal_159_blanco' => '/images/lente.png',
            'bifocal_159_ar_verde' => '/images/lente.png',
            'bifocal_159_blue_block' => '/images/lente.png',
            'bifocal_159_foto_ar_blue' => '/images/lente.png',
            'bifocal_159_ar_verde_foto_blue' => '/images/lente.png',
            // nara defaults
            'nara_basica' => '/image/narabasico.png',
            'nara_media' => '/image/naramedia.png',
            'nara_alta' => '/image/naraAlta.png',
            'nara_premium' => '/image/naraPREMIUM.png',
            self::DATA_MONO_DESCRIPTION => 'Son lentes con una sola fórmula en toda la superficie, diseñados para ver bien a una sola distancia: de lejos o de cerca. Son la opción más común, ideales para el uso diario, ofreciendo una visión clara, cómoda y sin complicaciones.',
            self::DATA_BIFOCAL_DESCRIPTION => 'Son lentes que tienen dos graduaciones en un mismo lente, una para ver de lejos y otra para ver de cerca. La parte superior se usa para visión lejana y en la parte inferior hay un pequeño segmento visible que permite leer o ver de cerca. Son una opción práctica para quienes necesitan ambas correcciones, aunque el cambio entre distancias no es gradual.',
            self::DATA_OCUPACIONAL_DESCRIPTION => 'Son lentes diseñados para actividades específicas como trabajar en computador, oficina o lectura prolongada. Tienen una graduación que permite ver con mayor comodidad a distancias intermedias y cercanas, reduciendo el esfuerzo visual durante largas jornadas. No están pensados para ver de lejos, sino para brindar descanso y enfoque en el trabajo diario.',
            self::DATA_PROGRESIVO_DESCRIPTION => 'Son lentes que tienen varias graduaciones en un mismo lente, lo que permite ver bien a diferentes distancias sin necesidad de cambiar de gafas. En la parte superior sirven para ver de lejos, en la zona media para distancias intermedias como el computador, y en la parte inferior para leer o ver de cerca. No tienen líneas visibles y la transición entre cada distancia es suave y natural.',
            self::DATA_BIFOCAL_BLANCO_DESCRIPTION => 'Son lentes con una sola fórmula en toda la superficie, diseñados para ver bien a una sola distancia: de lejos o de cerca. Son la opción más común, ideales para el uso diario, ofreciendo una visión clara, cómoda y sin complicaciones.',
            self::DATA_BIFOCAL_AR_AZUL_DESCRIPTION => 'Son lentes con tratamiento antirreflejo que incluye un filtro de luz azul, ideal para quienes pasan mucho tiempo frente a pantallas. Reducen reflejos molestos y ayudan a disminuir la fatiga visual, brindando mayor comodidad durante el día.',
            self::DATA_BIFOCAL_AR_VERDE_DESCRIPTION => 'Son lentes con tratamiento antirreflejo que mejora la claridad visual y la transparencia del lente, reduciendo reflejos de la luz natural y artificial. Son ideales para una visión más nítida y una mejor apariencia estética.',
            self::DATA_BIFOCAL_AR_AZUL_FOTO_BLUE_DESCRIPTION => 'Son lentes completos que combinan varias tecnologías en uno solo. Reducen reflejos, filtran la luz azul de pantallas y además se oscurecen con el sol, adaptándose automáticamente a la luz. Ofrecen protección total, comodidad y practicidad en cualquier ambiente.',
            self::DATA_BIFOCAL_AR_VERDE_FOTO_BLUE_DESCRIPTION => 'Son lentes de alta calidad que combinan antirreflejo para mayor claridad visual, filtro de luz azul y tecnología fotocromática. Permiten ver con mayor nitidez, proteger los ojos y adaptarse a diferentes condiciones de luz. Son ideales para quienes buscan rendimiento, estética y protección en un solo lente.',
            self::DATA_BIFOCAL_159_TRANSITIONS_DESCRIPTION => 'Son lentes de marca que se adaptan automáticamente a la luz. En interiores son completamente transparentes y al exponerse al sol se oscurecen, funcionando como gafas de sol. Están fabricados con tecnología avanzada que reacciona a los rayos UV, brindando protección, comodidad y practicidad en un solo lente.',
            self::DATA_BIFOCAL_159_BLANCO_DESCRIPTION => 'Son lentes con una sola fórmula en toda la superficie, diseñados para ver bien a una sola distancia: de lejos o de cerca. Son la opción más común, ideales para el uso diario, ofreciendo una visión clara, cómoda y sin complicaciones.',
            self::DATA_BIFOCAL_159_AR_VERDE_DESCRIPTION => 'Son lentes con tratamiento antirreflejo que mejora la claridad visual y la transparencia del lente, reduciendo reflejos de la luz natural y artificial. Son ideales para una visión más nítida y una mejor apariencia estética.',
            self::DATA_BIFOCAL_159_BLUE_BLOCK_DESCRIPTION => 'Son lentes con tratamiento antirreflejo que incluye un filtro de luz azul, ideal para quienes pasan mucho tiempo frente a pantallas. Reducen reflejos molestos y ayudan a disminuir la fatiga visual, brindando mayor comodidad durante el día.',
            self::DATA_BIFOCAL_159_FOTO_AR_BLUE_DESCRIPTION => 'Son lentes completos que combinan varias tecnologías en uno solo. Reducen reflejos, filtran la luz azul de pantallas y además se oscurecen con el sol, adaptándose automáticamente a la luz. Ofrecen protección total, comodidad y practicidad en cualquier ambiente.',
            self::DATA_BIFOCAL_159_AR_VERDE_FOTO_BLUE_DESCRIPTION => 'Son lentes de alta calidad que combinan antirreflejo para mayor claridad visual, filtro de luz azul y tecnología fotocromática. Permiten ver con mayor nitidez, proteger los ojos y adaptarse a diferentes condiciones de luz. Son ideales para quienes buscan rendimiento, estética y protección en un solo lente.',
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

        $uploaded = function (?\Illuminate\Database\Eloquent\Model $block, string $field) {
            if (! $block) return null;
            $asset = $block->archivos()->where('field_key', $field)->orderByDesc('orden')->orderByDesc('id')->first();
            return $asset ? $asset->publicUrl() : null;
        };

        $data = is_array($block?->datos) ? $block->datos : [];

        return [
            'mono' => $uploaded($block, self::FIELD_MONO) ?: $defaults['mono_url'],
            'mono_uploaded_name' => optional($block?->archivos()->where('field_key', self::FIELD_MONO)->orderByDesc('orden')->orderByDesc('id')->first())->original_name,

            'bifocal' => $uploaded($block, self::FIELD_BIFOCAL) ?: $defaults['bifocal_url'],
            'bifocal_uploaded_name' => optional($block?->archivos()->where('field_key', self::FIELD_BIFOCAL)->orderByDesc('orden')->orderByDesc('id')->first())->original_name,

            'progresivo_basic' => $uploaded($block, self::FIELD_PROGRESIVO_BASIC) ?: $defaults['progresivo_basic_url'],
            'progresivo_basic_uploaded_name' => optional($block?->archivos()->where('field_key', self::FIELD_PROGRESIVO_BASIC)->orderByDesc('orden')->orderByDesc('id')->first())->original_name,
            'progresivo_premium' => $uploaded($block, self::FIELD_PROGRESIVO_PREMIUM) ?: $defaults['progresivo_premium_url'],
            'progresivo_premium_uploaded_name' => optional($block?->archivos()->where('field_key', self::FIELD_PROGRESIVO_PREMIUM)->orderByDesc('orden')->orderByDesc('id')->first())->original_name,

            'ocupacional' => $uploaded($block, self::FIELD_OCUPACIONAL) ?: $defaults['ocupacional_url'],
            'ocupacional_uploaded_name' => optional($block?->archivos()->where('field_key', self::FIELD_OCUPACIONAL)->orderByDesc('orden')->orderByDesc('id')->first())->original_name,

                'default_lente' => $uploaded($block, self::FIELD_DEFAULT_LENTE) ?: $defaults['default_lente_url'],
                'default_lente_uploaded_name' => optional($block?->archivos()->where('field_key', self::FIELD_DEFAULT_LENTE)->orderByDesc('orden')->orderByDesc('id')->first())->original_name,

            // icons
            'mono_icon' => $uploaded($block, self::FIELD_MONO_ICON) ?: $defaults['mono_icon_url'],
            'bifocal_icon' => $uploaded($block, self::FIELD_BIFOCAL_ICON) ?: $defaults['bifocal_icon_url'],
            'ocupacional_icon' => $uploaded($block, self::FIELD_OCUPACIONAL_ICON) ?: $defaults['ocupacional_icon_url'],
            'progresivo_icon' => $uploaded($block, self::FIELD_PROGRESIVO_ICON) ?: $defaults['progresivo_icon_url'],

                // bifocal icons
                'bifocal_blanco_icon' => $uploaded($block, self::FIELD_BIFOCAL_BLANCO) ?: $defaults['bifocal_blanco'],
                'bifocal_ar_azul_icon' => $uploaded($block, self::FIELD_BIFOCAL_AR_AZUL) ?: $defaults['bifocal_ar_azul'],
                'bifocal_ar_verde_icon' => $uploaded($block, self::FIELD_BIFOCAL_AR_VERDE) ?: $defaults['bifocal_ar_verde'],
                'bifocal_ar_azul_foto_blue_icon' => $uploaded($block, self::FIELD_BIFOCAL_AR_AZUL_FOTO_BLUE) ?: $defaults['bifocal_ar_azul_foto_blue'],
                'bifocal_ar_verde_foto_blue_icon' => $uploaded($block, self::FIELD_BIFOCAL_AR_VERDE_FOTO_BLUE) ?: $defaults['bifocal_ar_verde_foto_blue'],
                'bifocal_159_transitions_icon' => $uploaded($block, self::FIELD_BIFOCAL_159_TRANSITIONS) ?: $defaults['bifocal_159_transitions'],
                'bifocal_159_blanco_icon' => $uploaded($block, self::FIELD_BIFOCAL_159_BLANCO) ?: $defaults['bifocal_159_blanco'],
                'bifocal_159_ar_verde_icon' => $uploaded($block, self::FIELD_BIFOCAL_159_AR_VERDE) ?: $defaults['bifocal_159_ar_verde'],
                'bifocal_159_blue_block_icon' => $uploaded($block, self::FIELD_BIFOCAL_159_BLUE_BLOCK) ?: $defaults['bifocal_159_blue_block'],
                'bifocal_159_foto_ar_blue_icon' => $uploaded($block, self::FIELD_BIFOCAL_159_FOTO_AR_BLUE) ?: $defaults['bifocal_159_foto_ar_blue'],
                'bifocal_159_ar_verde_foto_blue_icon' => $uploaded($block, self::FIELD_BIFOCAL_159_AR_VERDE_FOTO_BLUE) ?: $defaults['bifocal_159_ar_verde_foto_blue'],
                // nara images
                'nara_basica' => $uploaded($block, self::FIELD_NARA_BASICA) ?: $defaults['nara_basica'],
                'nara_media' => $uploaded($block, self::FIELD_NARA_MEDIA) ?: $defaults['nara_media'],
                'nara_alta' => $uploaded($block, self::FIELD_NARA_ALTA) ?: $defaults['nara_alta'],
                'nara_premium' => $uploaded($block, self::FIELD_NARA_PREMIUM) ?: $defaults['nara_premium'],
                self::DATA_MONO_DESCRIPTION => (string) ($data[self::DATA_MONO_DESCRIPTION] ?? $defaults[self::DATA_MONO_DESCRIPTION]),
                self::DATA_BIFOCAL_DESCRIPTION => (string) ($data[self::DATA_BIFOCAL_DESCRIPTION] ?? $defaults[self::DATA_BIFOCAL_DESCRIPTION]),
                self::DATA_OCUPACIONAL_DESCRIPTION => (string) ($data[self::DATA_OCUPACIONAL_DESCRIPTION] ?? $defaults[self::DATA_OCUPACIONAL_DESCRIPTION]),
                self::DATA_PROGRESIVO_DESCRIPTION => (string) ($data[self::DATA_PROGRESIVO_DESCRIPTION] ?? $defaults[self::DATA_PROGRESIVO_DESCRIPTION]),
                self::DATA_BIFOCAL_BLANCO_DESCRIPTION => (string) ($data[self::DATA_BIFOCAL_BLANCO_DESCRIPTION] ?? $defaults[self::DATA_BIFOCAL_BLANCO_DESCRIPTION]),
                self::DATA_BIFOCAL_AR_AZUL_DESCRIPTION => (string) ($data[self::DATA_BIFOCAL_AR_AZUL_DESCRIPTION] ?? $defaults[self::DATA_BIFOCAL_AR_AZUL_DESCRIPTION]),
                self::DATA_BIFOCAL_AR_VERDE_DESCRIPTION => (string) ($data[self::DATA_BIFOCAL_AR_VERDE_DESCRIPTION] ?? $defaults[self::DATA_BIFOCAL_AR_VERDE_DESCRIPTION]),
                self::DATA_BIFOCAL_AR_AZUL_FOTO_BLUE_DESCRIPTION => (string) ($data[self::DATA_BIFOCAL_AR_AZUL_FOTO_BLUE_DESCRIPTION] ?? $defaults[self::DATA_BIFOCAL_AR_AZUL_FOTO_BLUE_DESCRIPTION]),
                self::DATA_BIFOCAL_AR_VERDE_FOTO_BLUE_DESCRIPTION => (string) ($data[self::DATA_BIFOCAL_AR_VERDE_FOTO_BLUE_DESCRIPTION] ?? $defaults[self::DATA_BIFOCAL_AR_VERDE_FOTO_BLUE_DESCRIPTION]),
                self::DATA_BIFOCAL_159_TRANSITIONS_DESCRIPTION => (string) ($data[self::DATA_BIFOCAL_159_TRANSITIONS_DESCRIPTION] ?? $defaults[self::DATA_BIFOCAL_159_TRANSITIONS_DESCRIPTION]),
                self::DATA_BIFOCAL_159_BLANCO_DESCRIPTION => (string) ($data[self::DATA_BIFOCAL_159_BLANCO_DESCRIPTION] ?? $defaults[self::DATA_BIFOCAL_159_BLANCO_DESCRIPTION]),
                self::DATA_BIFOCAL_159_AR_VERDE_DESCRIPTION => (string) ($data[self::DATA_BIFOCAL_159_AR_VERDE_DESCRIPTION] ?? $defaults[self::DATA_BIFOCAL_159_AR_VERDE_DESCRIPTION]),
                self::DATA_BIFOCAL_159_BLUE_BLOCK_DESCRIPTION => (string) ($data[self::DATA_BIFOCAL_159_BLUE_BLOCK_DESCRIPTION] ?? $defaults[self::DATA_BIFOCAL_159_BLUE_BLOCK_DESCRIPTION]),
                self::DATA_BIFOCAL_159_FOTO_AR_BLUE_DESCRIPTION => (string) ($data[self::DATA_BIFOCAL_159_FOTO_AR_BLUE_DESCRIPTION] ?? $defaults[self::DATA_BIFOCAL_159_FOTO_AR_BLUE_DESCRIPTION]),
                self::DATA_BIFOCAL_159_AR_VERDE_FOTO_BLUE_DESCRIPTION => (string) ($data[self::DATA_BIFOCAL_159_AR_VERDE_FOTO_BLUE_DESCRIPTION] ?? $defaults[self::DATA_BIFOCAL_159_AR_VERDE_FOTO_BLUE_DESCRIPTION]),
        ];
    }
}
