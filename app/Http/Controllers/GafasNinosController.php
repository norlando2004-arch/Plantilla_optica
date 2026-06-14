<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Services\GuestShopperService;
use App\Services\GafasNinosPromoContent;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GafasNinosController extends Controller
{
    public function index(Request $request): View
    {
        $minPrice = $this->parseMoneyFilter($request->query('min_price'));
        $maxPrice = $this->parseMoneyFilter($request->query('max_price'));

        $baseQuery = Producto::query()
            ->where('tipo', 'gafas_ninos')
            ->whereIn('genero_objetivo', ['ninos', 'unisex'])
            ->where('esta_activo', true)
            ->latest('id');

        $priceRange = (clone $baseQuery)
            ->reorder()
            ->selectRaw('MIN(COALESCE(precio_oferta, precio)) as min_price, MAX(COALESCE(precio_oferta, precio)) as max_price')
            ->first();

        $minAvailablePrice = (float) ($priceRange?->min_price ?? 0);
        $maxAvailablePrice = (float) ($priceRange?->max_price ?? 0);

        $query = (clone $baseQuery);

        if ($minPrice !== null) {
            $query->whereRaw('COALESCE(precio_oferta, precio) >= ?', [$minPrice]);
        }

        if ($maxPrice !== null) {
            $query->whereRaw('COALESCE(precio_oferta, precio) <= ?', [$maxPrice]);
        }

        $productos = $query->paginate(12)->withQueryString();

        $favoriteProductIds = GuestShopperService::resolveFavoriteProductIds($request, $productos->pluck('id')->map(fn ($id) => (int) $id)->all());

        $promo = GafasNinosPromoContent::load();

        return view('gafas-ninos.index', [
            'productos' => $productos,
            'favoriteProductIds' => $favoriteProductIds,
            'minAvailablePrice' => $minAvailablePrice,
            'maxAvailablePrice' => $maxAvailablePrice,
            'minPriceFilter' => $minPrice,
            'maxPriceFilter' => $maxPrice,
            'storeBannerImage' => (string) ($promo['image_url'] ?? ''),
        ]);
    }

    private function parseMoneyFilter(mixed $value): ?float
    {
        if (!is_string($value) && !is_numeric($value)) {
            return null;
        }

        $raw = trim((string) $value);
        if ($raw === '') {
            return null;
        }

        $digits = preg_replace('/[^0-9]/', '', $raw);
        if (!$digits) {
            return null;
        }

        return (float) ((int) $digits);
    }
}
