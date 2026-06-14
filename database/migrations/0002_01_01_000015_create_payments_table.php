<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        if (Schema::hasTable('pagos')) {
            return;
        }

        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('carrito_id')->constrained('carritos')->cascadeOnDelete();

            $table->string('estado', 30)->default('pendiente')->index();
            $table->string('pasarela', 40)->default('dummy')->index();

            $table->string('moneda', 3)->default('COP');
            $table->decimal('monto', 10, 2);

            // Referencia interna (nuestra) para correlacionar con la pasarela.
            $table->string('referencia', 80)->unique();

            // IDs/estado entregados por la pasarela real (cuando se implemente).
            $table->string('pasarela_transaccion_id', 120)->nullable()->index();
            $table->string('pasarela_estado', 60)->nullable()->index();

            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
