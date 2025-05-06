<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('control_produccion', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('horno_id')->constrained('horno');
            $table->foreignId('paste_id')->constrained('inventarios');
            $table->integer('cantidad');
            $table->timestamp('tiempo_inicio_horneado')->nullable();
            $table->timestamp('tiempo_fin_horneado')->nullable();
            $table->timestamp('tiempo_retiro_horno')->nullable();
            $table->timestamp('tiempo_ultima_venta')->nullable();
            $table->integer('cantidad_vendida')->default(0);
            $table->enum('estado', ['horneando', 'retirado', 'vendido', 'desperdicio'])->default('horneando');
            $table->foreignId('sucursal_id')->constrained('sucursales');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('control_produccion');
    }
}; 