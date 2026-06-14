<?php

namespace App\Http\Controllers;

use App\Services\BrevoMailer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class TestMailController extends Controller
{
    private const DEFAULT_RECIPIENT = 'ronaldotoledoher49@gmail.com';

    public function __invoke(Request $request): JsonResponse
    {
        $defaultRecipient = self::DEFAULT_RECIPIENT;
        $to = trim((string) $request->query('to', $defaultRecipient));

        if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return response()->json([
                'ok' => false,
                'message' => 'Debes indicar un correo valido en el parametro to.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $brevoEnabledEnv = env('USE_BREVO_API', true);
            $brevoApiKey = config('services.brevo.api_key') ?? env('BREVO_API_KEY');
            $fromAddress = config('mail.from.address');

            $canUseBrevo = $brevoEnabledEnv
                && !empty($brevoApiKey)
                && !empty($fromAddress);

            if ($canUseBrevo) {
                $html = '<p>Este es un correo de prueba enviado desde el servidor de Optica usando la API HTTP de Brevo.</p>';
                $sent = app(BrevoMailer::class)->send(
                    $to,
                    'Usuario Optica',
                    'Prueba de correo - Optica (Brevo API)',
                    $html,
                );

                if ($sent) {
                    return response()->json([
                        'ok' => true,
                        'message' => 'Correo de prueba enviado correctamente usando Brevo API.',
                        'via' => 'brevo-api',
                        'to' => $to,
                    ]);
                }

                Log::warning('test-mail (Optica): Brevo API falló, intentando fallback SMTP');
            }

            Mail::mailer('smtp')->raw(
                "Prueba de correo desde Optica en " . now()->toDateTimeString() . "\n\n" .
                "Mailer: smtp\n" .
                "Host: " . config('mail.mailers.smtp.host') . "\n" .
                "App URL: " . config('app.url'),
                static function ($message) use ($to): void {
                    $message
                        ->to($to)
                        ->subject('Prueba de correo - Optica');
                }
            );

            return response()->json([
                'ok' => true,
                'message' => 'Correo de prueba enviado correctamente por SMTP (fallback).',
                'mailer' => 'smtp',
                'host' => config('mail.mailers.smtp.host'),
                'port' => config('mail.mailers.smtp.port'),
                'from' => config('mail.from.address'),
                'to' => $to,
            ]);
        } catch (Throwable $e) {
            Log::warning('Fallo en endpoint de prueba de correo (Optica)', [
                'to' => $to,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'ok' => false,
                'message' => 'Fallo el envio del correo de prueba.',
                'mailer' => 'smtp',
                'host' => config('mail.mailers.smtp.host'),
                'port' => config('mail.mailers.smtp.port'),
                'from' => config('mail.from.address'),
                'to' => $to,
                'error' => $e->getMessage(),
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }
    }
}