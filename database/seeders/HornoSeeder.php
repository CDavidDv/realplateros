<?php

namespace Database\Seeders;

use App\Models\Hornos;
use App\Models\Sucursal;
use Illuminate\Database\Seeder;

class HornoSeeder extends Seeder
{
    public function run()
    {
        // Obtener todas las sucursales
        $sucursales = Sucursal::all();

        foreach ($sucursales as $sucursal) {
            // Verificar si la sucursal ya tiene un horno
            $hornoExistente = Hornos::where('sucursal_id', $sucursal->id)->first();

            // Si no tiene horno, crear uno por defecto
            if (!$hornoExistente) {
                Hornos::create([
                    'sucursal_id' => $sucursal->id,
                    'estado' => false
                ]);
            }
        }
    }
} 