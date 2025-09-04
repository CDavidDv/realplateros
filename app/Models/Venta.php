<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Venta extends Model
{
    use HasFactory;
    protected $table = 'ventas';

    protected $fillable = ['id', 'usuario_id', 'sucursal_id', 'idVentaDia', 'total', 'metodo_pago'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($venta) {
            if (is_null($venta->idVentaDia)) {
                $venta->idVentaDia = $venta->generarIdVentaDia();
            }
        });
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function detalles()
    {
        return $this->hasMany(VentaProducto::class);
    }

    /**
     * Genera el ID de venta consecutivo desde el 1 de septiembre por sucursal
     */
    public function generarIdVentaDia()
    {
        $fechaInicio = Carbon::create(2024, 9, 1); // 1 de septiembre de 2024
        
        // Obtener el último ID de venta desde el 1 de septiembre para esta sucursal
        $ultimoIdVentaDia = self::where('sucursal_id', $this->sucursal_id)
            ->where('created_at', '>=', $fechaInicio)
            ->where('visible', true)
            ->where('estado', '!=', 'eliminada')
            ->max('idVentaDia');

        return $ultimoIdVentaDia ? $ultimoIdVentaDia + 1 : 1;
    }

    /**
     * Obtiene todas las ventas de un día específico para una sucursal
     */
    public static function obtenerVentasDelDia($sucursalId, $fecha = null)
    {
        $fecha = $fecha ? Carbon::parse($fecha) : Carbon::today();
        
        return self::where('sucursal_id', $sucursalId)
            ->whereDate('created_at', $fecha)
            ->where('visible', true)
            ->where('estado', '!=', 'eliminada')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Obtiene el conteo de ventas de un día específico para una sucursal
     */
    public static function contarVentasDelDia($sucursalId, $fecha = null)
    {
        $fecha = $fecha ? Carbon::parse($fecha) : Carbon::today();
        
        return self::where('sucursal_id', $sucursalId)
            ->whereDate('created_at', $fecha)
            ->where('visible', true)
            ->where('estado', '!=', 'eliminada')
            ->count();
    }

    /**
     * Renumera todas las ventas de una sucursal desde el 1 de septiembre de manera consecutiva
     */
    public static function renumerarVentasConsecutivas($sucursalId)
    {
        $fechaInicio = Carbon::create(2025, 9, 1); // 1 de septiembre de 2024
        
        // Obtener todas las ventas activas desde el 1 de septiembre ordenadas cronológicamente
        $ventas = self::where('sucursal_id', $sucursalId)
            ->where('created_at', '>=', $fechaInicio)
            ->where('visible', true)
            ->where('estado', '!=', 'eliminada')
            ->orderBy('created_at', 'asc')
            ->get();
        
        // Renumerar secuencialmente
        $contador = 1;
        foreach ($ventas as $venta) {
            $venta->idVentaDia = $contador;
            $venta->save();
            $contador++;
        }
        
        // Poner null a las ventas no visibles o eliminadas
        $ventasInactivas = self::where('sucursal_id', $sucursalId)
            ->where('created_at', '>=', $fechaInicio)
            ->where(function($query) {
                $query->where('visible', false)
                      ->orWhere('estado', 'eliminada');
            })
            ->get();
            
        foreach ($ventasInactivas as $venta) {
            $venta->idVentaDia = null;
            $venta->save();
        }
        
        return $contador - 1; // Retorna el número total de ventas renumeradas
    }
}
