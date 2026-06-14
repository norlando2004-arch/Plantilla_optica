<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        Schema::create('bloques_contenido', function (Blueprint $table) {
            $table->id();
            $table->string('clave', 80)->unique();
            $table->string('titulo')->nullable();
            $table->text('cuerpo')->nullable();
            $table->json('datos')->nullable();
            $table->boolean('esta_activo')->default(true)->index();
            $table->integer('orden')->default(0)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bloques_contenido');
    }
};
