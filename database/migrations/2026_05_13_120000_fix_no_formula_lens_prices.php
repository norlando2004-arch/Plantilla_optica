<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('gafa_lens_prices')) {
            return;
        }

        // Solo tocar la tabla de “Sin fórmula” (no_formula_fixed).
        // NO afecta Monofocal (mono_tier*) ni otros tipos.
        $target = [
            // lens_type => [old_price(s) => new_price]
            '156_blanco' => [55000 => 55000],
            '156_blue_block' => [89000 => 70000],
            '156_ar_verde' => [88000 => 70000],
            '156_fotocromatico_superhidrofobico' => [169000 => 120000],
            '156_ar_verde_fotocromatico_blue_block' => [169000 => 120000],
        ];

        $now = now();

        foreach ($target as $lensType => $map) {
            foreach ($map as $old => $new) {
                if ($old === $new) {
                    continue;
                }

                DB::table('gafa_lens_prices')
                    ->where('lens_type', $lensType)
                    ->where('nara_level', 'no_formula_fixed')
                    ->where('price', $old)
                    ->update([
                        'price' => $new,
                        'updated_at' => $now,
                    ]);
            }
        }
    }

    public function down(): void
    {
        // Intencionalmente sin reversión automática.
        // Evitamos pisar precios que el admin pudo editar manualmente.
    }
};
