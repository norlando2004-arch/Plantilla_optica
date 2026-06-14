<?php

namespace App\Http\Controllers;

use App\Models\GafaPrescription;
use App\Models\Producto;
use App\Services\Gafas\GafaLensPricing;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class GafaPrescriptionCheckoutController extends Controller
{
    public function storeAndRedirect(Request $request, Producto $producto): RedirectResponse
    {
        abort_unless(
            in_array($producto->tipo, ['gafas', 'gafas_ninas', 'gafas_ninos', 'gafas_polarizadas'], true)
                && in_array($producto->genero_objetivo, ['female', 'male', 'unisex', 'ninos', 'ninas', 'gafas_polarizadas', 'descanso'], true)
                && (bool) $producto->esta_activo,
            404
        );

        $gafitasFormula = (string) $request->input('gafitas_formula', '');
        $isFormula = $gafitasFormula === 'si_con_formula';

        $rules = [
            'no_prescription' => ['nullable', 'boolean'],
            'gafitas_formula' => [
                'required',
                'string',
                Rule::in([
                    'no_sin_formula',
                    'si_con_formula',
                    'sin_aumento_neutro',
                ]),
            ],
            'lens_type' => ['nullable', 'string', 'required_if:tipo_lente_necesitas,ocupacional,progresivos,bifocal', Rule::in(array_keys(GafaLensPricing::lensTypeOptions()))],
            'nara_level' => ['nullable', 'string', 'required_if:tipo_lente_necesitas,progresivos', Rule::in(array_keys(GafaLensPricing::naraLevelOptions()))],
            'lens_color' => ['nullable', 'string', Rule::in([
                'Gris',
                'Marrón',
                'Verde Grafito',
                'Verde esmeralda',
                'Zafiro',
                'Amatista',
                'Ambar',
                'Rubi',
            ]), 'required_if:lens_type,159_transitions_gens'],
        ];

        // Para flujo plano neutro necesitamos los rangos (ESFERA/CILINDRO)
        // para poder calcular el precio de los lentes en el checkout y en el pago.
        if (in_array($gafitasFormula, ['sin_aumento_neutro', 'no_sin_formula'], true)) {
            $rules['plano_neutro'] = ['nullable', 'boolean'];
            $rules['plano_rx_sphere_max'] = ['required', 'numeric', 'min:0', 'max:20'];
            $rules['plano_rx_cyl_max'] = ['required', 'numeric', 'min:0', 'max:20'];
        }

        if ($isFormula) {
            $rules['tipo_lente_necesitas'] = [
                'required',
                'string',
                Rule::in([
                    'con_aumento_monofocal',
                    'ocupacional',
                    'bifocal',
                    'progresivos',
                ]),
            ];
            $rules['rx_ano_nacimiento'] = ['nullable', 'integer', 'min:1900', 'max:' . now()->year];
            $rules['rx_od_esfera'] = ['required', 'numeric', 'min:-20', 'max:20'];
            $rules['rx_od_cilindro'] = ['required', 'numeric', 'min:-20', 'max:20'];
            $rules['rx_od_eje'] = ['required', 'integer', 'min:0', 'max:180'];

            $rules['rx_oi_esfera'] = ['required', 'numeric', 'min:-20', 'max:20'];
            $rules['rx_oi_cilindro'] = ['required', 'numeric', 'min:-20', 'max:20'];
            $rules['rx_oi_eje'] = ['required', 'integer', 'min:0', 'max:180'];

            $rules['rx_od_adicion'] = ['nullable', 'required_if:tipo_lente_necesitas,ocupacional,bifocal,progresivos', 'numeric', 'min:0.75', 'max:4.50'];
            $rules['rx_oi_adicion'] = ['nullable', 'numeric', 'min:0.75', 'max:4.50'];

            $rules['rx_distancia_pupilar'] = ['nullable', 'numeric', 'min:0'];
            $rules['prescription_pdf'] = [
                'required',
                'file',
                'mimes:pdf',
                'max:20480',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (!$value instanceof UploadedFile) {
                        $fail('Sube un PDF con tu fórmula.');
                        return;
                    }

                    if (!$value->isValid()) {
                        $fail('No se pudo subir el archivo (upload inválido). Intenta de nuevo.');
                        return;
                    }

                    $pathname = $value->getPathname();
                    if (!is_string($pathname) || trim($pathname) === '' || !is_file($pathname)) {
                        $fail('No se pudo subir el archivo (temporal no disponible). Intenta de nuevo.');
                    }
                },
            ];
        } else {
            $rules['tipo_lente_necesitas'] = ['nullable', 'string', Rule::in(['con_aumento_monofocal', 'ocupacional', 'bifocal', 'progresivos'])];
            $rules['rx_ano_nacimiento'] = ['nullable', 'integer', 'min:1900', 'max:' . now()->year];
            $rules['prescription_pdf'] = ['nullable', 'file', 'mimes:pdf', 'max:20480'];
        }

        $validated = $request->validate($rules, [
            'gafitas_formula.required' => 'Debes responder el PASO 1 para continuar.',
            'gafitas_formula.in' => 'Selecciona una opción válida en el PASO 1.',
            'tipo_lente_necesitas.required' => 'Debes responder el PASO 2 para continuar.',
            'tipo_lente_necesitas.in' => 'Selecciona una opción válida en el PASO 2.',
            'lens_type.required_if' => 'Debes seleccionar un lente en el PASO 4 para continuar.',
            'nara_level.required_if' => 'Debes seleccionar una categoría NARA en el PASO 4 para continuar.',
            'rx_od_esfera.required' => 'Completa Esfera (OD).',
            'rx_od_cilindro.required' => 'Completa Cilindro (OD).',
            'rx_od_eje.required' => 'Completa Eje (OD).',
            'rx_oi_esfera.required' => 'Completa Esfera (OI).',
            'rx_oi_cilindro.required' => 'Completa Cilindro (OI).',
            'rx_oi_eje.required' => 'Completa Eje (OI).',
            'rx_od_adicion.required_if' => 'Completa Adición.',
            'rx_distancia_pupilar.min' => 'La distancia pupilar no puede ser negativa.',
            'rx_ano_nacimiento.integer' => 'El año de nacimiento debe ser un número.',
            'rx_ano_nacimiento.min' => 'El año de nacimiento no es válido.',
            'rx_ano_nacimiento.max' => 'El año de nacimiento no es válido.',
            'prescription_pdf.required' => 'Debes subir tu fórmula en PDF para continuar.',
            'prescription_pdf.mimes' => 'La fórmula debe ser un PDF.',
            'prescription_pdf.max' => 'El PDF debe pesar máximo 20MB.',
            'lens_color.required_if' => 'Debes seleccionar un color para Transitions.',
            'lens_color.in' => 'Selecciona un color válido.',
        ]);
        $caracteristicas = is_array($producto->caracteristicas) ? $producto->caracteristicas : [];

        // Camino sin fórmula / neutro
        if (!$isFormula) {
            $isNoFormulaSimple = $gafitasFormula === 'no_sin_formula';

            // "No, sin fórmula" y "Sin aumento NEUTRO" usan lentes monofocales plano,
            // sin PDF pero con precio de lentes.
            // Aquí ya tenemos en $validated los rangos plano (plano_rx_*) y la selección de lente.
            $lensType = (string) ($validated['lens_type'] ?? GafaLensPricing::defaultLensType());
            if (!GafaLensPricing::isValidLensType($lensType)) {
                $lensType = GafaLensPricing::defaultLensType();
            }

            $tipoLenteNecesitas = 'con_aumento_monofocal';
            $naraLevel = (string) ($validated['nara_level'] ?? '');
            $lensColor = (string) ($validated['lens_color'] ?? '');
            $frameColor = trim((string) $request->input('frame_color', ''));
            if ($lensType !== '159_transitions_gens') {
                $lensColor = '';
            }

            $planoNeutro = (bool) ($validated['plano_neutro'] ?? true);
            $planoRxSphere = (float) abs((float) ($validated['plano_rx_sphere_max'] ?? 0));
            $planoRxCyl = (float) abs((float) ($validated['plano_rx_cyl_max'] ?? 0));

            $params = [
                'producto' => $producto->slug,
                'no_prescription' => 0,
                'plano_neutro' => $planoNeutro ? 1 : 0,
                'plano_rx_sphere_max' => $planoRxSphere,
                'plano_rx_cyl_max' => $planoRxCyl,
                'tipo_lente_necesitas' => $tipoLenteNecesitas,
                'lens_type' => $lensType,
                'nara_level' => $naraLevel,
                'lens_color' => $lensColor,
                'frame_color' => $frameColor,
                'no_formula_simple' => $isNoFormulaSimple ? 1 : 0,
            ];

            $redirectTo = route('checkout.gafa', $params);

            return redirect()->to($redirectTo);
        }

        /** @var UploadedFile $archivo */
        $archivo = $validated['prescription_pdf'];

        $lensType = (string) ($validated['lens_type'] ?? GafaLensPricing::defaultLensType());
        if (!GafaLensPricing::isValidLensType($lensType)) {
            $lensType = GafaLensPricing::defaultLensType();
        }

        $tipoLenteNecesitas = (string) ($validated['tipo_lente_necesitas'] ?? GafaLensPricing::TIPO_LENTE_PROGRESIVOS);
        $tipoLenteNecesitas = GafaLensPricing::sanitizeLensDesignForCharacteristics($caracteristicas, $tipoLenteNecesitas);
        $polyEnabled = GafaLensPricing::usesPolyForCharacteristics($caracteristicas);
        if (!GafaLensPricing::isLensDesignAllowedForCharacteristics($caracteristicas, $validated['tipo_lente_necesitas'] ?? null)) {
            return back()
                ->withInput()
                ->withErrors([
                    'tipo_lente_necesitas' => 'La montura seleccionada no es compatible con ese tipo de lente.',
                ]);
        }

        if (!GafaLensPricing::isLensTypeAllowedFor($tipoLenteNecesitas, $lensType, $polyEnabled)) {
            $lensType = GafaLensPricing::defaultLensTypeFor($tipoLenteNecesitas, $polyEnabled);
        }

        $naraLevel = (string) ($validated['nara_level'] ?? GafaLensPricing::defaultNaraLevel());
        if (!GafaLensPricing::isValidNaraLevel($naraLevel)) {
            $naraLevel = GafaLensPricing::defaultNaraLevel();
        }

        $frameColor = trim((string) $request->input('frame_color', ''));

        $lensColor = (string) ($validated['lens_color'] ?? '');
        if ($lensType !== '159_transitions_gens') {
            $lensColor = '';
        }

        $analysis = [
            'upload_mode' => 'no_ocr',
            'note' => 'PDF guardado sin verificación OCR del contenido.',
        ];

        $sessionId = $request->session()->getId();
        $userId = Auth::id();

        $disk = Storage::disk('local');
        $disk->makeDirectory('prescriptions');

        $uuid = (string) Str::uuid();
        $storedPath = 'prescriptions/' . $uuid . '.pdf';

        $pathname = $archivo->getPathname();
        $sha256 = null;
        try {
            if (is_string($pathname) && is_file($pathname)) {
                $sha256 = @hash_file('sha256', $pathname) ?: null;
            }
        } catch (\Throwable) {
            $sha256 = null;
        }

        $stream = @fopen($pathname, 'rb');
        if ($stream === false) {
            return back()
                ->withInput()
                ->withErrors([
                    'prescription_pdf' => 'No pude leer el PDF subido. Intenta seleccionarlo nuevamente.',
                ]);
        }

        try {
            $ok = $disk->put($storedPath, $stream);
        } finally {
            try {
                if (is_resource($stream)) {
                    fclose($stream);
                }
            } catch (\Throwable) {
                // noop
            }
        }

        if ($ok !== true) {
            return back()
                ->withInput()
                ->withErrors([
                    'prescription_pdf' => 'No se pudo guardar tu PDF. Intenta de nuevo.',
                ]);
        }

        // Adjuntar, si existen, los campos que el usuario escribió manualmente en el PASO 3
        // (no se usan para el cálculo de precio, pero quedan guardados como referencia).
        $rxOdAdicion = (string) $request->input('rx_od_adicion', '');
        $rxOiAdicion = (string) $request->input('rx_oi_adicion', '');
        if ($rxOdAdicion === '' && $rxOiAdicion !== '') {
            $rxOdAdicion = $rxOiAdicion;
        }
        if ($rxOiAdicion === '' && $rxOdAdicion !== '') {
            $rxOiAdicion = $rxOdAdicion;
        }

        $sharedDnp = (string) $request->input('rx_distancia_pupilar', '');

        $manualInput = [
            'od' => [
                'esfera' => (string) $request->input('rx_od_esfera', ''),
                'cilindro' => (string) $request->input('rx_od_cilindro', ''),
                'eje' => (string) $request->input('rx_od_eje', ''),
                'adicion' => $rxOdAdicion,
                'dnp' => $sharedDnp,
            ],
            'oi' => [
                'esfera' => (string) $request->input('rx_oi_esfera', ''),
                'cilindro' => (string) $request->input('rx_oi_cilindro', ''),
                'eje' => (string) $request->input('rx_oi_eje', ''),
                'adicion' => $rxOiAdicion,
                'dnp' => $sharedDnp,
            ],
            'distancia_pupilar' => $sharedDnp,
            'ano_nacimiento' => $validated['rx_ano_nacimiento'] ?? null,
        ];

        $analysis = is_array($analysis) ? $analysis : [];
        $analysis['manual_input'] = $manualInput;

        $prescription = GafaPrescription::query()->create([
            'user_id' => $userId,
            'session_id' => $sessionId,
            'storage_disk' => 'local',
            'storage_path' => $storedPath,
            'original_name' => (string) $archivo->getClientOriginalName(),
            'mime' => $archivo->getMimeType(),
            'size' => $archivo->getSize(),
            'sha256' => $sha256,
            'analysis' => $analysis,
        ]);

        $redirectTo = route('checkout.gafa', [
            'producto' => $producto->slug,
            'tipo_lente_necesitas' => $tipoLenteNecesitas,
            'lens_type' => $lensType,
            'nara_level' => $naraLevel,
            'prescription_id' => $prescription->id,
            'lens_color' => $lensColor,
            'frame_color' => $frameColor,
        ]);

        return redirect()->to($redirectTo);
    }

}
