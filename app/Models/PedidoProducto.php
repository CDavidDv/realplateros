<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PedidoProducto extends Model
{
    use HasFactory;

    protected $fillable = ['pedido_id', 'inventario_id', 'cantidad', 'precio'];

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    public function inventario()
    {
        return $this->belongsTo(Inventario::class);
    }
}
