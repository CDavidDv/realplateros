<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ControlProduccion extends Model
{
    protected $table = 'control_produccion';

    protected $fillable = [
        'horno_id',
        'paste_id',
        'sucursal_id',
        'cantidad',
        'tiempo_inicio_horneado',
        'tiempo_fin_horneado',
        'hora_ultima_venta',
        'cantidad_vendida',
        'cantidad_horneada',
        'estado',
        'hora_notificacion',
        'dia_notificacion',
        'updated_at'    
    ];

    protected $casts = [
        'tiempo_inicio_horneado' => 'datetime',
        'tiempo_fin_horneado' => 'datetime',
        'tiempo_retiro_horno' => 'datetime',
        'tiempo_ultima_venta' => 'datetime'
    ];

    public function horno()
    {
        return $this->belongsTo(Hornos::class);
    }

    public function paste()
    {
        return $this->belongsTo(Inventario::class, 'paste_id');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function actualizarVenta($cantidad)
    {
        $this->cantidad_vendida += $cantidad;
        $this->tiempo_ultima_venta = now();
        
        if ($this->cantidad_vendida >= $this->cantidad) {
            $this->estado = 'vendido';
        }
        
        $this->save();
    }
} 