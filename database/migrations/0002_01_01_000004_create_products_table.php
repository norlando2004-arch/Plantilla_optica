<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('slug')->unique();

            // glasses | blue_light | other
            $table->string('tipo', 30)->default('gafas')->index();

            // male | female | unisex
            $table->string('genero_objetivo', 20)->default('unisex')->index();

            $table->string('marca')->nullable()->index();
            $table->string('material_montura')->nullable();
            $table->string('color')->nullable();

            $table->text('descripcion')->nullable();
            $table->json('caracteristicas')->nullable();

            $table->decimal('precio', 10, 2)->nullable();
            $table->decimal('precio_oferta', 10, 2)->nullable();
            $table->string('moneda', 3)->default('COP');
            $table->integer('existencias')->nullable();

            $table->boolean('esta_activo')->default(true)->index();
            $table->boolean('destacado')->default(false)->index();

            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
