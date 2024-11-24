<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estimaciones extends Model
{
    use HasFactory;
    protected $table = 'estimaciones';
    protected $fillable = [
        'inventario_id',
        'sucursal_id',
        'dia',
        'hora',
        'cantidad'
    ];
    public function inventario()
    {
        return $this->belongsTo(Inventario::class, 'inventario_id', 'id');
    }

    /**
     * Relación con Sucursal (muchos a uno)
     */
    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id', 'id');
    }
}
