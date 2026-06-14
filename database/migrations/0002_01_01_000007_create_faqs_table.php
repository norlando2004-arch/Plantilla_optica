<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        Schema::create('preguntas_frecuentes', function (Blueprint $table) {
            $table->id();
            $table->string('pregunta');
            $table->text('respuesta');
            $table->boolean('esta_activo')->default(true)->index();
            $table->integer('orden')->default(0)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('preguntas_frecuentes');
    }
};
