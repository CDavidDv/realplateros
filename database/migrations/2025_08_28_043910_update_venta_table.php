<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateVentaTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('ventas', function (Blueprint $table) {
            // Agregar si requiere factura depues de metodo_pago
            $table->boolean('factura')->default(false)->after('metodo_pago');
            $table->string('folio')->nullable()->after('factura');
            $table->boolean('visible')->default(true)->after('folio');
            // Agregar estado de la venta estado: sin cambios, elminado, editada, 
            $table->string('estado')->default('sin cambios')->after('visible');
            $table->json('datos_originales')->nullable()->after('estado');
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
                'factura',
                'folio',
                'visible',
                'estado',
                'datos_originales',
            ]);
        });
    }
}