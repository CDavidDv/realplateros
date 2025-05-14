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
            $table->unsignedBigInteger('horno_id')->nullable();
            $table->unsignedBigInteger('paste_id')->nullable();
            $table->unsignedBigInteger('sucursal_id')->nullable();
            $table->integer('cantidad')->nullable();
            $table->integer('cantidad_horneada')->nullable();
            $table->timestamp('tiempo_inicio_horneado')->nullable();
            $table->timestamp('diferencia_notificacion_inicio')->nullable();
            $table->timestamp('hora_ultima_venta')->nullable();
            $table->integer('cantidad_vendida')->default(0)->nullable();
            $table->enum('estado', ['pendiente', 'horneando', 'en_espera', 'vendido', 'desperdicio'])->default('pendiente')->nullable();
            $table->string('hora_notificacion')->nullable();
            $table->string('dia_notificacion')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('control_produccion');
    }
}; 