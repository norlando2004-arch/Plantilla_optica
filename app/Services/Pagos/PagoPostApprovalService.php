<?php

namespace App\Services\Pagos;

use App\Mail\AdminNewShipmentAlertMail;
use App\Mail\UserPaymentInvoiceMail;
use App\Models\Producto;
use App\Models\Pago;
use App\Services\CompanyNotificationEmailsContent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PagoPostApprovalService
{
    /**
     * Idempotente: usa meta.inventory_updated para no duplicar descuentos de stock ni correos.
     * CRITICAL: Usa transaction lock para evitar race conditions.
     */
    public function processApproved(Pago $pago): void
    {
        Log::info('PagoPostApprovalService::processApproved iniciando', [
            'pago_id' => $pago->id,
            'ref' => $pago->referencia,
            'estado' => $pago->estado,
        ]);

        $itemsSummary = [];
        $finalPago = null;

        try {
            DB::transaction(function () use ($pago, &$itemsSummary, &$finalPago): void {
                /** @var Pago|null $lockedPago */
                $lockedPago = Pago::query()->whereKey($pago->id)->lockForUpdate()->first();
                if (!$lockedPago) {
                    Log::warning('PagoPostApprovalService: Pago no encontrado en transacción', [
                        'pago_id' => $pago->id,
                    ]);
                    return;
                }

                if ($lockedPago->estado !== 'aprobado') {
                    Log::warning('PagoPostApprovalService: Pago no en estado aprobado', [
                        'pago_id' => $lockedPago->id,
                        'ref' => $lockedPago->referencia,
                        'estado_actual' => $lockedPago->estado,
                    ]);
                    $finalPago = $lockedPago;
                    return;
                }

                $lockedPago->loadMissing('carrito.items.producto');
                if (!$lockedPago->carrito) {
                    Log::error('PagoPostApprovalService: Pago sin carrito', [
                        'pago_id' => $lockedPago->id,
                        'ref' => $lockedPago->referencia,
                    ]);
                    $finalPago = $lockedPago;
                    return;
                }

                $metaPago = is_array($lockedPago->meta) ? $lockedPago->meta : [];

                if (!empty($metaPago['inventory_updated'])) {
                    Log::info('PagoPostApprovalService: Ya fue procesado (inventory_updated=true)', [
                        'pago_id' => $lockedPago->id,
                        'ref' => $lockedPago->referencia,
                    ]);
                    $finalPago = $lockedPago;
                    return;
                }

                if (!empty($metaPago['stock_conflict'])) {
                    Log::warning('PagoPostApprovalService: Detectado conflicto de stock previo', [
                        'pago_id' => $lockedPago->id,
                        'ref' => $lockedPago->referencia,
                        'conflict' => $metaPago['stock_conflict'],
                    ]);
                    $lockedPago->update([
                        'estado' => 'rechazado',
                        'pasarela_estado' => 'rechazado_sin_stock',
                        'meta' => $metaPago,
                    ]);
                    $finalPago = $lockedPago->fresh();
                    return;
                }

                $lockedItems = [];
                $insufficientItems = [];

                foreach ($lockedPago->carrito->items as $item) {
                    $producto = Producto::query()->whereKey($item->producto_id)->lockForUpdate()->first();
                    if (!$producto) {
                        Log::warning('PagoPostApprovalService: Producto no encontrado', [
                            'pago_id' => $lockedPago->id,
                            'producto_id' => $item->producto_id,
                        ]);
                        continue;
                    }

                    $itemMeta = is_array($item->meta) ? $item->meta : [];
                    $frameColor = $this->normalizeFrameColorValue(
                        $itemMeta['frame_color'] ?? null,
                        (string) ($metaPago['montura']['color'] ?? ($producto->color ?? ''))
                    );

                    $cantidad = (int) $item->cantidad;
                    if (!$producto->tieneStockParaColor($frameColor, $cantidad)) {
                        Log::warning('PagoPostApprovalService: Stock insuficiente para producto', [
                            'pago_id' => $lockedPago->id,
                            'producto_id' => $producto->id,
                            'producto_nombre' => $producto->nombre,
                            'frame_color' => $frameColor,
                            'cantidad_solicitada' => $cantidad,
                            'stock_disponible' => $producto->stockDisponibleParaColor($frameColor),
                        ]);
                        $insufficientItems[] = [
                            'producto_id' => (int) $producto->id,
                            'nombre' => (string) $producto->nombre,
                            'color' => $frameColor !== '' ? $frameColor : null,
                            'cantidad' => $cantidad,
                            'stock_actual' => $producto->stockDisponibleParaColor($frameColor),
                        ];

                        continue;
                    }

                    $lockedItems[] = [
                        'item' => $item,
                        'producto' => $producto,
                        'frame_color' => $frameColor,
                        'cantidad' => $cantidad,
                    ];
                }

                if ($insufficientItems !== []) {
                    $metaPago['stock_conflict'] = [
                        'occurred_at' => now()->toISOString(),
                        'reason' => 'stock_unavailable_during_approval',
                        'message' => 'Lo sentimos, esta montura se acaba de ocupar en otra compra y ya no tiene stock disponible.',
                        'items' => $insufficientItems,
                    ];

                    Log::error('PagoPostApprovalService: Rechazado por conflicto de stock', [
                        'pago_id' => $lockedPago->id,
                        'ref' => $lockedPago->referencia,
                        'items' => $insufficientItems,
                    ]);

                    $lockedPago->update([
                        'estado' => 'rechazado',
                        'pasarela_estado' => 'rechazado_sin_stock',
                        'meta' => $metaPago,
                    ]);

                    $finalPago = $lockedPago->fresh();
                    return;
                }

                foreach ($lockedItems as $lockedItem) {
                    /** @var Producto $producto */
                    $producto = $lockedItem['producto'];
                    $frameColor = (string) $lockedItem['frame_color'];
                    $cantidad = (int) $lockedItem['cantidad'];

                    $existenciasRestantes = $producto->decrementStockForColor($frameColor, $cantidad);

                    Log::info('PagoPostApprovalService: Stock decrementado', [
                        'pago_id' => $lockedPago->id,
                        'producto_id' => $producto->id,
                        'producto_nombre' => $producto->nombre,
                        'frame_color' => $frameColor,
                        'cantidad' => $cantidad,
                        'existencias_restantes' => $existenciasRestantes,
                    ]);

                    $itemsSummary[] = [
                        'nombre' => $producto->nombre,
                        'color' => $frameColor !== '' ? $frameColor : null,
                        'cantidad' => $cantidad,
                        'existencias_restantes' => $existenciasRestantes,
                    ];
                }

                $metaPago['inventory_updated'] = true;
                $metaPago['post_approval_processed_at'] = now()->toISOString();
                $lockedPago->update([
                    'meta' => $metaPago,
                ]);

                Log::info('PagoPostApprovalService: Inventario actualizado en BD', [
                    'pago_id' => $lockedPago->id,
                    'ref' => $lockedPago->referencia,
                    'items_count' => count($itemsSummary),
                ]);

                $finalPago = $lockedPago->fresh();
            }, 3);
        } catch (\Throwable $e) {
            Log::error('PagoPostApprovalService: Excepción en transacción de inventario', [
                'pago_id' => $pago->id,
                'ref' => $pago->referencia,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }

        if (!$finalPago || $finalPago->estado !== 'aprobado') {
            Log::warning('PagoPostApprovalService: Pago no en estado aprobado después de procesamiento', [
                'pago_id' => $pago->id,
                'ref' => $pago->referencia,
                'estado_final' => $finalPago?->estado,
            ]);
            return;
        }

        Log::info('PagoPostApprovalService: Iniciando envío de emails', [
            'pago_id' => $finalPago->id,
            'ref' => $finalPago->referencia,
        ]);

        $metaPago = is_array($finalPago->meta) ? $finalPago->meta : [];
        $invoiceEmail = $this->resolveInvoiceEmail($finalPago, $metaPago);

        $blockedNotificationEmails = [
            'norlando2004@gmail.com',
        ];
        $blockedNotificationEmails = array_map(
            static fn ($email) => mb_strtolower(trim((string) $email)),
            $blockedNotificationEmails
        );

        $isBlockedNotificationEmail = static function (?string $email) use ($blockedNotificationEmails): bool {
            if (!is_string($email) || trim($email) === '') {
                return false;
            }

            return in_array(mb_strtolower(trim($email)), $blockedNotificationEmails, true);
        };

        if ($isBlockedNotificationEmail($invoiceEmail)) {
            Log::info('PagoPostApprovalService: Email bloqueado para usuario', [
                'pago_id' => $finalPago->id,
                'ref' => $finalPago->referencia,
                'blocked_email' => $invoiceEmail,
            ]);
            $invoiceEmail = null;
        }

        $companyNotificationEmails = CompanyNotificationEmailsContent::load()['emails'] ?? [];
        $companyNotificationEmails = is_array($companyNotificationEmails) ? $companyNotificationEmails : [];

        $adminRecipients = [];
        foreach ($companyNotificationEmails as $email) {
            $normalized = mb_strtolower(trim((string) $email));
            if ($normalized !== '' && filter_var($normalized, FILTER_VALIDATE_EMAIL) && !$isBlockedNotificationEmail($normalized)) {
                $adminRecipients[] = $normalized;
            }
        }

        $adminRecipients = array_values(array_unique($adminRecipients));

        $pagoId = $finalPago->id;

        if ($invoiceEmail) {
            dispatch(function () use ($pagoId, $invoiceEmail, $itemsSummary): void {
                try {
                    $freshPago = Pago::query()->find($pagoId);

                    if (!$freshPago) {
                        Log::warning('PagoPostApprovalService: Pago no encontrado al enviar email usuario', [
                            'pago_id' => $pagoId,
                        ]);
                        return;
                    }

                    Mail::to($invoiceEmail)->send(new UserPaymentInvoiceMail($freshPago, $itemsSummary));
                    Log::info('PagoPostApprovalService: Email de factura enviado a usuario', [
                        'pago_id' => $pagoId,
                        'email' => $invoiceEmail,
                    ]);
                } catch (\Throwable $e) {
                    Log::error('PagoPostApprovalService: Error al enviar email de factura', [
                        'pago_id' => $pagoId,
                        'email' => $invoiceEmail,
                        'error' => $e->getMessage(),
                    ]);
                }
            })->afterResponse();
        } else {
            Log::warning('PagoPostApprovalService: No hay email de factura para enviar', [
                'pago_id' => $pagoId,
            ]);
        }

        if (!empty($adminRecipients)) {
            $primaryAdminRecipient = array_shift($adminRecipients);

            dispatch(function () use ($pagoId, $invoiceEmail, $primaryAdminRecipient, $adminRecipients): void {
                try {
                    $freshPago = Pago::query()->find($pagoId);

                    if (!$freshPago) {
                        Log::warning('PagoPostApprovalService: Pago no encontrado al enviar email admin', [
                            'pago_id' => $pagoId,
                        ]);
                        return;
                    }

                    $mailer = Mail::to($primaryAdminRecipient);

                    if (!empty($adminRecipients)) {
                        $mailer->bcc($adminRecipients);
                    }

                    $mailer->send(new AdminNewShipmentAlertMail($freshPago));
                    Log::info('PagoPostApprovalService: Email de nuevo pedido enviado a admin', [
                        'pago_id' => $pagoId,
                        'primary_recipient' => $primaryAdminRecipient,
                        'bcc_count' => count($adminRecipients),
                    ]);
                } catch (\Throwable $e) {
                    Log::error('PagoPostApprovalService: Error al enviar email de notificación admin', [
                        'pago_id' => $pagoId,
                        'recipient_email' => $primaryAdminRecipient,
                        'error' => $e->getMessage(),
                    ]);
                }
            })->afterResponse();
        } else {
            Log::warning('PagoPostApprovalService: No hay emails de admin configurados', [
                'pago_id' => $pagoId,
            ]);
        }

        // CRÍTICO: Preparar datos de envío para que aparezcan en "Próximos Envíos" automáticamente
        // Esto asegura que TODOS los datos necesarios estén guardados en meta cuando el pago se aprueba
        try {
            Log::info('PagoPostApprovalService: Preparando datos de envío para Próximos Envíos', [
                'pago_id' => $finalPago->id,
                'ref' => $finalPago->referencia,
            ]);

            $freshPago = Pago::query()->find($finalPago->id);
            if ($freshPago) {
                app(ShippingDataPreparationService::class)->prepareShippingData($freshPago);
            }
        } catch (\Throwable $e) {
            Log::error('PagoPostApprovalService: Error preparando datos de envío', [
                'pago_id' => $finalPago->id,
                'ref' => $finalPago->referencia,
                'error' => $e->getMessage(),
            ]);
            // No lanzar excepción - la preparación de datos no debe interrumpir el flujo principal
        }

        Log::info('PagoPostApprovalService::processApproved completado', [
            'pago_id' => $finalPago->id,
            'ref' => $finalPago->referencia,
            'invoice_email' => $invoiceEmail,
            'admin_recipients' => count($adminRecipients) + 1,
        ]);
    }

    private function resolveInvoiceEmail(Pago $pago, array $metaPago): ?string
    {
        $invoiceEmail = null;

        if (isset($metaPago['guest']) && is_array($metaPago['guest'])) {
            $invoiceEmail = $metaPago['guest']['correo'] ?? null;
        }

        if (!$invoiceEmail && isset($metaPago['cliente']) && is_array($metaPago['cliente'])) {
            $invoiceEmail = $metaPago['cliente']['correo'] ?? null;
        }

        if (!$invoiceEmail) {
            $invoiceEmail = $metaPago['correo'] ?? null;
        }

        if (!$invoiceEmail) {
            $metaCarrito = is_array($pago->carrito?->meta) ? $pago->carrito->meta : [];
            if (isset($metaCarrito['cliente']) && is_array($metaCarrito['cliente'])) {
                $invoiceEmail = $metaCarrito['cliente']['correo'] ?? null;
            }
        }

        if (!$invoiceEmail) {
            $invoiceEmail = $pago->carrito?->usuario?->correo;
        }

        if (is_string($invoiceEmail)) {
            $invoiceEmail = trim($invoiceEmail);
        }

        if (!is_string($invoiceEmail) || $invoiceEmail === '' || !filter_var($invoiceEmail, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        return $invoiceEmail;
    }

    private function normalizeFrameColorValue(mixed $value, string $fallback = ''): string
    {
        $rawValues = [];
        $pushRaw = static function (mixed $candidate) use (&$rawValues): void {
            if (is_string($candidate) || is_numeric($candidate)) {
                $rawValues[] = trim((string) $candidate);
            }
        };

        if (is_array($value)) {
            array_walk_recursive($value, static function (mixed $candidate) use ($pushRaw): void {
                $pushRaw($candidate);
            });
        } else {
            $pushRaw($value);
        }

        if ($rawValues === [] && trim($fallback) !== '') {
            $rawValues[] = trim($fallback);
        }

        $parts = [];
        $seen = [];

        foreach ($rawValues as $rawValue) {
            if (!is_string($rawValue) || $rawValue === '') {
                continue;
            }

            $segments = preg_split('/\s*,\s*/u', $rawValue) ?: [];
            foreach ($segments as $segment) {
                $name = trim((string) $segment);
                if ($name === '') {
                    continue;
                }

                $key = mb_strtolower(trim(Str::ascii($name)));
                if ($key === '' || isset($seen[$key])) {
                    continue;
                }

                $seen[$key] = true;
                $parts[] = $name;
            }
        }

        return implode(', ', $parts);
    }
}
