<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EntradasInventario extends Model
{
    use HasFactory;

    protected $table = 'entradas_inventario';

    /**
     * Clave primaria del modelo.
     */
    protected $primaryKey = 'id';

    /**
     * Atributos que son asignables en masa.
     */
    protected $fillable = [
        'inventario_id',
        'trabajador_id',
        'sucursal_id',
        'cantidad',
    ];

    /**
     * Relación con el producto (asumiendo que hay una tabla 'productos').
     */
    public function inventario()
    {
        return $this->belongsTo(Inventario::class, 'inventario_id', 'id');
    }

    /**
     * Relación con el usuario/trabajador.
     */
    public function trabajador()
    {
        return $this->belongsTo(User::class, 'trabajador_id', 'id');
    }

    /**
     * Relación con la sucursal.
     */
    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id', 'id');
    }
}
