<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Inventario;

class InventariosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Coca Cola',
            'tipo' => 'bebida',
            'detalle' => 'Envase (Botella)',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 22,
        ]);
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Coca Cola',
            'tipo' => 'bebida',
            'detalle' => 'Lata',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 22,
        ]);
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Sidral mundet',
            'tipo' => 'bebida',
            'detalle' => 'Botella',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 22,
        ]);
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Sidral mundet',
            'tipo' => 'bebida',
            'detalle' => 'Lata',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 22,
        ]);
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Sprit',
            'tipo' => 'bebida',
            'detalle' => 'Botella',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 22,
        ]);
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Sprit',
            'tipo' => 'bebida',
            'detalle' => 'Lata',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 22,
        ]);
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Fanta naranja',
            'tipo' => 'bebida',
            'detalle' => 'Botella',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 22,
        ]);
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Fanta Fresa',
            'tipo' => 'bebida',
            'detalle' => 'Botella',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 22,
        ]);
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Fanta naranja',
            'tipo' => 'bebida',
            'detalle' => 'Botella',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 22,
        ]);
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Fresca',
            'tipo' => 'bebida',
            'detalle' => 'Botella',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 22,
        ]);
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Fresca',
            'tipo' => 'bebida',
            'detalle' => 'Lata',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 22,
        ]);
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Delawer',
            'tipo' => 'bebida',
            'detalle' => 'Botella',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 22,
        ]);
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Limonada',
            'tipo' => 'bebida',
            'detalle' => 'Botella',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 22,
        ]);
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Naranjada',
            'tipo' => 'bebida',
            'detalle' => 'Botella',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 22,
        ]);
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Valle frut guayaba',
            'tipo' => 'bebida',
            'detalle' => 'Botella',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 22,
        ]);
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Valle frut naranja',
            'tipo' => 'bebida',
            'detalle' => 'Botella',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 22,
        ]);
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Generosa mango',
            'tipo' => 'bebida',
            'detalle' => 'Botella',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 22,
        ]);
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Generosa durazno',
            'tipo' => 'bebida',
            'detalle' => 'Botella',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 22,
        ]);
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Agua 1.5L',
            'tipo' => 'bebida',
            'detalle' => 'Botella',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 22,
        ]);

        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Agua 1L',
            'tipo' => 'bebida',
            'detalle' => 'Botella',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 22,
        ]);
       
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Caja',
            'tipo' => 'extras',
            'detalle' => 'Pequeña',
            'cantidad' => 0,
            'costo' => 5,
            'precio' => 12,
        ]);
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Caja',
            'tipo' => 'extras',
            'detalle' => 'Mediana',
            'cantidad' => 0,
            'costo' => 5,
            'precio' => 12,
        ]);
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'caja',
            'tipo' => 'extras',
            'detalle' => 'Grande',
            'cantidad' => 0,
            'costo' => 5,
            'precio' => 12,
        ]);
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'bolsa',
            'tipo' => 'extras',
            'cantidad' => 0,
            'costo' => 0.5,
            'precio' => 1,
        ]);

        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'bola',
            'tipo' => 'masa',
            'detalle' => 'unidades',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 20,
        ]);
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'salada',
            'tipo' => 'masa',
            'detalle' => 'unidades',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 20,
        ]);
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'dulce',
            'tipo' => 'masa',
            'detalle' => 'unidades',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 20,
        ]);
        
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Papa con carne',
            'tipo' => 'relleno',
            'detalle' => 'unidades',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 23,
        ]);
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Crema con pollo',
            'tipo' => 'relleno',
            'detalle' => 'unidades',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 23,
        ]);
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Frijol con chorizo',
            'tipo' => 'relleno',
            'detalle' => 'unidades',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 23,
        ]);
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Mole rojo',
            'tipo' => 'relleno',
            'detalle' => 'unidades',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 20,
        ]);
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Mole verde',
            'tipo' => 'relleno',
            'detalle' => 'unidades',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 20,
        ]);
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Salchicha',
            'tipo' => 'relleno',
            'detalle' => 'unidades',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 20,
        ]);
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Tinga',
            'tipo' => 'relleno',
            'detalle' => 'unidades',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 20,
        ]);
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Minero',
            'tipo' => 'relleno',
            'detalle' => 'unidades',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 20,
        ]);
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Atún',
            'tipo' => 'relleno',
            'detalle' => 'unidades',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 20,
        ]);
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Choriqueso',
            'tipo' => 'relleno',
            'detalle' => 'unidades',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 20,
        ]);
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Rajas con champiñones',
            'tipo' => 'relleno',
            'detalle' => 'unidades',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 20,
        ]);

        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Hawaiano',
            'tipo' => 'relleno',
            'detalle' => 'unidades',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 20,
        ]);
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Arroz con leche',
            'tipo' => 'relleno',
            'detalle' => 'unidades',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 20,
        ]);
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Piña',
            'tipo' => 'relleno',
            'detalle' => 'unidades',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 20,
        ]);
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Manzana',
            'tipo' => 'relleno',
            'detalle' => 'unidades',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 20,
        ]);
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Zarzamora',
            'tipo' => 'relleno',
            'detalle' => 'unidades',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 20,
        ]);
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Cajeta',
            'tipo' => 'relleno',
            'detalle' => 'unidades',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 20,
        ]);
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Fresa',
            'tipo' => 'relleno',
            'detalle' => 'unidades',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 20,
        ]);
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Budin',
            'tipo' => 'relleno',
            'detalle' => 'unidades',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 20,
        ]);

        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Papa con carne',
            'tipo' => 'pastes',
            
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 23,
        ]);
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Crema con pollo',
            'tipo' => 'pastes',
            
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 23,
        ]);
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Frijol con chorizo',
            'tipo' => 'pastes',
            
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 23,
        ]);
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Mole rojo',
            'tipo' => 'empanadas saladas',
            
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 23,
        ]);
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Mole verde',
            'tipo' => 'empanadas saladas',
            
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 23,
        ]);
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Salchicha',
            'tipo' => 'empanadas saladas',
            
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 23,
        ]);
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Tinga',
            'tipo' => 'empanadas saladas',
            
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 23,
        ]);
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Minero',
            'tipo' => 'empanadas saladas',
            
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 23,
        ]);
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Atún',
            'tipo' => 'empanadas saladas',
            
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 23,
        ]);
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Choriqueso',
            'tipo' => 'empanadas saladas',
            
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 23,
        ]);
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Rajas con champiñones',
            'tipo' => 'empanadas saladas',
            
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 23,
        ]);

        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Hawaiano',
            'tipo' => 'empanadas dulces',
            
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 23,
        ]);
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Arroz con leche',
            'tipo' => 'empanadas dulces',
            
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 23,
        ]);
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Piña',
            'tipo' => 'empanadas dulces',
            
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 23,
        ]);
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Manzana',
            'tipo' => 'empanadas dulces',
            
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 23,
        ]);
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Zarzamora',
            'tipo' => 'empanadas dulces',
            
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 23,
        ]);
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Cajeta',
            'tipo' => 'empanadas dulces',
            
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 23,
        ]);
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Fresa',
            'tipo' => 'empanadas dulces',
            
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 23,
        ]);
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Budin',
            'tipo' => 'empanadas dulces',
            
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 23,
        ]);
    }
}
