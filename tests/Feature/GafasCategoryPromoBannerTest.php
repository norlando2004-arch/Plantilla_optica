<?php

namespace Tests\Feature;

use App\Models\BloqueContenido;
use App\Models\BloqueContenidoArchivo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GafasCategoryPromoBannerTest extends TestCase
{
    use RefreshDatabase;

    public function test_gafas_uses_specific_banner_for_each_category_filter(): void
    {
        BloqueContenido::query()->create([
            'clave' => 'gafas_page_promo',
            'titulo' => 'Promo gafas general',
            'cuerpo' => null,
            'datos' => ['image_url' => '/images/promo-general-test.png'],
            'esta_activo' => true,
            'orden' => 1,
        ]);

        $expectedByCategory = [
            'mujeres' => '/images/promo-mujeres-test.png',
            'hombre' => '/images/promo-hombre-test.png',
            'ninas' => '/images/promo-ninas-test.png',
            'ninos' => '/images/promo-ninos-test.png',
            'polarizadas' => '/images/promo-polarizadas-test.png',
            'descanso' => '/images/promo-descanso-test.png',
        ];

        foreach ($expectedByCategory as $category => $imageUrl) {
            BloqueContenido::query()->create([
                'clave' => 'gafas_' . $category . '_page_promo',
                'titulo' => 'Promo ' . $category,
                'cuerpo' => null,
                'datos' => ['image_url' => $imageUrl],
                'esta_activo' => true,
                'orden' => 1,
            ]);
        }

        foreach ($expectedByCategory as $category => $imageUrl) {
            $response = $this->get('/gafas?categories%5B0%5D=' . urlencode($category));

            $response->assertOk();
            $response->assertViewHas('storeBannerImage', $imageUrl);
        }
    }

    public function test_gafas_exposes_multiple_uploaded_banners_for_each_supported_category_filter(): void
    {
        $mapping = [
            'hombre' => 'gafas_hombre_page_promo',
            'ninas' => 'gafas_ninas_page_promo',
            'ninos' => 'gafas_ninos_page_promo',
            'polarizadas' => 'gafas_polarizadas_page_promo',
            'deportivas' => 'gafas_descanso_page_promo',
        ];

        foreach ($mapping as $category => $blockKey) {
            $block = BloqueContenido::query()->create([
                'clave' => $blockKey,
                'titulo' => 'Promo ' . $category,
                'cuerpo' => null,
                'datos' => ['image_url' => '/images/promo-' . $category . '-test.png'],
                'esta_activo' => true,
                'orden' => 1,
            ]);

            BloqueContenidoArchivo::query()->create([
                'bloque_contenido_id' => $block->id,
                'field_key' => 'promo_image',
                'orden' => 1,
                'mime_type' => 'image/png',
                'original_name' => $category . '-1.png',
                'size_bytes' => 512,
                'ruta_archivo' => 'config/' . $category . '-1.png',
            ]);

            BloqueContenidoArchivo::query()->create([
                'bloque_contenido_id' => $block->id,
                'field_key' => 'promo_image',
                'orden' => 2,
                'mime_type' => 'image/png',
                'original_name' => $category . '-2.png',
                'size_bytes' => 512,
                'ruta_archivo' => 'config/' . $category . '-2.png',
            ]);

            $response = $this->get('/gafas?categories%5B0%5D=' . urlencode($category));

            $response->assertOk();
            $response->assertViewHas('storeBannerImage', '/storage/config/' . $category . '-2.png');
            $response->assertViewHas('storeBannerImages', [
                '/storage/config/' . $category . '-2.png',
                '/storage/config/' . $category . '-1.png',
            ]);
        }
    }
}
