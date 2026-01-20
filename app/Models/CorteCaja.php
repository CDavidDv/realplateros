<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CorteCaja extends Model
{
    use HasFactory;

    // Nombre de la tabla (opcional si sigue la convención de nombres)
    protected $table = 'corte_caja';

    // Los atributos que se pueden asignar de forma masiva
    protected $fillable = [
        'sucursal_id',
        'usuario_id',
        'fecha',
        'dinero_inicio',
        'dinero_final',
        'ventas_total',
        'dinero_en_efectivo',
        'dinero_tarjeta',
        'efectivo_contado',
        'tarjeta_contada',
        'notas_reconciliacion',
    ];

    // Relación con la tabla Sucursal
    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    // Relación con la tabla Usuario
    public function usuario()
    {
        return $this->belongsTo(User::class);
    }

    // Relación con la tabla Sobrantes
    public function sobrantes()
    {
        return $this->hasMany(Sobrantes::class);
    }
}
