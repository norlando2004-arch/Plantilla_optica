<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        if (! Schema::hasTable('resenas')) {
            return;
        }

        Schema::table('resenas', function (Blueprint $table) {
            if (! Schema::hasColumn('resenas', 'autor_nombre')) {
                $table->string('autor_nombre', 120)->nullable()->after('usuario_id');
            }

            if (! Schema::hasColumn('resenas', 'foto_data')) {
                $table->longText('foto_data')->nullable()->after('foto_url');
            }

            if (! Schema::hasColumn('resenas', 'foto_nombre')) {
                $table->string('foto_nombre')->nullable()->after('foto_data');
            }

            if (! Schema::hasColumn('resenas', 'foto_mime')) {
                $table->string('foto_mime', 120)->nullable()->after('foto_nombre');
            }

            if (! Schema::hasColumn('resenas', 'foto_size')) {
                $table->unsignedInteger('foto_size')->nullable()->after('foto_mime');
            }
        });

        $driver = (string) DB::getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE resenas DROP CONSTRAINT IF EXISTS resenas_usuario_id_foreign');
            DB::statement('ALTER TABLE resenas ALTER COLUMN usuario_id DROP NOT NULL');
            DB::statement('ALTER TABLE resenas ADD CONSTRAINT resenas_usuario_id_foreign FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE CASCADE');
        }

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE resenas DROP FOREIGN KEY resenas_usuario_id_foreign');
            DB::statement('ALTER TABLE resenas MODIFY usuario_id BIGINT UNSIGNED NULL');
            DB::statement('ALTER TABLE resenas ADD CONSTRAINT resenas_usuario_id_foreign FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE CASCADE');
        }

        if ($driver === 'sqlite') {
            Schema::table('resenas', function (Blueprint $table) {
                $table->unsignedBigInteger('usuario_id')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('resenas')) {
            return;
        }

        Schema::table('resenas', function (Blueprint $table) {
            foreach (['foto_size', 'foto_mime', 'foto_nombre', 'foto_data', 'autor_nombre'] as $column) {
                if (Schema::hasColumn('resenas', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
