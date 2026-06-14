<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        Schema::create('mensajes_contacto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->nullOnDelete();

            $table->string('nombre')->nullable();
            $table->string('correo')->nullable()->index();
            $table->string('telefono', 40)->nullable();
            $table->string('asunto')->nullable();
            $table->text('mensaje');

            $table->string('estado', 30)->default('nuevo')->index();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mensajes_contacto');
    }
};
