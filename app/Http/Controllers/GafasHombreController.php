<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class GafasHombreController extends Controller
{
    public function index(Request $request): RedirectResponse
    {
        $baseQuery = Producto::query()
            ->where('tipo', 'gafas')
            ->whereIn('genero_objetivo', ['male', 'unisex'])
            ->where('esta_activo', true)
            ->latest('id');

        $priceRange = (clone $baseQuery)
            ->reorder()
            ->selectRaw('MIN(COALESCE(precio_oferta, precio)) as min_price, MAX(COALESCE(precio_oferta, precio)) as max_price')
            ->first();

        $maxAvailablePrice = (float) ($priceRange?->max_price ?? 0);

        $query = $request->query();
        $query['min_price'] = $request->query('min_price', '0');
        $query['max_price'] = $request->query('max_price', (string) ((int) $maxAvailablePrice));
        $query['categories'] = ['hombre'];

        return redirect()->route('gafas.index', $query);
    }
}
