<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;

class TrackUserActivity
{
    public function handle(Request $request, Closure $next)
    {
        // No registrar solicitudes de activos estáticos ni preflight requests
        $path = $request->path();
        $isAsset = $request->method() === 'OPTIONS' || 
                   preg_match('~\.(js|css|jpg|jpeg|png|gif|ico|svg|woff|woff2|ttf|eot)$~i', $path) ||
                   str_starts_with($path, 'build/') ||
                   str_starts_with($path, 'image/') ||
                   str_starts_with($path, 'images/');
        
        if (!$isAsset) {
            $identifier = null;

            // Si está autenticado, usar su ID
            if (Auth::check()) {
                $usuario = Auth::user();
                if ($usuario) {
                    $identifier = "auth:" . $usuario->id;
                }
            }

            // Si no está autenticado, usar token de invitado
            if (!$identifier) {
                $guestToken = $request->cookie('optica_guest_token') ?? 
                             $request->header('X-Guest-Token') ?? 
                             ($request->user() ? null : null);
                
                if ($guestToken) {
                    $identifier = "guest:" . $guestToken;
                }
            }

            // Si aún no hay identificador, generar uno basado en IP + User-Agent
            if (!$identifier) {
                $ip = $request->ip();
                $userAgent = substr(md5($request->header('User-Agent')), 0, 16);
                $identifier = "visitor:" . $ip . ":" . $userAgent;
            }

            // Guardar en Redis/Cache con TTL de 5 minutos
            if ($identifier) {
                Cache::put(
                    "user_activity:" . md5($identifier),
                    $identifier,
                    now()->addMinutes(5)
                );
            }
        }

        return $next($request);
    }
}
