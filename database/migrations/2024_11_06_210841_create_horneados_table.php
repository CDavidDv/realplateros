<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('horneados', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('sucursal_id');
            $table->string('relleno');
            $table->timestamp('hora')->default(DB::raw('CURRENT_TIME'));
            $table->timestamp('fecha')->default(DB::raw('CURRENT_DATE'));;
            $table->string('piezas');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('horneados');
    }
};
