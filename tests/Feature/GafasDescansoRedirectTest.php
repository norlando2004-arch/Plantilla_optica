<?php

namespace Tests\Feature;

use App\Models\Producto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GafasDescansoRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_gafas_descanso_redirects_to_gafas_with_descanso_filter_defaults(): void
    {
        Producto::query()->create([
            'nombre' => 'Gafa descanso',
            'slug' => 'gafa-descanso',
            'tipo' => 'gafas',
            'genero_objetivo' => 'descanso',
            'precio' => 62000,
            'precio_oferta' => null,
            'esta_activo' => true,
        ]);

        $response = $this->get('/gafas-descanso');

        $response->assertRedirect('/gafas?min_price=0&max_price=62000&categories%5B0%5D=descanso');
    }
}