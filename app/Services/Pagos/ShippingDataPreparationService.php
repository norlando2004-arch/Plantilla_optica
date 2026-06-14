<?php

namespace App\Services\Pagos;

use App\Models\Pago;
use App\Models\PerfilCliente;
use App\Models\GafaPrescription;
use Illuminate\Support\Facades\Log;

class ShippingDataPreparationService
{
    /**
     * Prepara y guarda TODOS los datos necesarios para "Próximos Envíos"
     * Esto se ejecuta cuando el pago se aprueba, asegurando que la información
     * está lista para el panel de "Próximos Envíos" sin intervención manual.
     */
    public function prepareShippingData(Pago $pago): void
    {
        Log::info('ShippingDataPreparationService: iniciando preparación de datos de envío', [
            'pago_id' => $pago->id,
            'ref' => $pago->referencia,
        ]);

        try {
            $pago->loadMissing(['carrito.usuario', 'carrito.items.producto']);

            $meta = is_array($pago->meta) ? $pago->meta : [];
            $carritoMeta = is_array($pago->carrito?->meta) ? $pago->carrito->meta : [];

            // 1. Asegurar que prescription_id está en meta (para ver la fórmula en Próximos Envíos)
            if (empty($meta['prescription_id']) && !empty($carritoMeta['prescription_id'])) {
                $meta['prescription_id'] = (int) $carritoMeta['prescription_id'];
                Log::info('ShippingDataPreparationService: prescription_id guardado en meta', [
                    'pago_id' => $pago->id,
                    'prescription_id' => $meta['prescription_id'],
                ]);
            }

            // 2. Guardar datos de perfil cliente si no están guardados
            $perfilId = (int) ($meta['perfil_cliente_id'] ?? $carritoMeta['perfil_cliente_id'] ?? 0);
            if ($perfilId) {
                $perfil = PerfilCliente::query()->find($perfilId);
                if ($perfil) {
                    // Guardar datos del perfil en meta para fácil acceso en Próximos Envíos
                    $meta['perfil_data'] = [
                        'id' => $perfil->id,
                        'numero_documento' => $perfil->numero_documento,
                        'tipo_documento' => $perfil->tipo_documento,
                        'nombre' => $perfil->usuario?->nombre ?? null,
                        'correo' => $perfil->usuario?->correo ?? null,
                        'telefono' => $perfil->telefono,
                        'direccion' => $perfil->direccion,
                        'ciudad' => $perfil->ciudad,
                        'fecha_nacimiento' => $perfil->fecha_nacimiento,
                        'genero' => $perfil->genero,
                    ];

                    Log::info('ShippingDataPreparationService: datos de perfil guardados', [
                        'pago_id' => $pago->id,
                        'perfil_id' => $perfilId,
                    ]);
                }
            } else {
                // Para compras de guest, guardar datos de guest en estructura clara
                $guest = is_array($meta['guest'] ?? null) ? $meta['guest'] : [];
                if (!empty($guest)) {
                    $meta['perfil_data'] = [
                        'numero_documento' => $guest['numero_documento'] ?? null,
                        'tipo_documento' => $guest['tipo_documento'] ?? null,
                        'nombre' => $guest['nombre'] ?? null,
                        'correo' => $guest['correo'] ?? null,
                        'telefono' => $guest['telefono'] ?? null,
                        'direccion' => $guest['direccion'] ?? null,
                        'ciudad' => $guest['ciudad'] ?? null,
                        'fecha_nacimiento' => $guest['fecha_nacimiento'] ?? null,
                        'genero' => $guest['genero'] ?? null,
                    ];

                    Log::info('ShippingDataPreparationService: datos de guest guardados', [
                        'pago_id' => $pago->id,
                    ]);
                }
            }

            // 3. Guardar datos del pedido/carrito en meta
            if ($pago->carrito) {
                $meta['carrito_data'] = [
                    'carrito_id' => $pago->carrito->id,
                    'total_items' => $pago->carrito->items->count(),
                    'items_summary' => $pago->carrito->items
                        ->map(function ($item) {
                            return [
                                'producto_id' => $item->producto_id,
                                'nombre_producto' => $item->producto?->nombre ?? 'Producto desconocido',
                                'cantidad' => $item->cantidad,
                                'precio_unitario' => $item->precio_unitario,
                            ];
                        })
                        ->values()
                        ->toArray(),
                ];

                Log::info('ShippingDataPreparationService: datos de carrito guardados', [
                    'pago_id' => $pago->id,
                    'total_items' => $meta['carrito_data']['total_items'],
                ]);
            }

            // 4. Asegurar que datos de pago están completos
            if (empty($meta['payment_data'])) {
                $meta['payment_data'] = [
                    'referencia' => $pago->referencia,
                    'monto' => $pago->monto,
                    'moneda' => $pago->moneda,
                    'pasarela' => $pago->pasarela,
                    'aprobado_en' => $pago->updated_at?->toISOString(),
                ];

                Log::info('ShippingDataPreparationService: datos de pago guardados', [
                    'pago_id' => $pago->id,
                    'monto' => $pago->monto,
                    'moneda' => $pago->moneda,
                ]);
            }

            // 5. Inicializar envio_estado si no existe (para que aparezca en "Próximos Envíos")
            if (empty($pago->envio_estado)) {
                $pago->envio_estado = null; // null significa "pendiente"
                Log::info('ShippingDataPreparationService: envio_estado inicializado a pendiente', [
                    'pago_id' => $pago->id,
                ]);
            }

            // Guardar todos los cambios en meta
            $pago->update(['meta' => $meta]);

            Log::info('ShippingDataPreparationService: datos de envío preparados exitosamente', [
                'pago_id' => $pago->id,
                'ref' => $pago->referencia,
                'meta_keys' => array_keys($meta),
            ]);
        } catch (\Throwable $e) {
            Log::error('ShippingDataPreparationService: error preparando datos', [
                'pago_id' => $pago->id,
                'ref' => $pago->referencia,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // NO lanzar excepción - esto es un paso de preparación, no debe romper el flujo
            // Los datos estarán disponibles cuando se necesiten cargar desde relaciones
        }
    }
}
