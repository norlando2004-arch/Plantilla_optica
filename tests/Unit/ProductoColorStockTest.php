<?php

namespace Tests\Unit;

use App\Models\Producto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProductoColorStockTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_tracks_and_decrements_stock_per_color(): void
    {
        $producto = Producto::query()->create([
            'nombre' => 'Gafa color test',
            'slug' => 'gafa-color-test-' . Str::lower(Str::random(6)),
            'tipo' => 'gafas',
            'genero_objetivo' => 'female',
            'color' => 'Gris',
            'precio' => 120000,
            'moneda' => 'COP',
            'existencias' => 7,
            'esta_activo' => true,
            'meta' => [
                'color' => 'Gris',
                'color_stock' => [
                    'Gris' => 5,
                    'Rojo' => 2,
                ],
                'color_variants' => [
                    ['name' => 'Gris', 'hex' => '#cec9bc', 'image' => 'https://example.com/gris.jpg', 'stock' => 5],
                    ['name' => 'Rojo', 'hex' => '#9f2b2b', 'image' => 'https://example.com/rojo.jpg', 'stock' => 2],
                ],
            ],
        ]);

        $this->assertSame(5, $producto->stockDisponibleParaColor('Gris'));
        $this->assertSame(2, $producto->stockDisponibleParaColor('Rojo'));
        $this->assertTrue($producto->tieneStockParaColor('Rojo', 2));
        $this->assertFalse($producto->tieneStockParaColor('Rojo', 3));

        $lowStockBefore = $producto->lowStockColors();
        $this->assertCount(2, $lowStockBefore);
        $this->assertSame('Gris', $lowStockBefore[0]['color']);
        $this->assertSame(5, $lowStockBefore[0]['stock']);
        $this->assertSame('Rojo', $lowStockBefore[1]['color']);
        $this->assertSame(2, $lowStockBefore[1]['stock']);

        $status = $producto->stockStatusForColor('Rojo');
        $this->assertTrue($status['is_low']);
        $this->assertSame('Te quedan pocas gafas del color Rojo', $status['message']);

        $restante = $producto->decrementStockForColor('Rojo', 1);
        $producto->refresh();

        $this->assertSame(1, $restante);
        $this->assertSame(1, $producto->stockDisponibleParaColor('Rojo'));
        $this->assertSame(6, (int) $producto->existencias);
        $this->assertSame('Te quedan pocas gafas del color Rojo', $producto->stockStatusForColor('Rojo')['message']);
    }
}
