<?php

namespace App\Services\Gafas;

use App\Models\GafaLensPrice;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;

final class GafaLensPricing
{
    /** Lens design keys used across the flow. */
    public const TIPO_LENTE_MONOFOCAL = 'con_aumento_monofocal';
    public const TIPO_LENTE_PROGRESIVOS = 'progresivos';
    public const TIPO_LENTE_BIFOCAL = 'bifocal';
    public const TIPO_LENTE_OCUPACIONAL = 'ocupacional';

    /**
     * Precios en COP.
     *
     * Estructura:
     *  - lens_type => [nara_level => price]
     */
    private const PRICE_MATRIX = [
        '156_blanco' => [
            'basica' => 130000,
            'media' => 140000,
            'alta' => 220000,
            'premium' => 260000,
        ],
        '156_ar_verde' => [
            'basica' => 170000,
            'media' => 250000,
            'alta' => 280000,
            'premium' => 340000,
        ],
        '156_blue_block' => [
            'basica' => 199000,
            'media' => 279000,
            'alta' => 320000,
            'premium' => 350000,
        ],
        '156_fotocromatico_superhidrofobico' => [
            'basica' => 288000,
            'media' => 369000,
            'alta' => 405000,
            'premium' => 435000,
        ],
        '160_premium' => [
            'basica' => 440000,
            'media' => 469000,
            'alta' => 495000,
            'premium' => 550000,
        ],
        '159_transitions_gens' => [
            'basica' => 560000,
            'media' => 599000,
            'alta' => 699000,
            'premium' => 790000,
        ],
    ];

    /**
     * Precios Monofocal (COP) según magnitud de la fórmula (ESFERA/CILINDRO).
     *
     * Reglas (según Excel del usuario):
     * - 1.56: precio 1 si |esf|<=3 y |cil|<=3; precio 2 si |esf|<=4 y |cil|<=4; si no, precio 3.
     * - 1.60 Premium: precio 1 si |esf|<=4 y |cil|<=4; si no, precio 2.
     * - Alto índice (1.67/1.74): NO varía por la fórmula.
     */
    private const MONOFOCAL_TIERED_PRICES = [
        // Nara Básica 1.56
        '156_blanco' => [1 => 55000, 2 => 75000, 3 => 105000],
        '156_ar_verde' => [1 => 88000, 2 => 120000, 3 => 170000],
        '156_blue_block' => [1 => 89000, 2 => 125000, 3 => 190000],
        '156_fotocromatico_superhidrofobico' => [1 => 169000, 2 => 189000, 3 => 235000],
        '156_ar_verde_fotocromatico_blue_block' => [1 => 169000, 2 => 189000, 3 => 235000],

        // Nara Premium 1.60
        '160_premium' => [1 => 375000, 2 => 399000],
    ];

    /** Precios Poly para 1.56 por rango de ESFERA/CILINDRO (COP). */
    private const POLY_156_TIERED_PRICES = [
        '156_blanco' => [1 => 75000, 2 => 99000, 3 => 120000, 4 => 160000],
        '156_ar_verde' => [1 => 115000, 2 => 140000, 3 => 220000, 4 => 245000],
        '156_blue_block' => [1 => 105000, 2 => 140000, 3 => 245000, 4 => 299000],
        '156_fotocromatico_superhidrofobico' => [1 => 199000, 2 => 250000, 3 => 299000, 4 => 330000],
        '156_ar_verde_fotocromatico_blue_block' => [1 => 199000, 2 => 233000, 3 => 280000, 4 => 310000],
    ];

    /** Precios Poly para “No, sin fórmula” (COP). */
    private const POLY_NO_FORMULA_PRICES = [
        '156_blanco' => 75000,
        '156_blue_block' => 105000,
        '156_ar_verde' => 115000,
        '156_fotocromatico_superhidrofobico' => 199000,
        '156_ar_verde_fotocromatico_blue_block' => 199000,
    ];

    /** Precios Poly para progresivos por nivel NARA (COP). */
    private const POLY_PROGRESIVOS_LEVEL_PRICES = [
        '156_blanco' => ['basica' => 175000, 'media' => 200000, 'alta' => 275000],
        '156_ar_verde' => ['basica' => 213000, 'media' => 275000, 'alta' => 350000],
        '156_blue_block' => ['basica' => 247000, 'media' => 350000, 'alta' => 439000],
        '156_fotocromatico_superhidrofobico' => ['basica' => 360000, 'media' => 459000, 'alta' => 499000],
        '159_transitions_gens' => ['basica' => 570000, 'media' => 620000, 'alta' => 660000],
    ];

    /** Precios Poly para ocupacional (+60.000 sobre base 299.000). */
    private const POLY_OCUPACIONAL_FIXED_PRICES = [
        '156_ar_verde' => 359000,
        '156_blue_block' => 359000,
    ];

    private const MONOFOCAL_FIXED_PRICES = [
        // Alto índice 1.67 / 1.74 (NO varía por fórmula)
        '167_ar_verde' => 330000,
        '174_ar_verde' => 490000,
        '167_blue_block' => 330000,
        '174_blue_block' => 490000,
        '167_ar_azul_fotocromatico_blue_block' => 440000,
        '174_ar_azul_fotocromatico_blue_block' => 560000,
        '167_ar_verde_fotocromatico_blue_block' => 440000,
        '174_ar_verde_fotocromatico_blue_block' => 560000,
    ];

