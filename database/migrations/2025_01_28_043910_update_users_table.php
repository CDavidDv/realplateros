<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUsersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Agregar nuevos campos
            $table->string('apellido_p')->nullable()->after('name');
            $table->string('apellido_m')->nullable()->after('apellido_p');
            $table->string('tel')->nullable()->after('apellido_m');
            $table->date('inicio_contrato')->nullable()->after('tel');
            $table->date('fin_contrato')->nullable()->after('inicio_contrato');
            $table->boolean('active')->default(true)->after('fin_contrato');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            // Eliminar los campos agregados
            $table->dropColumn([
                'apellido_p',
                'apellido_m',
                'tel',
                'inicio_contrato',
                'fin_contrato',
                'active',
            ]);
        });
    }
}