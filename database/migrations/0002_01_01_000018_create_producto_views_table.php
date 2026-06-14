<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        if (Schema::hasTable('producto_views')) {
            return;
        }

        Schema::create('producto_views', function (Blueprint $table) {
            $table->id();

            $table->foreignId('producto_id')
                ->constrained('productos')
                ->cascadeOnDelete();

            $table->foreignId('usuario_id')
                ->constrained('usuarios')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique(['producto_id', 'usuario_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('producto_views');
    }
};
