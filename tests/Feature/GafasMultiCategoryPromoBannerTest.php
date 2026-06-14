<?php

namespace Tests\Feature;

use App\Models\BloqueContenido;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GafasMultiCategoryPromoBannerTest extends TestCase
{
    use RefreshDatabase;

    public function test_gafas_exposes_multiple_banner_images_when_multiple_filters_are_selected(): void
    {
        BloqueContenido::query()->create([
            'clave' => 'gafas_mujeres_page_promo',
            'titulo' => 'Promo mujeres',
            'cuerpo' => null,
            'datos' => ['image_url' => '/images/promo-mujeres-test.png'],
            'esta_activo' => true,
            'orden' => 1,
        ]);

        BloqueContenido::query()->create([
            'clave' => 'gafas_hombre_page_promo',
            'titulo' => 'Promo hombre',
            'cuerpo' => null,
            'datos' => ['image_url' => '/images/promo-hombre-test.png'],
            'esta_activo' => true,
            'orden' => 1,
        ]);

        $response = $this->get('/gafas?categories%5B0%5D=mujeres&categories%5B1%5D=hombre');

        $response->assertOk();
        $response->assertViewHas('storeBannerImage', '/images/promo-mujeres-test.png');
        $response->assertViewHas('storeBannerImages', [
            '/images/promo-mujeres-test.png',
            '/images/promo-hombre-test.png',
        ]);
    }
}
