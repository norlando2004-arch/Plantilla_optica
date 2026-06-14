<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    // Ejecutar esta migración fuera de una transacción para evitar errores encadenados
    public $withinTransaction = false;
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('favoritos')) {
            return;
        }

        Schema::create('favoritos', function (Blueprint $table) {
            $table->id();
            // Nullable para soportar favoritos de invitados identificados por guest_token
            $table->unsignedBigInteger('usuario_id')->nullable();
            $table->unsignedBigInteger('producto_id');
            $table->timestamps();

            // Índices para acelerar las consultas por usuario y producto
            $table->index('usuario_id');
            $table->index('producto_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('favoritos');
    }
};
