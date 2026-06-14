<?php

namespace Database\Seeders;

use App\Models\Promocion;
use App\Models\Usuario;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Usuario::factory(10)->create();

        Usuario::factory()->create([
            'nombre' => 'Usuario de Prueba',
            'correo' => 'test@example.com',
            'rol' => 'dueno',
        ]);

        Promocion::query()->firstOrCreate(
            ['tipo' => 'bienvenida'],
            [
                'titulo' => 'Promo de bienvenida',
                'insignia' => '🎁 Promo de bienvenida:',
                'descripcion' => '¡Llévate 2 monturas y paga solo 1!',
                'codigo' => '2X1Y20',
                'esta_activa' => true,
                'orden' => 0,
            ]
        );
    }
}