    /** Monofocal Transitions (COP). Regla especial por ESFERA/CILINDRO y color. */
    private const MONOFOCAL_TRANSITIONS_RULE = [
        'tier1_max' => 2.0,
        'price_tier1' => 475000,
        'price_tier2' => 499000,
        'price_with_color' => 520000,
    ];

    /** Precios Bifocal (COP). NO varía por la fórmula. */
    private const BIFOCAL_FIXED_PRICES = [
        // 1.56
        '156_blanco' => 120000,
        '156_ar_verde' => 160000,
        '156_blue_block' => 160000,
        // Se usa este key existente como “AR azul + Fotocromático + Blue Block”
        '156_fotocromatico_superhidrofobico' => 270000,
        // Nuevo: “AR verde + Fotocromático + Blue Block”
        '156_ar_verde_fotocromatico_blue_block' => 270000,

        // 1.59 (Bifocal)
        '159_bifocal_blanco' => 190000,
        '159_bifocal_ar_verde' => 230000,
        '159_bifocal_blue_block' => 230000,
        '159_bifocal_fotocromatico_superhidrofobico' => 340000,
        '159_bifocal_ar_verde_fotocromatico_blue_block' => 340000,

        // Transitions (Bifocal)
        '159_transitions_gens' => 560000,
    ];

    /** Precios Ocupacional (COP). NO varía por la fórmula. */
    private const OCUPACIONAL_FIXED_PRICES = [
        '156_ar_verde' => 299000,
        '156_blue_block' => 299000,
    ];

    /** @return string[] */
    public static function progresivosLensTypes(): array
    {
        return [
            '156_blanco',
            '156_ar_verde',
            '156_blue_block',
            '156_fotocromatico_superhidrofobico',
            '160_premium',
            '159_transitions_gens',
        ];
    }

    /** @return string[] */
    public static function polyProgresivosLensTypes(): array
    {
        return [
            '156_blanco',
            '156_ar_verde',
            '156_blue_block',
            '156_fotocromatico_superhidrofobico',
            '160_premium',
            '159_transitions_gens',
        ];
    }

    /** @return string[] */
    public static function bifocalLensTypes(): array
    {
        return [
            // 1.56
            '156_blanco',
            '156_ar_verde',
            '156_blue_block',
            '156_fotocromatico_superhidrofobico',
            '156_ar_verde_fotocromatico_blue_block',

            // 1.59 bifocal
            '159_bifocal_blanco',
            '159_bifocal_ar_verde',
            '159_bifocal_blue_block',
            '159_bifocal_fotocromatico_superhidrofobico',
            '159_bifocal_ar_verde_fotocromatico_blue_block',

            // Transition
            '159_transitions_gens',
        ];
    }

    /** @return string[] */
    public static function ocupacionalLensTypes(): array
    {
        return [
            '156_ar_verde',
            '156_blue_block',
        ];
    }

    /** @return string[] */
    public static function monofocalLensTypes(): array
    {
        return [
            '156_blanco',
            '156_ar_verde',
            '156_blue_block',
            '156_fotocromatico_superhidrofobico',
            '156_ar_verde_fotocromatico_blue_block',
            '160_premium',

            // Transition (Monofocal)
            '159_transitions_gens',

            '167_ar_verde',
            '174_ar_verde',
            '167_blue_block',
            '174_blue_block',
            '167_ar_azul_fotocromatico_blue_block',
            '174_ar_azul_fotocromatico_blue_block',
            '167_ar_verde_fotocromatico_blue_block',
            '174_ar_verde_fotocromatico_blue_block',
        ];
    }

    /** @return array<string,string> */
    public static function lensTypeOptionsForProgresivos(bool $usePoly = false): array
    {
        $all = self::lensTypeOptions();
        $out = [];
        $keys = $usePoly ? self::polyProgresivosLensTypes() : self::progresivosLensTypes();
        foreach ($keys as $key) {
            if (isset($all[$key])) {
                $out[$key] = self::lensTypeDisplayLabel($key, $usePoly);
            }
        }
        return $out;
    }

    /** @return array<string,string> */
    public static function lensTypeOptionsForMonofocal(bool $usePoly = false): array
    {
        $all = self::lensTypeOptions();
        $out = [];
        foreach (self::monofocalLensTypes() as $key) {
            if (isset($all[$key])) {
                $out[$key] = self::lensTypeDisplayLabel($key, $usePoly);
            }
        }
        return $out;
    }

    /** @return array<string,string> */
    public static function lensTypeOptionsForBifocal(bool $usePoly = false): array
    {
        $all = self::lensTypeOptions();
        $out = [];
        foreach (self::bifocalLensTypes() as $key) {
            if (isset($all[$key])) {
                $out[$key] = self::lensTypeDisplayLabel($key, $usePoly);
            }
        }
        return $out;
    }

    /** @return array<string,string> */
    public static function lensTypeOptionsForOcupacional(bool $usePoly = false): array
    {
        $all = self::lensTypeOptions();
        $out = [];
        foreach (self::ocupacionalLensTypes() as $key) {
            if (isset($all[$key])) {
                $out[$key] = self::lensTypeDisplayLabel($key, $usePoly);
            }
        }
        return $out;
    }

