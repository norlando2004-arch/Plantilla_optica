<?php

namespace Tests\Feature;

use App\Models\BloqueContenido;
use App\Models\BloqueContenidoArchivo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GafasWomenPromoBannerTest extends TestCase
{
    use RefreshDatabase;

    public function test_gafas_uses_women_promo_banner_when_mujeres_filter_is_selected(): void
    {
        BloqueContenido::query()->create([
            'clave' => 'gafas_page_promo',
            'titulo' => 'Promo gafas general',
            'cuerpo' => null,
            'datos' => ['image_url' => '/images/promo-general-test.png'],
            'esta_activo' => true,
            'orden' => 1,
        ]);

        BloqueContenido::query()->create([
            'clave' => 'gafas_mujeres_page_promo',
            'titulo' => 'Promo gafas mujeres',
            'cuerpo' => null,
            'datos' => ['image_url' => '/images/promo-mujeres-test.png'],
            'esta_activo' => true,
            'orden' => 1,
        ]);

        $response = $this->get('/gafas?categories%5B0%5D=mujeres');

        $response->assertOk();
        $response->assertViewHas('storeBannerImage', '/images/promo-mujeres-test.png');
    }

    public function test_gafas_exposes_all_uploaded_women_banners_when_mujeres_filter_is_selected(): void
    {
        $womenBlock = BloqueContenido::query()->create([
            'clave' => 'gafas_mujeres_page_promo',
            'titulo' => 'Promo gafas mujeres',
            'cuerpo' => null,
            'datos' => ['image_url' => '/images/promo-mujeres-test.png'],
            'esta_activo' => true,
            'orden' => 1,
        ]);

        BloqueContenidoArchivo::query()->create([
            'bloque_contenido_id' => $womenBlock->id,
            'field_key' => 'promo_image',
            'orden' => 1,
            'mime_type' => 'image/png',
            'original_name' => 'mujeres-1.png',
            'size_bytes' => 1024,
            'ruta_archivo' => 'config/mujeres-1.png',
        ]);

        BloqueContenidoArchivo::query()->create([
            'bloque_contenido_id' => $womenBlock->id,
            'field_key' => 'promo_image',
            'orden' => 2,
            'mime_type' => 'image/png',
            'original_name' => 'mujeres-2.png',
            'size_bytes' => 1024,
            'ruta_archivo' => 'config/mujeres-2.png',
        ]);

        $response = $this->get('/gafas?categories%5B0%5D=mujeres');

        $response->assertOk();
        $response->assertViewHas('storeBannerImage', '/storage/config/mujeres-2.png');
        $response->assertViewHas('storeBannerImages', [
            '/storage/config/mujeres-2.png',
            '/storage/config/mujeres-1.png',
        ]);
    }
}
