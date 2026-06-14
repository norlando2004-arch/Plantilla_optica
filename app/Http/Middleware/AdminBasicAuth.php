<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminBasicAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !in_array((int) $user->rol_id, [2, 3, 4], true)) {
            return response()->view('errors.access-denied', [
                'redirectUrl' => route('landing'),
            ], 403);
        }

        return $next($request);
    }
}