    /** @return array<string,string> Commercial display names used in the "sin fórmula" branch only. */
    public static function lensTypeLabelsForNoFormula(bool $usePoly = false): array
    {
        if ($usePoly) {
            return [
                '156_blanco' => 'Blanco',
                '156_blue_block' => 'Blue block',
                '156_ar_verde' => 'Ar verde',
                '156_fotocromatico_superhidrofobico' => 'Fotoblue',
                '156_ar_verde_fotocromatico_blue_block' => 'Fotoverde',
            ];
        }

        return [
            '156_blanco'                           => 'Blanco',
            '156_blue_block'                       => 'AR Azul',
            '156_ar_verde'                         => 'AR Verde',
            '156_fotocromatico_superhidrofobico'   => 'AR azul + Fotocromatico + Blue Block',
            '156_ar_verde_fotocromatico_blue_block' => 'AR verde + Fotocromatico + Blue Block',
        ];
    }

    /** @return array<string,string> */
    public static function polyLensTypeLabels(): array
    {
        return [
            '156_blanco' => 'Nara POLY 1.59 Blanco',
            '156_ar_verde' => 'Nara POLY 1.59 AR Verde',
            '156_blue_block' => 'Nara POLY 1.59 Blue Block',
            '156_fotocromatico_superhidrofobico' => 'Nara POLY 1.59 Fotoblue',
            '156_ar_verde_fotocromatico_blue_block' => 'Nara POLY 1.59 Fotoverde',
            '159_transitions_gens' => 'Nara POLY 1.59 Transitions',
        ];
    }

    public static function lensTypeDisplayLabel(string $lensType, bool $usePoly = false, bool $forNoFormula = false): string
    {
        $lensType = trim($lensType);
        if ($lensType === '') {
            return '';
        }

        if ($forNoFormula) {
            $labels = self::lensTypeLabelsForNoFormula($usePoly);
            return (string) ($labels[$lensType] ?? $lensType);
        }

        if ($usePoly) {
            $polyLabels = self::polyLensTypeLabels();
            if (isset($polyLabels[$lensType])) {
                return $polyLabels[$lensType];
            }
        }

        $labels = self::lensTypeOptions();
        return (string) ($labels[$lensType] ?? $lensType);
    }

    private static function boolFromCharacteristicsValue(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (bool) $value;
        }

        if (!is_string($value)) {
            return false;
        }

