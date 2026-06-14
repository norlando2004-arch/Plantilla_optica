<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        if (Schema::hasTable('items_carrito')) {
            return;
        }

        Schema::create('items_carrito', function (Blueprint $table) {
            $table->id();
            $table->foreignId('carrito_id')->constrained('carritos')->cascadeOnDelete();
            $table->foreignId('producto_id')->nullable()->constrained('productos')->nullOnDelete();

            // Snapshot del producto al momento de añadirlo
            $table->string('nombre_producto');
            $table->decimal('precio_unitario', 10, 2);
            $table->integer('cantidad')->default(1);
            $table->string('moneda', 3)->default('COP');

            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['carrito_id', 'producto_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items_carrito');
    }
};
