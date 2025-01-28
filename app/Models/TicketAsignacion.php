<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketAsignacion extends Model
{
    use HasFactory;
    protected $table = 'tickets_asignacion';

    protected $fillable = ['estado', 'sucursal_id', 'empleado_id', 'hora_salida', 'hora_llegada'];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'empleado_id');
    }
    // En el modelo TicketAsignacion.php
    public function ticket_productos_asignacion()
    {
        return $this->hasMany(TicketProductosAsignacion::class, 'ticket_asignacion_id', 'id');
    }



    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function detalles()
    {
        return $this->hasMany(VentaProducto::class);
    }
}
