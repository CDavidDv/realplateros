<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PuntosEmpleado extends Model
{
    protected $table = 'puntos_empleado';

    protected $fillable = [
        'user_id',
        'sucursal_id',
        'concepto',
        'puntos',
        'descripcion',
        'referencia_id',
        'referencia_tipo',
    ];

    protected $casts = [
        'puntos' => 'integer',
        'referencia_id' => 'integer',
    ];

    /**
     * Relación con el usuario
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con la sucursal
     */
    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    /**
     * Registrar puntos para un empleado
     */
    public static function registrarPuntos(
        int $userId,
        int $sucursalId,
        string $concepto,
        ?int $referenciaId = null,
        ?string $referenciaTipo = null,
        ?string $descripcion = null
    ): ?self {
        $puntos = PuntosConfiguracion::obtenerPuntos($concepto);

        if ($puntos === 0) {
            return null;
        }

        return self::create([
            'user_id' => $userId,
            'sucursal_id' => $sucursalId,
            'concepto' => $concepto,
            'puntos' => $puntos,
            'descripcion' => $descripcion,
            'referencia_id' => $referenciaId,
            'referencia_tipo' => $referenciaTipo,
        ]);
    }

    /**
     * Scope por rango de fechas
     */
    public function scopeEntreFechas($query, $fechaInicio, $fechaFin)
    {
        return $query->whereDate('created_at', '>=', $fechaInicio)
                     ->whereDate('created_at', '<=', $fechaFin);
    }

    /**
     * Scope por sucursal
     */
    public function scopePorSucursal($query, $sucursalId)
    {
        return $query->where('sucursal_id', $sucursalId);
    }

    /**
     * Scope por usuario
     */
    public function scopePorUsuario($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
