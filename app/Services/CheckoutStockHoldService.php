<?php

namespace App\Services;

use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class CheckoutStockHoldService
{
    private const LOW_STOCK_THRESHOLD = 1;
    private const HOLD_TTL_MINUTES = 10;

    /**
     * @return array{allowed: bool, message?: string}
     */
    public function guardLowStockCheckout(Request $request, Producto $producto, ?string $frameColor = null): array
    {
        $resolvedColor = trim((string) ($frameColor ?? $producto->color ?? ''));
        $stock = $producto->stockDisponibleParaColor($resolvedColor);

        // Si no tenemos control de stock o no está en nivel crítico, no bloqueamos checkout.
        if ($stock === null || $stock > self::LOW_STOCK_THRESHOLD) {
            return ['allowed' => true];
        }

        $owner = $this->ownerToken($request);
        $key = $this->buildKey($producto->id, $resolvedColor);
        $currentOwner = Cache::get($key);

        if (is_string($currentOwner) && $currentOwner === $owner) {
            Cache::put($key, $owner, now()->addMinutes(self::HOLD_TTL_MINUTES));
            return ['allowed' => true];
        }

        if ($currentOwner === null && Cache::add($key, $owner, now()->addMinutes(self::HOLD_TTL_MINUTES))) {
            return ['allowed' => true];
        }

        return [
            'allowed' => false,
            'message' => 'Esta montura se acaba de ocupar en otro checkout. Intenta nuevamente en unos minutos o elige otra gafa.',
        ];
    }

    private function ownerToken(Request $request): string
    {
        if (Auth::check()) {
            return 'u:' . (string) Auth::id();
        }

        $guestToken = GuestShopperService::ensureGuestToken($request);
        if ($guestToken !== '') {
            return 'g:' . $guestToken;
        }

        return 's:' . $request->session()->getId();
    }

    private function buildKey(int $productId, string $frameColor): string
    {
        $normalizedColor = Str::lower(trim(Str::ascii($frameColor)));
        $colorPart = $normalizedColor !== '' ? $normalizedColor : '__general__';

        return 'checkout_hold:gafa:' . $productId . ':' . $colorPart;
    }
}
