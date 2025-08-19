<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ControlProduccion;
use App\Models\Inventario;
use App\Models\Sucursal;
use Carbon\Carbon;

class ControlProduccionTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener sucursal de prueba
        $sucursal = Sucursal::first();
        if (!$sucursal) {
            $this->command->error('No hay sucursales disponibles. Ejecuta SucursalSeeder primero.');
            return;
        }

        // Obtener productos de inventario
        $productos = Inventario::where('sucursal_id', $sucursal->id)
            ->whereIn('tipo', ['pastes', 'empanadas dulces', 'empanadas saladas'])
            ->take(5)
            ->get();

        if ($productos->isEmpty()) {
            $this->command->error('No hay productos de inventario disponibles. Ejecuta InventariosSeeder primero.');
            return;
        }

        // Datos de prueba con diferentes horas y días
        $datosPrueba = [
            [
                'hora_notificacion' => '7:00 am',
                'dia_notificacion' => 'lunes',
                'estado' => 'pendiente',
                'cantidad' => 20
            ],
            [
                'hora_notificacion' => '8:00 am',
                'dia_notificacion' => 'lunes',
                'estado' => 'horneando',
                'cantidad' => 15
            ],
            [
                'hora_notificacion' => '9:00 am',
                'dia_notificacion' => 'lunes',
                'estado' => 'en_espera',
                'cantidad' => 25
            ],
            [
                'hora_notificacion' => '10:00 am',
                'dia_notificacion' => 'lunes',
                'estado' => 'vendido',
                'cantidad' => 30
            ],
            [
                'hora_notificacion' => '11:00 am',
                'dia_notificacion' => 'lunes',
                'estado' => 'pendiente',
                'cantidad' => 18
            ],
            [
                'hora_notificacion' => '12:00 pm',
                'dia_notificacion' => 'lunes',
                'estado' => 'horneando',
                'cantidad' => 22
            ],
            [
                'hora_notificacion' => '1:00 pm',
                'dia_notificacion' => 'lunes',
                'estado' => 'pendiente',
                'cantidad' => 12
            ],
            [
                'hora_notificacion' => '2:00 pm',
                'dia_notificacion' => 'lunes',
                'estado' => 'en_espera',
                'cantidad' => 28
            ],
            [
                'hora_notificacion' => '3:00 pm',
                'dia_notificacion' => 'lunes',
                'estado' => 'pendiente',
                'cantidad' => 35
            ],
            [
                'hora_notificacion' => '4:00 pm',
                'dia_notificacion' => 'lunes',
                'estado' => 'vendido',
                'cantidad' => 40
            ],
            // Datos para martes
            [
                'hora_notificacion' => '7:00 am',
                'dia_notificacion' => 'martes',
                'estado' => 'pendiente',
                'cantidad' => 16
            ],
            [
                'hora_notificacion' => '8:00 am',
                'dia_notificacion' => 'martes',
                'estado' => 'horneando',
                'cantidad' => 19
            ],
            [
                'hora_notificacion' => '9:00 am',
                'dia_notificacion' => 'martes',
                'estado' => 'pendiente',
                'cantidad' => 24
            ],
            [
                'hora_notificacion' => '10:00 am',
                'dia_notificacion' => 'martes',
                'estado' => 'en_espera',
                'cantidad' => 31
            ],
            [
                'hora_notificacion' => '11:00 am',
                'dia_notificacion' => 'martes',
                'estado' => 'vendido',
                'cantidad' => 27
            ]
        ];

        $this->command->info('Creando datos de prueba para ControlProduccion...');

        foreach ($datosPrueba as $index => $dato) {
            $producto = $productos[$index % $productos->count()];
            
            ControlProduccion::create([
                'horno_id' => null, // Sin horno asignado para pruebas
                'paste_id' => $producto->id,
                'sucursal_id' => $sucursal->id,
                'cantidad' => $dato['cantidad'],
                'tiempo_inicio_horneado' => $dato['estado'] === 'horneando' ? now() : null,
                'tiempo_fin_horneado' => $dato['estado'] === 'en_espera' ? now()->addMinutes(30) : null,
                'hora_ultima_venta' => $dato['estado'] === 'vendido' ? now()->addMinutes(45) : null,
                'cantidad_vendida' => $dato['estado'] === 'vendido' ? $dato['cantidad'] : 0,
                'cantidad_horneada' => in_array($dato['estado'], ['horneando', 'en_espera', 'vendido']) ? $dato['cantidad'] : 0,
                'estado' => $dato['estado'],
                'hora_notificacion' => $dato['hora_notificacion'],
                'dia_notificacion' => $dato['dia_notificacion'],
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        $this->command->info('Se crearon ' . count($datosPrueba) . ' registros de prueba para ControlProduccion.');
        $this->command->info('Horas disponibles: 7:00 am, 8:00 am, 9:00 am, 10:00 am, 11:00 am, 12:00 pm, 1:00 pm, 2:00 pm, 3:00 pm, 4:00 pm');
        $this->command->info('Días disponibles: lunes, martes');
        $this->command->info('Estados disponibles: pendiente, horneando, en_espera, vendido');
    }
}
