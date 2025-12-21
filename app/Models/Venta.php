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
     * Genera el ID de venta del día (se resetea por día)
     */
    public function generarIdVentaDia()
    {
        $hoy = Carbon::today();
        
        // Obtener el último ID de venta del día para esta sucursal
        $ultimoIdVentaDia = self::where('sucursal_id', $this->sucursal_id)
            ->whereDate('created_at', $hoy)
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
     * Renumera ventas del día actual (idVentaDia se resetea por día)
     */
    public static function renumerarVentasDelDia($sucursalId, $fecha = null)
    {
        $fecha = $fecha ? Carbon::parse($fecha) : Carbon::today();
        
        // Obtener todas las ventas visibles del día ordenadas cronológicamente
        $ventas = self::where('sucursal_id', $sucursalId)
            ->whereDate('created_at', $fecha)
            ->where('visible', true)
            ->where('estado', '!=', 'eliminada')
            ->orderBy('created_at', 'asc')
            ->get();
        
        // Renumerar secuencialmente empezando desde 1
        $contador = 1;
        foreach ($ventas as $venta) {
            $venta->idVentaDia = $contador;
            $venta->save();
            $contador++;
        }
        
        // Poner null a las ventas no visibles o eliminadas del día
        $ventasInactivas = self::where('sucursal_id', $sucursalId)
            ->whereDate('created_at', $fecha)
            ->where(function($query) {
                $query->where('visible', false)
                      ->orWhere('estado', 'eliminada');
            })
            ->get();
            
        foreach ($ventasInactivas as $venta) {
            $venta->idVentaDia = null;
            $venta->save();
        }
        
        return $contador - 1;
    }

    /**
     * Genera el folio consecutivo desde el 1 de septiembre (solo para ventas con tarjeta O facturadas)
     */
    public static function generarFolioConsecutivo($sucursalId)
    {
        $fechaInicio = Carbon::create(2025, 9, 1);
        
        // Obtener el último folio desde el 1 de septiembre para esta sucursal
        // Solo considerar ventas con tarjeta O facturadas
        $ultimoFolio = self::where('sucursal_id', $sucursalId)
            ->where('created_at', '>=', $fechaInicio)
            ->where('visible', true)
            ->where('estado', '!=', 'eliminada')
            ->where(function($query) {
                $query->where('factura', true)
                      ->orWhere('metodo_pago', 'tarjeta');
            })
            ->whereNotNull('folio')
            ->max('folio');

        return $ultimoFolio ? $ultimoFolio + 1 : 1;
    }

    /**
     * Renumera idVentaNormal (solo para ventas no facturadas y visibles, por día)
     */
    public static function renumerarVentasNormales($sucursalId, $fecha = null)
    {
        $fecha = $fecha ? Carbon::parse($fecha) : Carbon::today();

        // Obtener ventas no facturadas y visibles del día
        $ventas = self::where('sucursal_id', $sucursalId)
            ->whereDate('created_at', $fecha)
            ->where('factura', false)
            ->where('visible', true)
            ->where('estado', '!=', 'eliminada')
            ->orderBy('created_at', 'asc')
            ->get();

        // Renumerar secuencialmente empezando desde 1
        $contador = 1;
        foreach ($ventas as $venta) {
            $venta->idVentaNormal = $contador;
            $venta->save();
            $contador++;
        }

        // Poner null a las ventas facturadas, no visibles o eliminadas del día
        $ventasOtras = self::where('sucursal_id', $sucursalId)
            ->whereDate('created_at', $fecha)
            ->where(function($query) {
                $query->where('factura', true)
                      ->orWhere('visible', false)
                      ->orWhere('estado', 'eliminada');
            })
            ->get();

        foreach ($ventasOtras as $venta) {
            $venta->idVentaNormal = null;
            $venta->save();
        }

        return $contador - 1;
    }

    /**
     * Renumera los folios de ventas facturadas o con tarjeta desde el 1 de septiembre
     */
    public static function renumerarFolios($sucursalId, $fecha = null)
    {
        $fechaInicio = Carbon::create(2025, 9, 1);

        // PRIMERO: Poner null todos los folios que no cumplen las condiciones (no factura Y no tarjeta)
        $ventasSinFolio = self::where('sucursal_id', $sucursalId)
            ->where('created_at', '>=', $fechaInicio)
            ->where(function($query) {
                $query->where(function($q) {
                    $q->where('factura', false)
                      ->where('metodo_pago', '!=', 'tarjeta');
                })
                ->orWhere('visible', false)
                ->orWhere('estado', 'eliminada');
            })
            ->get();

        foreach ($ventasSinFolio as $venta) {
            $venta->folio = null;
            $venta->save();
        }

        // SEGUNDO: Obtener TODAS las ventas que requieren folio (factura O tarjeta) desde el 1 de septiembre ordenadas cronológicamente
        $ventasConFolio = self::where('sucursal_id', $sucursalId)
            ->where('created_at', '>=', $fechaInicio)
            ->where(function($query) {
                $query->where('factura', true)
                      ->orWhere('metodo_pago', 'tarjeta');
            })
            ->where('estado', '!=', 'eliminada')
            ->where('visible', true)
            ->orderBy('created_at', 'asc')
            ->get();

        // Renumerar secuencialmente desde 1
        $contador = 1;
        foreach ($ventasConFolio as $venta) {
            $venta->folio = $contador;
            $venta->save();
            $contador++;
        }

        return $contador - 1;
    }
}
