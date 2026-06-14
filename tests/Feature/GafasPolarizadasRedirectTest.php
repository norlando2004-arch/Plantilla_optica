<?php

namespace Tests\Feature;

use App\Models\Producto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GafasPolarizadasRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_gafas_polarizadas_redirects_to_gafas_with_polarizadas_filter_defaults(): void
    {
        Producto::query()->create([
            'nombre' => 'Gafa de sol',
            'slug' => 'gafa-de-sol',
            'tipo' => 'gafas_polarizadas',
            'genero_objetivo' => 'gafas_polarizadas',
            'precio' => 62000,
            'precio_oferta' => null,
            'esta_activo' => true,
        ]);

        $response = $this->get('/gafas-polarizadas');

        $response->assertRedirect('/gafas?min_price=0&max_price=62000&categories%5B0%5D=polarizadas');
    }
}