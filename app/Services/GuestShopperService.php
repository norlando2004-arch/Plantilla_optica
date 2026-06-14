<?php

namespace App\Services;

use App\Models\Carrito;
use App\Models\Favorito;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

class GuestShopperService
{
    public const COOKIE_NAME = 'optica_guest_token';
    private const COOKIE_MINUTES = 60 * 24 * 365;

    public static function ensureGuestToken(Request $request): string
    {
        $existing = self::currentGuestToken($request);
        if ($existing !== null) {
            $request->attributes->set(self::COOKIE_NAME, $existing);
            return $existing;
        }

        $token = (string) Str::uuid();
        $request->attributes->set(self::COOKIE_NAME, $token);

        Cookie::queue(cookie(
            self::COOKIE_NAME,
            $token,
            self::COOKIE_MINUTES,
            config('session.path', '/'),
            config('session.domain'),
            (bool) config('session.secure', false),
            (bool) config('session.http_only', true),
            false,
            (string) config('session.same_site', 'lax')
        ));

        return $token;
    }

    public static function currentGuestToken(?Request $request = null): ?string
    {
        $request ??= request();

        $token = $request->attributes->get(self::COOKIE_NAME)
            ?? $request->cookie(self::COOKIE_NAME);

        if (! is_string($token)) {
            return null;
        }

        $token = trim($token);

        return Str::isUuid($token) ? $token : null;
    }

    public static function resolveFavoriteProductIds(Request $request, array $productIds = []): array
    {
        if (Auth::check()) {
            self::mergeIntoUser($request, (int) Auth::id());

            $query = Auth::user()->productosFavoritos();
            if ($productIds !== []) {
                $query->whereIn('productos.id', $productIds);
            }

            return $query->pluck('productos.id')->map(fn ($id) => (int) $id)->all();
        }

        self::migrateLegacySessionFavorites($request);

        $query = Favorito::query()
            ->whereNull('usuario_id')
            ->where('guest_token', self::ensureGuestToken($request));

        if ($productIds !== []) {
            $query->whereIn('producto_id', $productIds);
        }

        return $query->pluck('producto_id')->map(fn ($id) => (int) $id)->all();
    }

    public static function mergeIntoUser(Request $request, int $userId): void
    {
        if ($userId <= 0) {
            return;
        }

        self::migrateLegacySessionFavorites($request);
        self::mergeGuestFavoritesIntoUser($request, $userId);
        self::mergeGuestCartIntoUser($request, $userId);
    }

    public static function locateGuestCart(Request $request, string $estado = 'carrito'): ?Carrito
    {
        $guestToken = self::ensureGuestToken($request);
        $sessionId = $request->session()->getId();

        $cart = Carrito::query()
            ->whereNull('usuario_id')
            ->where('estado', $estado)
            ->where(function ($query) use ($guestToken, $sessionId) {
                $query->where('guest_token', $guestToken)
                    ->orWhere(function ($legacyQuery) use ($sessionId) {
                        $legacyQuery->whereNull('guest_token')
                            ->where('sesion_id', $sessionId);
                    });
            })
            ->latest('id')
            ->first();

        if ($cart && $cart->guest_token !== $guestToken) {
            $cart->update(['guest_token' => $guestToken]);
        }

        return $cart;
    }

    public static function migrateLegacySessionFavorites(Request $request): void
    {
        $legacyIds = collect((array) $request->session()->get('guest_favorite_product_ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        if ($legacyIds->isEmpty()) {
            return;
        }

        $guestToken = self::ensureGuestToken($request);

        foreach ($legacyIds as $productId) {
            Favorito::query()->firstOrCreate([
                'usuario_id' => null,
                'guest_token' => $guestToken,
                'producto_id' => (int) $productId,
            ]);
        }

        $request->session()->forget('guest_favorite_product_ids');
    }

    private static function mergeGuestFavoritesIntoUser(Request $request, int $userId): void
    {
        $guestToken = self::ensureGuestToken($request);

        $guestFavorites = Favorito::query()
            ->whereNull('usuario_id')
            ->where('guest_token', $guestToken)
            ->get();

        if ($guestFavorites->isEmpty()) {
            return;
        }

        $existingProductIds = Favorito::query()
            ->where('usuario_id', $userId)
            ->pluck('producto_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        foreach ($guestFavorites as $favorite) {
            if (! in_array((int) $favorite->producto_id, $existingProductIds, true)) {
                Favorito::query()->create([
                    'usuario_id' => $userId,
                    'guest_token' => null,
                    'producto_id' => (int) $favorite->producto_id,
                ]);

                $existingProductIds[] = (int) $favorite->producto_id;
            }

            $favorite->delete();
        }
    }

    private static function mergeGuestCartIntoUser(Request $request, int $userId): void
    {
        $guest = self::locateGuestCart($request, 'carrito');

        if (! $guest) {
            return;
        }

        $guest->loadMissing('items');
        if ($guest->items->isEmpty()) {
            $guest->delete();
            return;
        }

        $user = Carrito::query()
            ->where('usuario_id', $userId)
            ->where('estado', 'carrito')
            ->latest('id')
            ->first();

        if (! $user) {
            $guest->update([
                'usuario_id' => $userId,
                'guest_token' => null,
            ]);
            return;
        }

        $user->loadMissing('items');

        foreach ($guest->items as $guestItem) {
            if ($guestItem->producto_id) {
                $userItem = $user->items->firstWhere('producto_id', $guestItem->producto_id);
                if ($userItem) {
                    $userItem->update([
                        'cantidad' => min(99, (int) $userItem->cantidad + (int) $guestItem->cantidad),
                    ]);
                    $guestItem->delete();
                    continue;
                }
            }

            $guestItem->update([
                'carrito_id' => $user->id,
            ]);
        }

        $subtotal = 0.0;
        $user->refresh()->loadMissing('items');
        foreach ($user->items as $item) {
            $subtotal += ((float) $item->precio_unitario) * ((int) $item->cantidad);
        }

        $subtotal = round($subtotal, 2);
        $user->update([
            'subtotal' => $subtotal,
            'total_descuento' => 0,
            'total' => $subtotal,
        ]);

        $guest->refresh()->loadMissing('items');
        if ($guest->items->isEmpty()) {
            $guest->delete();
        }
    }
}