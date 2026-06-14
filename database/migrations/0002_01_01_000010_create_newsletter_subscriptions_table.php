<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        if (Schema::hasTable('suscripciones_boletin')) {
            return;
        }

        Schema::create('suscripciones_boletin', function (Blueprint $table) {
            $table->id();
            $table->string('correo')->unique();
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->nullOnDelete();

            $table->string('estado', 30)->default('suscrito')->index();
            $table->string('origen', 60)->nullable();
            $table->timestamp('suscrito_en')->nullable();
            $table->timestamp('cancelado_en')->nullable();
            $table->json('meta')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suscripciones_boletin');
    }
};
