<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public $withinTransaction = false;
    
    public function up(): void
    {
        if (! Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table) {
                $table->id();
                $table->string('nombre', 50);
                $table->string('slug', 50)->unique();
                $table->timestamps();
            });
        }

        if (DB::table('roles')->where('slug', 'cliente')->doesntExist()) {
            DB::table('roles')->insert([
                'id' => 1,
                'nombre' => 'Cliente',
                'slug' => 'cliente',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (DB::table('roles')->where('slug', 'empleado')->doesntExist()) {
            DB::table('roles')->insert([
                'id' => 2,
                'nombre' => 'Empleado',
                'slug' => 'empleado',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (DB::table('roles')->where('slug', 'admin3')->doesntExist()) {
            DB::table('roles')->insert([
                'id' => 3,
                'nombre' => 'Administrador principal',
                'slug' => 'admin3',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (DB::table('roles')->where('slug', 'programador')->doesntExist()) {
            DB::table('roles')->insert([
                'id' => 4,
                'nombre' => 'Programador',
                'slug' => 'programador',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (! Schema::hasColumn('usuarios', 'rol_id')) {
            Schema::table('usuarios', function (Blueprint $table) {
                $table->unsignedBigInteger('rol_id')->default(1)->after('id');
                $table->foreign('rol_id')->references('id')->on('roles');
            });
        }
    }

    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropForeign(['rol_id']);
            $table->dropColumn('rol_id');
        });

        Schema::dropIfExists('roles');
    }
};
