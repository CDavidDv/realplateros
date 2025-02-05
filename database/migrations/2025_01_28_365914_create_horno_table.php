<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHornoTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    { 
        Schema::create('horno', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('sucursal_id');
            $table->timestamp('tiempo_inicio')->nullable(); 
            $table->timestamp('tiempo_fin')->nullable();
            $table->boolean('estado')->default(false);
            $table->json('pastesHorneando')->nullable();
            $table->timestamps(); 
        }); 
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('horno');
    }
}