<?php

namespace App\Http\Controllers;

use App\Models\Carrito;
use App\Models\ItemCarrito;
use App\Models\Producto;
use App\Services\GuestShopperService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CarritoController extends Controller
{
    public function index(Request $request): View
    {
        $usuarioId = Auth::check() ? (int) Auth::id() : null;

        $carrito = $usuarioId !== null
            ? Carrito::query()
                ->where('estado', 'carrito')
                ->where('usuario_id', $usuarioId)
                ->latest('id')
                ->first()
            : GuestShopperService::locateGuestCart($request, 'carrito');

        $items = collect();
        if ($carrito) {
            $carrito->loadMissing('items.producto');
            $items = $carrito->items;
            $this->recalculateAndPersist($carrito);
            $carrito->refresh();
        }

        return view('carrito.index', [
            'carrito' => $carrito,
            'items' => $items,
        ]);
    }

    public function add(Request $request, Producto $producto): RedirectResponse|JsonResponse
    {
        abort_unless(
            in_array($producto->tipo, ['gafas', 'gafas_ninas', 'gafas_ninos', 'gafas_polarizadas'], true)
                && in_array($producto->genero_objetivo, ['female', 'male', 'unisex', 'ninos', 'ninas', 'gafas_polarizadas', 'descanso'], true)
                && (bool) $producto->esta_activo,
            404
        );

        $precio = $producto->precio_oferta ?? $producto->precio;
        if ($precio === null) {
            return back()->with('status', 'Este producto aún no tiene precio configurado.');
        }

        $productMeta = is_array($producto->meta) ? $producto->meta : [];
        $frameColor = trim((string) $request->input('frame_color', $productMeta['color'] ?? ($productMeta['color_nombre'] ?? ($producto->color ?? ''))));

        $cantidad = (int) $request->input('cantidad', 1);
        if ($cantidad < 1) {
            $cantidad = 1;
        }
        if ($cantidad > 99) {
            $cantidad = 99;
        }

        if (! $producto->tieneStockParaColor($frameColor, $cantidad)) {
            $suffix = $frameColor !== '' ? ' del color ' . $frameColor : '';
            return back()->with('status', 'No hay suficiente stock' . $suffix . ' para agregar al carrito.');
        }

        $usuarioId = Auth::check() ? (int) Auth::id() : null;
        $carrito = $this->findOrCreateCurrentCart($request, $usuarioId, (string) ($producto->moneda ?? 'COP'));

        $item = ItemCarrito::query()
            ->where('carrito_id', $carrito->id)
            ->where('producto_id', $producto->id)
            ->get()
            ->first(function (ItemCarrito $existingItem) use ($frameColor) {
                $meta = is_array($existingItem->meta) ? $existingItem->meta : [];
                $existingColor = trim((string) ($meta['frame_color'] ?? ''));

                return Str::lower($existingColor) === Str::lower($frameColor);
            });

        if ($item) {
            $newQuantity = min(99, (int) $item->cantidad + $cantidad);
            if (! $producto->tieneStockParaColor($frameColor, $newQuantity)) {
                $suffix = $frameColor !== '' ? ' del color ' . $frameColor : '';
                return back()->with('status', 'No hay suficiente stock' . $suffix . ' para esa cantidad.');
            }

            $meta = is_array($item->meta) ? $item->meta : [];
            $meta['frame_color'] = $frameColor;

            $item->update([
                'cantidad' => $newQuantity,
                'meta' => $meta,
            ]);
        } else {
            ItemCarrito::query()->create([
                'carrito_id' => $carrito->id,
                'producto_id' => $producto->id,
                'nombre_producto' => $producto->nombre,
                'precio_unitario' => $precio,
                'cantidad' => $cantidad,
                'moneda' => $carrito->moneda,
                'meta' => [
                    'slug' => $producto->slug,
                    'tipo' => $producto->tipo,
                    'genero_objetivo' => $producto->genero_objetivo,
                    'frame_color' => $frameColor,
                ],
            ]);
        }

        $this->recalculateAndPersist($carrito);

        $carrito->loadMissing('items');
        $cartCount = (int) $carrito->items->sum('cantidad');

        if ($request->expectsJson() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'ok' => true,
                'cart_count' => $cartCount,
                'added' => $cantidad,
            ]);
        }

        return redirect()->route('carrito.index');
    }

    public function updateItem(Request $request, ItemCarrito $item): RedirectResponse
    {
        $carrito = $this->resolveOwnedCart($request, $item);

        abort_unless((bool) $carrito, 404);

        $validated = $request->validate([
            'cantidad' => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        $item->update([
            'cantidad' => (int) $validated['cantidad'],
        ]);

        $this->recalculateAndPersist($carrito);

        return redirect()->route('carrito.index');
    }

    public function removeItem(Request $request, ItemCarrito $item): RedirectResponse
    {
        $carrito = $this->resolveOwnedCart($request, $item);

        abort_unless((bool) $carrito, 404);

        $item->delete();

        $this->recalculateAndPersist($carrito);

        return redirect()->route('carrito.index');
    }

    private function findOrCreateCurrentCart(Request $request, ?int $usuarioId, string $moneda): Carrito
    {
        $sessionId = $request->session()->getId();
        $guestToken = GuestShopperService::ensureGuestToken($request);

        $query = Carrito::query()->where('estado', 'carrito');

        if ($usuarioId) {
            $query->where('usuario_id', $usuarioId);
        } else {
            $query->whereNull('usuario_id')
                ->where(function ($guestQuery) use ($guestToken, $sessionId) {
                    $guestQuery->where('guest_token', $guestToken)
                        ->orWhere(function ($legacyQuery) use ($sessionId) {
                            $legacyQuery->whereNull('guest_token')
                                ->where('sesion_id', $sessionId);
                        });
                });
        }

        $existing = $query->latest('id')->first();
        if ($existing) {
            $updates = [];
            if (! $existing->moneda) {
                $updates['moneda'] = $moneda;
            }
            if (! $usuarioId && $existing->guest_token !== $guestToken) {
                $updates['guest_token'] = $guestToken;
            }
            if ($updates !== []) {
                $existing->update($updates);
            }

            return $existing;
        }

        return Carrito::query()->create([
            'usuario_id' => $usuarioId,
            'sesion_id' => $sessionId,
            'guest_token' => $usuarioId ? null : $guestToken,
            'estado' => 'carrito',
            'moneda' => $moneda ?: 'COP',
            'subtotal' => 0,
            'total_descuento' => 0,
            'total' => 0,
            'meta' => [
                'source' => 'shopping_cart',
            ],
        ]);
    }

    private function recalculateAndPersist(Carrito $carrito): void
    {
        $carrito->loadMissing('items');

        $subtotal = 0.0;
        foreach ($carrito->items as $item) {
            $subtotal += ((float) $item->precio_unitario) * ((int) $item->cantidad);
        }

        $subtotal = round($subtotal, 2);
        $totalDescuento = 0.0;
        $total = round(max(0, $subtotal - $totalDescuento), 2);

        $carrito->update([
            'subtotal' => $subtotal,
            'total_descuento' => $totalDescuento,
            'total' => $total,
        ]);
    }

    private function resolveOwnedCart(Request $request, ItemCarrito $item): ?Carrito
    {
        $query = Carrito::query()
            ->where('id', $item->carrito_id)
            ->where('estado', 'carrito');

        if (Auth::check()) {
            $query->where('usuario_id', (int) Auth::id());
        } else {
            $guestToken = GuestShopperService::ensureGuestToken($request);
            $sessionId = $request->session()->getId();

            $query->whereNull('usuario_id')
                ->where(function ($guestQuery) use ($guestToken, $sessionId) {
                    $guestQuery->where('guest_token', $guestToken)
                        ->orWhere(function ($legacyQuery) use ($sessionId) {
                            $legacyQuery->whereNull('guest_token')
                                ->where('sesion_id', $sessionId);
                        });
                });
        }

        return $query->first();
    }
}
