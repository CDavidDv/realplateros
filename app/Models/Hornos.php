<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hornos extends Model
{
    use HasFactory;

    protected $table = 'horno';

    protected $fillable = [
        'nombre',
        'sucursal_id',
        'estado',
        'tiempo_inicio',
        'tiempo_fin',
        'pastesHorneando'
    ];

    protected $casts = [
        'estado' => 'boolean',
        'pastesHorneando' => 'array',
        'tiempo_inicio' => 'datetime',
        'tiempo_fin' => 'datetime'
    ];

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }
}
