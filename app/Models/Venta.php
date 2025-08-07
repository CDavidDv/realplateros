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
     * Genera el ID de venta del día para la sucursal
     */
    public function generarIdVentaDia()
    {
        $hoy = Carbon::today();
        
        // Obtener el último ID de venta del día para esta sucursal
        $ultimoIdVentaDia = self::where('sucursal_id', $this->sucursal_id)
            ->whereDate('created_at', $hoy)
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
            ->orderBy('idVentaDia', 'asc')
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
            ->count();
    }
}
