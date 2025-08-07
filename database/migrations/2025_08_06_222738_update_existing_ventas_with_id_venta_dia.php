<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Obtener todas las ventas agrupadas por sucursal y fecha
        $ventas = DB::table('ventas')
            ->select('id', 'sucursal_id', 'created_at')
            ->orderBy('sucursal_id')
            ->orderBy('created_at')
            ->get();

        $ventasPorDia = [];

        // Agrupar ventas por sucursal y fecha
        foreach ($ventas as $venta) {
            $fecha = Carbon::parse($venta->created_at)->format('Y-m-d');
            $key = $venta->sucursal_id . '_' . $fecha;
            
            if (!isset($ventasPorDia[$key])) {
                $ventasPorDia[$key] = [];
            }
            
            $ventasPorDia[$key][] = $venta;
        }

        // Actualizar cada grupo de ventas con el idVentaDia
        foreach ($ventasPorDia as $key => $ventasDelDia) {
            $idVentaDia = 1;
            
            foreach ($ventasDelDia as $venta) {
                DB::table('ventas')
                    ->where('id', $venta->id)
                    ->update(['idVentaDia' => $idVentaDia]);
                
                $idVentaDia++;
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No es necesario revertir esta migración ya que solo actualiza datos existentes
    }
};
