<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        if (! Schema::hasColumn('promociones', 'uploaded_image_data')) {
            Schema::table('promociones', function (Blueprint $table) {
                $table->longText('uploaded_image_data')->nullable();
            });
        }

        if (! Schema::hasColumn('promociones', 'uploaded_image_mime')) {
            Schema::table('promociones', function (Blueprint $table) {
                $table->string('uploaded_image_mime', 120)->nullable();
            });
        }

        if (! Schema::hasColumn('promociones', 'uploaded_image_original_name')) {
            Schema::table('promociones', function (Blueprint $table) {
                $table->string('uploaded_image_original_name')->nullable();
            });
        }

        if (! Schema::hasColumn('promociones', 'uploaded_image_size')) {
            Schema::table('promociones', function (Blueprint $table) {
                $table->unsignedBigInteger('uploaded_image_size')->nullable();
            });
        }
    }

    public function down(): void
    {
        foreach (['uploaded_image_size', 'uploaded_image_original_name', 'uploaded_image_mime', 'uploaded_image_data'] as $column) {
            if (! Schema::hasColumn('promociones', $column)) {
                continue;
            }

            Schema::table('promociones', function (Blueprint $table) use ($column) {
                $table->dropColumn($column);
            });
        }
    }
};