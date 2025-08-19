<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ControlProduccion;
use Carbon\Carbon;

class TestControlProduccionFiltros extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:control-produccion-filtros {--fecha=} {--hora=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Probar los filtros del control de producción';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $fecha = $this->option('fecha') ?: Carbon::now()->format('Y-m-d');
        $hora = $this->option('hora') ?: 'todas';

        $this->info("Probando filtros del control de producción:");
        $this->info("Fecha: {$fecha}");
        $this->info("Hora: {$hora}");
        $this->info("");

        // Simular la lógica del controlador
        $query = ControlProduccion::query();

        // Aplicar filtro de fecha
        if ($fecha) {
            $fechaObj = Carbon::parse($fecha);
            $query->whereDate('created_at', $fechaObj->toDateString());
            $this->info("✓ Filtro de fecha aplicado: {$fechaObj->toDateString()}");
        }

        // Aplicar filtro de hora
        if ($hora && $hora !== 'todas' && $hora !== 'actual') {
            // Convertir formato de hora
            $horaFormateada = $this->formatearHoraParaBD($hora);
            if ($horaFormateada) {
                $query->where('hora_notificacion', $horaFormateada);
                $this->info("✓ Filtro de hora aplicado: {$hora} → {$horaFormateada}");
            } else {
                $this->warn("⚠ No se pudo formatear la hora: {$hora}");
            }
        } elseif ($hora === 'actual') {
            $horaSiguiente = Carbon::now()->addHour()->format('g:i A');
            $query->where('hora_notificacion', $horaSiguiente);
            $this->info("✓ Filtro hora siguiente aplicado: {$horaSiguiente}");
        }

        // Obtener resultados
        $total = $query->count();
        $this->info("");
        $this->info("Total de registros encontrados: {$total}");

        if ($total > 0) {
            $this->info("");
            $this->info("Primeros 5 registros:");
            $this->info("");

            $registros = $query->take(5)->get();
            foreach ($registros as $registro) {
                $this->info("ID: {$registro->id}");
                $this->info("  - Hora notificación: {$registro->hora_notificacion}");
                $this->info("  - Día notificación: {$registro->dia_notificacion}");
                $this->info("  - Estado: {$registro->estado}");
                $this->info("  - Created at: {$registro->created_at}");
                $this->info("");
            }
        }

        // Mostrar estadísticas por estado
        $estados = $query->get()->groupBy('estado');
        $this->info("Estadísticas por estado:");
        foreach ($estados as $estado => $registros) {
            $this->info("  - {$estado}: " . $registros->count());
        }

        return Command::SUCCESS;
    }

    /**
     * Formatear hora del frontend al formato de la base de datos
     */
    private function formatearHoraParaBD($hora)
    {
        if (!$hora || !is_string($hora)) {
            return null;
        }

        try {
            // Si ya está en formato correcto (ej: "7:00 AM"), devolverlo tal como está
            if (preg_match('/^\d{1,2}:\d{2}\s?(AM|PM)$/i', $hora)) {
                return strtolower($hora);
            }

            // Convertir formato "7:00 AM" a "7:00 am"
            if (preg_match('/^(\d{1,2}):(\d{2})\s?(AM|PM)$/i', $hora, $matches)) {
                $horaNum = (int)$matches[1];
                $minutos = $matches[2];
                $modifier = strtolower($matches[3]);
                
                // Asegurar formato consistente
                return sprintf('%d:%02d %s', $horaNum, $minutos, $modifier);
            }

            // Convertir formato "7-am" a "7:00 am"
            if (preg_match('/^(\d{1,2})-(am|pm)$/i', $hora, $matches)) {
                $horaNum = (int)$matches[1];
                $modifier = strtolower($matches[2]);
                
                return sprintf('%d:00 %s', $horaNum, $modifier);
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
