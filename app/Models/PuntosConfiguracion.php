<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PuntosConfiguracion extends Model
{
    protected $table = 'puntos_configuracion';

    protected $fillable = [
        'concepto',
        'puntos',
        'descripcion',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'puntos' => 'integer',
    ];

    /**
     * Scope para obtener solo configuraciones activas
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Obtener puntos por concepto
     */
    public static function obtenerPuntos(string $concepto): int
    {
        $config = self::where('concepto', $concepto)->where('activo', true)->first();
        return $config ? $config->puntos : 0;
    }
}