        return in_array(strtolower(trim($value)), ['1', 'si', 'sí', 'true'], true);
    }

    public static function allowsMultifocalForCharacteristics(?array $characteristics): bool
    {
        $features = is_array($characteristics) ? $characteristics : [];
        return self::boolFromCharacteristicsValue($features['progresivos'] ?? null);
    }

    public static function usesPolyForCharacteristics(?array $characteristics): bool
    {
        $features = is_array($characteristics) ? $characteristics : [];
        return self::boolFromCharacteristicsValue($features['poly'] ?? null);
    }

    /** @return string[] */
    public static function allowedLensDesignsForCharacteristics(?array $characteristics): array
    {
        $allowed = [
            self::TIPO_LENTE_MONOFOCAL,
        ];

        if (self::allowsMultifocalForCharacteristics($characteristics)) {
            $allowed[] = self::TIPO_LENTE_OCUPACIONAL;
            $allowed[] = self::TIPO_LENTE_BIFOCAL;
            $allowed[] = self::TIPO_LENTE_PROGRESIVOS;
        }

        return array_values(array_unique($allowed));
    }

    public static function isLensDesignAllowedForCharacteristics(?array $characteristics, ?string $tipoLenteNecesitas): bool
    {
        return in_array((string) ($tipoLenteNecesitas ?? ''), self::allowedLensDesignsForCharacteristics($characteristics), true);
    }

    public static function defaultLensDesignForCharacteristics(?array $characteristics): string
    {
        $allowed = self::allowedLensDesignsForCharacteristics($characteristics);

        return $allowed[0] ?? self::TIPO_LENTE_MONOFOCAL;
    }

    public static function sanitizeLensDesignForCharacteristics(?array $characteristics, ?string $tipoLenteNecesitas): string
    {
        $tipo = (string) ($tipoLenteNecesitas ?? '');

        if (self::isLensDesignAllowedForCharacteristics($characteristics, $tipo)) {
            return $tipo;
        }

        return self::defaultLensDesignForCharacteristics($characteristics);
    }

    public static function isLensTypeAllowedFor(?string $tipoLenteNecesitas, string $lensType, bool $usePoly = false): bool
    {
        $tipo = (string) ($tipoLenteNecesitas ?? '');
        if ($tipo === self::TIPO_LENTE_MONOFOCAL) {
            return in_array($lensType, self::monofocalLensTypes(), true);
        }
        if ($tipo === self::TIPO_LENTE_BIFOCAL) {
            return in_array($lensType, self::bifocalLensTypes(), true);
        }
        if ($tipo === self::TIPO_LENTE_OCUPACIONAL) {
            return in_array($lensType, self::ocupacionalLensTypes(), true);
        }
        if ($tipo === self::TIPO_LENTE_PROGRESIVOS) {
            return in_array($lensType, $usePoly ? self::polyProgresivosLensTypes() : self::progresivosLensTypes(), true);
        }

        // Si no sabemos, permitimos solo los de progresivos (default histórico).
        return in_array($lensType, $usePoly ? self::polyProgresivosLensTypes() : self::progresivosLensTypes(), true);
    }

    public static function defaultLensTypeFor(?string $tipoLenteNecesitas, bool $usePoly = false): string
    {
        $tipo = (string) ($tipoLenteNecesitas ?? '');

        if ($tipo === self::TIPO_LENTE_OCUPACIONAL) {
            return '156_ar_verde';
        }

        if ($tipo === self::TIPO_LENTE_PROGRESIVOS && $usePoly) {
            return '156_blanco';
        }

        return self::defaultLensType();
    }

    /** @return array{sphere: float, cyl: float} */
    public static function rxMaxAbsFromAnalysis(array $analysis): array
    {
        $odSph = $analysis['od']['sph'] ?? null;
        $oiSph = $analysis['oi']['sph'] ?? null;
        $odCyl = $analysis['od']['cyl'] ?? null;
        $oiCyl = $analysis['oi']['cyl'] ?? null;

        $manual = is_array($analysis['manual_input'] ?? null) ? $analysis['manual_input'] : [];
        $manualOd = is_array($manual['od'] ?? null) ? $manual['od'] : [];
        $manualOi = is_array($manual['oi'] ?? null) ? $manual['oi'] : [];

        $parseManualRx = static function (mixed $value): ?float {
            if (is_float($value) || is_int($value)) {
                return (float) $value;
            }

            if (!is_string($value)) {
                return null;
            }

            $raw = trim($value);
            if ($raw === '') {
                return null;
            }

            // Compatibilidad con etiquetas visuales del picker.
            if (strcasecmp($raw, 'Neutro/N') === 0) {
                return 0.0;
            }

            $normalized = str_replace(',', '.', $raw);
            if (!is_numeric($normalized)) {
                return null;
            }

            return (float) $normalized;
        };

        $odSphManual = $parseManualRx($manualOd['esfera'] ?? null);
        $oiSphManual = $parseManualRx($manualOi['esfera'] ?? null);
        $odCylManual = $parseManualRx($manualOd['cilindro'] ?? null);
        $oiCylManual = $parseManualRx($manualOi['cilindro'] ?? null);

        $sphereMax = 0.0;
        foreach ([$odSph, $oiSph, $odSphManual, $oiSphManual] as $v) {
            if (is_float($v) || is_int($v)) {
                $sphereMax = max($sphereMax, abs((float) $v));
            }
        }

        $cylMax = 0.0;
        foreach ([$odCyl, $oiCyl, $odCylManual, $oiCylManual] as $v) {
            if (is_float($v) || is_int($v)) {
                $cylMax = max($cylMax, abs((float) $v));
            }
        }

        return ['sphere' => $sphereMax, 'cyl' => $cylMax];
    }

    private static function monofocalTierFor156(float $sphereMaxAbs, float $cylMaxAbs): int
    {
        if ($sphereMaxAbs <= 3.0 && $cylMaxAbs <= 3.0) {
            return 1;
        }
        if ($sphereMaxAbs <= 4.0 && $cylMaxAbs <= 4.0) {
            return 2;
        }
        return 3;
    }

    private static function monofocalTierFor160(float $sphereMaxAbs, float $cylMaxAbs): int
    {
        if ($sphereMaxAbs <= 4.0 && $cylMaxAbs <= 4.0) {
            return 1;
        }
        return 2;
    }

    private static function polyTierFor156(float $sphereMaxAbs, float $cylMaxAbs): int
    {
        if ($sphereMaxAbs <= 3.0 && $cylMaxAbs <= 2.0) {
            return 1;
        }
        if ($sphereMaxAbs <= 4.0 && $cylMaxAbs <= 4.0) {
            return 2;
        }
        if ($sphereMaxAbs <= 9.0 && $cylMaxAbs <= 9.0) {
            return 3;
        }
        return 4;
    }

    /** @return array<string,array<string,int>> */
    private static function pricingRows(): array
    {
        $rows = [];

        try {
            if (!Schema::hasTable('gafa_lens_prices')) {
                return $rows;
            }

            $dbRows = GafaLensPrice::query()->get(['lens_type', 'nara_level', 'price']);
            foreach ($dbRows as $row) {
                $lensType = (string) ($row->lens_type ?? '');
                $level = (string) ($row->nara_level ?? '');
                if ($lensType === '' || $level === '') {
                    continue;
                }

                $rows[$lensType][$level] = (int) ($row->price ?? 0);
            }
        } catch (QueryException) {
            return [];
        }

        return $rows;
    }

    private static function rowOverride(array $rows, string $lensType, string $key, int $default): int
    {
        if (isset($rows[$lensType]) && array_key_exists($key, $rows[$lensType])) {
            return (int) $rows[$lensType][$key];
        }

        return $default;
    }

    /** @return array{fixed: array<string,int>, tiered: array<string,array<int,int>>, transitions: array<string,int>, no_formula: array<string,int>} */
    private static function monofocalPricingSnapshot(): array
    {
        $rows = self::pricingRows();

        $fixed = self::MONOFOCAL_FIXED_PRICES;
        foreach ($fixed as $lensType => $price) {
            $fixed[$lensType] = self::rowOverride($rows, $lensType, 'mono_fixed', (int) $price);
        }

        $tiered = self::MONOFOCAL_TIERED_PRICES;
        foreach ($tiered as $lensType => $tiers) {
            $basica = self::rowOverride($rows, $lensType, 'mono_tier1', (int) ($tiers[1] ?? 0));
            $media = self::rowOverride($rows, $lensType, 'mono_tier2', (int) ($tiers[2] ?? $basica));
            $alta = self::rowOverride($rows, $lensType, 'mono_tier3', (int) ($tiers[3] ?? $media));

            $tiered[$lensType] = [
                1 => $basica,
                2 => $media,
                3 => $alta,
            ];
        }

        $transitions = [
            'tier1' => self::rowOverride($rows, '159_transitions_gens', 'mono_trans_tier1', (int) (self::MONOFOCAL_TRANSITIONS_RULE['price_tier1'] ?? 0)),
            'tier2' => self::rowOverride($rows, '159_transitions_gens', 'mono_trans_tier2', (int) (self::MONOFOCAL_TRANSITIONS_RULE['price_tier2'] ?? 0)),
            'with_color' => self::rowOverride($rows, '159_transitions_gens', 'mono_trans_with_color', (int) (self::MONOFOCAL_TRANSITIONS_RULE['price_with_color'] ?? 0)),
        ];

        // Precios para “No, sin fórmula” (sin aumento). No dependen de esfera/cilindro.
        $noFormulaDefaults = [
            '156_blanco' => 55000,
            '156_blue_block' => 70000,
            '156_ar_verde' => 70000,
            '156_fotocromatico_superhidrofobico' => 120000,
            '156_ar_verde_fotocromatico_blue_block' => 120000,
        ];

        $noFormula = [];
        foreach ($noFormulaDefaults as $lensType => $defaultPrice) {
            $noFormula[$lensType] = self::rowOverride($rows, $lensType, 'no_formula_fixed', $defaultPrice);
        }

        return [
            'fixed' => $fixed,
            'tiered' => $tiered,
            'transitions' => $transitions,
            'no_formula' => $noFormula,
        ];
    }

    /** @return array{tiered: array<string,array<int,int>>, no_formula: array<string,int>} */
    private static function polyPricingSnapshot(): array
    {
        $rows = self::pricingRows();

        $tiered = self::POLY_156_TIERED_PRICES;
        foreach ($tiered as $lensType => $tiers) {
            $t1 = self::rowOverride($rows, $lensType, 'poly_tier1', (int) ($tiers[1] ?? 0));
            $t2 = self::rowOverride($rows, $lensType, 'poly_tier2', (int) ($tiers[2] ?? $t1));
            $t3 = self::rowOverride($rows, $lensType, 'poly_tier3', (int) ($tiers[3] ?? $t2));
            $t4 = self::rowOverride($rows, $lensType, 'poly_tier4', (int) ($tiers[4] ?? $t3));

            $tiered[$lensType] = [
                1 => $t1,
                2 => $t2,
                3 => $t3,
                4 => $t4,
            ];
        }

        $noFormula = [];
        foreach (self::POLY_NO_FORMULA_PRICES as $lensType => $defaultPrice) {
            $noFormula[$lensType] = self::rowOverride($rows, $lensType, 'poly_no_formula_fixed', (int) $defaultPrice);
        }

        return [
            'tiered' => $tiered,
            'no_formula' => $noFormula,
        ];
    }

    /** @return array<string,array<int,int>> */
    public static function monofocalTieredPricingTable(): array
    {
        $snapshot = self::monofocalPricingSnapshot();
        return $snapshot['tiered'] ?? [];
    }

    /** @return array<string,int> */
    public static function monofocalFixedPricingTable(): array
    {
        $snapshot = self::monofocalPricingSnapshot();
        return $snapshot['fixed'] ?? [];
    }

    /** @return array<string,int> */
    public static function monofocalTransitionsPricing(): array
    {
        $snapshot = self::monofocalPricingSnapshot();
        return $snapshot['transitions'] ?? [];
    }

    /** @return array<string,int> */
    public static function noFormulaPricingTable(): array
    {
        $snapshot = self::monofocalPricingSnapshot();
        return $snapshot['no_formula'] ?? [];
    }

    /** @return array<string,int> */
    public static function polyNoFormulaPricingTable(): array
    {
        $snapshot = self::polyPricingSnapshot();
        return $snapshot['no_formula'] ?? [];
    }

    /** @return array<string,array<int,int>> */
    public static function polyTieredPricingTable(): array
    {
        $snapshot = self::polyPricingSnapshot();
        return $snapshot['tiered'] ?? [];
    }

    /** @return array<string,array<string,int>> */
    public static function polyProgresivosPricingTable(): array
    {
        $rows = self::pricingRows();
        $table = self::POLY_PROGRESIVOS_LEVEL_PRICES;

        foreach ($table as $lensType => $levels) {
            $basica = self::rowOverride($rows, $lensType, 'poly_prog_basica', (int) ($levels['basica'] ?? 0));
            $media = self::rowOverride($rows, $lensType, 'poly_prog_media', (int) ($levels['media'] ?? $basica));
            $alta = self::rowOverride($rows, $lensType, 'poly_prog_alta', (int) ($levels['alta'] ?? $media));

            $table[$lensType] = [
                'basica' => $basica,
                'media' => $media,
                'alta' => $alta,
            ];
        }

        return $table;
    }

    /** @return array<string,int> */
    public static function polyOcupacionalFixedPricingTable(): array
    {
        $rows = self::pricingRows();
        $fixed = [];
        foreach (self::POLY_OCUPACIONAL_FIXED_PRICES as $lensType => $defaultPrice) {
            $fixed[$lensType] = self::rowOverride($rows, $lensType, 'poly_ocupacional_fixed', (int) $defaultPrice);
        }

        return $fixed;
    }

    /** @return array<string,int> */
    public static function bifocalFixedPricingTable(): array
    {
        $rows = self::pricingRows();
        $fixed = [];
        foreach (self::BIFOCAL_FIXED_PRICES as $lensType => $defaultPrice) {
            $fixed[$lensType] = self::rowOverride($rows, $lensType, 'bifocal_fixed', (int) $defaultPrice);
        }

        return $fixed;
    }

    /** @return array<string,int> */
    public static function ocupacionalFixedPricingTable(): array
    {
        $rows = self::pricingRows();
        $fixed = [];
        foreach (self::OCUPACIONAL_FIXED_PRICES as $lensType => $defaultPrice) {
            $fixed[$lensType] = self::rowOverride($rows, $lensType, 'ocupacional_fixed', (int) $defaultPrice);
        }

        return $fixed;
    }

    public static function monofocalLensPrice(string $lensType, float $sphereMaxAbs, float $cylMaxAbs, ?string $lensColor = null, bool $usePoly = false): int
    {
        if (!self::isValidLensType($lensType)) {
            return 0;
        }

        if ($usePoly) {
            $polyTiered = self::polyTieredPricingTable();
            if (isset($polyTiered[$lensType]) && is_array($polyTiered[$lensType])) {
                $tier = self::polyTierFor156($sphereMaxAbs, $cylMaxAbs);
                return (int) ($polyTiered[$lensType][$tier] ?? 0);
            }
        }

        $snapshot = self::monofocalPricingSnapshot();
        $fixed = $snapshot['fixed'] ?? [];
        $tieredMatrix = $snapshot['tiered'] ?? [];

        if ($lensType === '159_transitions_gens') {
            $color = trim((string) ($lensColor ?? ''));
            if ($color !== '') {
                return (int) (($snapshot['transitions']['with_color'] ?? 0));
            }

            $max = (float) (self::MONOFOCAL_TRANSITIONS_RULE['tier1_max'] ?? 0);
            $p1 = (int) (($snapshot['transitions']['tier1'] ?? 0));
            $p2 = (int) (($snapshot['transitions']['tier2'] ?? 0));
            return ($sphereMaxAbs <= $max && $cylMaxAbs <= $max) ? $p1 : $p2;
        }

        if (isset($fixed[$lensType])) {
            return (int) $fixed[$lensType];
        }

        $tiers = $tieredMatrix[$lensType] ?? null;
        if (!is_array($tiers) || $tiers === []) {
            return 0;
        }

        if ($lensType === '160_premium') {
            $tier = self::monofocalTierFor160($sphereMaxAbs, $cylMaxAbs);
            return (int) ($tiers[$tier] ?? 0);
        }

        // Por defecto: reglas 1.56
        $tier = self::monofocalTierFor156($sphereMaxAbs, $cylMaxAbs);
        return (int) ($tiers[$tier] ?? 0);
    }

    public static function bifocalLensPrice(string $lensType, float $sphereMaxAbs = 0.0, float $cylMaxAbs = 0.0, bool $usePoly = false): int
    {
        if (!self::isValidLensType($lensType)) {
            return 0;
        }

        if ($usePoly) {
            $polyTiered = self::polyTieredPricingTable();
            if (isset($polyTiered[$lensType]) && is_array($polyTiered[$lensType])) {
                $tier = self::polyTierFor156($sphereMaxAbs, $cylMaxAbs);
                return (int) ($polyTiered[$lensType][$tier] ?? 0);
            }
        }

        $fixed = self::bifocalFixedPricingTable();
        return (int) ($fixed[$lensType] ?? 0);
    }

    public static function ocupacionalLensPrice(string $lensType, bool $usePoly = false): int
    {
        if (!self::isValidLensType($lensType)) {
            return 0;
        }

        if ($usePoly) {
            $polyFixed = self::polyOcupacionalFixedPricingTable();
            if (array_key_exists($lensType, $polyFixed)) {
                return (int) ($polyFixed[$lensType] ?? 0);
            }
        }

        $fixed = self::ocupacionalFixedPricingTable();
        return (int) ($fixed[$lensType] ?? 0);
    }

    public static function noFormulaLensPrice(string $lensType, bool $usePoly = false): int
    {
        if (!self::isValidLensType($lensType)) {
            return 0;
        }

        if ($usePoly) {
            $polyNoFormula = self::polyNoFormulaPricingTable();
            return (int) ($polyNoFormula[$lensType] ?? 0);
        }

        $noFormula = self::noFormulaPricingTable();
        return (int) ($noFormula[$lensType] ?? 0);
    }

    /**
     * Config para UI (JS) para calcular precio monofocal en el cliente.
     *
     * @return array{fixed: array<string,int>, tiered: array<string,array<int,int>>, rules: array<string,mixed>}
     */
    public static function monofocalClientPricing(bool $usePoly = false): array
    {
        $snapshot = self::monofocalPricingSnapshot();
        $polySnapshot = $usePoly ? self::polyPricingSnapshot() : ['tiered' => [], 'no_formula' => []];

        return [
            'fixed' => $snapshot['fixed'],
            'tiered' => $usePoly ? ($polySnapshot['tiered'] ?? []) : $snapshot['tiered'],
            'no_formula' => $usePoly ? ($polySnapshot['no_formula'] ?? []) : $snapshot['no_formula'],
            'rules' => [
                '156' => ['tier1_max' => 3.0, 'tier2_max' => 4.0],
                '160' => ['tier1_max' => 4.0],
                'poly_156' => [
                    'enabled' => $usePoly,
                    'tier1_sphere_max' => 3.0,
                    'tier1_cyl_max' => 2.0,
                    'tier2_sphere_max' => 4.0,
                    'tier2_cyl_max' => 4.0,
                    'tier3_sphere_max' => 9.0,
                    'tier3_cyl_max' => 9.0,
                ],
                'transitions' => [
                    'tier1_max' => self::MONOFOCAL_TRANSITIONS_RULE['tier1_max'] ?? 2.0,
                    'price_tier1' => (int) ($snapshot['transitions']['tier1'] ?? 0),
                    'price_tier2' => (int) ($snapshot['transitions']['tier2'] ?? 0),
                    'price_with_color' => (int) ($snapshot['transitions']['with_color'] ?? 0),
                ],
            ],
        ];
    }

    /** @return array{fixed: array<string,int>} */
    public static function bifocalClientPricing(bool $usePoly = false): array
    {
        if (!$usePoly) {
            return ['fixed' => self::BIFOCAL_FIXED_PRICES, 'tiered' => []];
        }

        return [
            'fixed' => self::BIFOCAL_FIXED_PRICES,
            'tiered' => self::polyTieredPricingTable(),
            'rules' => [
                'poly_156' => [
                    'enabled' => true,
                    'tier1_sphere_max' => 3.0,
                    'tier1_cyl_max' => 2.0,
                    'tier2_sphere_max' => 4.0,
                    'tier2_cyl_max' => 4.0,
                    'tier3_sphere_max' => 9.0,
                    'tier3_cyl_max' => 9.0,
                ],
            ],
        ];
    }

    /** @return array{fixed: array<string,int>} */
    public static function ocupacionalClientPricing(bool $usePoly = false): array
    {
        return ['fixed' => $usePoly ? self::polyOcupacionalFixedPricingTable() : self::OCUPACIONAL_FIXED_PRICES];
    }

    /** @return array<string,string> */
    public static function lensTypeOptions(): array
    {
        return [
            '156_blanco' => '1.56 Blanco',
            '156_ar_verde' => '1.56 AR Verde',
            '156_blue_block' => '1.56 Blue Block',
            '156_fotocromatico_superhidrofobico' => '1.56 Fotocromático + AR + Blue Block (Super hidrofóbico)',
            '156_ar_verde_fotocromatico_blue_block' => '1.56 AR verde + Fotocromático + Blue Block',
            '160_premium' => '1.60 Premium',
            '159_transitions_gens' => '1.59 Transitions Gens',

            // Bifocal 1.59 (NO transitions)
            '159_bifocal_blanco' => 'Bifocal 1.59 Blanco',
            '159_bifocal_ar_verde' => 'Bifocal 1.59 AR Verde',
            '159_bifocal_blue_block' => 'Bifocal 1.59 Blue Block',
            '159_bifocal_fotocromatico_superhidrofobico' => 'Bifocal 1.59 Fotocromático + AR + Blue Block',
            '159_bifocal_ar_verde_fotocromatico_blue_block' => 'Bifocal 1.59 AR verde + Fotocromático + Blue Block',

            // Alto índice (Monofocal)
            '167_ar_verde' => 'Alto índice 1.67 AR Verde',
            '174_ar_verde' => 'Alto índice 1.74 AR Verde',
            '167_blue_block' => 'Alto índice 1.67 Blue Block',
            '174_blue_block' => 'Alto índice 1.74 Blue Block',
            '167_ar_azul_fotocromatico_blue_block' => 'Alto índice 1.67 AR azul + Fotocromático + Blue Block',
            '174_ar_azul_fotocromatico_blue_block' => 'Alto índice 1.74 AR azul + Fotocromático + Blue Block',
            '167_ar_verde_fotocromatico_blue_block' => 'Alto índice 1.67 AR verde + Fotocromático + Blue Block',
            '174_ar_verde_fotocromatico_blue_block' => 'Alto índice 1.74 AR verde + Fotocromático + Blue Block',
        ];
    }

    /** @return array<string,string> */
    public static function naraLevelOptions(): array
    {
        return [
            'basica' => 'NARA Básica',
            'media' => 'NARA Media',
            'alta' => 'NARA Alta',
            'premium' => 'NARA Premium',
        ];
    }

    public static function defaultLensType(): string
    {
        return '156_blanco';
    }

    public static function defaultNaraLevel(): string
    {
        return 'basica';
    }

    public static function isValidLensType(?string $lensType): bool
    {
        if ($lensType === null) {
            return false;
        }

        $types = self::lensTypeOptions();
        return array_key_exists($lensType, $types);
    }

    public static function isValidNaraLevel(?string $naraLevel): bool
    {
        if ($naraLevel === null) {
            return false;
        }

        $levels = self::naraLevelOptions();
        return array_key_exists($naraLevel, $levels);
    }

    public static function lensPrice(string $lensType, string $naraLevel, bool $usePoly = false): int
    {
        if (!self::isValidLensType($lensType) || !self::isValidNaraLevel($naraLevel)) {
            return 0;
        }

        if ($usePoly) {
            $polyTable = self::polyProgresivosPricingTable();
            if (isset($polyTable[$lensType])) {
                if ($naraLevel === 'premium') {
                    return (int) ($polyTable[$lensType]['alta'] ?? 0);
                }

                return (int) ($polyTable[$lensType][$naraLevel] ?? 0);
            }
        }

        $matrix = self::matrix();
        return (int) (($matrix[$lensType][$naraLevel] ?? 0));
    }

    /** @return array<string,array<string,int>> */
    public static function matrixForCheckout(bool $usePoly = false): array
    {
        $matrix = self::matrix();
        if (!$usePoly) {
            return $matrix;
        }

        $polyProgressive = self::polyProgresivosPricingTable();
        foreach ($polyProgressive as $lensType => $levels) {
            if (!isset($matrix[$lensType])) {
                continue;
            }

            $matrix[$lensType]['basica'] = (int) ($levels['basica'] ?? 0);
            $matrix[$lensType]['media'] = (int) ($levels['media'] ?? 0);
            $matrix[$lensType]['alta'] = (int) ($levels['alta'] ?? 0);
            $matrix[$lensType]['premium'] = (int) ($levels['alta'] ?? 0);
        }

        return $matrix;
    }

    /** @return array<string,array<string,int>> */
    private static function defaultMatrix(): array
    {
        $matrix = [];
        $naraLevels = array_keys(self::naraLevelOptions());

        foreach (array_keys(self::lensTypeOptions()) as $lensType) {
            $matrix[$lensType] = array_fill_keys($naraLevels, 0);
        }

        foreach (self::PRICE_MATRIX as $lensType => $prices) {
            foreach ($prices as $naraLevel => $price) {
                $matrix[$lensType][$naraLevel] = (int) $price;
            }
        }

        foreach (self::MONOFOCAL_FIXED_PRICES as $lensType => $price) {
            if (isset(self::PRICE_MATRIX[$lensType]) || !isset($matrix[$lensType])) {
                continue;
            }

            foreach ($naraLevels as $naraLevel) {
                $matrix[$lensType][$naraLevel] = (int) $price;
            }
        }

        foreach (self::BIFOCAL_FIXED_PRICES as $lensType => $price) {
            if (isset(self::PRICE_MATRIX[$lensType]) || !isset($matrix[$lensType])) {
                continue;
            }

            foreach ($naraLevels as $naraLevel) {
                $matrix[$lensType][$naraLevel] = max((int) $matrix[$lensType][$naraLevel], (int) $price);
            }
        }

        foreach (self::OCUPACIONAL_FIXED_PRICES as $lensType => $price) {
            if (isset(self::PRICE_MATRIX[$lensType]) || !isset($matrix[$lensType])) {
                continue;
            }

            foreach ($naraLevels as $naraLevel) {
                $matrix[$lensType][$naraLevel] = max((int) $matrix[$lensType][$naraLevel], (int) $price);
            }
        }

        foreach (self::MONOFOCAL_TIERED_PRICES as $lensType => $tiers) {
            if (isset(self::PRICE_MATRIX[$lensType]) || !isset($matrix[$lensType])) {
                continue;
            }

            $tier1 = (int) ($tiers[1] ?? 0);
            $tier2 = (int) ($tiers[2] ?? $tier1);
            $tier3 = (int) ($tiers[3] ?? $tier2);
            $lastTier = (int) end($tiers);
            if ($lastTier <= 0) {
                $lastTier = max($tier1, $tier2, $tier3);
            }

            $matrix[$lensType]['basica'] = $tier1;
            $matrix[$lensType]['media'] = $tier2;
            $matrix[$lensType]['alta'] = $tier3;
            $matrix[$lensType]['premium'] = $lastTier;
        }

        return $matrix;
    }

    /** @return array<string,array<string,int>> */
    public static function matrix(): array
    {
        $base = self::defaultMatrix();
        $rows = self::pricingRows();

        foreach ($rows as $lensType => $levels) {
            foreach ($levels as $naraLevel => $price) {
                if (!isset($base[$lensType]) || !array_key_exists($naraLevel, $base[$lensType])) {
                    continue;
                }

                $base[$lensType][$naraLevel] = (int) $price;
            }
        }

        return $base;
    }
}
