<?php

namespace App\Http\Middleware;

use App\Services\GuestShopperService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureGuestToken
{
    public function handle(Request $request, Closure $next): Response
    {
        GuestShopperService::ensureGuestToken($request);

        if (Auth::check()) {
            GuestShopperService::mergeIntoUser($request, (int) Auth::id());
        }

        return $next($request);
    }
}