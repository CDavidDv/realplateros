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
        Schema::create('ticket_productos_asignacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_asignacion_id')->constrained('tickets_asignacion')->onDelete('cascade');
            $table->foreignId('producto_id')->constrained('inventarios');
            $table->integer('cantidad');
            $table->decimal('precio_unitario', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_productos_asignacion');
    }
};
