<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Horneados extends Model
{
    use HasFactory;

    protected $table = 'horneados';

    protected $fillable = [
        'sucursal_id',
        'relleno',
        'piezas',
    ];

}
