<?php

namespace App\Http\Controllers;

use App\Services\Bold\BoldLinkClient;
use App\Models\Carrito;
use App\Models\GafaPrescription;
use App\Models\ItemCarrito;
use App\Models\Pago;
use App\Models\PerfilCliente;
use App\Models\Producto;
use App\Services\GuestShopperService;
use App\Services\Gafas\GafaLensPricing;
use App\Services\Pagos\PagoFulfillmentService;
use App\Services\Pagos\PagoPostApprovalService;
use App\Services\Pagos\PasarelaPagoFactory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PagosController extends Controller
{
    public function startGuest(Request $request, Producto $producto): RedirectResponse
    {
        abort_unless(
            in_array($producto->tipo, ['gafas', 'gafas_ninas', 'gafas_ninos', 'gafas_polarizadas'], true)
                && in_array($producto->genero_objetivo, ['female', 'male', 'unisex', 'ninos', 'ninas', 'gafas_polarizadas', 'descanso'], true)
                && (bool) $producto->esta_activo,
            404
        );

        $precioBase = $producto->precio_oferta ?? $producto->precio;
        if ($precioBase === null) {
            return redirect()
                ->route('gafas.show', $producto)
                ->with('status', 'Este producto aún no tiene precio configurado.');
        }

        $caracteristicas = is_array($producto->caracteristicas) ? $producto->caracteristicas : [];
        $polyEnabled = GafaLensPricing::usesPolyForCharacteristics($caracteristicas);

        $caracteristicas = is_array($producto->caracteristicas) ? $producto->caracteristicas : [];

        $planoNeutro = $request->boolean('plano_neutro');
        $noPrescription = $request->boolean('no_prescription');
        if ($planoNeutro) {
            $noPrescription = false;
        }

        $validated = $request->validate([
            'no_prescription' => ['nullable', 'boolean'],
            'plano_neutro' => ['nullable', 'boolean'],
            'plano_rx_sphere_max' => ['nullable', 'numeric', 'min:0', 'max:20', 'required_if:plano_neutro,1'],
            'plano_rx_cyl_max' => ['nullable', 'numeric', 'min:0', 'max:20', 'required_if:plano_neutro,1'],
            'correo' => ['nullable', 'email', 'max:190'],
            'nombres' => ['required', 'string', 'max:120'],
            'apellidos' => ['required', 'string', 'max:120'],
            'tipo_documento' => ['required', 'string', 'max:30'],
            'numero_documento' => ['required', 'string', 'max:60'],
            'telefono' => ['required', 'string', 'max:40'],
            'direccion' => ['required', 'string'],
            'ciudad' => ['required', 'string', 'max:80'],
            'fecha_nacimiento' => ['nullable', 'date'],
            'genero' => ['nullable', 'string', 'max:20'],
            'notas' => ['nullable', 'string'],
            'tipo_lente_necesitas' => ['nullable', 'string', Rule::in([
                GafaLensPricing::TIPO_LENTE_MONOFOCAL,
                GafaLensPricing::TIPO_LENTE_PROGRESIVOS,
                'ocupacional',
                'bifocal',
            ])],
            'lens_type' => ['nullable', 'string', 'required_if:tipo_lente_necesitas,ocupacional,progresivos,bifocal', Rule::in(array_keys(GafaLensPricing::lensTypeOptions()))],
            'nara_level' => ['nullable', 'string', 'required_if:tipo_lente_necesitas,progresivos', Rule::in(array_keys(GafaLensPricing::naraLevelOptions()))],
            'lens_color' => ['nullable', 'string', Rule::in(['Gris', 'Marrón', 'Verde Grafito', 'Verde esmeralda', 'Zafiro', 'Amatista', 'Ambar', 'Rubi']), 'required_if:lens_type,159_transitions_gens'],
            'prescription_id' => ($noPrescription || $planoNeutro) ? ['nullable', 'integer'] : ['required', 'integer'],
        ]);

        $caracteristicas = is_array($producto->caracteristicas) ? $producto->caracteristicas : [];

        $caracteristicas = is_array($producto->caracteristicas) ? $producto->caracteristicas : [];

        $tipoLenteNecesitas = '';
        $lensType = '';
        $naraLevel = '';
        $lensColor = '';
        $precioLentes = 0.0;
        if (!$noPrescription) {
            if ($planoNeutro) {
                $tipoLenteNecesitas = GafaLensPricing::TIPO_LENTE_MONOFOCAL;
            }
            $tipoLenteNecesitas = (string) ($validated['tipo_lente_necesitas'] ?? GafaLensPricing::TIPO_LENTE_PROGRESIVOS);
            if (!in_array($tipoLenteNecesitas, [GafaLensPricing::TIPO_LENTE_MONOFOCAL, GafaLensPricing::TIPO_LENTE_PROGRESIVOS, 'ocupacional', 'bifocal'], true)) {
                $tipoLenteNecesitas = GafaLensPricing::TIPO_LENTE_PROGRESIVOS;
            }

            if (!GafaLensPricing::isLensDesignAllowedForCharacteristics($caracteristicas, $tipoLenteNecesitas)) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'tipo_lente_necesitas' => 'La montura seleccionada no es compatible con ese tipo de lente.',
                    ]);
            }

            if ($planoNeutro) {
                $tipoLenteNecesitas = GafaLensPricing::TIPO_LENTE_MONOFOCAL;
            }

            $lensType = (string) ($validated['lens_type'] ?? GafaLensPricing::defaultLensType());
            if (!GafaLensPricing::isValidLensType($lensType)) {
                $lensType = GafaLensPricing::defaultLensType();
            }

            if (!GafaLensPricing::isLensTypeAllowedFor($tipoLenteNecesitas, $lensType, $polyEnabled)) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'lens_type' => 'La opción de lente seleccionada no es válida para el tipo de lente elegido.',
                    ]);
            }

            $naraLevel = (string) ($validated['nara_level'] ?? GafaLensPricing::defaultNaraLevel());
            if (!GafaLensPricing::isValidNaraLevel($naraLevel)) {
                $naraLevel = GafaLensPricing::defaultNaraLevel();
            }
            if ($polyEnabled && $tipoLenteNecesitas === GafaLensPricing::TIPO_LENTE_PROGRESIVOS && !in_array($naraLevel, ['basica', 'media', 'alta'], true)) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'nara_level' => 'Para Poly, en progresivos solo están disponibles Básica, Media y Alta.',
                    ]);
            }

            $lensColor = (string) ($validated['lens_color'] ?? '');
            if ($lensType !== '159_transitions_gens') {
                $lensColor = '';
            }
        }

        $sessionId = $request->session()->getId();

        $prescriptionId = null;
        $rxSphereMax = 0.0;
        $rxCylMax = 0.0;
        if (!$noPrescription && !$planoNeutro) {
            $prescriptionId = (int) $validated['prescription_id'];
            $prescription = GafaPrescription::query()
                ->whereKey($prescriptionId)
                ->whereNull('user_id')
                ->where('session_id', $sessionId)
                ->first();

            if (!$prescription) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'prescription_id' => 'Debes subir tu fórmula en PDF para continuar.',
                    ]);
            }

            $rx = GafaLensPricing::rxMaxAbsFromAnalysis((array) ($prescription->analysis ?? []));
            $rxSphereMax = (float) ($rx['sphere'] ?? 0);
            $rxCylMax = (float) ($rx['cyl'] ?? 0);

            if ($tipoLenteNecesitas === GafaLensPricing::TIPO_LENTE_MONOFOCAL) {
                $lensColor = is_string($request->input('lens_color')) ? $request->input('lens_color') : null;
                $precioLentes = (float) GafaLensPricing::monofocalLensPrice($lensType, $rxSphereMax, $rxCylMax, $lensColor, $polyEnabled);
                $naraLevel = '';
            } elseif ($tipoLenteNecesitas === GafaLensPricing::TIPO_LENTE_BIFOCAL) {
                $precioLentes = (float) GafaLensPricing::bifocalLensPrice($lensType, $rxSphereMax, $rxCylMax, $polyEnabled);
                $naraLevel = '';
            } elseif ($tipoLenteNecesitas === GafaLensPricing::TIPO_LENTE_OCUPACIONAL) {
                $precioLentes = (float) GafaLensPricing::ocupacionalLensPrice($lensType, $polyEnabled);
                $naraLevel = '';
            } else {
                $precioLentes = (float) GafaLensPricing::lensPrice($lensType, $naraLevel, $polyEnabled);
            }
        } elseif ($planoNeutro) {
            $tipoLenteNecesitas = GafaLensPricing::TIPO_LENTE_MONOFOCAL;
            $rxSphereMax = (float) abs((float) ($validated['plano_rx_sphere_max'] ?? 0));
            $rxCylMax = (float) abs((float) ($validated['plano_rx_cyl_max'] ?? 0));
            $precioLentes = (float) GafaLensPricing::noFormulaLensPrice($lensType, $polyEnabled);
            $naraLevel = '';
        }

        $precioTotal = (float) $precioBase + (float) $precioLentes;

        $nombres = trim((string) ($validated['nombres'] ?? ''));
        $apellidos = trim((string) ($validated['apellidos'] ?? ''));
        $nombreCompleto = trim($nombres . ' ' . $apellidos);

        $guestData = [
            'nombre' => $nombreCompleto,
            'nombres' => $nombres,
            'apellidos' => $apellidos,
            'correo' => ($validated['correo'] ?? null) ? trim((string) $validated['correo']) : null,
            'tipo_documento' => $validated['tipo_documento'],
            'numero_documento' => $validated['numero_documento'],
            'telefono' => $validated['telefono'],
            'direccion' => $validated['direccion'],
            'ciudad' => $validated['ciudad'],
            'fecha_nacimiento' => $validated['fecha_nacimiento'] ?? null,
            'genero' => $validated['genero'] ?? null,
            'notas' => $validated['notas'] ?? null,
        ];

        $productMeta = is_array($producto->meta) ? $producto->meta : [];
        $productFeatures = is_array($producto->caracteristicas) ? $producto->caracteristicas : [];
        $frameColor = $this->normalizeFrameColorValue(
            $request->input('frame_color'),
            (string) ($productMeta['color'] ?? ($productMeta['color_nombre'] ?? ($producto->color ?? '')))
        );

        if (! $producto->tieneStockParaColor($frameColor, 1)) {
            $status = $frameColor !== ''
                ? 'El color ' . $frameColor . ' está agotado o ya no tiene unidades disponibles.'
                : 'Este producto está agotado y no se puede pagar en este momento.';

            return back()->withInput()->with('status', $status);
        }

        $frameData = [
            'producto_id' => $producto->id,
            'nombre' => $producto->nombre,
            'slug' => $producto->slug,
            'tipo' => $producto->tipo,
            'genero_objetivo' => $producto->genero_objetivo,
            'marca' => $producto->marca,
            'material_montura' => $producto->material_montura,
            'color' => $frameColor,
            'descripcion' => $producto->descripcion,
            'caracteristicas' => $productFeatures,
        ];

        $carrito = Carrito::query()->create([
            'usuario_id' => null,
            'sesion_id' => $sessionId,
            'guest_token' => GuestShopperService::ensureGuestToken($request),
            'estado' => 'abierto',
            'moneda' => $producto->moneda ?? 'COP',
            'subtotal' => $precioTotal,
            'total_descuento' => 0,
            'total' => $precioTotal,
            'meta' => [
                'source' => 'gafas_detail_guest',
                'cliente' => $guestData,
                'montura' => $frameData,
                'prescription_id' => $prescriptionId,
                'lentes' => [
                    'tipo_lente_necesitas' => $tipoLenteNecesitas,
                    'lens_type' => $lensType,
                    'nara_level' => $naraLevel,
                    'poly_enabled' => $polyEnabled,
                    'color' => $lensColor,
                    'rx_sphere_max' => $rxSphereMax,
                    'rx_cyl_max' => $rxCylMax,
                    'precio_montura' => (float) $precioBase,
                    'precio_lentes' => (float) $precioLentes,
                    'precio_total' => (float) $precioTotal,
                ],
            ],
        ]);

        ItemCarrito::query()->create([
            'carrito_id' => $carrito->id,
            'producto_id' => $producto->id,
            'nombre_producto' => $producto->nombre,
            'precio_unitario' => $precioTotal,
            'cantidad' => 1,
            'moneda' => $carrito->moneda,
            'meta' => [
                'slug' => $producto->slug,
                'tipo' => $producto->tipo,
                'genero_objetivo' => $producto->genero_objetivo,
                'frame_color' => $frameColor,
                'prescription_id' => $prescriptionId,
                'lentes' => [
                    'tipo_lente_necesitas' => $tipoLenteNecesitas,
                    'lens_type' => $lensType,
                    'nara_level' => $naraLevel,
                    'poly_enabled' => $polyEnabled,
                    'color' => $lensColor,
                    'rx_sphere_max' => $rxSphereMax,
                    'rx_cyl_max' => $rxCylMax,
                    'precio_montura' => (float) $precioBase,
                    'precio_lentes' => (float) $precioLentes,
                    'precio_total' => (float) $precioTotal,
                ],
            ],
        ]);

        $driver = (string) config('pagos.driver', 'dummy');

        $pago = Pago::query()->create([
            'carrito_id' => $carrito->id,
            'estado' => 'pendiente',
            'pasarela' => $driver,
            'moneda' => $carrito->moneda,
            'monto' => $carrito->total,
            'referencia' => 'PAY-'.Str::ulid()->toBase32(),
            'meta' => [
                'producto_id' => $producto->id,
                'producto_slug' => $producto->slug,
                'cliente' => $guestData,
                'montura' => $frameData,
                'prescription_id' => $prescriptionId,
                'lentes' => [
                    'tipo_lente_necesitas' => $tipoLenteNecesitas,
                    'lens_type' => $lensType,
                    'nara_level' => $naraLevel,
                    'poly_enabled' => $polyEnabled,
                    'color' => $lensColor,
                    'rx_sphere_max' => $rxSphereMax,
                    'rx_cyl_max' => $rxCylMax,
                    'precio_montura' => (float) $precioBase,
                    'precio_lentes' => (float) $precioLentes,
                    'precio_total' => (float) $precioTotal,
                ],
                'guest' => $guestData,
            ],
        ]);

        try {
            $pasarela = PasarelaPagoFactory::make($driver);
            $redirectUrl = $pasarela->iniciar($pago);
            return redirect()->away($redirectUrl);
        } catch (\Throwable $e) {
            Log::error('Error iniciando pago invitado', [
                'driver' => $driver,
                'pago_id' => $pago->id,
                'ref' => $pago->referencia,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('checkout.gafa', ['producto' => $producto->slug])
                ->with('status', 'No se pudo iniciar el pago. Intenta nuevamente en unos minutos.');
        }
    }

    public function start(Request $request, Producto $producto): RedirectResponse
    {
        if (!Auth::check()) {
            return $this->startGuest($request, $producto);
        }

        abort_unless(
            in_array($producto->tipo, ['gafas', 'gafas_ninas', 'gafas_ninos', 'gafas_polarizadas'], true)
                && in_array($producto->genero_objetivo, ['female', 'male', 'unisex', 'ninos', 'ninas', 'gafas_polarizadas', 'descanso'], true)
                && (bool) $producto->esta_activo,
            404
        );

        $noPrescription = $request->boolean('no_prescription');
        $planoNeutro = $request->boolean('plano_neutro');

        $caracteristicas = is_array($producto->caracteristicas) ? $producto->caracteristicas : [];
        $polyEnabled = GafaLensPricing::usesPolyForCharacteristics($caracteristicas);

        $validated = $request->validate([
            'no_prescription' => ['nullable', 'boolean'],
            'plano_neutro' => ['nullable', 'boolean'],
            'plano_rx_sphere_max' => ['nullable', 'numeric', 'min:0', 'max:20', 'required_if:plano_neutro,1'],
            'plano_rx_cyl_max' => ['nullable', 'numeric', 'min:0', 'max:20', 'required_if:plano_neutro,1'],
            'correo' => ['nullable', 'email', 'max:190'],
            'nombres' => ['nullable', 'string', 'max:120'],
            'apellidos' => ['nullable', 'string', 'max:120'],
            'perfil_cliente_id' => ['nullable', 'integer'],
            'crear_nuevo_perfil' => ['nullable', 'boolean'],
            'tipo_documento' => ['nullable', 'string', 'max:30'],
            'numero_documento' => ['nullable', 'string', 'max:60'],
            'telefono' => ['nullable', 'string', 'max:40'],
            'fecha_nacimiento' => ['nullable', 'date'],
            'genero' => ['nullable', 'string', 'max:20'],
            'direccion' => ['nullable', 'string'],
            'ciudad' => ['nullable', 'string', 'max:80'],
            'notas' => ['nullable', 'string'],
            'tipo_lente_necesitas' => ['nullable', 'string', Rule::in([
                GafaLensPricing::TIPO_LENTE_MONOFOCAL,
                GafaLensPricing::TIPO_LENTE_PROGRESIVOS,
                'ocupacional',
                'bifocal',
            ])],
            'lens_type' => ['nullable', 'string', 'required_if:tipo_lente_necesitas,ocupacional,progresivos,bifocal', Rule::in(array_keys(GafaLensPricing::lensTypeOptions()))],
            'nara_level' => ['nullable', 'string', 'required_if:tipo_lente_necesitas,progresivos', Rule::in(array_keys(GafaLensPricing::naraLevelOptions()))],
            'lens_color' => ['nullable', 'string', Rule::in(['Gris', 'Marrón', 'Verde Grafito', 'Verde esmeralda', 'Zafiro', 'Amatista', 'Ambar', 'Rubi']), 'required_if:lens_type,159_transitions_gens'],
            'prescription_id' => ($noPrescription || $planoNeutro) ? ['nullable', 'integer'] : ['required', 'integer'],
        ]);

        $precioBase = $producto->precio_oferta ?? $producto->precio;
        if ($precioBase === null) {
            return redirect()
                ->route('gafas.show', $producto)
                ->with('status', 'Este producto aún no tiene precio configurado.');
        }

        $tipoLenteNecesitas = '';
        $lensType = '';
        $naraLevel = '';
        $lensColor = '';
        $precioLentes = 0.0;
        if (!$noPrescription) {
            if ($planoNeutro) {
                $tipoLenteNecesitas = GafaLensPricing::TIPO_LENTE_MONOFOCAL;
            }
            $tipoLenteNecesitas = (string) ($validated['tipo_lente_necesitas'] ?? GafaLensPricing::TIPO_LENTE_PROGRESIVOS);
            if (!in_array($tipoLenteNecesitas, [GafaLensPricing::TIPO_LENTE_MONOFOCAL, GafaLensPricing::TIPO_LENTE_PROGRESIVOS, 'ocupacional', 'bifocal'], true)) {
                $tipoLenteNecesitas = GafaLensPricing::TIPO_LENTE_PROGRESIVOS;
            }

            if (!GafaLensPricing::isLensDesignAllowedForCharacteristics($caracteristicas, $tipoLenteNecesitas)) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'tipo_lente_necesitas' => 'La montura seleccionada no es compatible con ese tipo de lente.',
                    ]);
            }

            if ($planoNeutro) {
                $tipoLenteNecesitas = GafaLensPricing::TIPO_LENTE_MONOFOCAL;
            }

            $lensType = (string) ($validated['lens_type'] ?? GafaLensPricing::defaultLensType());
            if (!GafaLensPricing::isValidLensType($lensType)) {
                $lensType = GafaLensPricing::defaultLensType();
            }

            if (!GafaLensPricing::isLensTypeAllowedFor($tipoLenteNecesitas, $lensType, $polyEnabled)) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'lens_type' => 'La opción de lente seleccionada no es válida para el tipo de lente elegido.',
                    ]);
            }

            $naraLevel = (string) ($validated['nara_level'] ?? GafaLensPricing::defaultNaraLevel());
            if (!GafaLensPricing::isValidNaraLevel($naraLevel)) {
                $naraLevel = GafaLensPricing::defaultNaraLevel();
            }
            if ($polyEnabled && $tipoLenteNecesitas === GafaLensPricing::TIPO_LENTE_PROGRESIVOS && !in_array($naraLevel, ['basica', 'media', 'alta'], true)) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'nara_level' => 'Para Poly, en progresivos solo están disponibles Básica, Media y Alta.',
                    ]);
            }

            $lensColor = (string) ($validated['lens_color'] ?? '');
            if ($lensType !== '159_transitions_gens') {
                $lensColor = '';
            }
        }

        if ($producto->existencias !== null && $producto->existencias <= 0) {
            return redirect()
                ->route('gafas.show', $producto)
                ->with('status', 'Este producto está agotado y no se puede pagar en este momento.');
        }

        $usuarioId = Auth::id();
        $sessionId = $request->session()->getId();

        $prescriptionId = null;
        $rxSphereMax = 0.0;
        $rxCylMax = 0.0;
        if (!$noPrescription && !$planoNeutro) {
            $prescriptionId = (int) $validated['prescription_id'];
            $prescription = GafaPrescription::query()
                ->whereKey($prescriptionId)
                ->where('user_id', $usuarioId)
                ->first();

            if (!$prescription) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'prescription_id' => 'Debes subir tu fórmula en PDF para continuar.',
                    ]);
            }

            $rx = GafaLensPricing::rxMaxAbsFromAnalysis((array) ($prescription->analysis ?? []));
            $rxSphereMax = (float) ($rx['sphere'] ?? 0);
            $rxCylMax = (float) ($rx['cyl'] ?? 0);

            if ($tipoLenteNecesitas === GafaLensPricing::TIPO_LENTE_MONOFOCAL) {
                $lensColor = is_string($request->input('lens_color')) ? $request->input('lens_color') : null;
                $precioLentes = (float) GafaLensPricing::monofocalLensPrice($lensType, $rxSphereMax, $rxCylMax, $lensColor, $polyEnabled);
                $naraLevel = '';
            } elseif ($tipoLenteNecesitas === GafaLensPricing::TIPO_LENTE_BIFOCAL) {
                $precioLentes = (float) GafaLensPricing::bifocalLensPrice($lensType, $rxSphereMax, $rxCylMax, $polyEnabled);
                $naraLevel = '';
            } elseif ($tipoLenteNecesitas === GafaLensPricing::TIPO_LENTE_OCUPACIONAL) {
                $precioLentes = (float) GafaLensPricing::ocupacionalLensPrice($lensType, $polyEnabled);
                $naraLevel = '';
            } else {
                $precioLentes = (float) GafaLensPricing::lensPrice($lensType, $naraLevel, $polyEnabled);
            }
        } elseif ($planoNeutro) {
            $tipoLenteNecesitas = GafaLensPricing::TIPO_LENTE_MONOFOCAL;
            $rxSphereMax = (float) abs((float) ($validated['plano_rx_sphere_max'] ?? 0));
            $rxCylMax = (float) abs((float) ($validated['plano_rx_cyl_max'] ?? 0));
            $precioLentes = (float) GafaLensPricing::noFormulaLensPrice($lensType, $polyEnabled);
            $naraLevel = '';
        }

        $precioTotal = (float) $precioBase + (float) $precioLentes;

        $useNew = (bool) ($validated['crear_nuevo_perfil'] ?? false);

        $perfil = null;
        if (!$useNew && !empty($validated['perfil_cliente_id'])) {
            $perfil = PerfilCliente::query()
                ->where('id', (int) $validated['perfil_cliente_id'])
                ->where('usuario_id', $usuarioId)
                ->first();
        }

        if ($useNew) {
            $request->validate([
                'nombres' => ['required', 'string', 'max:120'],
                'apellidos' => ['required', 'string', 'max:120'],
                'tipo_documento' => ['required', 'string', 'max:30'],
                'numero_documento' => ['required', 'string', 'max:60'],
                'telefono' => ['required', 'string', 'max:40'],
                'direccion' => ['required', 'string'],
                'ciudad' => ['required', 'string', 'max:80'],
            ], [
                'nombres.required' => 'Falta rellenar los nombres.',
                'apellidos.required' => 'Falta rellenar los apellidos.',
                'tipo_documento.required' => 'Falta rellenar el tipo de documento.',
                'numero_documento.required' => 'Falta rellenar el número de documento.',
                'telefono.required' => 'Falta rellenar el teléfono.',
                'direccion.required' => 'Falta rellenar la dirección.',
                'ciudad.required' => 'Falta rellenar la ciudad.',
            ]);

            $perfil = PerfilCliente::query()->create([
                'usuario_id' => $usuarioId,
                'tipo_documento' => $validated['tipo_documento'] ?? null,
                'numero_documento' => $validated['numero_documento'] ?? null,
                'telefono' => $validated['telefono'] ?? null,
                'fecha_nacimiento' => $validated['fecha_nacimiento'] ?? null,
                'genero' => $validated['genero'] ?? null,
                'direccion' => $validated['direccion'] ?? null,
                'ciudad' => $validated['ciudad'] ?? null,
                'notas' => $validated['notas'] ?? null,
                'preferencias' => null,
            ]);
        }

        if (!$perfil) {
            return redirect()
                ->route('checkout.gafa', ['producto' => $producto->slug])
                ->withErrors(['perfil_cliente_id' => 'Debes seleccionar o agregar tu información personal antes de pagar.']);
        }

        $usuario = Auth::user();
        $nombres = trim((string) ($validated['nombres'] ?? ''));
        $apellidos = trim((string) ($validated['apellidos'] ?? ''));
        $nombreCompletoComprador = trim($nombres . ' ' . $apellidos);
        if ($nombreCompletoComprador === '') {
            $nombreCompletoComprador = 'Cliente';
        }

        $buyerData = [
            'nombre' => $nombreCompletoComprador,
            'nombres' => $nombres,
            'apellidos' => $apellidos,
            'correo' => ($validated['correo'] ?? null) ? trim((string) $validated['correo']) : null,
            'tipo_documento' => $perfil->tipo_documento,
            'numero_documento' => $perfil->numero_documento,
            'telefono' => $perfil->telefono,
            'direccion' => $perfil->direccion,
            'ciudad' => $perfil->ciudad,
            'fecha_nacimiento' => $perfil->fecha_nacimiento,
            'genero' => $perfil->genero,
            'notas' => $perfil->notas,
        ];

        $productMeta = is_array($producto->meta) ? $producto->meta : [];
        $productFeatures = is_array($producto->caracteristicas) ? $producto->caracteristicas : [];
        $frameColor = $this->normalizeFrameColorValue(
            $request->input('frame_color'),
            (string) ($productMeta['color'] ?? ($productMeta['color_nombre'] ?? ($producto->color ?? '')))
        );

        if (! $producto->tieneStockParaColor($frameColor, 1)) {
            $status = $frameColor !== ''
                ? 'El color ' . $frameColor . ' está agotado o ya no tiene unidades disponibles.'
                : 'Este producto está agotado y no se puede pagar en este momento.';

            return back()->withInput()->with('status', $status);
        }

        $frameData = [
            'producto_id' => $producto->id,
            'nombre' => $producto->nombre,
            'slug' => $producto->slug,
            'tipo' => $producto->tipo,
            'genero_objetivo' => $producto->genero_objetivo,
            'marca' => $producto->marca,
            'material_montura' => $producto->material_montura,
            'color' => $frameColor,
            'descripcion' => $producto->descripcion,
            'caracteristicas' => $productFeatures,
        ];

        $carrito = Carrito::query()->create([
            'usuario_id' => $usuarioId,
            'sesion_id' => $sessionId,
            'estado' => 'abierto',
            'moneda' => $producto->moneda ?? 'COP',
            'subtotal' => $precioTotal,
            'total_descuento' => 0,
            'total' => $precioTotal,
            'meta' => [
                'source' => 'gafas_detail',
                'perfil_cliente_id' => $perfil->id,
                'cliente' => $buyerData,
                'montura' => $frameData,
                'prescription_id' => $prescriptionId,
                'lentes' => [
                    'tipo_lente_necesitas' => $tipoLenteNecesitas,
                    'lens_type' => $lensType,
                    'nara_level' => $naraLevel,
                    'poly_enabled' => $polyEnabled,
                    'color' => $lensColor,
                    'rx_sphere_max' => $rxSphereMax,
                    'rx_cyl_max' => $rxCylMax,
                    'precio_montura' => (float) $precioBase,
                    'precio_lentes' => (float) $precioLentes,
                    'precio_total' => (float) $precioTotal,
                ],
            ],
        ]);

        ItemCarrito::query()->create([
            'carrito_id' => $carrito->id,
            'producto_id' => $producto->id,
            'nombre_producto' => $producto->nombre,
            'precio_unitario' => $precioTotal,
            'cantidad' => 1,
            'moneda' => $carrito->moneda,
            'meta' => [
                'slug' => $producto->slug,
                'tipo' => $producto->tipo,
                'genero_objetivo' => $producto->genero_objetivo,
                'frame_color' => $frameColor,
                'prescription_id' => $prescriptionId,
                'lentes' => [
                    'tipo_lente_necesitas' => $tipoLenteNecesitas,
                    'lens_type' => $lensType,
                    'nara_level' => $naraLevel,
                    'poly_enabled' => $polyEnabled,
                    'color' => $lensColor,
                    'rx_sphere_max' => $rxSphereMax,
                    'rx_cyl_max' => $rxCylMax,
                    'precio_montura' => (float) $precioBase,
                    'precio_lentes' => (float) $precioLentes,
                    'precio_total' => (float) $precioTotal,
                ],
            ],
        ]);

        $driver = (string) config('pagos.driver', 'dummy');

        $pago = Pago::query()->create([
            'carrito_id' => $carrito->id,
            'estado' => 'pendiente',
            'pasarela' => $driver,
            'moneda' => $carrito->moneda,
            'monto' => $carrito->total,
            'referencia' => 'PAY-'.Str::ulid()->toBase32(),
            'meta' => [
                'producto_id' => $producto->id,
                'producto_slug' => $producto->slug,
                'usuario_id' => $usuarioId,
                'perfil_cliente_id' => $perfil->id,
                'cliente' => $buyerData,
                'montura' => $frameData,
                'prescription_id' => $prescriptionId,
                'lentes' => [
                    'tipo_lente_necesitas' => $tipoLenteNecesitas,
                    'lens_type' => $lensType,
                    'nara_level' => $naraLevel,
                    'poly_enabled' => $polyEnabled,
                    'color' => $lensColor,
                    'rx_sphere_max' => $rxSphereMax,
                    'rx_cyl_max' => $rxCylMax,
                    'precio_montura' => (float) $precioBase,
                    'precio_lentes' => (float) $precioLentes,
                    'precio_total' => (float) $precioTotal,
                ],
            ],
        ]);

        try {
            $pasarela = PasarelaPagoFactory::make($driver);
            $redirectUrl = $pasarela->iniciar($pago);
            return redirect()->away($redirectUrl);
        } catch (\Throwable $e) {
            Log::error('Error iniciando pago', [
                'driver' => $driver,
                'pago_id' => $pago->id,
                'ref' => $pago->referencia,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('checkout.gafa', ['producto' => $producto->slug])
                ->with('status', 'No se pudo iniciar el pago. Intenta nuevamente en unos minutos.');
        }
    }

    public function startCarrito(Request $request): RedirectResponse
    {
        if (!Auth::check()) {
            $sessionId = $request->session()->getId();
            $guestToken = GuestShopperService::ensureGuestToken($request);

            $carrito = GuestShopperService::locateGuestCart($request, 'carrito');

            if (!$carrito) {
                return redirect()->route('landing')->with('status', 'Tu carrito está vacío.');
            }

            $carrito->loadMissing('items');
            if ($carrito->items->isEmpty()) {
                return redirect()->route('landing')->with('status', 'Tu carrito está vacío.');
            }

            $validated = $request->validate([
                'tipo_documento' => ['required', 'string', 'max:30'],
                'numero_documento' => ['required', 'string', 'max:60'],
                'telefono' => ['required', 'string', 'max:40'],
                'direccion' => ['required', 'string'],
                'ciudad' => ['required', 'string', 'max:80'],
                'fecha_nacimiento' => ['nullable', 'date'],
                'genero' => ['nullable', 'string', 'max:20'],
                'notas' => ['nullable', 'string'],
            ], [
                'tipo_documento.required' => 'Falta rellenar el tipo de documento.',
                'numero_documento.required' => 'Falta rellenar el número de documento.',
                'telefono.required' => 'Falta rellenar el teléfono.',
                'direccion.required' => 'Falta rellenar la dirección.',
                'ciudad.required' => 'Falta rellenar la ciudad.',
            ]);

            $subtotal = 0.0;
            foreach ($carrito->items as $item) {
                $subtotal += ((float) $item->precio_unitario) * ((int) $item->cantidad);
            }
            $subtotal = round($subtotal, 2);
            $totalDescuento = 0.0;
            $total = round(max(0, $subtotal - $totalDescuento), 2);

            $moneda = (string) ($carrito->moneda ?: ($carrito->items->first()->moneda ?? 'COP'));

            $carritoPago = Carrito::query()->create([
                'usuario_id' => null,
                'sesion_id' => $sessionId,
                'guest_token' => $guestToken,
                'estado' => 'abierto',
                'moneda' => $moneda,
                'subtotal' => $subtotal,
                'total_descuento' => $totalDescuento,
                'total' => $total,
                'meta' => [
                    'source' => 'shopping_cart_guest',
                    'carrito_origen_id' => $carrito->id,
                    'items_count' => (int) $carrito->items->count(),
                    'guest' => [
                        'tipo_documento' => $validated['tipo_documento'],
                        'numero_documento' => $validated['numero_documento'],
                        'telefono' => $validated['telefono'],
                        'direccion' => $validated['direccion'],
                        'ciudad' => $validated['ciudad'],
                        'fecha_nacimiento' => $validated['fecha_nacimiento'] ?? null,
                        'genero' => $validated['genero'] ?? null,
                        'notas' => $validated['notas'] ?? null,
                    ],
                ],
            ]);

            foreach ($carrito->items as $item) {
                ItemCarrito::query()->create([
                    'carrito_id' => $carritoPago->id,
                    'producto_id' => $item->producto_id,
                    'nombre_producto' => $item->nombre_producto,
                    'precio_unitario' => $item->precio_unitario,
                    'cantidad' => $item->cantidad,
                    'moneda' => $moneda,
                    'meta' => is_array($item->meta) ? $item->meta : null,
                ]);
            }

            $driver = (string) config('pagos.driver', 'dummy');

            $pago = Pago::query()->create([
                'carrito_id' => $carritoPago->id,
                'estado' => 'pendiente',
                'pasarela' => $driver,
                'moneda' => $moneda,
                'monto' => $carritoPago->total,
                'referencia' => 'PAY-'.Str::ulid()->toBase32(),
                'meta' => [
                    'carrito_origen_id' => $carrito->id,
                    'guest' => [
                        'tipo_documento' => $validated['tipo_documento'],
                        'numero_documento' => $validated['numero_documento'],
                        'telefono' => $validated['telefono'],
                        'direccion' => $validated['direccion'],
                        'ciudad' => $validated['ciudad'],
                        'fecha_nacimiento' => $validated['fecha_nacimiento'] ?? null,
                        'genero' => $validated['genero'] ?? null,
                        'notas' => $validated['notas'] ?? null,
                    ],
                ],
            ]);

            $metaOrigen = is_array($carrito->meta) ? $carrito->meta : [];
            $metaOrigen['checked_out_at'] = now()->toISOString();
            $metaOrigen['pago_referencia'] = $pago->referencia;

            $carrito->update([
                'estado' => 'cerrado',
                'meta' => $metaOrigen,
            ]);

            try {
                $pasarela = PasarelaPagoFactory::make($driver);
                $redirectUrl = $pasarela->iniciar($pago);
                return redirect()->away($redirectUrl);
            } catch (\Throwable $e) {
                Log::error('Error iniciando pago carrito invitado', [
                    'driver' => $driver,
                    'pago_id' => $pago->id,
                    'ref' => $pago->referencia,
                    'error' => $e->getMessage(),
                ]);

                return redirect()
                    ->route('checkout.carrito')
                    ->with('status', 'No se pudo iniciar el pago. Intenta nuevamente en unos minutos.');
            }
        }

        $usuarioId = (int) Auth::id();
        $sessionId = $request->session()->getId();

        $carrito = Carrito::query()
            ->where('usuario_id', $usuarioId)
            ->where('estado', 'carrito')
            ->latest('id')
            ->first();

        if (!$carrito) {
            return redirect()->route('landing')->with('status', 'Tu carrito está vacío.');
        }

        $carrito->loadMissing('items');
        if ($carrito->items->isEmpty()) {
            return redirect()->route('landing')->with('status', 'Tu carrito está vacío.');
        }

        $validated = $request->validate([
            'perfil_cliente_id' => ['nullable', 'integer'],
            'crear_nuevo_perfil' => ['nullable', 'boolean'],
            'tipo_documento' => ['nullable', 'string', 'max:30'],
            'numero_documento' => ['nullable', 'string', 'max:60'],
            'telefono' => ['nullable', 'string', 'max:40'],
            'fecha_nacimiento' => ['nullable', 'date'],
            'genero' => ['nullable', 'string', 'max:20'],
            'direccion' => ['nullable', 'string'],
            'ciudad' => ['nullable', 'string', 'max:80'],
            'notas' => ['nullable', 'string'],
        ]);

        $useNew = (bool) ($validated['crear_nuevo_perfil'] ?? false);

        $perfil = null;
        if (!$useNew && !empty($validated['perfil_cliente_id'])) {
            $perfil = PerfilCliente::query()
                ->where('id', (int) $validated['perfil_cliente_id'])
                ->where('usuario_id', $usuarioId)
                ->first();
        }

        if ($useNew) {
            $request->validate([
                'tipo_documento' => ['required', 'string', 'max:30'],
                'numero_documento' => ['required', 'string', 'max:60'],
                'telefono' => ['required', 'string', 'max:40'],
                'direccion' => ['required', 'string'],
                'ciudad' => ['required', 'string', 'max:80'],
            ], [
                'tipo_documento.required' => 'Falta rellenar el tipo de documento.',
                'numero_documento.required' => 'Falta rellenar el número de documento.',
                'telefono.required' => 'Falta rellenar el teléfono.',
                'direccion.required' => 'Falta rellenar la dirección.',
                'ciudad.required' => 'Falta rellenar la ciudad.',
            ]);

            $perfil = PerfilCliente::query()->create([
                'usuario_id' => $usuarioId,
                'tipo_documento' => $validated['tipo_documento'] ?? null,
                'numero_documento' => $validated['numero_documento'] ?? null,
                'telefono' => $validated['telefono'] ?? null,
                'fecha_nacimiento' => $validated['fecha_nacimiento'] ?? null,
                'genero' => $validated['genero'] ?? null,
                'direccion' => $validated['direccion'] ?? null,
                'ciudad' => $validated['ciudad'] ?? null,
                'notas' => $validated['notas'] ?? null,
                'preferencias' => null,
            ]);
        }

        if (!$perfil) {
            return redirect()
                ->route('checkout.carrito')
                ->withErrors(['perfil_cliente_id' => 'Debes seleccionar o agregar tu información personal antes de pagar.']);
        }

        $subtotal = 0.0;
        foreach ($carrito->items as $item) {
            $subtotal += ((float) $item->precio_unitario) * ((int) $item->cantidad);
        }
        $subtotal = round($subtotal, 2);
        $totalDescuento = 0.0;
        $total = round(max(0, $subtotal - $totalDescuento), 2);

        $moneda = (string) ($carrito->moneda ?: ($carrito->items->first()->moneda ?? 'COP'));

        $carritoPago = Carrito::query()->create([
            'usuario_id' => $usuarioId,
            'sesion_id' => $sessionId,
            'estado' => 'abierto',
            'moneda' => $moneda,
            'subtotal' => $subtotal,
            'total_descuento' => $totalDescuento,
            'total' => $total,
            'meta' => [
                'source' => 'shopping_cart',
                'perfil_cliente_id' => $perfil->id,
                'carrito_origen_id' => $carrito->id,
                'items_count' => (int) $carrito->items->count(),
            ],
        ]);

        foreach ($carrito->items as $item) {
            ItemCarrito::query()->create([
                'carrito_id' => $carritoPago->id,
                'producto_id' => $item->producto_id,
                'nombre_producto' => $item->nombre_producto,
                'precio_unitario' => $item->precio_unitario,
                'cantidad' => $item->cantidad,
                'moneda' => $moneda,
                'meta' => is_array($item->meta) ? $item->meta : null,
            ]);
        }

        $driver = (string) config('pagos.driver', 'dummy');

        $pago = Pago::query()->create([
            'carrito_id' => $carritoPago->id,
            'estado' => 'pendiente',
            'pasarela' => $driver,
            'moneda' => $moneda,
            'monto' => $carritoPago->total,
            'referencia' => 'PAY-'.Str::ulid()->toBase32(),
            'meta' => [
                'usuario_id' => $usuarioId,
                'perfil_cliente_id' => $perfil->id,
                'carrito_origen_id' => $carrito->id,
            ],
        ]);

        $metaOrigen = is_array($carrito->meta) ? $carrito->meta : [];
        $metaOrigen['checked_out_at'] = now()->toISOString();
        $metaOrigen['pago_referencia'] = $pago->referencia;

        $carrito->update([
            'estado' => 'cerrado',
            'meta' => $metaOrigen,
        ]);

        try {
            $pasarela = PasarelaPagoFactory::make($driver);
            $redirectUrl = $pasarela->iniciar($pago);
            return redirect()->away($redirectUrl);
        } catch (\Throwable $e) {
            Log::error('Error iniciando pago carrito', [
                'driver' => $driver,
                'pago_id' => $pago->id,
                'ref' => $pago->referencia,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('checkout.carrito')
                ->with('status', 'No se pudo iniciar el pago. Intenta nuevamente en unos minutos.');
        }
    }

    public function show(Pago $pago): View
    {
        if ($pago->pasarela === 'bold' && in_array((string) $pago->estado, ['pendiente', 'procesando'], true)) {
            $pago = $this->tryUpdateBoldPaymentStatus($pago);

            if ($pago->estado === 'aprobado') {
                app(PagoFulfillmentService::class)->handleApproved($pago, false);
                $pago = $pago->fresh() ?? $pago;

                app(PagoPostApprovalService::class)->processApproved($pago);
                $pago = $pago->fresh() ?? $pago;
            }
        }

        $pago->loadMissing('carrito.items');

        return view('pagos.show', [
            'pago' => $pago,
        ]);
    }

    public function approved(Pago $pago): View|RedirectResponse
    {
        if ((string) $pago->estado !== 'aprobado') {
            return redirect()->route('pagos.show', $pago);
        }

        return view('pagos.approved', [
            'pago' => $pago,
        ]);
    }

    public function dummy(Pago $pago): View
    {
        $pago->loadMissing('carrito.items');

        abort_unless($pago->pasarela === 'dummy', 404);

        return view('pagos.dummy', [
            'pago' => $pago,
        ]);
    }

    public function dummyConfirm(Request $request, Pago $pago): View
    {
        abort_unless($pago->pasarela === 'dummy', 404);

        $validated = $request->validate([
            'resultado' => ['required', 'in:aprobado,rechazado'],
        ]);

        $resultado = $validated['resultado'];
        $estado = $resultado === 'aprobado' ? 'aprobado' : 'rechazado';

        $meta = is_array($pago->meta) ? $pago->meta : [];
        $meta['dummy'] = [
            'confirmed_at' => now()->toISOString(),
        ];

        $pago->update([
            'estado' => $estado,
            'pasarela_estado' => $resultado,
            'meta' => $meta,
        ]);

        if ($estado === 'aprobado') {
            $pago->refresh();

            app(PagoFulfillmentService::class)->handleApproved($pago, true);
            $pago->refresh();

            app(PagoPostApprovalService::class)->processApproved($pago);

            $pago->loadMissing('carrito.items');

            return view('pagos.dummy', [
                'pago' => $pago,
            ]);
        }

        $pago->loadMissing('carrito.items');

        return view('pagos.dummy', [
            'pago' => $pago,
        ]);
    }
    private function normalizeFrameColorValue(mixed $value, string $fallback = ''): string
    {
        $rawValues = [];
        $pushRaw = static function (mixed $candidate) use (&$rawValues): void {
            if (is_string($candidate) || is_numeric($candidate)) {
                $rawValues[] = trim((string) $candidate);
            }
        };

        if (is_array($value)) {
            array_walk_recursive($value, static function (mixed $candidate) use ($pushRaw): void {
                $pushRaw($candidate);
            });
        } else {
            $pushRaw($value);
        }

        if ($rawValues === [] && trim($fallback) !== '') {
            $rawValues[] = trim($fallback);
        }

        $parts = [];
        $seen = [];

        foreach ($rawValues as $rawValue) {
            if (!is_string($rawValue) || $rawValue === '') {
                continue;
            }

            $segments = preg_split('/\s*,\s*/u', $rawValue) ?: [];
            foreach ($segments as $segment) {
                $name = trim((string) $segment);
                if ($name === '') {
                    continue;
                }

                $key = mb_strtolower(trim(Str::ascii($name)));
                if ($key === '' || isset($seen[$key])) {
                    continue;
                }

                $seen[$key] = true;
                $parts[] = $name;
            }
        }

        return implode(', ', $parts);
    }

    private function tryUpdateBoldPaymentStatus(Pago $pago): Pago
    {
        if ($pago->estado === 'aprobado' || $pago->estado === 'rechazado') {
            return $pago;
        }

        $cfg = (array) config('pagos.drivers.bold', []);
        if (!(bool) ($cfg['enabled'] ?? false)) {
            return $pago;
        }

        $identityKey = trim((string) ($cfg['identity_key'] ?? ''));
        if ($identityKey === '') {
            return $pago;
        }

        $meta = is_array($pago->meta) ? $pago->meta : [];
        $paymentLink = (string) ($meta['bold']['payment_link'] ?? $pago->pasarela_transaccion_id ?? '');
        $paymentLink = trim($paymentLink);
        if ($paymentLink === '') {
            return $pago;
        }

        try {
            $client = new BoldLinkClient(
                (string) ($cfg['base_url'] ?? 'https://integrations.api.bold.co'),
                $identityKey,
                (int) ($cfg['timeout_seconds'] ?? 20),
                (bool) ($cfg['verify_ssl'] ?? true),
            );

            $json = $client->getLink($paymentLink);
            $estado = $this->guessEstadoFromBoldResponse($json);
            if (!$estado) {
                return $pago;
            }

            $meta['bold'] = array_merge((array) ($meta['bold'] ?? []), [
                'last_checked_at' => now()->toISOString(),
            ]);

            $pago->update([
                'estado' => $estado,
                'pasarela_estado' => 'checked',
                'pasarela_transaccion_id' => $paymentLink,
                'meta' => $meta,
            ]);

            return $pago->fresh() ?? $pago;
        } catch (\Throwable $e) {
            Log::warning('No se pudo consultar estado Bold en pagos.show', [
                'pago_id' => $pago->id,
                'ref' => $pago->referencia,
                'error' => $e->getMessage(),
            ]);

            return $pago;
        }
    }

    private function guessEstadoFromBoldResponse(array $json): ?string
    {
        $text = strtoupper((string) json_encode($json));

        foreach (['SALE_APPROVED', 'APPROVED', 'PAID', 'SUCCESS'] as $needle) {
            if (str_contains($text, $needle)) {
                return 'aprobado';
            }
        }

        foreach (['SALE_REJECTED', 'REJECTED', 'DECLINED', 'FAILED'] as $needle) {
            if (str_contains($text, $needle)) {
                return 'rechazado';
            }
        }

        return null;
    }
}
