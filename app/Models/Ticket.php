<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;
    protected $fillable = [
        'venta_id',
        'numero_ticket',
        'total',
        'creado_por',
        'sucursal_id',
        'metodo_pago',
    ];

    // Relación con la tabla de ventas
    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }

    // Relación con el usuario que creó el ticket
    public function usuario()
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    // Relación con la sucursal
    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }
}
