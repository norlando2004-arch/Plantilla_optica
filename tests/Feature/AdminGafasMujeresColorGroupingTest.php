<?php

namespace Tests\Feature;

use App\Models\Producto;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class AdminGafasMujeresColorGroupingTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_groups_uploaded_images_by_color_and_includes_main_uploaded_image(): void
    {
        $admin = Usuario::factory()->create([
            'rol_id' => 2,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.gafas-mujeres.store'), [
            'nombre' => 'Gafa agrupada por color',
            'categoria' => 'female',
            'material_montura' => 'TR90',
            'recomendado_para' => 'Ovalada',
            'incluye' => 'Estuche',
            'clip_on_compatible' => '0',
            'progresivos' => '0',
            'tipo_formula' => 'Bajas',
            'precio' => '150.000',
            'precio_oferta' => '120.000',
            'color' => 'Rojo',
            'color_stock' => [
                'Rojo' => 4,
                'Azul' => 2,
            ],
            'esta_activo' => '1',
            'uploaded_image' => UploadedFile::fake()->image('rojo-principal.jpg', 1200, 900),
            'uploaded_color_images' => [
                UploadedFile::fake()->image('rojo-lateral.jpg', 1024, 768),
                UploadedFile::fake()->image('azul-principal.jpg', 900, 900),
                UploadedFile::fake()->image('rojo-detalle.jpg', 800, 1200),
            ],
            'uploaded_color_images_color' => [
                'Rojo',
                'Azul',
                'Rojo',
            ],
        ]);

        $response->assertRedirect(route('admin.gafas-mujeres.index'));

        $producto = Producto::query()->firstOrFail();
        $meta = is_array($producto->meta) ? $producto->meta : [];
        $variants = collect($meta['color_variants'] ?? []);

        $this->assertCount(2, $variants);
        $this->assertSame('Rojo', $producto->color);

        $redVariant = $variants->firstWhere('name', 'Rojo');
        $blueVariant = $variants->firstWhere('name', 'Azul');

        $this->assertIsArray($redVariant);
        $this->assertIsArray($blueVariant);
        $this->assertSame(4, $redVariant['stock'] ?? null);
        $this->assertSame(2, $blueVariant['stock'] ?? null);
        $this->assertCount(3, $redVariant['images'] ?? []);
        $this->assertCount(1, $blueVariant['images'] ?? []);
        $this->assertSame($redVariant['images'][0] ?? null, $redVariant['image'] ?? null);
        $this->assertSame($blueVariant['images'][0] ?? null, $blueVariant['image'] ?? null);
        $this->assertSame($meta['imagen_url'] ?? null, $redVariant['images'][0] ?? null);
    }
}
