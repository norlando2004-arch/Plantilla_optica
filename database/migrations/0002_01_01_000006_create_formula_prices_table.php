<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        Schema::create('precios_formulas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('formula_id')->constrained()->cascadeOnDelete();

            $table->string('etiqueta');
            $table->decimal('precio', 10, 2);
            $table->string('moneda', 3)->default('COP');
            $table->boolean('esta_activo')->default(true)->index();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['formula_id', 'etiqueta']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('precios_formulas');
    }
};
