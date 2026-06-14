<?php

namespace App\Http\Controllers;

use App\Models\Carrito;
use App\Models\GafaPrescription;
use App\Models\PerfilCliente;
use App\Models\Producto;
use App\Services\CheckoutStockHoldService;
use App\Services\GuestShopperService;
use App\Services\Gafas\GafaLensPricing;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function gafaInvitado(Request $request, Producto $producto): RedirectResponse
    {
        $params = array_merge(['producto' => $producto->slug], $request->query());

        return redirect()->route('checkout.gafa', $params);
    }

    public function gafa(Request $request, Producto $producto): RedirectResponse|View
    {
        abort_unless(
            in_array($producto->tipo, ['gafas', 'gafas_ninas', 'gafas_ninos', 'gafas_polarizadas'], true)
                && in_array($producto->genero_objetivo, ['female', 'male', 'unisex', 'ninos', 'ninas', 'gafas_polarizadas', 'descanso'], true)
                && (bool) $producto->esta_activo,
            404
        );

        if ($producto->existencias !== null && $producto->existencias <= 0) {
            return redirect()
                ->route('gafas.show', ['producto' => $producto->slug])
                ->with('status', 'Este producto está agotado y no se puede pagar en este momento.');
        }

        $frameColor = trim((string) $request->query('frame_color', (string) ($producto->color ?? '')));
        $checkoutGuard = app(CheckoutStockHoldService::class)->guardLowStockCheckout($request, $producto, $frameColor);
        if (($checkoutGuard['allowed'] ?? false) !== true) {
            return redirect()
                ->route('gafas.show', ['producto' => $producto->slug])
                ->with('status', (string) ($checkoutGuard['message'] ?? 'Esta gafa está siendo tomada por otro checkout en este momento.'));
        }

        $usuarioId = Auth::id();

        $perfiles = $usuarioId !== null
            ? PerfilCliente::query()
                ->where('usuario_id', $usuarioId)
                ->latest('id')
                ->get()
            : collect();

        $noPrescription = $request->boolean('no_prescription');
        $planoNeutro = $request->boolean('plano_neutro');
        $caracteristicas = is_array($producto->caracteristicas) ? $producto->caracteristicas : [];
        $tipoLente = GafaLensPricing::sanitizeLensDesignForCharacteristics(
            $caracteristicas,
            (string) $request->query('tipo_lente_necesitas', GafaLensPricing::defaultLensDesignForCharacteristics($caracteristicas))
        );

        $rxSphereMax = 0.0;
        $rxCylMax = 0.0;

        if (!$noPrescription) {
            if ($planoNeutro) {
                $rxSphereMax = (float) abs((float) $request->query('plano_rx_sphere_max', 0));
                $rxCylMax = (float) abs((float) $request->query('plano_rx_cyl_max', 0));
            } else {
                $prescriptionIdRaw = $request->query('prescription_id');
                $prescriptionId = is_numeric($prescriptionIdRaw) ? (int) $prescriptionIdRaw : null;
                if ($prescriptionId) {
                    $prescriptionQuery = GafaPrescription::query()
                        ->whereKey($prescriptionId);

                    if ($usuarioId !== null) {
                        $prescriptionQuery->where('user_id', $usuarioId);
                    } else {
                        $prescriptionQuery
                            ->whereNull('user_id')
                            ->where('session_id', $request->session()->getId());
                    }

                    $prescription = $prescriptionQuery->first();

                    if ($prescription) {
                        $rx = GafaLensPricing::rxMaxAbsFromAnalysis((array) ($prescription->analysis ?? []));
                        $rxSphereMax = (float) ($rx['sphere'] ?? 0);
                        $rxCylMax = (float) ($rx['cyl'] ?? 0);
                    }
                }
            }
        }

        return view('checkout.gafa', [
            'producto' => $producto,
            'perfiles' => $perfiles,
            'tipo_lente_necesitas' => $tipoLente,
            'rx_sphere_max' => $rxSphereMax,
            'rx_cyl_max' => $rxCylMax,
        ]);
    }

    public function carrito(): RedirectResponse|View
    {
        $usuarioId = Auth::check() ? (int) Auth::id() : null;

        $carrito = $usuarioId !== null
            ? Carrito::query()
                ->where('estado', 'carrito')
                ->where('usuario_id', $usuarioId)
                ->latest('id')
                ->first()
            : GuestShopperService::locateGuestCart(request(), 'carrito');

        if (!$carrito) {
            return redirect()
                ->route('landing')
                ->with('status', 'Tu carrito está vacío.');
        }

        $carrito->loadMissing('items.producto');

        if ($carrito->items->isEmpty()) {
            return redirect()
                ->route('landing')
                ->with('status', 'Tu carrito está vacío.');
        }

        $perfiles = $usuarioId !== null
            ? PerfilCliente::query()
                ->where('usuario_id', $usuarioId)
                ->latest('id')
                ->get()
            : collect();

        return view('checkout.carrito', [
            'carrito' => $carrito,
            'perfiles' => $perfiles,
        ]);
    }
}
