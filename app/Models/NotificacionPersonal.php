<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificacionPersonal extends Model
{
    use HasFactory;

    protected $table = 'notificaciones_personal';

    protected $fillable = [
        'control_produccion_id',
        'sucursal_id',
        'user_id',
        'tipo',
        'descripcion',
        'atendida',
        'atendida_at',
        'atendida_por',
    ];

    protected $casts = [
        'atendida' => 'boolean',
        'atendida_at' => 'datetime',
    ];

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function atendidaPor()
    {
        return $this->belongsTo(User::class, 'atendida_por');
    }

    public function controlProduccion()
    {
        return $this->belongsTo(ControlProduccion::class, 'control_produccion_id');
    }
}
