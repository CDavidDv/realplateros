<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use HasRoles;

    protected $guard_name = 'web';

    protected $table = 'users';

    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'apellido_p',
        'apellido_m',
        'tel',
        'email',
        'password',
        'sucursal_id',
        'inicio_contrato',
        'fin_contrato',
        'active',
        'es_almacen',
    ];

    public function checkInCheckOut()
    {
        return $this->hasMany(CheckInCheckOut::class);
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function ventas()
    {
        return $this->belongsTo(Venta::class);
    }
    
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'es_almacen' => 'boolean',
        'active' => 'boolean',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    public function cortesDeCaja()
    {
        return $this->hasMany(CorteCaja::class);
    }

    /**
     * Verifica si el usuario es de almacén
     */
    public function esAlmacen(): bool
    {
        return $this->es_almacen === true;
    }

    /**
     * Verifica si el usuario pertenece a una sucursal normal (no almacén)
     */
    public function esSucursal(): bool
    {
        return !$this->es_almacen && $this->sucursal_id !== null && $this->sucursal_id !== 0;
    }

    /**
     * Scope para filtrar solo usuarios de almacén
     */
    public function scopeAlmacen($query)
    {
        return $query->where('es_almacen', true);
    }

    /**
     * Scope para filtrar solo usuarios de sucursales normales
     */
    public function scopeSucursales($query)
    {
        return $query->where('es_almacen', false);
    }
}
