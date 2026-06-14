<?php

namespace Tests\Feature;

use App\Models\Producto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GafasHombreRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_gafas_hombre_redirects_to_gafas_with_hombre_filter_defaults(): void
    {
        Producto::query()->create([
            'nombre' => 'Montura hombre',
            'slug' => 'montura-hombre',
            'tipo' => 'gafas',
            'genero_objetivo' => 'male',
            'precio' => 62000,
            'precio_oferta' => null,
            'esta_activo' => true,
        ]);

        $response = $this->get('/gafas-hombre');

        $response->assertRedirect('/gafas?min_price=0&max_price=62000&categories%5B0%5D=hombre');
    }
}