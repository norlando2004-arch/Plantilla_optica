<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        if (Schema::hasColumn('productos', 'views_count')) {
            return;
        }

        Schema::table('productos', function (Blueprint $table) {
            $table->unsignedBigInteger('views_count')->default(0)->index();
        });
    }

    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropIndex(['views_count']);
            $table->dropColumn('views_count');
        });
    }
};
