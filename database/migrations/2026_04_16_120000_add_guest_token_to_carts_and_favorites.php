<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        if (Schema::hasTable('carritos') && ! Schema::hasColumn('carritos', 'guest_token')) {
            Schema::table('carritos', function (Blueprint $table) {
                $table->uuid('guest_token')->nullable()->after('sesion_id')->index();
            });
        }

        if (Schema::hasTable('favoritos') && ! Schema::hasColumn('favoritos', 'guest_token')) {
            Schema::table('favoritos', function (Blueprint $table) {
                $table->uuid('guest_token')->nullable()->after('usuario_id')->index();
                $table->index(['guest_token', 'producto_id']);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('favoritos') && Schema::hasColumn('favoritos', 'guest_token')) {
            Schema::table('favoritos', function (Blueprint $table) {
                $table->dropIndex(['guest_token', 'producto_id']);
                $table->dropColumn('guest_token');
            });
        }

        if (Schema::hasTable('carritos') && Schema::hasColumn('carritos', 'guest_token')) {
            Schema::table('carritos', function (Blueprint $table) {
                $table->dropColumn('guest_token');
            });
        }
    }
};