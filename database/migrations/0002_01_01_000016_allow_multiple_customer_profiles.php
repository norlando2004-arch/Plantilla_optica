<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        if (! Schema::hasTable('perfiles_clientes')) {
            return;
        }

        Schema::table('perfiles_clientes', function (Blueprint $table) {
            // Antes existía unique(usuario_id). Esto impedía tener más de un perfil.
            // Lo removemos para permitir que el usuario elija/cree varios perfiles.
            try {
                $table->dropUnique('perfiles_clientes_usuario_id_unique');
            } catch (\Throwable $e) {
                // Ya fue eliminado en una ejecución parcial anterior.
            }

            try {
                $table->index('usuario_id', 'perfiles_clientes_usuario_id_index');
            } catch (\Throwable $e) {
                // El índice ya existe en esta base de datos.
            }
        });
    }

    public function down(): void
    {
        Schema::table('perfiles_clientes', function (Blueprint $table) {
            try {
                $table->dropIndex('perfiles_clientes_usuario_id_index');
            } catch (\Throwable $e) {
                // El índice no existe.
            }

            try {
                $table->unique('usuario_id');
            } catch (\Throwable $e) {
                // La restricción unique ya existe.
            }
        });
    }
};
