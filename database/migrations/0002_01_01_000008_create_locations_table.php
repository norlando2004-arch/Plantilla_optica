<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        Schema::create('ubicaciones', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->nullable();
            $table->text('direccion')->nullable();
            $table->string('ciudad', 80)->nullable();
            $table->string('region', 80)->nullable();
            $table->string('pais', 2)->nullable();

            $table->string('telefono', 40)->nullable();
            $table->string('correo')->nullable();
            $table->string('url_google_maps', 2048)->nullable();

            $table->decimal('latitud', 10, 7)->nullable();
            $table->decimal('longitud', 10, 7)->nullable();
            $table->json('horario')->nullable();

            $table->boolean('esta_activo')->default(true)->index();
            $table->integer('orden')->default(0)->index();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ubicaciones');
    }
};
