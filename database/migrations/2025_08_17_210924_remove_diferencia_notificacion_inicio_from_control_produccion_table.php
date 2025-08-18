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
        Schema::table('control_produccion', function (Blueprint $table) {
            $table->dropColumn('diferencia_notificacion_inicio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('control_produccion', function (Blueprint $table) {
            $table->timestamp('diferencia_notificacion_inicio')->nullable();
        });
    }
};
