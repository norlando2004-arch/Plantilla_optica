<?php

namespace Database\Seeders;

use App\Models\Pago;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class PagoSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiar datos anteriores
        Pago::truncate();

        // Crear pagos de ejemplo con diferentes estados y meses
        $datos = [
            // Enero
            ['monto' => 150.00, 'estado' => 'completado', 'mes' => 1],
            ['monto' => 200.00, 'estado' => 'completado', 'mes' => 1],
            ['monto' => 100.00, 'estado' => 'pendiente', 'mes' => 1],
            ['monto' => 50.00, 'estado' => 'rechazado', 'mes' => 1],
            
            // Febrero
            ['monto' => 300.00, 'estado' => 'completado', 'mes' => 2],
            ['monto' => 180.00, 'estado' => 'completado', 'mes' => 2],
            ['monto' => 120.00, 'estado' => 'completado', 'mes' => 2],
            ['monto' => 90.00, 'estado' => 'rechazado', 'mes' => 2],
            
            // Marzo
            ['monto' => 250.00, 'estado' => 'completado', 'mes' => 3],
            ['monto' => 200.00, 'estado' => 'completado', 'mes' => 3],
            ['monto' => 75.00, 'estado' => 'cancelado', 'mes' => 3],
            ['monto' => 110.00, 'estado' => 'pendiente', 'mes' => 3],
            
            // Abril
            ['monto' => 320.00, 'estado' => 'completado', 'mes' => 4],
            ['monto' => 180.00, 'estado' => 'completado', 'mes' => 4],
            ['monto' => 95.00, 'estado' => 'completado', 'mes' => 4],
            ['monto' => 60.00, 'estado' => 'rechazado', 'mes' => 4],
            ['monto' => 140.00, 'estado' => 'pendiente', 'mes' => 4],
        ];

        foreach ($datos as $indice => $dato) {
            Pago::create([
                'carrito_id' => null,
                'monto' => $dato['monto'],
                'estado' => $dato['estado'],
                'referencia' => 'REF-' . str_pad($indice + 1, 5, '0', STR_PAD_LEFT),
                'created_at' => Carbon::now()->year(2026)->month($dato['mes'])->day(rand(1, 28)),
            ]);
        }

        $this->command->info('✅ ' . count($datos) . ' pagos de ejemplo creados');
    }
}

