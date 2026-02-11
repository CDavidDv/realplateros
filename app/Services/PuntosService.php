<?php

namespace App\Services;

use App\Models\PuntosConfiguracion;
use App\Models\PuntosEmpleado;
use App\Models\ControlProduccion;
use App\Models\NotificacionPersonal;
use App\Models\CheckInCheckOut;
use App\Models\Venta;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PuntosService
{
    /**
     * Registrar puntos por una acción específica
     */
    public function registrar(
        int $userId,
        int $sucursalId,
        string $concepto,
        ?int $referenciaId = null,
        ?string $referenciaTipo = null,
        ?string $descripcion = null
    ): ?PuntosEmpleado {
        try {
            return PuntosEmpleado::registrarPuntos(
                $userId,
                $sucursalId,
                $concepto,
                $referenciaId,
                $referenciaTipo,
                $descripcion
            );
        } catch (\Exception $e) {
            Log::error('Error al registrar puntos:', [
                'user_id' => $userId,
                'concepto' => $concepto,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Calcular puntos totales de un empleado en rango de fechas
     */
    public function calcularTotal(int $userId, string $fechaInicio, string $fechaFin): int
    {
        return PuntosEmpleado::porUsuario($userId)
            ->entreFechas($fechaInicio, $fechaFin)
            ->sum('puntos');
    }

    /**
     * Obtener ranking de empleados
     */
    public function obtenerRanking(string $fechaInicio, string $fechaFin, ?int $sucursalId = null): array
    {
        $query = PuntosEmpleado::select('user_id')
            ->selectRaw('SUM(puntos) as total_puntos')
            ->selectRaw('SUM(CASE WHEN concepto = "venta" THEN 1 ELSE 0 END) as total_ventas')
            ->selectRaw('SUM(CASE WHEN concepto = "horneado" THEN 1 ELSE 0 END) as total_horneados')
            ->selectRaw('SUM(CASE WHEN concepto = "notificacion_atendida" THEN 1 ELSE 0 END) as notificaciones_atendidas')
            ->selectRaw('SUM(CASE WHEN concepto = "check_in" THEN 1 ELSE 0 END) as total_check_ins')
            ->entreFechas($fechaInicio, $fechaFin)
            ->groupBy('user_id');

        if ($sucursalId) {
            $query->porSucursal($sucursalId);
        }

        $resultados = $query->orderByDesc('total_puntos')->get();

        $ranking = [];
        $posicion = 1;

        foreach ($resultados as $resultado) {
            $user = User::find($resultado->user_id);
            if (!$user) continue;

            // Obtener horas trabajadas desde check_in_check_out
            $horasTrabajadas = $this->calcularHorasTrabajadas($resultado->user_id, $fechaInicio, $fechaFin);

            $ranking[] = [
                'posicion' => $posicion,
                'user_id' => $resultado->user_id,
                'nombre' => $user->name,
                'email' => $user->email,
                'sucursal' => $user->sucursal?->nombre ?? '-',
                'sucursal_id' => $user->sucursal_id,
                'total_puntos' => (int) $resultado->total_puntos,
                'total_ventas' => (int) $resultado->total_ventas,
                'total_horneados' => (int) $resultado->total_horneados,
                'notificaciones_atendidas' => (int) $resultado->notificaciones_atendidas,
                'total_check_ins' => (int) $resultado->total_check_ins,
                'horas_trabajadas' => $horasTrabajadas,
            ];
            $posicion++;
        }

        return $ranking;
    }

    /**
     * Obtener métricas detalladas de un empleado
     */
    public function obtenerMetricas(int $userId, string $fechaInicio, string $fechaFin): array
    {
        $puntosPorConcepto = PuntosEmpleado::porUsuario($userId)
            ->entreFechas($fechaInicio, $fechaFin)
            ->select('concepto')
            ->selectRaw('SUM(puntos) as total_puntos')
            ->selectRaw('COUNT(*) as cantidad')
            ->groupBy('concepto')
            ->get()
            ->keyBy('concepto');

        $totalPuntos = $this->calcularTotal($userId, $fechaInicio, $fechaFin);
        $horasTrabajadas = $this->calcularHorasTrabajadas($userId, $fechaInicio, $fechaFin);

        // Historial de puntos
        $historial = PuntosEmpleado::porUsuario($userId)
            ->entreFechas($fechaInicio, $fechaFin)
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        return [
            'total_puntos' => $totalPuntos,
            'horas_trabajadas' => $horasTrabajadas,
            'puntos_por_concepto' => $puntosPorConcepto,
            'historial' => $historial,
            'resumen' => [
                'check_ins' => $puntosPorConcepto->get('check_in')?->cantidad ?? 0,
                'ventas' => $puntosPorConcepto->get('venta')?->cantidad ?? 0,
                'horneados' => $puntosPorConcepto->get('horneado')?->cantidad ?? 0,
                'notificaciones_atendidas' => $puntosPorConcepto->get('notificacion_atendida')?->cantidad ?? 0,
                'notificaciones_no_atendidas' => $puntosPorConcepto->get('notificacion_no_atendida')?->cantidad ?? 0,
                'sobrantes' => $puntosPorConcepto->get('sobrante')?->cantidad ?? 0,
                'cortes_caja' => $puntosPorConcepto->get('corte_caja')?->cantidad ?? 0,
            ]
        ];
    }

    /**
     * Calcular horas trabajadas de un empleado
     */
    public function calcularHorasTrabajadas(int $userId, string $fechaInicio, string $fechaFin): float
    {
        $minutosTrabajados = CheckInCheckOut::where('user_id', $userId)
            ->whereDate('created_at', '>=', $fechaInicio)
            ->whereDate('created_at', '<=', $fechaFin)
            ->whereNotNull('horas_trabajadas')
            ->sum('horas_trabajadas');

        // horas_trabajadas está en minutos, convertir a horas
        return round($minutosTrabajados / 60, 2);
    }

    /**
     * Procesar notificaciones no atendidas al fin del día
     */
    public function procesarNotificacionesNoAtendidas(string $fecha, ?int $sucursalId = null): int
    {
        $query = ControlProduccion::whereDate('created_at', $fecha)
            ->whereIn('estado', ['pendiente', 'desperdicio', 'horneando', 'en_espera']);

        if ($sucursalId) {
            $query->where('sucursal_id', $sucursalId);
        }

        $controles = $query->get();
        $procesados = 0;

        foreach ($controles as $control) {
            // Verificar si ya fue atendida
            $atendida = NotificacionPersonal::where('control_produccion_id', $control->id)
                ->where('atendida', true)
                ->exists();

            if (!$atendida) {
                // Buscar el trabajador de turno
                $turno = CheckInCheckOut::where('sucursal_id', $control->sucursal_id)
                    ->whereDate('check_in', $fecha)
                    ->first();

                if ($turno && $turno->user_id) {
                    $this->registrar(
                        $turno->user_id,
                        $control->sucursal_id,
                        'notificacion_no_atendida',
                        $control->id,
                        'control_produccion',
                        'Notificacion no atendida: ' . ($control->paste?->nombre ?? 'Producto')
                    );
                    $procesados++;
                }
            }
        }

        Log::info('Notificaciones no atendidas procesadas:', [
            'fecha' => $fecha,
            'sucursal_id' => $sucursalId,
            'procesados' => $procesados
        ]);

        return $procesados;
    }

    /**
     * Obtener mejor empleado por categoría
     */
    public function obtenerMejoresPorCategoria(string $fechaInicio, string $fechaFin, ?int $sucursalId = null): array
    {
        $ranking = $this->obtenerRanking($fechaInicio, $fechaFin, $sucursalId);

        if (empty($ranking)) {
            return [
                'mejor_general' => null,
                'mas_ventas' => null,
                'mas_horneados' => null,
            ];
        }

        // Mejor general (más puntos)
        $mejorGeneral = $ranking[0] ?? null;

        // Más ventas
        $masVentas = collect($ranking)->sortByDesc('total_ventas')->first();

        // Más horneados
        $masHorneados = collect($ranking)->sortByDesc('total_horneados')->first();

        return [
            'mejor_general' => $mejorGeneral,
            'mas_ventas' => $masVentas,
            'mas_horneados' => $masHorneados,
        ];
    }

    /**
     * Obtener estadísticas generales
     */
    public function obtenerEstadisticasGenerales(string $fechaInicio, string $fechaFin, ?int $sucursalId = null): array
    {
        $query = PuntosEmpleado::entreFechas($fechaInicio, $fechaFin);

        if ($sucursalId) {
            $query->porSucursal($sucursalId);
        }

        $totalPuntos = (clone $query)->sum('puntos');
        $totalEmpleados = (clone $query)->distinct('user_id')->count('user_id');
        $promedioPuntos = $totalEmpleados > 0 ? round($totalPuntos / $totalEmpleados, 2) : 0;

        $puntosPorConcepto = (clone $query)
            ->select('concepto')
            ->selectRaw('SUM(puntos) as total')
            ->selectRaw('COUNT(*) as cantidad')
            ->groupBy('concepto')
            ->get()
            ->keyBy('concepto');

        // Calcular promedio de horas trabajadas
        $queryHoras = CheckInCheckOut::whereDate('created_at', '>=', $fechaInicio)
            ->whereDate('created_at', '<=', $fechaFin)
            ->whereNotNull('horas_trabajadas');

        if ($sucursalId) {
            $queryHoras->where('sucursal_id', $sucursalId);
        }

        $totalMinutos = $queryHoras->sum('horas_trabajadas');
        $totalCheckIns = $queryHoras->count();
        $promedioHoras = $totalCheckIns > 0 ? round(($totalMinutos / $totalCheckIns) / 60, 2) : 0;

        return [
            'total_puntos' => $totalPuntos,
            'total_empleados' => $totalEmpleados,
            'promedio_puntos' => $promedioPuntos,
            'promedio_horas_trabajadas' => $promedioHoras,
            'puntos_por_concepto' => $puntosPorConcepto,
        ];
    }

    /**
     * Calcular puntos en tiempo real desde datos existentes (si no hay registros en puntos_empleado)
     */
    private function calcularPuntosEnTiempoReal(int $userId, string $fechaInicio, string $fechaFin): array
    {
        $config = PuntosConfiguracion::activos()->get()->keyBy('concepto');

        $startDate = Carbon::parse($fechaInicio)->startOfDay();
        $endDate = Carbon::parse($fechaFin)->endOfDay();

        // Contar eventos por concepto
        $checkIns = CheckInCheckOut::where('user_id', $userId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $ventas = Venta::where('usuario_id', $userId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $horneados = ControlProduccion::where('empleado_id', $userId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $notificacionesAtendidas = NotificacionPersonal::where('user_id', $userId)
            ->where('atendida', true)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        // Calcular puntos
        $puntos = 0;
        $puntos += ($checkIns * ($config->get('check_in')?->puntos ?? 5));
        $puntos += ($ventas * ($config->get('venta')?->puntos ?? 2));
        $puntos += ($horneados * ($config->get('horneado')?->puntos ?? 3));
        $puntos += ($notificacionesAtendidas * ($config->get('notificacion_atendida')?->puntos ?? 10));

        return [
            'total_puntos' => $puntos,
            'check_ins' => $checkIns,
            'ventas' => $ventas,
            'horneados' => $horneados,
            'notificaciones_atendidas' => $notificacionesAtendidas,
        ];
    }

    /**
     * Obtener datos de evaluación (con fallback a tiempo real si no hay registros)
     */
    private function obtenerDatosEvaluacion(int $userId, string $fechaInicio, string $fechaFin): array
    {
        // Primero intentar desde registros de puntos
        $puntos = PuntosEmpleado::where('user_id', $userId)
            ->entreFechas($fechaInicio, $fechaFin)
            ->get();

        // Si no hay registros, calcular en tiempo real
        if ($puntos->isEmpty()) {
            return $this->calcularPuntosEnTiempoReal($userId, $fechaInicio, $fechaFin);
        }

        // Si hay registros, usar los datos registrados
        return [
            'total_puntos' => $puntos->sum('puntos'),
            'check_ins' => $puntos->where('concepto', 'check_in')->count(),
            'ventas' => $puntos->where('concepto', 'venta')->count(),
            'horneados' => $puntos->where('concepto', 'horneado')->count(),
            'notificaciones_atendidas' => $puntos->where('concepto', 'notificacion_atendida')->count(),
        ];
    }

    /**
     * Obtener ranking con cálculo en tiempo real como fallback
     */
    public function obtenerRankingConTiempoReal(string $fechaInicio, string $fechaFin, ?int $sucursalId = null): array
    {
        // Obtener todos los usuarios activos, excluyendo roles específicos
        $rolesExcluir = ['sucursal', 'almacen', 'gestor.ventas'];

        $usuarios = User::where('active', true)
            ->when($sucursalId, fn($q) => $q->where('sucursal_id', $sucursalId))
            ->with(['sucursal', 'roles'])
            ->get()
            ->filter(function ($user) use ($rolesExcluir) {
                // Excluir usuarios que tengan alguno de los roles especificados
                $userRoles = $user->roles->pluck('name')->toArray();
                foreach ($rolesExcluir as $role) {
                    if (in_array($role, $userRoles)) {
                        return false;
                    }
                }
                return true;
            });

        $ranking = [];

        foreach ($usuarios as $user) {
            $datos = $this->obtenerDatosEvaluacion($user->id, $fechaInicio, $fechaFin);
            $horasTrabajadas = $this->calcularHorasTrabajadas($user->id, $fechaInicio, $fechaFin);

            // Filtrar empleados sin actividades en el período
            $tieneActividad = $datos['total_puntos'] > 0 ||
                             $datos['check_ins'] > 0 ||
                             $datos['ventas'] > 0 ||
                             $datos['horneados'] > 0 ||
                             $horasTrabajadas > 0;

            if (!$tieneActividad) {
                continue;
            }

            $ranking[] = [
                'posicion' => 0, // Se asignará después
                'user_id' => $user->id,
                'nombre' => $user->name,
                'email' => $user->email,
                'sucursal' => $user->sucursal?->nombre ?? '-',
                'sucursal_id' => $user->sucursal_id,
                'total_puntos' => $datos['total_puntos'],
                'total_ventas' => $datos['ventas'],
                'total_horneados' => $datos['horneados'],
                'notificaciones_atendidas' => $datos['notificaciones_atendidas'],
                'total_check_ins' => $datos['check_ins'],
                'horas_trabajadas' => $horasTrabajadas,
            ];
        }

        // Ordenar por puntos y asignar posiciones
        usort($ranking, fn($a, $b) => $b['total_puntos'] <=> $a['total_puntos']);

        foreach ($ranking as &$item) {
            static $posicion = 1;
            $item['posicion'] = $posicion++;
        }
        unset($posicion);

        return $ranking;
    }

    /**
     * Obtener mejores empleados con cálculo en tiempo real
     */
    public function obtenerMejoresPorCategoriaConTiempoReal(string $fechaInicio, string $fechaFin, ?int $sucursalId = null): array
    {
        $ranking = $this->obtenerRankingConTiempoReal($fechaInicio, $fechaFin, $sucursalId);

        if (empty($ranking)) {
            return [
                'mejor_general' => null,
                'mas_ventas' => null,
                'mas_horneados' => null,
            ];
        }

        // Mejor general
        $mejorGeneral = $ranking[0] ?? null;

        // Más ventas
        $masVentas = collect($ranking)->sortByDesc('total_ventas')->first();

        // Más horneados
        $masHorneados = collect($ranking)->sortByDesc('total_horneados')->first();

        return [
            'mejor_general' => $mejorGeneral,
            'mas_ventas' => $masVentas,
            'mas_horneados' => $masHorneados,
        ];
    }

    /**
     * Obtener estadísticas generales con cálculo en tiempo real
     */
    public function obtenerEstadisticasGeneralesConTiempoReal(string $fechaInicio, string $fechaFin, ?int $sucursalId = null): array
    {
        $ranking = $this->obtenerRankingConTiempoReal($fechaInicio, $fechaFin, $sucursalId);

        if (empty($ranking)) {
            return [
                'total_puntos' => 0,
                'total_empleados' => 0,
                'promedio_puntos' => 0,
                'promedio_horas_trabajadas' => 0,
            ];
        }

        $totalPuntos = collect($ranking)->sum('total_puntos');
        $totalEmpleados = count($ranking);
        $promedioPuntos = round($totalPuntos / $totalEmpleados, 2);
        $promedioHoras = round(collect($ranking)->avg('horas_trabajadas'), 2);

        return [
            'total_puntos' => $totalPuntos,
            'total_empleados' => $totalEmpleados,
            'promedio_puntos' => $promedioPuntos,
            'promedio_horas_trabajadas' => $promedioHoras,
        ];
    }
}
