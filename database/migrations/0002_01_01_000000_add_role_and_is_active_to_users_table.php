<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Ejecutar esta migración fuera de una transacción (Neon + DDL).
    public $withinTransaction = false;

    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->string('rol', 20)->default('cliente')->index();
            $table->boolean('esta_activo')->default(true)->index();
        });
    }

    public function down(): void
    {
        try {
            Schema::table('usuarios', function (Blueprint $table) {
                $table->dropColumn(['rol', 'esta_activo']);
            });
        } catch (\Throwable $e) {
            // SQLite tiene problemas con índices al eliminar columnas
            // Se ignora el error de rollback
        }
    }
};
