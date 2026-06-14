<?php

namespace App\Console\Commands;

use App\Models\Pago;
use App\Services\Pagos\PagoFulfillmentService;
use App\Services\Pagos\PagoPostApprovalService;
use App\Services\Pagos\ShippingDataPreparationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RetryInconsistentPayments extends Command
{
    protected $signature = 'payments:retry-inconsistent 
                          {--dry-run : Mostrar qué se haría sin ejecutar}
                          {--verbose : Mostrar detalles de cada pago}';

    protected $description = 'Detecta y reintenta pagos aprobados que no fueron completamente procesados';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $verbose = (bool) $this->option('verbose');

        $this->info('🔍 Buscando pagos inconsistentes...');

        // Pagos aprobados pero sin fulfillment completado
        $incompletePayments = Pago::query()
            ->where('estado', 'aprobado')
            ->whereRaw("(meta->>'fulfillment'->>'completed_at' IS NULL)")
            ->orWhere(function ($query) {
                $query->where('estado', 'aprobado')
                    ->whereRaw("(meta->>'post_approval_processed_at' IS NULL)");
            })
            ->orderBy('created_at', 'desc')
            ->get();

        if ($incompletePayments->isEmpty()) {
            $this->info('✅ No hay pagos inconsistentes.');
            return 0;
        }

        $this->warn("⚠️  Encontrados {$incompletePayments->count()} pagos inconsistentes:");

        $successCount = 0;
        $failCount = 0;

        foreach ($incompletePayments as $pago) {
            $meta = is_array($pago->meta) ? $pago->meta : [];
            $fulfillmentCompleted = !empty($meta['fulfillment']['completed_at']);
            $postApprovalCompleted = !empty($meta['post_approval_processed_at']);

            $status = [
                'Fulfillment' => $fulfillmentCompleted ? '✅' : '❌',
                'PostApproval' => $postApprovalCompleted ? '✅' : '❌',
            ];

            $this->line("");
            $this->info("Pago: {$pago->referencia} (ID: {$pago->id})");
            $this->line("  Creado: {$pago->created_at}");
            $this->line("  Estado: {$status['Fulfillment']} Fulfillment, {$status['PostApproval']} PostApproval");

            if ($verbose) {
                $this->line("  Carrito: {$pago->carrito_id}");
                $this->line("  Monto: {$pago->monto} {$pago->moneda}");
            }

            if ($dryRun) {
                $this->line("  [DRY RUN] Se intentaría procesar este pago");
                continue;
            }

            try {
                if (!$fulfillmentCompleted) {
                    $this->line("  → Ejecutando fulfillment...");
                    app(PagoFulfillmentService::class)->handleApproved($pago, false);
                    $this->line("  ✅ Fulfillment exitoso");
                }

                if (!$postApprovalCompleted) {
                    $this->line("  → Ejecutando post-approval...");
                    app(PagoPostApprovalService::class)->processApproved($pago);
                    $this->line("  ✅ Post-approval exitoso");
                }

                // Preparar datos de envío para Próximos Envíos
                $this->line("  → Preparando datos de envío...");
                app(ShippingDataPreparationService::class)->prepareShippingData($pago);
                $this->line("  ✅ Datos de envío preparados");

                $successCount++;
                $this->info("  ✅ Pago procesado exitosamente");
            } catch (\Throwable $e) {
                $failCount++;
                $this->error("  ❌ Error: {$e->getMessage()}");
                Log::error('RetryInconsistentPayments error', [
                    'pago_id' => $pago->id,
                    'ref' => $pago->referencia,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        $this->line("");
        $this->info("📊 Resumen:");
        $this->line("  Procesados exitosamente: {$successCount}");
        $this->line("  Errores: {$failCount}");

        if ($dryRun) {
            $this->warn("📝 Este fue un DRY RUN. Sin --dry-run se ejecutarían los cambios.");
        }

        return $failCount > 0 ? 1 : 0;
    }
}
