<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cocina extends Model
{
    use HasFactory;

    protected $fillable = ['sucursal_id', 'tipo_paste', 'cantidad', 'estado', 'tiempo_preparacion'];

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }
}
