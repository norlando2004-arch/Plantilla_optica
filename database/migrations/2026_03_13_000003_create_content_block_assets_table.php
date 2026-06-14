<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        if (Schema::hasTable('bloques_contenido_archivos')) {
            return;
        }

        Schema::create('bloques_contenido_archivos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bloque_contenido_id')->constrained('bloques_contenido')->cascadeOnDelete();
            $table->string('field_key', 80)->index();
            $table->unsignedInteger('orden')->default(0)->index();
            $table->string('mime_type', 120);
            $table->string('original_name')->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->longText('contenido_base64');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bloques_contenido_archivos');
    }
};