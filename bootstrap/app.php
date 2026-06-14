<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $trustedProxies = env('TRUSTED_PROXIES');

        // En local sin proxy explícito no añadimos nada.
        // En VPS con Nginx en el mismo servidor usa TRUSTED_PROXIES=127.0.0.1
        // En Render/plataformas gestionadas usa TRUSTED_PROXIES=*
        if (! is_null($trustedProxies) && $trustedProxies !== '') {
            $middleware->trustProxies(
                at: $trustedProxies,
                headers:
                    \Illuminate\Http\Request::HEADER_X_FORWARDED_FOR |
                    \Illuminate\Http\Request::HEADER_X_FORWARDED_HOST |
                    \Illuminate\Http\Request::HEADER_X_FORWARDED_PORT |
                    \Illuminate\Http\Request::HEADER_X_FORWARDED_PROTO,
            );
        }

        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminBasicAuth::class,
            'admin3' => \App\Http\Middleware\AdminRoleThreeOnly::class,
        ]);

        $middleware->appendToGroup('web', \App\Http\Middleware\EnsureGuestToken::class);
        $middleware->appendToGroup('web', \App\Http\Middleware\ApplyDatabaseTimeout::class);
        $middleware->appendToGroup('web', \App\Http\Middleware\TrackUserActivity::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Http\Exceptions\PostTooLargeException $exception, \Illuminate\Http\Request $request) {
            $maxPostSize = ini_get('post_max_size') ?: 'el configurado en el servidor';
            $maxUploadSize = ini_get('upload_max_filesize') ?: 'el configurado en el servidor';
            $message = "La carga excede el limite permitido por el servidor. Reduce el peso total de los archivos o subelos por partes. Limite por solicitud: {$maxPostSize}. Limite por archivo: {$maxUploadSize}.";

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $message,
                ], 413);
            }

            return back()->withInput()->withErrors([
                'upload' => $message,
            ]);
        });
    })->create();

