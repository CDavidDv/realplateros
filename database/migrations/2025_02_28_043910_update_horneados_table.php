<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateHorneadosTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('horneados', function (Blueprint $table) {
            // Agregar nuevos campos
            $table->unsignedBigInteger('responsable_id')->nullable()->after('sucursal_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('horneados', function (Blueprint $table) {
            // Eliminar los campos agregados
            $table->dropColumn([
                'responsable',
            ]);
        });
    }
}