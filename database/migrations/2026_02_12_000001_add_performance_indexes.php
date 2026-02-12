<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->index(['sucursal_id', 'created_at'], 'ventas_sucursal_created_idx');
        });

        Schema::table('venta_productos', function (Blueprint $table) {
            $table->index(['sucursal_id', 'created_at'], 'venta_productos_sucursal_created_idx');
        });

        Schema::table('corte_caja', function (Blueprint $table) {
            $table->index(['sucursal_id', 'created_at'], 'corte_caja_sucursal_created_idx');
        });

        Schema::table('gastos', function (Blueprint $table) {
            $table->index(['sucursal_id', 'created_at'], 'gastos_sucursal_created_idx');
        });

        Schema::table('entradas_inventario', function (Blueprint $table) {
            $table->index(['sucursal_id', 'created_at'], 'entradas_inventario_sucursal_created_idx');
        });

        Schema::table('sobrantes', function (Blueprint $table) {
            $table->index(['sucursal_id', 'created_at'], 'sobrantes_sucursal_created_idx');
        });

        Schema::table('check_in_check_out', function (Blueprint $table) {
            $table->index(['user_id', 'created_at'], 'check_in_check_out_user_created_idx');
            $table->index(['sucursal_id', 'created_at'], 'check_in_check_out_sucursal_created_idx');
        });

        Schema::table('estimaciones', function (Blueprint $table) {
            $table->index(['sucursal_id', 'dia'], 'estimaciones_sucursal_dia_idx');
        });
    }

    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropIndex('ventas_sucursal_created_idx');
        });

        Schema::table('venta_productos', function (Blueprint $table) {
            $table->dropIndex('venta_productos_sucursal_created_idx');
        });

        Schema::table('corte_caja', function (Blueprint $table) {
            $table->dropIndex('corte_caja_sucursal_created_idx');
        });

        Schema::table('gastos', function (Blueprint $table) {
            $table->dropIndex('gastos_sucursal_created_idx');
        });

        Schema::table('entradas_inventario', function (Blueprint $table) {
            $table->dropIndex('entradas_inventario_sucursal_created_idx');
        });

        Schema::table('sobrantes', function (Blueprint $table) {
            $table->dropIndex('sobrantes_sucursal_created_idx');
        });

        Schema::table('check_in_check_out', function (Blueprint $table) {
            $table->dropIndex('check_in_check_out_user_created_idx');
            $table->dropIndex('check_in_check_out_sucursal_created_idx');
        });

        Schema::table('estimaciones', function (Blueprint $table) {
            $table->dropIndex('estimaciones_sucursal_dia_idx');
        });
    }
};
