<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateGastosTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('gastos', function (Blueprint $table) {
            // Agregar nuevos campos
            $table->unsignedBigInteger('trabajador_id')->nullable()->after('nombre');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('gastos', function (Blueprint $table) {
            // Eliminar los campos agregados
            $table->dropColumn([
                'trabajador_id',
            ]);
        });
    }
}