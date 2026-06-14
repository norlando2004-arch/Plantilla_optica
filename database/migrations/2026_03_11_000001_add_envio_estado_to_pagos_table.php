<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        if (! Schema::hasTable('pagos')) {
            return;
        }

        Schema::table('pagos', function (Blueprint $table) {
            if (! Schema::hasColumn('pagos', 'envio_estado')) {
                $table->string('envio_estado', 30)->default('pendiente')->index()->after('estado');
            }
            if (! Schema::hasColumn('pagos', 'envio_marcado_por')) {
                $table->foreignId('envio_marcado_por')->nullable()->after('envio_estado')->constrained('usuarios')->nullOnDelete();
            }
            if (! Schema::hasColumn('pagos', 'envio_marcado_en')) {
                $table->timestamp('envio_marcado_en')->nullable()->after('envio_marcado_por');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            if (Schema::hasColumn('pagos', 'envio_marcado_en')) {
                $table->dropColumn('envio_marcado_en');
            }
            if (Schema::hasColumn('pagos', 'envio_marcado_por')) {
                $table->dropConstrainedForeignId('envio_marcado_por');
            }
            if (Schema::hasColumn('pagos', 'envio_estado')) {
                $table->dropColumn('envio_estado');
            }
        });
    }
};
