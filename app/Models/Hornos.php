<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hornos extends Model
{
    use HasFactory;

    protected $table = 'horno';

    protected $fillable = [
        'id',
        'sucursal_id',
        'tiempo_inicio',
        'tiempo_fin',
        'estado',
        'created_at',
        'updated_at',
        'pastesHorneando'
    ];

    protected $casts = [
        'pastesHorneando' => 'array',
    ];
    
}
