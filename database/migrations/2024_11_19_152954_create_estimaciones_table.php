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
        Schema::create('estimaciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('sucursal_id');
            $table->unsignedInteger('inventario_id');
            $table->string('dia'); //semana
            $table->string('hora');
            $table->integer('cantidad');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estimaciones');
    }
};
