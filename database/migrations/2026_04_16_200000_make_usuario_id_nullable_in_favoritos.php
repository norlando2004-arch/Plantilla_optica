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
        if (! Schema::hasTable('favoritos') || ! Schema::hasColumn('favoritos', 'usuario_id')) {
            return;
        }

        if (DB::getDriverName() !== 'sqlite') {
            Schema::table('favoritos', function (Blueprint $table) {
                $table->unsignedBigInteger('usuario_id')->nullable()->change();
            });
            return;
        }

        $oldTable = 'favoritos_old_nullable_fix';

        if (Schema::hasTable($oldTable)) {
            Schema::drop($oldTable);
        }

        Schema::rename('favoritos', $oldTable);
        $hasGuestToken = Schema::hasColumn($oldTable, 'guest_token');

        Schema::create('favoritos', function (Blueprint $table) use ($hasGuestToken) {
            $table->id();
            $table->unsignedBigInteger('usuario_id')->nullable();

            if ($hasGuestToken) {
                $table->uuid('guest_token')->nullable();
            }

            $table->unsignedBigInteger('producto_id');
            $table->timestamps();
        });

        if ($hasGuestToken) {
            DB::statement(
                "INSERT INTO favoritos (id, usuario_id, guest_token, producto_id, created_at, updated_at)
                 SELECT id, usuario_id, guest_token, producto_id, created_at, updated_at
                 FROM {$oldTable}"
            );
        } else {
            DB::statement(
                "INSERT INTO favoritos (id, usuario_id, producto_id, created_at, updated_at)
                 SELECT id, usuario_id, producto_id, created_at, updated_at
                 FROM {$oldTable}"
            );
        }

        Schema::drop($oldTable);

        // Crear índices después de eliminar la tabla antigua para evitar colisiones de nombre en SQLite.
        Schema::table('favoritos', function (Blueprint $table) use ($hasGuestToken) {
            $table->index('usuario_id');
            $table->index('producto_id');

            if ($hasGuestToken) {
                $table->index('guest_token');
                $table->index(['guest_token', 'producto_id']);
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('favoritos') || ! Schema::hasColumn('favoritos', 'usuario_id')) {
            return;
        }

        if (DB::getDriverName() !== 'sqlite') {
            Schema::table('favoritos', function (Blueprint $table) {
                $table->unsignedBigInteger('usuario_id')->nullable(false)->change();
            });
            return;
        }

        $nullCount = DB::table('favoritos')->whereNull('usuario_id')->count();
        if ($nullCount > 0) {
            throw new RuntimeException('No se puede revertir: existen favoritos de invitados con usuario_id null.');
        }

        $oldTable = 'favoritos_old_notnull_fix';

        if (Schema::hasTable($oldTable)) {
            Schema::drop($oldTable);
        }

        Schema::rename('favoritos', $oldTable);
        $hasGuestToken = Schema::hasColumn($oldTable, 'guest_token');

        Schema::create('favoritos', function (Blueprint $table) use ($hasGuestToken) {
            $table->id();
            $table->unsignedBigInteger('usuario_id');

            if ($hasGuestToken) {
                $table->uuid('guest_token')->nullable();
            }

            $table->unsignedBigInteger('producto_id');
            $table->timestamps();
        });

        if ($hasGuestToken) {
            DB::statement(
                "INSERT INTO favoritos (id, usuario_id, guest_token, producto_id, created_at, updated_at)
                 SELECT id, usuario_id, guest_token, producto_id, created_at, updated_at
                 FROM {$oldTable}"
            );
        } else {
            DB::statement(
                "INSERT INTO favoritos (id, usuario_id, producto_id, created_at, updated_at)
                 SELECT id, usuario_id, producto_id, created_at, updated_at
                 FROM {$oldTable}"
            );
        }

        Schema::drop($oldTable);

        Schema::table('favoritos', function (Blueprint $table) use ($hasGuestToken) {
            $table->index('usuario_id');
            $table->index('producto_id');

            if ($hasGuestToken) {
                $table->index('guest_token');
                $table->index(['guest_token', 'producto_id']);
            }
        });
    }
};
