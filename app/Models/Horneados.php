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
        'responsable_id',
        'relleno',
        'piezas',
    ];

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    public function responsable()
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }

}
