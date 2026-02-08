<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notificaciones_personal', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('control_produccion_id')->nullable();
            $table->unsignedBigInteger('sucursal_id');
            $table->unsignedBigInteger('user_id');
            $table->enum('tipo', ['faltante', 'excedente', 'horneado']);
            $table->string('descripcion');
            $table->boolean('atendida')->default(false);
            $table->timestamp('atendida_at')->nullable();
            $table->unsignedBigInteger('atendida_por')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificaciones_personal');
    }
};
