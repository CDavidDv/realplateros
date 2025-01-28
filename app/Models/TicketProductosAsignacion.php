<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class TicketProductosAsignacion extends Model
{
    use HasFactory;
    protected $table = 'ticket_productos_asignacion'; // Nombre de la tabla en la base de datos

    protected $fillable = ['tickets_asignacion_id', 'producto_id', 'cantidad', 'precio_unitario'];


    public function ticket_asignacion()
    {
        return $this->belongsTo(TicketAsignacion::class, 'ticket_asignacion_id', 'id');
    }

    public function producto()
    {
        return $this->belongsTo(Inventario::class, 'producto_id');
    }

    public function inventario()
    {
        return $this->belongsTo(Inventario::class, 'producto_id');
    }
    
}