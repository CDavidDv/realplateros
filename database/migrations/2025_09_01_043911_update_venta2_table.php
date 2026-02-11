<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateVenta2Table extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('ventas', function (Blueprint $table) {
            // Agregar si requiere factura depues de metodo_pago
            if (!Schema::hasColumn('ventas', 'idVentaNormal')) {
                $table->integer('idVentaNormal')->nullable()->after('folio');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('ventas', function (Blueprint $table) {
            // Eliminar si requiere factura despues de metodo_pago
            $table->dropColumn([
                'idVentaNormal'
            ]);
        });
    }
}