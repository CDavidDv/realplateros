<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sobrantes extends Model
{
    use HasFactory;
    protected $table = 'sobrantes';
    protected $fillable = [
        'inventario_id',
        'sucursal_id',
        'trabajador_id',
        'cantidad',
        'corte_caja_id',
    ];
    public function inventario()
    {
        return $this->belongsTo(Inventario::class, 'inventario_id', 'id');
    }
    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id', 'id');
    }
    public function corteCaja()
    {
        return $this->belongsTo(CorteCaja::class, 'corte_caja_id', 'id');
    }

    public function trabajador()
    {
        return $this->belongsTo(User::class, 'trabajador_id');
    }
}
