<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Ejecutar esta migración fuera de una transacción (Postgres).
    public $withinTransaction = false;

    public function up(): void
    {
        if (Schema::hasTable('resenas')) {
            return;
        }

        Schema::create('resenas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            $table->unsignedTinyInteger('estrellas');
            $table->text('comentario');
            $table->string('foto_url')->nullable();
            $table->timestamps();

            $table->index(['created_at']);
            $table->index(['usuario_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resenas');
    }
};
