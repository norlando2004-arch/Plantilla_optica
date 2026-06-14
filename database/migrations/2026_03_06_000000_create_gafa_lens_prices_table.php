<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Algunos poolers de Postgres (p.ej. Neon en modo pooler) pueden fallar
     * con DDL dentro de transacciones.
     */
    public $withinTransaction = false;

    public function up(): void
    {
        if (Schema::hasTable('gafa_lens_prices')) {
            return;
        }

        Schema::create('gafa_lens_prices', function (Blueprint $table) {
            $table->id();
            $table->string('lens_type');
            $table->string('nara_level');
            $table->unsignedInteger('price');
            $table->timestamps();

            $table->unique(['lens_type', 'nara_level']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gafa_lens_prices');
    }
};
