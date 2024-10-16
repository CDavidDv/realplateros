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
        Schema::create('usuario_sucursales', function (Blueprint $table) {
            $table->foreignId('usuario_id')->constrained('users');
            $table->foreignId('sucursal_id')->constrained('sucursales');
            $table->primary(['usuario_id', 'sucursal_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuario_sucursales');
    }
};
