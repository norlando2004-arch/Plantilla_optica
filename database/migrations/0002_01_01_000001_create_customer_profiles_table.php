<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        Schema::create('perfiles_clientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();

            $table->string('tipo_documento', 30)->nullable();
            $table->string('numero_documento', 60)->nullable();
            $table->string('telefono', 40)->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->string('genero', 20)->nullable();

            $table->text('direccion')->nullable();
            $table->string('ciudad', 80)->nullable();

            $table->text('notas')->nullable();
            $table->json('preferencias')->nullable();

            $table->timestamps();

            $table->unique('usuario_id');
            $table->index(['tipo_documento', 'numero_documento']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('perfiles_clientes');
    }
};
