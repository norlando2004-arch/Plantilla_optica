<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Services\GuestShopperService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class GafaController extends Controller
{
    public function show(Request $request, Producto $producto): View
    {
        $allowedTipos = ['gafas', 'gafas_ninas', 'gafas_ninos', 'gafas_polarizadas'];
        $allowedGeneros = ['female', 'male', 'unisex', 'ninos', 'ninas', 'gafas_polarizadas', 'descanso'];

        abort_unless(
            in_array($producto->tipo, $allowedTipos, true)
                && in_array($producto->genero_objetivo, $allowedGeneros, true)
                && (bool) $producto->esta_activo,
            404
        );

        // Popularidad: 1 usuario = 1 conteo por producto.
        // Invitados: no se cuentan.
        if (auth()->check()) {
            $usuarioId = auth()->id();

            if ($usuarioId) {
                try {
                    $inserted = DB::table('producto_views')->insertOrIgnore([
                        'producto_id' => $producto->id,
                        'usuario_id' => $usuarioId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // Solo incrementa si fue la primera vez que este usuario vio esta gafa.
                    if ($inserted === 1) {
                        Producto::withoutTimestamps(function () use ($producto) {
                            $producto->increment('views_count');
                        });
                    }
                } catch (\Throwable $e) {
                    // noop
                }
            }
        }

        $relatedProducts = Producto::query()
            ->where('id', '!=', $producto->id)
            ->where('esta_activo', true)
            ->whereIn('tipo', $allowedTipos)
            ->whereIn('genero_objetivo', $allowedGeneros)
            ->where(function ($query) use ($producto) {
                $query
                    ->where('tipo', $producto->tipo)
                    ->orWhere('genero_objetivo', $producto->genero_objetivo);
            })
            ->orderByRaw('case when tipo = ? then 0 else 1 end', [$producto->tipo])
            ->orderByRaw('case when genero_objetivo = ? then 0 else 1 end', [$producto->genero_objetivo])
            ->orderByDesc('views_count')
            ->orderByDesc('id')
            ->limit(4)
            ->get();

        $isFavorited = in_array((int) $producto->id, GuestShopperService::resolveFavoriteProductIds($request, [(int) $producto->id]), true);

        return view('gafas.show', [
            'producto' => $producto,
            'relatedProducts' => $relatedProducts,
            'isFavorited' => $isFavorited,
        ]);
    }
}
