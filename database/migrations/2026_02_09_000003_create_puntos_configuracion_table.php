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
        Schema::create('puntos_configuracion', function (Blueprint $table) {
            $table->id();
            $table->string('concepto');
            $table->integer('puntos');
            $table->string('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // Insertar valores iniciales
        DB::table('puntos_configuracion')->insert([
            [
                'concepto' => 'check_in',
                'puntos' => 5,
                'descripcion' => 'Marcar asistencia',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'concepto' => 'venta',
                'puntos' => 2,
                'descripcion' => 'Por cada venta realizada',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'concepto' => 'horneado',
                'puntos' => 3,
                'descripcion' => 'Por cada lote horneado',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'concepto' => 'notificacion_atendida',
                'puntos' => 10,
                'descripcion' => 'Atender notificacion correctamente',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'concepto' => 'notificacion_no_atendida',
                'puntos' => -15,
                'descripcion' => 'Notificacion sin atender al fin del turno',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'concepto' => 'sobrante',
                'puntos' => -5,
                'descripcion' => 'Por cada registro de sobrante',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'concepto' => 'corte_caja',
                'puntos' => 5,
                'descripcion' => 'Realizar corte de caja',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('puntos_configuracion');
    }
};
