<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        Schema::create('promociones', function (Blueprint $table) {
            $table->id();
            $table->string('tipo', 30)->default('promo')->index();

            $table->string('titulo');
            $table->string('codigo', 60)->nullable()->index();
            $table->string('insignia', 60)->nullable();
            $table->text('descripcion')->nullable();

            $table->string('texto_cta', 80)->nullable();
            $table->string('url_cta', 2048)->nullable();
            $table->string('ruta_imagen', 2048)->nullable();

            $table->timestamp('empieza_en')->nullable()->index();
            $table->timestamp('termina_en')->nullable()->index();
            $table->boolean('esta_activa')->default(true)->index();
            $table->integer('orden')->default(0)->index();
            $table->json('meta')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promociones');
    }
};
