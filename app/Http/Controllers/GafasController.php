<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Services\GuestShopperService;
use App\Services\GafasDescansoPromoContent;
use App\Services\GafasHombrePromoContent;
use App\Services\GafasMujeresPromoContent;
use App\Services\GafasNinasPromoContent;
use App\Services\GafasNinosPromoContent;
use App\Services\GafasPolarizadasPromoContent;
use App\Services\GafasPromoContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GafasController extends Controller
{

    public function index(Request $request): View|RedirectResponse
    {
        $isDeportivas = $request->route()?->getName() === 'gafas-deportivas.index';
        $mustShowOnlyProductsWithImage = $this->shouldShowOnlyProductsWithImage($request);

        if ($isDeportivas) {
            $baseQuery = Producto::query()
                ->where('tipo', 'gafas')
                ->where('genero_objetivo', 'descanso')
                ->where('esta_activo', true)
                ->latest('id');

            if ($mustShowOnlyProductsWithImage) {
                $this->applyProductsWithImageFilter($baseQuery);
            }

            $priceRange = (clone $baseQuery)
                ->reorder()
                ->selectRaw('MAX(COALESCE(precio_oferta, precio)) as max_price')
                ->first();

            $query = $request->query();
            $query['min_price'] = $request->query('min_price', '0');
            $query['max_price'] = $request->query('max_price', (string) ((int) ($priceRange?->max_price ?? 0)));
            $query['categories'] = ['deportivas'];

            return redirect()->route('gafas.index', $query);
        }

        $minPrice = $this->parseMoneyFilter($request->query('min_price'));
        $maxPrice = $this->parseMoneyFilter($request->query('max_price'));
        $selectedCategories = $this->parseCategoryFilters($request->query('categories'));
        $progresivosFilter = $this->parseProgresivosFilter($request->query('progresivos'));

        $baseQuery = Producto::query()
            ->whereIn('tipo', ['gafas', 'gafas_ninas', 'gafas_ninos', 'gafas_polarizadas'])
            ->whereIn('genero_objetivo', ['female', 'male', 'unisex', 'ninos', 'ninas', 'gafas_polarizadas', 'descanso'])
            ->where('esta_activo', true)
            ->latest('id');

        if ($mustShowOnlyProductsWithImage) {
            $this->applyProductsWithImageFilter($baseQuery);
        }

        $productsForRange = (clone $baseQuery)
            ->reorder()
            ->get(['precio', 'precio_oferta']);

        $effectivePrices = $productsForRange
            ->map(static function (Producto $producto): float {
                $offer = (float) ($producto->precio_oferta ?? 0);
                $base = (float) ($producto->precio ?? 0);
                return $offer > 0 ? $offer : $base;
            })
            ->filter(static fn (float $value) => $value > 0)
            ->values();

        $minAvailablePrice = (float) ($effectivePrices->min() ?? 0);
        $maxAvailablePrice = (float) ($effectivePrices->max() ?? 0);

        $query = (clone $baseQuery);

        if (!$isDeportivas && $selectedCategories !== []) {
            $query->where(function ($categoryQuery) use ($selectedCategories) {
                foreach ($selectedCategories as $category) {
                    $categoryQuery->orWhere(function ($matchQuery) use ($category) {
                        match ($category) {
                            'ninos' => $matchQuery
                                ->where('tipo', 'gafas_ninos')
                                ->whereIn('genero_objetivo', ['ninos', 'unisex']),
                            'hombre' => $matchQuery
                                ->where('tipo', 'gafas')
                                ->whereIn('genero_objetivo', ['male', 'unisex']),
                            'mujeres' => $matchQuery
                                ->where('tipo', 'gafas')
                                ->whereIn('genero_objetivo', ['female', 'unisex']),
                            'deportivas', 'descanso' => $matchQuery
                                ->where('tipo', 'gafas')
                                ->where('genero_objetivo', 'descanso'),
                            'polarizadas' => $matchQuery
                                ->where('tipo', 'gafas_polarizadas')
                                ->whereIn('genero_objetivo', ['gafas_polarizadas']),
                            'ninas' => $matchQuery
                                ->where('tipo', 'gafas_ninas')
                                ->whereIn('genero_objetivo', ['ninas', 'unisex']),
                            default => $matchQuery,
                        };
                    });
                }
            });
        }

        if ($minPrice !== null) {
            $query->where(function ($priceQuery) use ($minPrice) {
                $priceQuery
                    ->where(function ($offerQuery) use ($minPrice) {
                        $offerQuery
                            ->whereNotNull('precio_oferta')
                            ->where('precio_oferta', '>', 0)
                            ->where('precio_oferta', '>=', $minPrice);
                    })
                    ->orWhere(function ($baseQuery) use ($minPrice) {
                        $baseQuery
                            ->where(function ($emptyOfferQuery) {
                                $emptyOfferQuery
                                    ->whereNull('precio_oferta')
                                    ->orWhere('precio_oferta', '<=', 0);
                            })
                            ->whereNotNull('precio')
                            ->where('precio', '>=', $minPrice);
                    });
            });
        }

        if ($maxPrice !== null) {
            $query->where(function ($priceQuery) use ($maxPrice) {
                $priceQuery
                    ->where(function ($offerQuery) use ($maxPrice) {
                        $offerQuery
                            ->whereNotNull('precio_oferta')
                            ->where('precio_oferta', '>', 0)
                            ->where('precio_oferta', '<=', $maxPrice);
                    })
                    ->orWhere(function ($baseQuery) use ($maxPrice) {
                        $baseQuery
                            ->where(function ($emptyOfferQuery) {
                                $emptyOfferQuery
                                    ->whereNull('precio_oferta')
                                    ->orWhere('precio_oferta', '<=', 0);
                            })
                            ->whereNotNull('precio')
                            ->where('precio', '<=', $maxPrice);
                    });
            });
        }

        if ($progresivosFilter === true) {
            $query->where('caracteristicas->progresivos', true);
        }

        $productos = $query->paginate(12)->withQueryString();

        $favoriteProductIds = GuestShopperService::resolveFavoriteProductIds($request, $productos->pluck('id')->map(fn ($id) => (int) $id)->all());

        $defaultPromo = GafasPromoContent::load();
        $bannerImages = [];

        foreach ($selectedCategories as $category) {
            $resolvedPromo = match ($category) {
                'mujeres' => GafasMujeresPromoContent::load(),
                'hombre' => GafasHombrePromoContent::load(),
                'ninas' => GafasNinasPromoContent::load(),
                'ninos' => GafasNinosPromoContent::load(),
                'polarizadas' => GafasPolarizadasPromoContent::load(),
                'deportivas', 'descanso' => GafasDescansoPromoContent::load(),
                default => null,
            };

            if (!is_array($resolvedPromo)) {
                continue;
            }

            $resolvedImages = [];
            if (is_array($resolvedPromo['image_urls'] ?? null)) {
                foreach ($resolvedPromo['image_urls'] as $imageCandidate) {
                    $imageCandidate = trim((string) $imageCandidate);
                    if ($imageCandidate !== '' && !in_array($imageCandidate, $resolvedImages, true)) {
                        $resolvedImages[] = $imageCandidate;
                    }
                }
            }

            if ($resolvedImages === []) {
                $imageUrl = trim((string) ($resolvedPromo['image_url'] ?? ''));
                if ($imageUrl !== '') {
                    $resolvedImages[] = $imageUrl;
                }
            }

            foreach ($resolvedImages as $imageUrl) {
                if (!in_array($imageUrl, $bannerImages, true)) {
                    $bannerImages[] = $imageUrl;
                }
            }
        }

        if ($bannerImages === []) {
            $fallbackBanner = trim((string) ($defaultPromo['image_url'] ?? ''));
            if ($fallbackBanner !== '') {
                $bannerImages[] = $fallbackBanner;
            }
        }

        $storeBannerImage = $bannerImages[0] ?? '';

        return view('gafas.index', [
            'productos' => $productos,
            'favoriteProductIds' => $favoriteProductIds,
            'minAvailablePrice' => $minAvailablePrice,
            'maxAvailablePrice' => $maxAvailablePrice,
            'minPriceFilter' => $minPrice,
            'maxPriceFilter' => $maxPrice,
            'selectedCategories' => $selectedCategories,
            'progresivosFilter' => $progresivosFilter,
            'storeBannerImage' => $storeBannerImage,
            'storeBannerImages' => $bannerImages,
            'browserTitle' => 'Gafas — Óptica',
            'pageTitle' => 'Gafas',
            'pageSubtitle' => 'Explora todas las colecciones',
        ]);
    }

    private function parseCategoryFilters(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        $allowed = ['ninos', 'hombre', 'mujeres', 'deportivas', 'descanso', 'polarizadas', 'ninas'];

        $normalized = array_map(static function ($item): string {
            $item = is_string($item) ? trim($item) : '';
            return $item === 'descanso' ? 'deportivas' : $item;
        }, $value);

        return array_values(array_unique(array_filter(
            $normalized,
            static fn ($item) => in_array($item, $allowed, true)
        )));
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

    private function parseProgresivosFilter(mixed $value): ?bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (!is_string($value) && !is_numeric($value)) {
            return null;
        }

        $normalized = strtolower(trim((string) $value));
        if ($normalized === '') {
            return null;
        }

        return in_array($normalized, ['1', 'true', 'si', 'sí', 'yes', 'on'], true)
            ? true
            : null;
    }

    private function shouldShowOnlyProductsWithImage(Request $request): bool
    {
        $user = $request->user();
        if (!$user) {
            return true;
        }

        $rolId = $user->rol_id;

        return $rolId === null || (int) $rolId === 1;
    }

    private function applyProductsWithImageFilter($query): void
    {
        $query
            ->whereNotNull('meta->imagen_url')
            ->where('meta->imagen_url', '!=', '');
    }
}
