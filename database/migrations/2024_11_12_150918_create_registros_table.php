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
        Schema::create('registros', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('inventario_id');
            $table->unsignedInteger('sucursal_id');
            $table->integer('existe')->default(0);
            $table->integer('entra')->default(0);
            $table->integer('total')->default(0);
            $table->integer('vende')->default(0);
            $table->integer('sobra')->default(0);
            $table->float('precio')->default(0)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registros');
    }
};
