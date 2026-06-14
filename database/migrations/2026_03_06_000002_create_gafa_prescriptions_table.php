<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Neon/pooler: evita DDL dentro de transacción.
    public $withinTransaction = false;

    public function up(): void
    {
        if (Schema::hasTable('gafa_prescriptions')) {
            return;
        }

        Schema::create('gafa_prescriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('session_id', 120)->nullable()->index();

            $table->string('storage_disk', 50)->default('local');
            $table->string('storage_path');

            $table->string('original_name');
            $table->string('mime', 120)->nullable();
            $table->unsignedInteger('size')->nullable();
            $table->string('sha256', 64)->nullable();

            $table->json('analysis')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gafa_prescriptions');
    }
};
