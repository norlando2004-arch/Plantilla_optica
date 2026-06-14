<?php

namespace Tests\Feature;

use App\Models\Carrito;
use App\Models\Pago;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CarritoTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_consult_order_status_by_cedula_only(): void
    {
        $carrito = Carrito::query()->create([
            'estado' => 'cerrado',
            'moneda' => 'COP',
            'subtotal' => 150000,
            'total_descuento' => 0,
            'total' => 150000,
            'meta' => [
                'cliente' => [
                    'nombre' => 'Ana Perez',
                    'numero_documento' => '123456789',
                ],
            ],
        ]);

        Pago::query()->create([
            'carrito_id' => $carrito->id,
            'estado' => 'aprobado',
            'envio_estado' => 'enviado',
            'pasarela' => 'dummy',
            'moneda' => 'COP',
            'monto' => 150000,
            'referencia' => 'PAY-TRACK-001',
            'meta' => [
                'cliente' => [
                    'nombre' => 'Ana Perez',
                    'numero_documento' => '123456789',
                ],
            ],
        ]);

        $response = $this->get('/seguimiento-pedido?cedula=123456789');

        $response->assertOk();
        $response->assertSee('Pedidos encontrados');
        $response->assertSee('PAY-TRACK-001');
        $response->assertSee('Enviado');
    }

    public function test_customer_sees_error_when_document_is_not_found(): void
    {
        $carrito = Carrito::query()->create([
            'estado' => 'cerrado',
            'moneda' => 'COP',
            'subtotal' => 98000,
            'total_descuento' => 0,
            'total' => 98000,
            'meta' => [
                'cliente' => [
                    'nombre' => 'Luis Gomez',
                    'numero_documento' => '987654321',
                ],
            ],
        ]);

        Pago::query()->create([
            'carrito_id' => $carrito->id,
            'estado' => 'aprobado',
            'envio_estado' => 'pendiente',
            'pasarela' => 'dummy',
            'moneda' => 'COP',
            'monto' => 98000,
            'referencia' => 'PAY-TRACK-002',
            'meta' => [
                'cliente' => [
                    'nombre' => 'Luis Gomez',
                    'numero_documento' => '987654321',
                ],
            ],
        ]);

        $response = $this->get('/seguimiento-pedido?cedula=111111111');

        $response->assertOk();
        $response->assertSee('No encontramos pedidos asociados a esa cédula.');
    }
}
