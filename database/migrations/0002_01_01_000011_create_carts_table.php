<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        if (Schema::hasTable('carritos')) {
            return;
        }

        Schema::create('carritos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->string('sesion_id')->nullable()->index();

            $table->string('estado', 30)->default('abierto')->index();
            $table->string('moneda', 3)->default('COP');

            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('total_descuento', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);

            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carritos');
    }
};
