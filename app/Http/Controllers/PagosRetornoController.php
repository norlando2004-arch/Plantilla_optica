<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use App\Services\Bold\BoldLinkClient;
use App\Services\Pagos\PagoFulfillmentService;
use App\Services\Pagos\PagoPostApprovalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PagosRetornoController extends Controller
{
    public function handle(Request $request, string $driver): RedirectResponse
    {
        // Endpoint genérico para que la pasarela redirija al usuario.
        // Convención: pasar nuestra referencia como query string ?ref=PAY-...
        $ref = (string) $request->query('ref', '');
        $ref = trim($ref);

        Log::info('PagosRetornoController: Usuario retornando de pasarela', [
            'driver' => $driver,
            'ref' => $ref,
        ]);

        if ($ref === '') {
            Log::warning('PagosRetornoController: No hay referencia en query string');
            return redirect()->route('gafas.index');
        }

        $pago = Pago::query()->where('referencia', $ref)->first();
        if (!$pago) {
            Log::warning('PagosRetornoController: Pago no encontrado', [
                'driver' => $driver,
                'ref' => $ref,
            ]);
            return redirect()->route('gafas.index');
        }

        Log::info('PagosRetornoController: Pago encontrado', [
            'pago_id' => $pago->id,
            'driver' => $driver,
            'pasarela' => $pago->pasarela,
            'estado' => $pago->estado,
            'ref' => $ref,
        ]);

        if ($driver === 'bold' && $pago->pasarela === 'bold') {
            // Intenta actualizar el estado desde Bold
            $pago = $this->tryUpdateFromBoldLink($pago);

            Log::info('PagosRetornoController: Estado después de consultar Bold', [
                'pago_id' => $pago->id,
                'ref' => $pago->referencia,
                'estado' => $pago->estado,
            ]);

            // Si el pago está aprobado, procesar fulfillment y post-approval
            if ($pago->estado === 'aprobado') {
                Log::info('PagosRetornoController: Pago aprobado, procesando fulfillment y post-approval', [
                    'pago_id' => $pago->id,
                    'ref' => $pago->referencia,
                ]);

                try {
                    // Intenta fulfillment
                    app(PagoFulfillmentService::class)->handleApproved($pago, true);
                    Log::info('PagosRetornoController: Fulfillment completado', [
                        'pago_id' => $pago->id,
                        'ref' => $pago->referencia,
                    ]);
                } catch (\Throwable $e) {
                    Log::error('PagosRetornoController: Error en fulfillment', [
                        'pago_id' => $pago->id,
                        'ref' => $pago->referencia,
                        'error' => $e->getMessage(),
                    ]);
                    // Continúa de todos modos - el post-approval podría completarse
                }

                try {
                    // Intenta post-approval (incluso si fulfillment falló)
                    app(PagoPostApprovalService::class)->processApproved($pago);
                    Log::info('PagosRetornoController: Post-approval completado', [
                        'pago_id' => $pago->id,
                        'ref' => $pago->referencia,
                    ]);
                } catch (\Throwable $e) {
                    Log::error('PagosRetornoController: Error en post-approval', [
                        'pago_id' => $pago->id,
                        'ref' => $pago->referencia,
                        'error' => $e->getMessage(),
                    ]);
                    // Log pero no falla - el usuario ya vio la confirmación de Bold
                }

                Log::info('PagosRetornoController: Redirigiendo a página de aprobación', [
                    'pago_id' => $pago->id,
                    'ref' => $pago->referencia,
                ]);
                return redirect()->route('pagos.approved', $pago);
            }
        }

        Log::info('PagosRetornoController: Redirigiendo a página de estado de pago', [
            'pago_id' => $pago->id,
            'ref' => $pago->referencia,
            'estado' => $pago->estado,
        ]);
        return redirect()->route('pagos.show', $pago);
    }

    private function tryUpdateFromBoldLink(Pago $pago): Pago
    {
        if ($pago->estado === 'aprobado' || $pago->estado === 'rechazado') {
            return $pago;
        }

        $cfg = (array) config('pagos.drivers.bold', []);
        if (!(bool) ($cfg['enabled'] ?? false)) {
            return $pago;
        }

        $identityKey = trim((string) ($cfg['identity_key'] ?? ''));
        if ($identityKey === '') {
            return $pago;
        }

        $meta = is_array($pago->meta) ? $pago->meta : [];
        $paymentLink = (string) ($meta['bold']['payment_link'] ?? $pago->pasarela_transaccion_id ?? '');
        $paymentLink = trim($paymentLink);
        if ($paymentLink === '') {
            return $pago;
        }

        try {
            $client = new BoldLinkClient(
                (string) ($cfg['base_url'] ?? 'https://integrations.api.bold.co'),
                $identityKey,
                (int) ($cfg['timeout_seconds'] ?? 20),
                (bool) ($cfg['verify_ssl'] ?? true),
            );

            $json = $client->getLink($paymentLink);
            $estado = $this->guessEstadoFromBoldResponse($json);
            if (!$estado) {
                return $pago;
            }

            $meta['bold'] = array_merge((array) ($meta['bold'] ?? []), [
                'last_checked_at' => now()->toISOString(),
            ]);

            $pago->update([
                'estado' => $estado,
                'pasarela_estado' => 'checked',
                'pasarela_transaccion_id' => $paymentLink,
                'meta' => $meta,
            ]);

            return $pago->fresh();
        } catch (\Throwable $e) {
            Log::warning('No se pudo consultar estado Bold en retorno', [
                'pago_id' => $pago->id,
                'ref' => $pago->referencia,
                'error' => $e->getMessage(),
            ]);

            return $pago;
        }
    }

    private function guessEstadoFromBoldResponse(array $json): ?string
    {
        $text = strtoupper((string) json_encode($json));

        foreach (['SALE_APPROVED', 'APPROVED', 'PAID', 'SUCCESS'] as $needle) {
            if (str_contains($text, $needle)) {
                return 'aprobado';
            }
        }

        foreach (['SALE_REJECTED', 'REJECTED', 'DECLINED', 'FAILED'] as $needle) {
            if (str_contains($text, $needle)) {
                return 'rechazado';
            }
        }

        return null;
    }
}
