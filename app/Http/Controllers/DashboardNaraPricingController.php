<?php

namespace App\Http\Controllers;

use App\Models\GafaLensPrice;
use App\Services\Gafas\GafaLensPricing;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardNaraPricingController extends Controller
{
    private function parsePriceToInt(mixed $raw): ?int
    {
        $text = trim((string) ($raw ?? ''));
        if ($text === '') {
            return null;
        }

        // Permitir formatos como: 790.000 / 790,000 / $ 790.000
        $digits = preg_replace('/\D+/', '', $text);
        if ($digits === null || $digits === '') {
            return null;
        }

        $num = (int) $digits;
        if ($num < 0) {
            return null;
        }

        // Evitar overflow de int32 en Postgres (integer)
        if ($num > 2147483647) {
            return null;
        }

        return $num;
    }

    public function edit(): View
    {
        return view('dashboard.precios_naratodo', [
            'lensTypeOptions' => GafaLensPricing::lensTypeOptions(),
            'naraOptions' => GafaLensPricing::naraLevelOptions(),
            'matrix' => GafaLensPricing::matrix(),
            'progresivosLensTypes' => GafaLensPricing::progresivosLensTypes(),
            'monofocalTiered' => GafaLensPricing::monofocalTieredPricingTable(),
            'monofocalFixed' => GafaLensPricing::monofocalFixedPricingTable(),
            'monofocalTransitions' => GafaLensPricing::monofocalTransitionsPricing(),
            'bifocalFixed' => GafaLensPricing::bifocalFixedPricingTable(),
            'ocupacionalFixed' => GafaLensPricing::ocupacionalFixedPricingTable(),
            'noFormulaFixed' => GafaLensPricing::noFormulaPricingTable(),
            'polyTiered' => GafaLensPricing::polyTieredPricingTable(),
            'polyNoFormula' => GafaLensPricing::polyNoFormulaPricingTable(),
            'polyProgresivos' => GafaLensPricing::polyProgresivosPricingTable(),
            'polyOcupacional' => GafaLensPricing::polyOcupacionalFixedPricingTable(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $lensTypeOptions = GafaLensPricing::lensTypeOptions();
        $naraOptions = GafaLensPricing::naraLevelOptions();
        $progresivosLensTypes = GafaLensPricing::progresivosLensTypes();
        $monofocalTieredDefaults = GafaLensPricing::monofocalTieredPricingTable();
        $monofocalFixedDefaults = GafaLensPricing::monofocalFixedPricingTable();
        $bifocalFixedDefaults = GafaLensPricing::bifocalFixedPricingTable();
        $ocupacionalFixedDefaults = GafaLensPricing::ocupacionalFixedPricingTable();
        $noFormulaDefaults = GafaLensPricing::noFormulaPricingTable();
        $polyTieredDefaults = GafaLensPricing::polyTieredPricingTable();
        $polyNoFormulaDefaults = GafaLensPricing::polyNoFormulaPricingTable();
        $polyProgresivosDefaults = GafaLensPricing::polyProgresivosPricingTable();
        $polyOcupacionalDefaults = GafaLensPricing::polyOcupacionalFixedPricingTable();

        $prices = $request->input('prices');
        if (!is_array($prices)) {
            return back()->withErrors(['prices' => 'Los precios enviados no son válidos.'])->withInput();
        }

        $monofocalTiered = $request->input('monofocal_tiered', []);
        $monofocalFixed = $request->input('monofocal_fixed', []);
        $bifocalFixed = $request->input('bifocal_fixed', []);
        $ocupacionalFixed = $request->input('ocupacional_fixed', []);
        $noFormulaPrices = $request->input('no_formula_prices', []);
        $monofocalTransitions = $request->input('monofocal_transitions', []);
        $polyTiered = $request->input('poly_tiered', []);
        $polyNoFormula = $request->input('poly_no_formula_prices', []);
        $polyProgresivos = $request->input('poly_progresivos_prices', []);
        $polyOcupacional = $request->input('poly_ocupacional_fixed', []);

        if (!is_array($monofocalTiered) || !is_array($monofocalFixed) || !is_array($bifocalFixed) || !is_array($ocupacionalFixed) || !is_array($noFormulaPrices) || !is_array($monofocalTransitions) || !is_array($polyTiered) || !is_array($polyNoFormula) || !is_array($polyProgresivos) || !is_array($polyOcupacional)) {
            return back()->withErrors(['prices' => 'Los bloques de precios enviados no son válidos.'])->withInput();
        }

        $errors = [];
        foreach ($progresivosLensTypes as $lensKey) {
            if (!isset($lensTypeOptions[$lensKey])) {
                continue;
            }

            foreach ($naraOptions as $naraKey => $_label) {
                $raw = $prices[$lensKey][$naraKey] ?? null;
                if ($raw === null || trim((string) $raw) === '') {
                    $errors["prices.$lensKey.$naraKey"] = 'Este campo es obligatorio.';
                    continue;
                }

                $num = $this->parsePriceToInt($raw);
                if ($num === null) {
                    $errors["prices.$lensKey.$naraKey"] = 'Debe ser un número válido (ej: 790.000).';
                }
            }
        }

        foreach ($monofocalTieredDefaults as $lensKey => $_tiers) {
            foreach (['tier1', 'tier2', 'tier3'] as $tierKey) {
                $raw = $monofocalTiered[$lensKey][$tierKey] ?? null;
                if ($raw === null || trim((string) $raw) === '') {
                    $errors["monofocal_tiered.$lensKey.$tierKey"] = 'Este campo es obligatorio.';
                    continue;
                }

                if ($this->parsePriceToInt($raw) === null) {
                    $errors["monofocal_tiered.$lensKey.$tierKey"] = 'Debe ser un número válido.';
                }
            }
        }

        foreach ($monofocalFixedDefaults as $lensKey => $_default) {
            $raw = $monofocalFixed[$lensKey] ?? null;
            if ($raw === null || trim((string) $raw) === '') {
                $errors["monofocal_fixed.$lensKey"] = 'Este campo es obligatorio.';
                continue;
            }

            if ($this->parsePriceToInt($raw) === null) {
                $errors["monofocal_fixed.$lensKey"] = 'Debe ser un número válido.';
            }
        }

        foreach ($bifocalFixedDefaults as $lensKey => $_default) {
            $raw = $bifocalFixed[$lensKey] ?? null;
            if ($raw === null || trim((string) $raw) === '') {
                $errors["bifocal_fixed.$lensKey"] = 'Este campo es obligatorio.';
                continue;
            }

            if ($this->parsePriceToInt($raw) === null) {
                $errors["bifocal_fixed.$lensKey"] = 'Debe ser un número válido.';
            }
        }

        foreach ($ocupacionalFixedDefaults as $lensKey => $_default) {
            $raw = $ocupacionalFixed[$lensKey] ?? null;
            if ($raw === null || trim((string) $raw) === '') {
                $errors["ocupacional_fixed.$lensKey"] = 'Este campo es obligatorio.';
                continue;
            }

            if ($this->parsePriceToInt($raw) === null) {
                $errors["ocupacional_fixed.$lensKey"] = 'Debe ser un número válido.';
            }
        }

        foreach ($noFormulaDefaults as $lensKey => $_default) {
            $raw = $noFormulaPrices[$lensKey] ?? null;
            if ($raw === null || trim((string) $raw) === '') {
                $errors["no_formula_prices.$lensKey"] = 'Este campo es obligatorio.';
                continue;
            }

            if ($this->parsePriceToInt($raw) === null) {
                $errors["no_formula_prices.$lensKey"] = 'Debe ser un número válido.';
            }
        }

        foreach (['tier1', 'tier2', 'with_color'] as $transitionKey) {
            $raw = $monofocalTransitions[$transitionKey] ?? null;
            if ($raw === null || trim((string) $raw) === '') {
                $errors["monofocal_transitions.$transitionKey"] = 'Este campo es obligatorio.';
                continue;
            }

            if ($this->parsePriceToInt($raw) === null) {
                $errors["monofocal_transitions.$transitionKey"] = 'Debe ser un número válido.';
            }
        }

        foreach ($polyTieredDefaults as $lensKey => $_tiers) {
            foreach (['tier1', 'tier2', 'tier3', 'tier4'] as $tierKey) {
                $raw = $polyTiered[$lensKey][$tierKey] ?? null;
                if ($raw === null || trim((string) $raw) === '') {
                    $errors["poly_tiered.$lensKey.$tierKey"] = 'Este campo es obligatorio.';
                    continue;
                }

                if ($this->parsePriceToInt($raw) === null) {
                    $errors["poly_tiered.$lensKey.$tierKey"] = 'Debe ser un número válido.';
                }
            }
        }

        foreach ($polyNoFormulaDefaults as $lensKey => $_default) {
            $raw = $polyNoFormula[$lensKey] ?? null;
            if ($raw === null || trim((string) $raw) === '') {
                $errors["poly_no_formula_prices.$lensKey"] = 'Este campo es obligatorio.';
                continue;
            }

            if ($this->parsePriceToInt($raw) === null) {
                $errors["poly_no_formula_prices.$lensKey"] = 'Debe ser un número válido.';
            }
        }

        foreach ($polyProgresivosDefaults as $lensKey => $_levels) {
            foreach (['basica', 'media', 'alta'] as $levelKey) {
                $raw = $polyProgresivos[$lensKey][$levelKey] ?? null;
                if ($raw === null || trim((string) $raw) === '') {
                    $errors["poly_progresivos_prices.$lensKey.$levelKey"] = 'Este campo es obligatorio.';
                    continue;
                }

                if ($this->parsePriceToInt($raw) === null) {
                    $errors["poly_progresivos_prices.$lensKey.$levelKey"] = 'Debe ser un número válido.';
                }
            }
        }

        foreach ($polyOcupacionalDefaults as $lensKey => $_default) {
            $raw = $polyOcupacional[$lensKey] ?? null;
            if ($raw === null || trim((string) $raw) === '') {
                $errors["poly_ocupacional_fixed.$lensKey"] = 'Este campo es obligatorio.';
                continue;
            }

            if ($this->parsePriceToInt($raw) === null) {
                $errors["poly_ocupacional_fixed.$lensKey"] = 'Debe ser un número válido.';
            }
        }

        if (!empty($errors)) {
            return back()->withErrors($errors)->withInput();
        }

        $rows = [];
        foreach ($progresivosLensTypes as $lensKey) {
            if (!isset($lensTypeOptions[$lensKey])) {
                continue;
            }

            foreach ($naraOptions as $naraKey => $_label) {
                $parsed = $this->parsePriceToInt($prices[$lensKey][$naraKey] ?? null);
                $parsed = $parsed ?? 0;
                $rows[] = [
                    'lens_type' => (string) $lensKey,
                    'nara_level' => (string) $naraKey,
                    'price' => $parsed,
                    'updated_at' => now(),
                    'created_at' => now(),
                ];
            }
        }

        foreach ($monofocalTieredDefaults as $lensKey => $_tiers) {
            $rows[] = [
                'lens_type' => (string) $lensKey,
                'nara_level' => 'mono_tier1',
                'price' => (int) ($this->parsePriceToInt($monofocalTiered[$lensKey]['tier1'] ?? null) ?? 0),
                'updated_at' => now(),
                'created_at' => now(),
            ];
            $rows[] = [
                'lens_type' => (string) $lensKey,
                'nara_level' => 'mono_tier2',
                'price' => (int) ($this->parsePriceToInt($monofocalTiered[$lensKey]['tier2'] ?? null) ?? 0),
                'updated_at' => now(),
                'created_at' => now(),
            ];
            $rows[] = [
                'lens_type' => (string) $lensKey,
                'nara_level' => 'mono_tier3',
                'price' => (int) ($this->parsePriceToInt($monofocalTiered[$lensKey]['tier3'] ?? null) ?? 0),
                'updated_at' => now(),
                'created_at' => now(),
            ];
        }

        foreach ($monofocalFixedDefaults as $lensKey => $_default) {
            $rows[] = [
                'lens_type' => (string) $lensKey,
                'nara_level' => 'mono_fixed',
                'price' => (int) ($this->parsePriceToInt($monofocalFixed[$lensKey] ?? null) ?? 0),
                'updated_at' => now(),
                'created_at' => now(),
            ];
        }

        foreach ($bifocalFixedDefaults as $lensKey => $_default) {
            $rows[] = [
                'lens_type' => (string) $lensKey,
                'nara_level' => 'bifocal_fixed',
                'price' => (int) ($this->parsePriceToInt($bifocalFixed[$lensKey] ?? null) ?? 0),
                'updated_at' => now(),
                'created_at' => now(),
            ];
        }

        foreach ($ocupacionalFixedDefaults as $lensKey => $_default) {
            $rows[] = [
                'lens_type' => (string) $lensKey,
                'nara_level' => 'ocupacional_fixed',
                'price' => (int) ($this->parsePriceToInt($ocupacionalFixed[$lensKey] ?? null) ?? 0),
                'updated_at' => now(),
                'created_at' => now(),
            ];
        }

        foreach ($noFormulaDefaults as $lensKey => $_default) {
            $rows[] = [
                'lens_type' => (string) $lensKey,
                'nara_level' => 'no_formula_fixed',
                'price' => (int) ($this->parsePriceToInt($noFormulaPrices[$lensKey] ?? null) ?? 0),
                'updated_at' => now(),
                'created_at' => now(),
            ];
        }

        $rows[] = [
            'lens_type' => '159_transitions_gens',
            'nara_level' => 'mono_trans_tier1',
            'price' => (int) ($this->parsePriceToInt($monofocalTransitions['tier1'] ?? null) ?? 0),
            'updated_at' => now(),
            'created_at' => now(),
        ];
        $rows[] = [
            'lens_type' => '159_transitions_gens',
            'nara_level' => 'mono_trans_tier2',
            'price' => (int) ($this->parsePriceToInt($monofocalTransitions['tier2'] ?? null) ?? 0),
            'updated_at' => now(),
            'created_at' => now(),
        ];
        $rows[] = [
            'lens_type' => '159_transitions_gens',
            'nara_level' => 'mono_trans_with_color',
            'price' => (int) ($this->parsePriceToInt($monofocalTransitions['with_color'] ?? null) ?? 0),
            'updated_at' => now(),
            'created_at' => now(),
        ];

        foreach ($polyTieredDefaults as $lensKey => $_tiers) {
            $rows[] = [
                'lens_type' => (string) $lensKey,
                'nara_level' => 'poly_tier1',
                'price' => (int) ($this->parsePriceToInt($polyTiered[$lensKey]['tier1'] ?? null) ?? 0),
                'updated_at' => now(),
                'created_at' => now(),
            ];
            $rows[] = [
                'lens_type' => (string) $lensKey,
                'nara_level' => 'poly_tier2',
                'price' => (int) ($this->parsePriceToInt($polyTiered[$lensKey]['tier2'] ?? null) ?? 0),
                'updated_at' => now(),
                'created_at' => now(),
            ];
            $rows[] = [
                'lens_type' => (string) $lensKey,
                'nara_level' => 'poly_tier3',
                'price' => (int) ($this->parsePriceToInt($polyTiered[$lensKey]['tier3'] ?? null) ?? 0),
                'updated_at' => now(),
                'created_at' => now(),
            ];
            $rows[] = [
                'lens_type' => (string) $lensKey,
                'nara_level' => 'poly_tier4',
                'price' => (int) ($this->parsePriceToInt($polyTiered[$lensKey]['tier4'] ?? null) ?? 0),
                'updated_at' => now(),
                'created_at' => now(),
            ];
        }

        foreach ($polyNoFormulaDefaults as $lensKey => $_default) {
            $rows[] = [
                'lens_type' => (string) $lensKey,
                'nara_level' => 'poly_no_formula_fixed',
                'price' => (int) ($this->parsePriceToInt($polyNoFormula[$lensKey] ?? null) ?? 0),
                'updated_at' => now(),
                'created_at' => now(),
            ];
        }

        foreach ($polyProgresivosDefaults as $lensKey => $_levels) {
            foreach (['basica', 'media', 'alta'] as $levelKey) {
                $rows[] = [
                    'lens_type' => (string) $lensKey,
                    'nara_level' => 'poly_prog_' . $levelKey,
                    'price' => (int) ($this->parsePriceToInt($polyProgresivos[$lensKey][$levelKey] ?? null) ?? 0),
                    'updated_at' => now(),
                    'created_at' => now(),
                ];
            }
        }

        foreach ($polyOcupacionalDefaults as $lensKey => $_default) {
            $rows[] = [
                'lens_type' => (string) $lensKey,
                'nara_level' => 'poly_ocupacional_fixed',
                'price' => (int) ($this->parsePriceToInt($polyOcupacional[$lensKey] ?? null) ?? 0),
                'updated_at' => now(),
                'created_at' => now(),
            ];
        }

        try {
            GafaLensPrice::query()->upsert(
                $rows,
                ['lens_type', 'nara_level'],
                ['price', 'updated_at']
            );
        } catch (QueryException) {
            return back()
                ->withErrors(['prices' => 'No se pudo guardar porque falta la tabla de precios. Ejecuta las migraciones (php artisan migrate) e intenta de nuevo.'])
                ->withInput();
        }

        return redirect()
            ->route('dashboard.precios-naratodo.edit')
            ->with('status', 'Precios actualizados correctamente.');
    }
}
