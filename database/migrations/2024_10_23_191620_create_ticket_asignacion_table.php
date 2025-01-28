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
        Schema::create('tickets_asignacion', function (Blueprint $table) {
            $table->id();
            //estados
            $table->enum('estado', ['asignado', 'cancelado', 'cerrado'])->default('asignado');
            $table->unsignedInteger('sucursal_id')->nullable();
            $table->unsignedInteger('empleado_id')->nullable();
            $table->time('hora_salida')->nullable();
            $table->time('hora_llegada')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets_asignacion');
    }
};
