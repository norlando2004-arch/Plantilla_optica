<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Refactoriza el almacenamiento de imágenes de configuración:
 *
 * 1. bloques_contenido_archivos → agrega columna ruta_archivo (nullable)
 *    para que los nuevos registros guarden la ruta física en lugar de base64.
 *    Se hace contenido_base64 nullable para aceptar registros sin base64.
 *
 * 2. promociones → no requiere cambio de esquema porque ruta_imagen ya existe;
 *    el controlador simplemente empieza a usarla en lugar de uploaded_image_data.
 */
return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        // ── bloques_contenido_archivos ──────────────────────────────────────
        if (Schema::hasTable('bloques_contenido_archivos')) {
            if (! Schema::hasColumn('bloques_contenido_archivos', 'ruta_archivo')) {
                Schema::table('bloques_contenido_archivos', function (Blueprint $table) {
                    $table->string('ruta_archivo')->nullable()->after('contenido_base64');
                });
            }

            // Hacer nullable contenido_base64 para nuevos registros sin base64
            // Solo si no es ya nullable (SQLite no soporta ALTER COLUMN, así que
            // envolvemos en try/catch para que no falle en SQLite local).
            try {
                Schema::table('bloques_contenido_archivos', function (Blueprint $table) {
                    $table->longText('contenido_base64')->nullable()->change();
                });
            } catch (\Throwable $e) {
                // SQLite local: ignorar, el campo ya acepta NULL en práctica
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('bloques_contenido_archivos')) {
            if (Schema::hasColumn('bloques_contenido_archivos', 'ruta_archivo')) {
                Schema::table('bloques_contenido_archivos', function (Blueprint $table) {
                    $table->dropColumn('ruta_archivo');
                });
            }
        }
    }
};
