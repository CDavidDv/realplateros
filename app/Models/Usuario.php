<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class Usuario extends Authenticatable
{
    use HasFactory, Notifiable;
    use HasRoles;

    protected $guard_name = 'web';
    protected $fillable = ['name', 'email', 'password', 'sucursal_id'];

    protected $hidden = ['password', 'remember_token'];

    protected $table = 'users';

    // Relación Muchos a Muchos con sucursales
    public function sucursales()
    {
        return $this->belongsToMany(Sucursal::class, 'usuario_sucursales')
                    ->withTimestamps(); // Para registrar fechas de asignación
    }

    public function checkInCheckOuts()
    {
        return $this->hasMany(CheckInCheckOut::class);
    }

    public function ventas()
    {
        return $this->hasMany(Venta::class);
    }

    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'surtidor_id');
    }

    public function cortesDeCaja()
    {
        return $this->hasMany(CorteCaja::class);
    }
}
