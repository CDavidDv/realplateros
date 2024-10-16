<?php

namespace Database\Seeders;

use App\Models\Sucursal;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SucursalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Sucursal::create([
            'nombre' => 'Sucursal 1',
            'direccion' => 'Calle 1',
            'telefono' => '123',
        ]);
        Sucursal::create([
            'nombre' => 'Sucursal 2',
            'direccion' => 'Calle 2',
            'telefono' => '123',
        ]);
    }
}
