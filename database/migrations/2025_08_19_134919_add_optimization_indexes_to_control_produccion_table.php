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
            // Índices compuestos para optimizar consultas del middleware
            $table->index(['sucursal_id', 'estado'], 'idx_sucursal_estado');
            $table->index(['sucursal_id', 'tiempo_inicio_horneado'], 'idx_sucursal_tiempo');
            $table->index(['sucursal_id', 'created_at'], 'idx_sucursal_created');
            
            // Índices simples para consultas específicas
            $table->index('estado', 'idx_estado');
            $table->index('tiempo_inicio_horneado', 'idx_tiempo_inicio');
            
            // Índices para relaciones
            $table->index('paste_id', 'idx_paste_id');
            $table->index('sucursal_id', 'idx_sucursal_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('control_produccion', function (Blueprint $table) {
            // Eliminar índices compuestos
            $table->dropIndex('idx_sucursal_estado');
            $table->dropIndex('idx_sucursal_tiempo');
            $table->dropIndex('idx_sucursal_created');
            
            // Eliminar índices simples
            $table->dropIndex('idx_estado');
            $table->dropIndex('idx_tiempo_inicio');
            
            // Eliminar índices de relaciones
            $table->dropIndex('idx_paste_id');
            $table->dropIndex('idx_sucursal_id');
        });
    }
};
