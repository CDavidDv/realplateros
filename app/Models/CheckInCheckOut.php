<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CheckInCheckOut extends Model
{
    use HasFactory;

    
    protected $table = 'check_in_check_out';

    protected $fillable = [
        'user_id',
        'sucursal_id',
        'estado',
        'horas_trabajadas',
        'check_in',
        'check_out',
    ];

    
        
    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }


    protected $casts = [
        'check_in' => 'datetime',
        'check_out' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }
}
