<?php

namespace App\Services;

use App\Models\PuntosConfiguracion;
use App\Models\PuntosEmpleado;
use App\Models\ControlProduccion;
use App\Models\Horneados;
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
     * Registrar puntos por una accion especifica
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
     * Obtener metricas detalladas de un empleado
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

        // horas_trabajadas esta en minutos, convertir a horas
        return round($minutosTrabajados / 60, 2);
    }

    /**
     * Calcular horas trabajadas en batch para multiples usuarios (1 query)
     */
    private function calcularHorasTrabajadasBatch(array $userIds, string $fechaInicio, string $fechaFin): array
    {
        if (empty($userIds)) {
            return [];
        }

        $results = CheckInCheckOut::whereIn('user_id', $userIds)
            ->whereDate('created_at', '>=', $fechaInicio)
            ->whereDate('created_at', '<=', $fechaFin)
            ->whereNotNull('horas_trabajadas')
            ->selectRaw('user_id, SUM(horas_trabajadas) as total_minutos')
            ->groupBy('user_id')
            ->pluck('total_minutos', 'user_id');

        $horas = [];
        foreach ($userIds as $userId) {
            $minutos = $results->get($userId, 0);
            $horas[$userId] = round($minutos / 60, 2);
        }

        return $horas;
    }

    /**
     * Calcular puntos en tiempo real en batch para multiples usuarios (4 queries)
     */
    private function calcularPuntosEnTiempoRealBatch(array $userIds, string $fechaInicio, string $fechaFin): array
    {
        if (empty($userIds)) {
            return [];
        }

        $config = PuntosConfiguracion::activos()->get()->keyBy('concepto');

        $startDate = Carbon::parse($fechaInicio)->startOfDay();
        $endDate = Carbon::parse($fechaFin)->endOfDay();

        $checkInsCounts = CheckInCheckOut::whereIn('user_id', $userIds)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('user_id, COUNT(*) as total')
            ->groupBy('user_id')
            ->pluck('total', 'user_id');

        $ventasCounts = Venta::whereIn('usuario_id', $userIds)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('usuario_id, COUNT(*) as total')
            ->groupBy('usuario_id')
            ->pluck('total', 'usuario_id');

        $horneadosCounts = ControlProduccion::whereIn('empleado_id', $userIds)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('empleado_id, COUNT(*) as total')
            ->groupBy('empleado_id')
            ->pluck('total', 'empleado_id');

        // También contar desde tabla horneados por responsable_id como respaldo
        $horneadosDirectos = Horneados::whereIn('responsable_id', $userIds)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('responsable_id, COUNT(*) as total')
            ->groupBy('responsable_id')
            ->pluck('total', 'responsable_id');

        $notifCounts = NotificacionPersonal::whereIn('user_id', $userIds)
            ->where('atendida', true)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('user_id, COUNT(*) as total')
            ->groupBy('user_id')
            ->pluck('total', 'user_id');

        $results = [];
        foreach ($userIds as $userId) {
            $checkIns = (int) ($checkInsCounts->get($userId, 0));
            $ventas = (int) ($ventasCounts->get($userId, 0));
            // Usar el mayor entre control_produccion y horneados directos
            $horneadosCP = (int) ($horneadosCounts->get($userId, 0));
            $horneadosDir = (int) ($horneadosDirectos->get($userId, 0));
            $horneados = max($horneadosCP, $horneadosDir);
            $notificaciones = (int) ($notifCounts->get($userId, 0));

            $puntos = 0;
            $puntos += ($checkIns * ($config->get('check_in')?->puntos ?? 5));
            $puntos += ($ventas * ($config->get('venta')?->puntos ?? 2));
            $puntos += ($horneados * ($config->get('horneado')?->puntos ?? 3));
            $puntos += ($notificaciones * ($config->get('notificacion_atendida')?->puntos ?? 10));

            $results[$userId] = [
                'total_puntos' => $puntos,
                'check_ins' => $checkIns,
                'ventas' => $ventas,
                'horneados' => $horneados,
                'notificaciones_atendidas' => $notificaciones,
            ];
        }

        return $results;
    }

    /**
     * Obtener datos de evaluacion en batch (1 query PuntosEmpleado + batch fallback)
     */
    private function obtenerDatosEvaluacionBatch(array $userIds, string $fechaInicio, string $fechaFin): array
    {
        if (empty($userIds)) {
            return [];
        }

        // 1 query: obtener todos los registros de puntos para todos los usuarios
        $allPuntos = PuntosEmpleado::whereIn('user_id', $userIds)
            ->entreFechas($fechaInicio, $fechaFin)
            ->get()
            ->groupBy('user_id');

        $results = [];
        $usersWithoutPuntos = [];

        foreach ($userIds as $userId) {
            $puntos = $allPuntos->get($userId);

            if ($puntos && $puntos->isNotEmpty()) {
                $results[$userId] = [
                    'total_puntos' => $puntos->sum('puntos'),
                    'check_ins' => $puntos->where('concepto', 'check_in')->count(),
                    'ventas' => $puntos->where('concepto', 'venta')->count(),
                    'horneados' => $puntos->where('concepto', 'horneado')->count(),
                    'notificaciones_atendidas' => $puntos->where('concepto', 'notificacion_atendida')->count(),
                ];
            } else {
                $usersWithoutPuntos[] = $userId;
            }
        }

        // Batch fallback para usuarios sin registros de puntos
        if (!empty($usersWithoutPuntos)) {
            $batchResults = $this->calcularPuntosEnTiempoRealBatch($usersWithoutPuntos, $fechaInicio, $fechaFin);
            foreach ($batchResults as $userId => $datos) {
                $results[$userId] = $datos;
            }
        }

        return $results;
    }

    /**
     * Procesar notificaciones no atendidas al fin del dia
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

        if ($controles->isEmpty()) {
            return 0;
        }

        // Pre-cargar IDs de controles que ya fueron atendidos (1 query en batch)
        $controlesAtendidos = NotificacionPersonal::whereIn('control_produccion_id', $controles->pluck('id'))
            ->where('atendida', true)
            ->pluck('control_produccion_id')
            ->toArray();

        // Pre-cargar empleados por sucursal (1 query)
        $rolesExcluir = ['sucursal', 'almacen', 'gestor.ventas'];
        $sucursalIds = $controles->pluck('sucursal_id')->unique()->toArray();

        $empleadosPorSucursal = User::whereIn('sucursal_id', $sucursalIds)
            ->where('active', true)
            ->with('roles')
            ->get()
            ->filter(function ($user) use ($rolesExcluir) {
                return !$user->roles->pluck('name')->intersect($rolesExcluir)->isNotEmpty();
            })
            ->groupBy('sucursal_id');

        foreach ($controles as $control) {
            // Saltar si ya fue atendida
            if (in_array($control->id, $controlesAtendidos)) {
                continue;
            }

            // Obtener empleados de la sucursal del control
            $empleados = $empleadosPorSucursal->get($control->sucursal_id, collect());

            foreach ($empleados as $empleado) {
                $this->registrar(
                    $empleado->id,
                    $control->sucursal_id,
                    'notificacion_no_atendida',
                    $control->id,
                    'control_produccion',
                    'Notificacion no atendida: ' . ($control->paste?->nombre ?? 'Producto')
                );
                $procesados++;
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
     * Obtener mejor empleado por categoria
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

        // Mejor general (mas puntos)
        $mejorGeneral = $ranking[0] ?? null;

        // Mas ventas
        $masVentas = collect($ranking)->sortByDesc('total_ventas')->first();

        // Mas horneados
        $masHorneados = collect($ranking)->sortByDesc('total_horneados')->first();

        return [
            'mejor_general' => $mejorGeneral,
            'mas_ventas' => $masVentas,
            'mas_horneados' => $masHorneados,
        ];
    }

    /**
     * Obtener estadisticas generales
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

        $horneadosCP = ControlProduccion::where('empleado_id', $userId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        // También contar desde tabla horneados por responsable_id como respaldo
        $horneadosDir = Horneados::where('responsable_id', $userId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $horneados = max($horneadosCP, $horneadosDir);

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
     * Obtener datos de evaluacion (con fallback a tiempo real si no hay registros)
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
     * Obtener ranking con calculo en tiempo real como fallback (optimizado con batch)
     */
    public function obtenerRankingConTiempoReal(string $fechaInicio, string $fechaFin, ?int $sucursalId = null): array
    {
        // Obtener todos los usuarios activos, excluyendo roles especificos
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

        $userIds = $usuarios->pluck('id')->toArray();

        // Batch: obtener datos de evaluacion y horas para todos los usuarios
        $allDatos = $this->obtenerDatosEvaluacionBatch($userIds, $fechaInicio, $fechaFin);
        $allHoras = $this->calcularHorasTrabajadasBatch($userIds, $fechaInicio, $fechaFin);

        $ranking = [];

        foreach ($usuarios as $user) {
            $datos = $allDatos[$user->id] ?? [
                'total_puntos' => 0,
                'check_ins' => 0,
                'ventas' => 0,
                'horneados' => 0,
                'notificaciones_atendidas' => 0,
            ];
            $horasTrabajadas = $allHoras[$user->id] ?? 0;

            // Filtrar empleados sin actividades en el periodo
            $tieneActividad = $datos['total_puntos'] > 0 ||
                             $datos['check_ins'] > 0 ||
                             $datos['ventas'] > 0 ||
                             $datos['horneados'] > 0 ||
                             $horasTrabajadas > 0;

            if (!$tieneActividad) {
                continue;
            }

            $ranking[] = [
                'posicion' => 0,
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

        $posicion = 1;
        foreach ($ranking as &$item) {
            $item['posicion'] = $posicion++;
        }

        return $ranking;
    }

    /**
     * Obtener mejores empleados desde ranking pre-calculado (0 queries)
     */
    public function obtenerMejoresPorCategoriaDesdeRanking(array $ranking): array
    {
        if (empty($ranking)) {
            return [
                'mejor_general' => null,
                'mas_ventas' => null,
                'mas_horneados' => null,
            ];
        }

        return [
            'mejor_general' => $ranking[0] ?? null,
            'mas_ventas' => collect($ranking)->sortByDesc('total_ventas')->first(),
            'mas_horneados' => collect($ranking)->sortByDesc('total_horneados')->first(),
        ];
    }

    /**
     * Obtener estadisticas desde ranking pre-calculado (0 queries)
     */
    public function obtenerEstadisticasDesdeRanking(array $ranking): array
    {
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

    /**
     * Obtener mejores empleados con calculo en tiempo real (wrapper para backward compatibility)
     */
    public function obtenerMejoresPorCategoriaConTiempoReal(string $fechaInicio, string $fechaFin, ?int $sucursalId = null): array
    {
        $ranking = $this->obtenerRankingConTiempoReal($fechaInicio, $fechaFin, $sucursalId);
        return $this->obtenerMejoresPorCategoriaDesdeRanking($ranking);
    }

    /**
     * Obtener estadisticas generales con calculo en tiempo real (wrapper para backward compatibility)
     */
    public function obtenerEstadisticasGeneralesConTiempoReal(string $fechaInicio, string $fechaFin, ?int $sucursalId = null): array
    {
        $ranking = $this->obtenerRankingConTiempoReal($fechaInicio, $fechaFin, $sucursalId);
        return $this->obtenerEstadisticasDesdeRanking($ranking);
    }
}
