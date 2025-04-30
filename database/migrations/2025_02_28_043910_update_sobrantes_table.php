<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateSobrantesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('sobrantes', function (Blueprint $table) {
            // Agregar nuevos campos
            $table->unsignedBigInteger('corte_caja_id')->nullable()->after('cantidad');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('sobrantes', function (Blueprint $table) {
            // Eliminar los campos agregados
            $table->dropColumn([
                'corte_caja_id',
            ]);
        });
    }
}