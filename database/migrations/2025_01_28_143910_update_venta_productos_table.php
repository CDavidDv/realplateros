<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateVentaProductosTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    { 
        Schema::table('venta_productos', function (Blueprint $table) {
            // Agregar nuevos campos
            $table->integer('cantidadEditado')->nullable()->after('cantidad');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('venta_productos', function (Blueprint $table) {
            // Eliminar los campos agregados
            $table->dropColumn([
                'cantidadEditado',
            ]);
        });
    }
}