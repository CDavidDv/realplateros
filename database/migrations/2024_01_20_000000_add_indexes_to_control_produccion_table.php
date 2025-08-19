<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('control_produccion', function (Blueprint $table) {
            // Índices para optimizar filtros
            $table->index('sucursal_id');
            $table->index('created_at');
            $table->index('estado');
            $table->index(['sucursal_id', 'created_at']);
            $table->index(['sucursal_id', 'estado']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('control_produccion', function (Blueprint $table) {
            $table->dropIndex(['sucursal_id']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['estado']);
            $table->dropIndex(['sucursal_id', 'created_at']);
            $table->dropIndex(['sucursal_id', 'estado']);
        });
    }
};
