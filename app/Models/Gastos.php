<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gastos extends Model
{
    use HasFactory;

    protected $table = 'gastos';

    /**
     * Atributos que son asignables en masa.
     */
    protected $fillable = [
        'inventario_id',
        'sucursal_id',
        'costo',
        'nombre ',
        'trabajador_id'
    ];

    /**
     * Relación con el inventario.
     */
    public function inventario()
    {
        return $this->belongsTo(Inventario::class);
    }

    /**
     * Relación con la sucursal.
     */
    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function trabajador()
    {
        return $this->belongsTo(User::class, 'trabajador_id', 'id');
    }
}
