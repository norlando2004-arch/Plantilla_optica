<?php

namespace App\Http\Controllers;

use App\Models\Favorito;
use App\Models\Producto;
use App\Services\GuestShopperService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class FavoritoController extends Controller
{
    public function index(Request $request): View
    {
        if (Auth::check()) {
            $user = Auth::user();

            $productos = $user->productosFavoritos()
                ->where('esta_activo', true)
                ->orderByDesc('favoritos.created_at')
                ->paginate(12)
                ->withQueryString();

            $favoriteProductIds = $productos->pluck('id')->all();
        } else {
            $guestToken = GuestShopperService::ensureGuestToken($request);
            GuestShopperService::migrateLegacySessionFavorites($request);

            $productos = Producto::query()
                ->join('favoritos', 'favoritos.producto_id', '=', 'productos.id')
                ->whereNull('favoritos.usuario_id')
                ->where('favoritos.guest_token', $guestToken)
                ->where('esta_activo', true)
                ->select('productos.*')
                ->orderByDesc('favoritos.created_at')
                ->paginate(12)
                ->withQueryString();

            $favoriteProductIds = $productos->pluck('id')->map(fn ($id) => (int) $id)->all();
        }

        return view('favoritos.index', [
            'productos' => $productos,
            'favoriteProductIds' => $favoriteProductIds,
        ]);
    }

    public function toggle(Request $request, Producto $producto): RedirectResponse|JsonResponse
    {
        $nowFavorited = false;

        if (Auth::check()) {
            $user = Auth::user();

            $exists = $user->favoritos()
                ->where('producto_id', $producto->id)
                ->exists();

            if ($exists) {
                $user->favoritos()
                    ->where('producto_id', $producto->id)
                    ->delete();
            } else {
                $user->favoritos()->create([
                    'producto_id' => $producto->id,
                ]);
                $nowFavorited = true;
            }
        } else {
            $guestToken = GuestShopperService::ensureGuestToken($request);
            GuestShopperService::migrateLegacySessionFavorites($request);

            $favorite = Favorito::query()
                ->whereNull('usuario_id')
                ->where('guest_token', $guestToken)
                ->where('producto_id', $producto->id)
                ->first();

            if ($favorite) {
                $favorite->delete();
                $nowFavorited = false;
            } else {
                Favorito::query()->create([
                    'usuario_id' => null,
                    'guest_token' => $guestToken,
                    'producto_id' => $producto->id,
                ]);
                $nowFavorited = true;
            }
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'ok' => true,
                'favorited' => $nowFavorited,
                'producto_id' => $producto->id,
            ]);
        }

        return redirect()->back();
    }
}
