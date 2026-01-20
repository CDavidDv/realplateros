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
        Schema::table('corte_caja', function (Blueprint $table) {
            $table->decimal('efectivo_contado', 10, 2)->nullable()->after('dinero_final');
            $table->decimal('tarjeta_contada', 10, 2)->nullable()->after('efectivo_contado');
            $table->text('notas_reconciliacion')->nullable()->after('tarjeta_contada');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('corte_caja', function (Blueprint $table) {
            $table->dropColumn(['efectivo_contado', 'tarjeta_contada', 'notas_reconciliacion']);
        });
    }
};
