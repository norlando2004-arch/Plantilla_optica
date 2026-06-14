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
        if (! Schema::hasTable('resenas')) {
            return;
        }

        Schema::table('resenas', function (Blueprint $table) {
            try {
                $table->unique('usuario_id', 'resenas_usuario_id_unique');
            } catch (\Throwable $e) {
                // El índice ya existe en esta base de datos.
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('resenas')) {
            return;
        }

        Schema::table('resenas', function (Blueprint $table) {
            try {
                $table->dropUnique('resenas_usuario_id_unique');
            } catch (\Throwable $e) {
                // El índice no existe.
            }
        });
    }
};
