<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('es_almacen')->default(false)->after('active');
        });

        // Actualizar usuarios existentes que pertenecen al almacén (sucursal_id = 0)
        DB::table('users')
            ->where('sucursal_id', 0)
            ->update(['es_almacen' => true]);

        // También actualizar usuarios que tengan el rol 'almacen'
        $usersWithAlmacenRole = DB::table('model_has_roles')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('roles.name', 'almacen')
            ->where('model_has_roles.model_type', 'App\\Models\\User')
            ->pluck('model_has_roles.model_id');

        if ($usersWithAlmacenRole->isNotEmpty()) {
            DB::table('users')
                ->whereIn('id', $usersWithAlmacenRole)
                ->update(['es_almacen' => true]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('es_almacen');
        });
    }
};
