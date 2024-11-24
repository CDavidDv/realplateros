<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventario extends Model
{
    use HasFactory;

    protected $fillable = ['sucursal_id', 'nombre', 'tipo', 'cantidad', 'precio'];

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function pedidos()
    {
        return $this->hasMany(PedidoProducto::class);
    }

    public function ventas()
    {
        return $this->hasMany(VentaProducto::class, 'producto_id');
    }

}
