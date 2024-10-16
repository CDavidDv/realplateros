<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    use HasFactory;

    protected $fillable = ['sucursal_id', 'surtidor_id', 'total', 'estado'];

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function surtidor()
    {
        return $this->belongsTo(Usuario::class, 'surtidor_id');
    }

    public function productos()
    {
        return $this->hasMany(PedidoProducto::class);
    }
}
