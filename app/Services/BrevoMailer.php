<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BrevoMailer
{
    protected string $apiKey;
    protected string $baseUrl = 'https://api.brevo.com/v3';

    public function __construct()
    {
        $this->apiKey = config('services.brevo.api_key') ?? env('BREVO_API_KEY');
    }

    public function send(string $toEmail, string $toName, string $subject, string $html): bool
    {
        try {
            $response = Http::withHeaders([
                'api-key' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/smtp/email", [
                'to' => [
                    [
                        'email' => $toEmail,
                        'name' => $toName,
                    ],
                ],
                'sender' => [
                    'email' => config('mail.from.address'),
                    'name' => config('mail.from.name'),
                ],
                'subject' => $subject,
                'htmlContent' => $html,
            ]);

            if ($response->successful()) {
                Log::info('Correo enviado exitosamente con Brevo API (Optica)', [
                    'to' => $toEmail,
                    'subject' => $subject,
                ]);

                return true;
            }

            Log::error('Error al enviar correo con Brevo API (Optica)', [
                'status' => $response->status(),
                'response' => $response->json(),
            ]);

            return false;
        } catch (\Throwable $e) {
            Log::error('Excepción al enviar correo con Brevo API (Optica)', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
