<?php

use Illuminate\Database\Migrations\Migration;
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

        $driver = (string) DB::getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE resenas DROP CONSTRAINT IF EXISTS resenas_usuario_id_unique');
            return;
        }

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE resenas DROP INDEX resenas_usuario_id_unique');
            return;
        }

        Schema::table('resenas', function ($table) {
            $table->dropUnique('resenas_usuario_id_unique');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('resenas')) {
            return;
        }

        $driver = (string) DB::getDriverName();

        if ($driver === 'pgsql') {
            $exists = DB::selectOne(
                "SELECT 1 FROM pg_indexes WHERE schemaname='public' AND tablename='resenas' AND indexname='resenas_usuario_id_unique'"
            );
            if (! $exists) {
                DB::statement('CREATE UNIQUE INDEX resenas_usuario_id_unique ON resenas (usuario_id)');
            }
            return;
        }

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            $exists = DB::selectOne(
                "SELECT 1 FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'resenas' AND index_name = 'resenas_usuario_id_unique'"
            );
            if (! $exists) {
                DB::statement('ALTER TABLE resenas ADD UNIQUE INDEX resenas_usuario_id_unique (usuario_id)');
            }
            return;
        }

        Schema::table('resenas', function ($table) {
            $table->unique('usuario_id', 'resenas_usuario_id_unique');
        });
    }
};
