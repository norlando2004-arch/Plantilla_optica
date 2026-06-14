<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use App\Services\Pagos\PagoFulfillmentService;
use App\Services\Pagos\PagoPostApprovalService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class PagosWebhookController extends Controller
{
    public function handle(Request $request, string $driver): Response
    {
        Log::info('PagosWebhookController: Webhook recibido', [
            'driver' => $driver,
            'method' => $request->method(),
        ]);

        if ($driver !== 'bold') {
            Log::info('PagosWebhookController: Webhook para driver no-Bold ignorado', [
                'driver' => $driver,
            ]);
            return response('ok', 200);
        }

        if (!$this->verifyBoldSignature($request)) {
            Log::error('PagosWebhookController: Firma Bold inválida', [
                'header' => $request->header('x-bold-signature'),
            ]);
            return response('invalid signature', 401);
        }

        $payload = $request->json()->all();

        Log::debug('PagosWebhookController: Payload recibido', [
            'payload_keys' => array_keys($payload),
        ]);

        $ref = (string) (
            data_get($payload, 'data.metadata.reference')
                ?? data_get($payload, 'metadata.reference')
                ?? data_get($payload, 'data.reference')
                ?? data_get($payload, 'reference')
                ?? ''
        );
        $ref = trim($ref);

        if ($ref === '') {
            Log::warning('PagosWebhookController: Payload sin referencia identificable', [
                'payload' => $payload,
            ]);
            return response('ok', 200);
        }

        Log::info('PagosWebhookController: Referencia extraída', [
            'ref' => $ref,
        ]);

        $pago = Pago::query()->where('referencia', $ref)->first();
        if (!$pago) {
            Log::error('PagosWebhookController: Pago no encontrado en BD', [
                'ref' => $ref,
            ]);
            return response('ok', 200);
        }

        Log::info('PagosWebhookController: Pago encontrado', [
            'pago_id' => $pago->id,
            'ref' => $ref,
            'estado_actual' => $pago->estado,
        ]);

        $event = (string) (
            data_get($payload, 'event_type')
                ?? data_get($payload, 'event')
                ?? data_get($payload, 'type')
                ?? ''
        );
        $event = strtoupper(trim($event));

        $estado = $this->mapBoldPayloadToEstado($payload, $event);

        Log::info('PagosWebhookController: Estado mapeado', [
            'pago_id' => $pago->id,
            'ref' => $ref,
            'event' => $event,
            'estado_mapeado' => $estado,
        ]);

        $paymentLink = (string) (
            data_get($payload, 'data.payment_link')
                ?? data_get($payload, 'data.metadata.payment_link')
                ?? data_get($payload, 'payment_link')
                ?? ''
        );
        $paymentLink = trim($paymentLink);

        $meta = is_array($pago->meta) ? $pago->meta : [];
        $meta['bold'] = array_merge((array) ($meta['bold'] ?? []), [
            'last_webhook_at' => now()->toISOString(),
            'last_event' => $event ?: null,
        ]);

        $update = [
            'pasarela_estado' => $event ?: 'webhook',
            'meta' => $meta,
        ];

        if ($paymentLink !== '') {
            $update['pasarela_transaccion_id'] = $paymentLink;
            $meta['bold']['payment_link'] = $meta['bold']['payment_link'] ?? $paymentLink;
            $update['meta'] = $meta;
        }

        if ($estado) {
            $update['estado'] = $estado;
        }

        try {
            $pago->update($update);
            Log::info('PagosWebhookController: Pago actualizado en BD', [
                'pago_id' => $pago->id,
                'ref' => $ref,
                'updates' => array_keys($update),
            ]);
        } catch (\Throwable $e) {
            Log::error('PagosWebhookController: Error al actualizar pago', [
                'pago_id' => $pago->id,
                'ref' => $ref,
                'error' => $e->getMessage(),
            ]);
            // Continúa - intentará procesar de todos modos
        }

        if (($estado ?? null) === 'aprobado') {
            Log::info('PagosWebhookController: Iniciando fulfillment y post-approval', [
                'pago_id' => $pago->id,
                'ref' => $ref,
            ]);

            try {
                app(PagoFulfillmentService::class)->handleApproved($pago, false);
                Log::info('PagosWebhookController: Fulfillment completado', [
                    'pago_id' => $pago->id,
                    'ref' => $ref,
                ]);
            } catch (\Throwable $e) {
                Log::error('PagosWebhookController: Error en fulfillment', [
                    'pago_id' => $pago->id,
                    'ref' => $ref,
                    'error' => $e->getMessage(),
                ]);
                // Continúa - intentará post-approval de todos modos
            }

            try {
                app(PagoPostApprovalService::class)->processApproved($pago);
                Log::info('PagosWebhookController: Post-approval completado', [
                    'pago_id' => $pago->id,
                    'ref' => $ref,
                ]);
            } catch (\Throwable $e) {
                Log::error('PagosWebhookController: Error en post-approval', [
                    'pago_id' => $pago->id,
                    'ref' => $ref,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                // No falla el webhook - Bold espera 200 OK
                // El usuario puede reintentar desde return URL
            }
        }

        Log::info('PagosWebhookController: Respondiendo con OK', [
            'pago_id' => $pago->id,
            'ref' => $ref,
        ]);
        return response('ok', 200);
    }

    private function verifyBoldSignature(Request $request): bool
    {
        $signature = (string) $request->header('x-bold-signature', '');
        $signature = trim($signature);
        $cfg = (array) config('pagos.drivers.bold', []);
        $secret = (string) ($cfg['webhook_secret'] ?? '');
        $allowUnsigned = (bool) ($cfg['allow_unsigned_webhooks'] ?? false);

        if (trim($secret) === '') {
            // Entornos de desarrollo/sandbox donde Bold no provee firma.
            return $allowUnsigned || app()->environment('local');
        }

        if ($signature === '') {
            return false;
        }

        $body = (string) $request->getContent();

        // Variante documentada: HMAC-SHA256( base64(body) ) -> base64
        $base64Body = base64_encode($body);
        $computedA = base64_encode(hash_hmac('sha256', $base64Body, $secret, true));

        // Variante alternativa: HMAC-SHA256( body ) -> base64
        $computedB = base64_encode(hash_hmac('sha256', $body, $secret, true));

        return hash_equals($computedA, $signature) || hash_equals($computedB, $signature);
    }

    private function mapBoldPayloadToEstado(array $payload, string $event): ?string
    {
        $candidates = [$event];

        $statusCandidates = [
            data_get($payload, 'data.status'),
            data_get($payload, 'data.transaction.status'),
            data_get($payload, 'data.payment.status'),
            data_get($payload, 'status'),
            data_get($payload, 'transaction.status'),
            data_get($payload, 'payment.status'),
        ];

        foreach ($statusCandidates as $candidate) {
            if (!is_scalar($candidate)) {
                continue;
            }
            $candidates[] = strtoupper(trim((string) $candidate));
        }

        if ($candidates === []) {
            return null;
        }

        $haystack = implode(' ', array_filter($candidates, static fn ($v) => is_string($v) && $v !== ''));
        if ($haystack === '') {
            return null;
        }

        if (str_contains($haystack, 'APPROVED') || str_contains($haystack, 'PAID') || str_contains($haystack, 'SUCCESS')) {
            return 'aprobado';
        }

        if (str_contains($haystack, 'REJECT') || str_contains($haystack, 'DECLIN') || str_contains($haystack, 'FAILED') || str_contains($haystack, 'CANCEL')) {
            return 'rechazado';
        }

        return null;
    }
}
