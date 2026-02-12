<?php

namespace App\Http\Controllers;

use App\Models\CheckInCheckOut;
use App\Models\ControlProduccion;
use App\Models\CorteCaja;
use App\Models\EntradasInventario;
use App\Models\Gastos;
use App\Models\PuntosConfiguracion;
use App\Models\Sucursal;
use App\Models\User;
use App\Models\Venta;
use App\Services\PuntosService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ActividadPersonalController extends Controller
{
    /**
     * Vista principal de actividad del personal
     */
    public function index()
    {
        $user = Auth::user();

        // Obtener usuarios activos (excluir cuentas de sucursal)
        $usuarios = User::where('active', true)
            ->whereDoesntHave('roles', fn($q) => $q->where('name', 'sucursal'))
            ->with(['roles', 'sucursal'])
            ->orderBy('name')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'apellido_p' => $user->apellido_p,
                    'apellido_m' => $user->apellido_m,
                    'nombre_completo' => trim("{$user->name} {$user->apellido_p} {$user->apellido_m}"),
                    'sucursal_id' => $user->sucursal_id,
                    'sucursal' => $user->sucursal->nombre ?? 'Sin sucursal',
                    'roles' => $user->roles->pluck('name'),
                ];
            });

        // Obtener sucursales
        $sucursales = Sucursal::where('id', '!=', 1000)
            ->orderBy('nombre')
            ->get()
            ->map(function ($sucursal) {
                return [
                    'id' => $sucursal->id,
                    'nombre' => $sucursal->nombre,
                ];
            });

        // Obtener actividades del día actual por defecto
        $fechaHoy = Carbon::now()->setTimezone('America/Mexico_City')->toDateString();
        $actividades = $this->obtenerActividades($fechaHoy, $fechaHoy, null, null, null);

        // Calcular resumen
        $resumen = $this->calcularResumen($actividades);

        // Datos de evaluación (solo para admin)
        $evaluacionData = [];
        if ($user->hasRole('admin')) {
            $puntosService = new PuntosService();
            $fechaInicioMes = Carbon::today()->startOfMonth()->toDateString();

            $ranking = $puntosService->obtenerRankingConTiempoReal($fechaInicioMes, $fechaHoy);
            $evaluacionData = [
                'ranking' => $ranking,
                'mejores' => $puntosService->obtenerMejoresPorCategoriaDesdeRanking($ranking),
                'estadisticas' => $puntosService->obtenerEstadisticasDesdeRanking($ranking),
                'configuracion' => PuntosConfiguracion::activos()->get(),
            ];
        }

        return Inertia::render('ActividadPersonal/index', [
            'usuarios' => $usuarios,
            'sucursales' => $sucursales,
            'actividades' => $actividades,
            'resumen' => $resumen,
            'fechaInicio' => $fechaHoy,
            'fechaFin' => $fechaHoy,
            'evaluacion' => $evaluacionData,
        ]);
    }

    /**
     * Filtrar actividades
     */
    public function filtro(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio', Carbon::today()->toDateString());
        $fechaFin = $request->input('fecha_fin', Carbon::today()->toDateString());
        $sucursalId = $request->input('sucursal_id');
        $usuarioId = $request->input('usuario_id');
        $tipoActividad = $request->input('tipo_actividad');

        $actividades = $this->obtenerActividades($fechaInicio, $fechaFin, $sucursalId, $usuarioId, $tipoActividad);
        $resumen = $this->calcularResumen($actividades);

        return response()->json([
            'actividades' => $actividades,
            'resumen' => $resumen,
        ]);
    }

    /**
     * Obtener actividades de múltiples fuentes
     */
    private function obtenerActividades($fechaInicio, $fechaFin, $sucursalId = null, $usuarioId = null, $tipoActividad = null)
    {
        $actividades = collect();

        $startDate = Carbon::parse($fechaInicio)->startOfDay();
        $endDate = Carbon::parse($fechaFin)->endOfDay();

        // 1. Producción (control_produccion)
        if (!$tipoActividad || $tipoActividad === 'produccion') {
            $produccion = ControlProduccion::with(['paste', 'sucursal'])
                ->whereBetween('created_at', [$startDate, $endDate])
                ->when($sucursalId, fn($q) => $q->where('sucursal_id', $sucursalId))
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => 'produccion_' . $item->id,
                        'fecha_hora' => $item->created_at->format('Y-m-d H:i:s'),
                        'fecha' => $item->created_at->format('Y-m-d'),
                        'hora' => $item->created_at->format('H:i'),
                        'usuario_id' => null,
                        'usuario' => 'Sistema',
                        'sucursal_id' => $item->sucursal_id,
                        'sucursal' => $item->sucursal->nombre ?? 'Sin sucursal',
                        'tipo' => 'produccion',
                        'tipo_label' => 'Producción',
                        'subtipo' => $item->estado,
                        'subtipo_label' => $this->getEstadoProduccionLabel($item->estado),
                        'detalles' => $item->paste->nombre ?? 'Producto desconocido',
                        'cantidad' => $item->cantidad,
                        'extra' => [
                            'paste' => $item->paste->nombre ?? null,
                            'cantidad_horneada' => $item->cantidad_horneada,
                            'cantidad_vendida' => $item->cantidad_vendida,
                        ],
                    ];
                });
            $actividades = $actividades->concat($produccion);
        }

        // 2. Ventas
        if (!$tipoActividad || $tipoActividad === 'venta') {
            $ventas = Venta::with(['usuario', 'sucursal', 'detalles.producto'])
                ->whereBetween('created_at', [$startDate, $endDate])
                ->when($sucursalId, fn($q) => $q->where('sucursal_id', $sucursalId))
                ->when($usuarioId, fn($q) => $q->where('usuario_id', $usuarioId))
                ->where('visible', true)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($item) {
                    // Determinar si es manual o sistema
                    $esManual = in_array($item->estado, ['creada', 'editada']);

                    return [
                        'id' => 'venta_' . $item->id,
                        'fecha_hora' => $item->created_at->format('Y-m-d H:i:s'),
                        'fecha' => $item->created_at->format('Y-m-d'),
                        'hora' => $item->created_at->format('H:i'),
                        'usuario_id' => $item->usuario_id,
                        'usuario' => $item->usuario ? trim("{$item->usuario->name} {$item->usuario->apellido_p}") : 'Sin usuario',
                        'sucursal_id' => $item->sucursal_id,
                        'sucursal' => $item->sucursal->nombre ?? 'Sin sucursal',
                        'tipo' => 'venta',
                        'tipo_label' => 'Venta',
                        'subtipo' => $esManual ? 'manual' : 'sistema',
                        'subtipo_label' => $esManual ? 'Manual' : 'Sistema (POS)',
                        'detalles' => '$' . number_format($item->total, 2) . ' - ' . ucfirst($item->metodo_pago),
                        'cantidad' => 1,
                        'extra' => [
                            'total' => $item->total,
                            'metodo_pago' => $item->metodo_pago,
                            'factura' => $item->factura,
                            'folio' => $item->folio,
                            'estado' => $item->estado,
                            'productos' => $item->detalles->map(fn($d) => [
                                'nombre' => $d->producto->nombre ?? 'Desconocido',
                                'cantidad' => $d->cantidad,
                                'precio_unitario' => $d->precio_unitario,
                            ])->toArray(),
                        ],
                    ];
                });
            $actividades = $actividades->concat($ventas);
        }

        // 3. Asistencia (check_in_check_out)
        if (!$tipoActividad || $tipoActividad === 'asistencia') {
            $asistencias = CheckInCheckOut::with(['user', 'sucursal'])
                ->whereBetween('created_at', [$startDate, $endDate])
                ->when($sucursalId, fn($q) => $q->where('sucursal_id', $sucursalId))
                ->when($usuarioId, fn($q) => $q->where('user_id', $usuarioId))
                ->orderBy('created_at', 'desc')
                ->get();

            // Crear dos registros por asistencia: entrada y salida
            foreach ($asistencias as $item) {
                // Entrada
                if ($item->check_in) {
                    $actividades->push([
                        'id' => 'asistencia_entrada_' . $item->id,
                        'fecha_hora' => Carbon::parse($item->check_in)->format('Y-m-d H:i:s'),
                        'fecha' => Carbon::parse($item->check_in)->format('Y-m-d'),
                        'hora' => Carbon::parse($item->check_in)->format('H:i'),
                        'usuario_id' => $item->user_id,
                        'usuario' => $item->user ? trim("{$item->user->name} {$item->user->apellido_p}") : 'Sin usuario',
                        'sucursal_id' => $item->sucursal_id,
                        'sucursal' => $item->sucursal->nombre ?? 'Sin sucursal',
                        'tipo' => 'asistencia',
                        'tipo_label' => 'Asistencia',
                        'subtipo' => 'entrada',
                        'subtipo_label' => 'Entrada',
                        'detalles' => 'Check-in en ' . ($item->sucursal->nombre ?? 'sucursal'),
                        'cantidad' => 1,
                        'extra' => [
                            'estado' => $item->estado,
                            'horas_trabajadas' => $item->horas_trabajadas,
                        ],
                    ]);
                }

                // Salida
                if ($item->check_out) {
                    $actividades->push([
                        'id' => 'asistencia_salida_' . $item->id,
                        'fecha_hora' => Carbon::parse($item->check_out)->format('Y-m-d H:i:s'),
                        'fecha' => Carbon::parse($item->check_out)->format('Y-m-d'),
                        'hora' => Carbon::parse($item->check_out)->format('H:i'),
                        'usuario_id' => $item->user_id,
                        'usuario' => $item->user ? trim("{$item->user->name} {$item->user->apellido_p}") : 'Sin usuario',
                        'sucursal_id' => $item->sucursal_id,
                        'sucursal' => $item->sucursal->nombre ?? 'Sin sucursal',
                        'tipo' => 'asistencia',
                        'tipo_label' => 'Asistencia',
                        'subtipo' => 'salida',
                        'subtipo_label' => 'Salida',
                        'detalles' => 'Check-out - ' . ($item->horas_trabajadas ?? '0') . ' hrs trabajadas',
                        'cantidad' => 1,
                        'extra' => [
                            'estado' => $item->estado,
                            'horas_trabajadas' => $item->horas_trabajadas,
                        ],
                    ]);
                }
            }
        }

        // 4. Inventario (entradas_inventario)
        if (!$tipoActividad || $tipoActividad === 'inventario') {
            $entradas = EntradasInventario::with(['inventario', 'trabajador', 'sucursal'])
                ->whereBetween('created_at', [$startDate, $endDate])
                ->when($sucursalId, fn($q) => $q->where('sucursal_id', $sucursalId))
                ->when($usuarioId, fn($q) => $q->where('trabajador_id', $usuarioId))
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => 'inventario_' . $item->id,
                        'fecha_hora' => $item->created_at->format('Y-m-d H:i:s'),
                        'fecha' => $item->created_at->format('Y-m-d'),
                        'hora' => $item->created_at->format('H:i'),
                        'usuario_id' => $item->trabajador_id,
                        'usuario' => $item->trabajador ? trim("{$item->trabajador->name} {$item->trabajador->apellido_p}") : 'Sin usuario',
                        'sucursal_id' => $item->sucursal_id,
                        'sucursal' => $item->sucursal->nombre ?? 'Sin sucursal',
                        'tipo' => 'inventario',
                        'tipo_label' => 'Inventario',
                        'subtipo' => 'entrada',
                        'subtipo_label' => 'Entrada',
                        'detalles' => ($item->inventario->nombre ?? 'Producto') . ' x' . $item->cantidad,
                        'cantidad' => $item->cantidad,
                        'extra' => [
                            'producto' => $item->inventario->nombre ?? null,
                        ],
                    ];
                });
            $actividades = $actividades->concat($entradas);
        }

        // 5. Gastos
        if (!$tipoActividad || $tipoActividad === 'gasto') {
            $gastos = Gastos::with(['trabajador', 'sucursal'])
                ->whereBetween('created_at', [$startDate, $endDate])
                ->when($sucursalId, fn($q) => $q->where('sucursal_id', $sucursalId))
                ->when($usuarioId, fn($q) => $q->where('trabajador_id', $usuarioId))
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => 'gasto_' . $item->id,
                        'fecha_hora' => $item->created_at->format('Y-m-d H:i:s'),
                        'fecha' => $item->created_at->format('Y-m-d'),
                        'hora' => $item->created_at->format('H:i'),
                        'usuario_id' => $item->trabajador_id,
                        'usuario' => $item->trabajador ? trim("{$item->trabajador->name} {$item->trabajador->apellido_p}") : 'Sin usuario',
                        'sucursal_id' => $item->sucursal_id,
                        'sucursal' => $item->sucursal->nombre ?? 'Sin sucursal',
                        'tipo' => 'gasto',
                        'tipo_label' => 'Gasto',
                        'subtipo' => 'registro',
                        'subtipo_label' => 'Registro',
                        'detalles' => $item->nombre . ' - $' . number_format($item->costo, 2),
                        'cantidad' => 1,
                        'extra' => [
                            'nombre' => $item->nombre,
                            'costo' => $item->costo,
                        ],
                    ];
                });
            $actividades = $actividades->concat($gastos);
        }

        // 6. Corte de caja
        if (!$tipoActividad || $tipoActividad === 'corte_caja') {
            $cortes = CorteCaja::with(['usuario', 'sucursal'])
                ->whereBetween('created_at', [$startDate, $endDate])
                ->when($sucursalId, fn($q) => $q->where('sucursal_id', $sucursalId))
                ->when($usuarioId, fn($q) => $q->where('usuario_id', $usuarioId))
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => 'corte_' . $item->id,
                        'fecha_hora' => $item->created_at->format('Y-m-d H:i:s'),
                        'fecha' => $item->created_at->format('Y-m-d'),
                        'hora' => $item->created_at->format('H:i'),
                        'usuario_id' => $item->usuario_id,
                        'usuario' => $item->usuario ? trim("{$item->usuario->name} {$item->usuario->apellido_p}") : 'Sin usuario',
                        'sucursal_id' => $item->sucursal_id,
                        'sucursal' => $item->sucursal->nombre ?? 'Sin sucursal',
                        'tipo' => 'corte_caja',
                        'tipo_label' => 'Corte de Caja',
                        'subtipo' => 'registro',
                        'subtipo_label' => 'Registro',
                        'detalles' => 'Total: $' . number_format(($item->dinero_en_efectivo ?? 0) + ($item->dinero_tarjeta ?? 0), 2) . ' (Ef: $' . number_format($item->dinero_en_efectivo ?? 0, 2) . ' / Tj: $' . number_format($item->dinero_tarjeta ?? 0, 2) . ')',
                        'cantidad' => 1,
                        'extra' => [
                            'dinero_inicio' => $item->dinero_inicio,
                            'dinero_final' => $item->dinero_final,
                            'dinero_en_efectivo' => $item->dinero_en_efectivo,
                            'dinero_tarjeta' => $item->dinero_tarjeta,
                        ],
                    ];
                });
            $actividades = $actividades->concat($cortes);
        }

        // Filtrar por usuario si se especifica
        if ($usuarioId) {
            $actividades = $actividades->filter(fn($a) => $a['usuario_id'] == $usuarioId);
        }

        // Ordenar por fecha/hora descendente
        return $actividades->sortByDesc('fecha_hora')->values()->all();
    }

    /**
     * Calcular resumen de actividades
     */
    private function calcularResumen($actividades)
    {
        $actividades = collect($actividades);

        $ventasManuales = $actividades->filter(fn($a) => $a['tipo'] === 'venta' && $a['subtipo'] === 'manual');
        $ventasSistema = $actividades->filter(fn($a) => $a['tipo'] === 'venta' && $a['subtipo'] === 'sistema');

        return [
            'total_actividades' => $actividades->count(),
            'por_tipo' => [
                'produccion' => $actividades->where('tipo', 'produccion')->count(),
                'venta' => $actividades->where('tipo', 'venta')->count(),
                'asistencia' => $actividades->where('tipo', 'asistencia')->count(),
                'inventario' => $actividades->where('tipo', 'inventario')->count(),
                'gasto' => $actividades->where('tipo', 'gasto')->count(),
                'corte_caja' => $actividades->where('tipo', 'corte_caja')->count(),
            ],
            'ventas' => [
                'manuales' => [
                    'cantidad' => $ventasManuales->count(),
                    'total' => $ventasManuales->sum(fn($v) => $v['extra']['total'] ?? 0),
                ],
                'sistema' => [
                    'cantidad' => $ventasSistema->count(),
                    'total' => $ventasSistema->sum(fn($v) => $v['extra']['total'] ?? 0),
                ],
            ],
            'por_sucursal' => $actividades->groupBy('sucursal')->map->count()->toArray(),
        ];
    }

    /**
     * Obtener label del estado de producción
     */
    private function getEstadoProduccionLabel($estado)
    {
        return match($estado) {
            'pendiente' => 'Pendiente',
            'horneando' => 'Horneando',
            'en_espera' => 'En Espera',
            'vendido' => 'Vendido',
            'desperdicio' => 'Desperdicio',
            'retirado' => 'Retirado',
            default => ucfirst($estado),
        };
    }

    /**
     * Filtrar evaluación de empleados
     */
    public function filtroEvaluacion(Request $request)
    {
        $user = Auth::user();

        if (!$user->hasRole('admin')) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $fechaInicio = $request->input('fecha_inicio', Carbon::today()->startOfMonth()->toDateString());
        $fechaFin = $request->input('fecha_fin', Carbon::today()->toDateString());
        $sucursalId = $request->input('sucursal_id');

        $puntosService = new PuntosService();
        $ranking = $puntosService->obtenerRankingConTiempoReal($fechaInicio, $fechaFin, $sucursalId);

        return response()->json([
            'ranking' => $ranking,
            'mejores' => $puntosService->obtenerMejoresPorCategoriaDesdeRanking($ranking),
            'estadisticas' => $puntosService->obtenerEstadisticasDesdeRanking($ranking),
        ]);
    }

    /**
     * Obtener detalle de un empleado
     */
    public function detalleEmpleado(Request $request, int $userId)
    {
        $user = Auth::user();

        if (!$user->hasRole('admin')) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $fechaInicio = $request->input('fecha_inicio', Carbon::today()->startOfMonth()->toDateString());
        $fechaFin = $request->input('fecha_fin', Carbon::today()->toDateString());

        $empleado = User::with('sucursal')->findOrFail($userId);
        $puntosService = new PuntosService();

        // Usar obtenerMetricas con fallback a tiempo real
        // Primero obtener del ranking con tiempo real
        $ranking = $puntosService->obtenerRankingConTiempoReal($fechaInicio, $fechaFin);
        $datosEmpleado = collect($ranking)->firstWhere('user_id', $userId);

        // Obtener métricas detalladas
        $metricas = $puntosService->obtenerMetricas($userId, $fechaInicio, $fechaFin);

        // Si no hay historial de puntos registrados, mostrar aviso
        if (empty($metricas['historial'])) {
            $metricas['historial'] = [];
            $metricas['es_tiempo_real'] = true;
        }

        return response()->json([
            'success' => true,
            'empleado' => [
                'id' => $empleado->id,
                'nombre' => $empleado->name,
                'email' => $empleado->email,
                'sucursal' => $empleado->sucursal?->nombre ?? '-',
            ],
            'metricas' => $metricas,
            'datos_ranking' => $datosEmpleado,
        ]);
    }

    /**
     * Exportar ranking a CSV
     */
    public function exportarEvaluacion(Request $request)
    {
        $user = Auth::user();

        if (!$user->hasRole('admin')) {
            abort(403);
        }

        $fechaInicio = $request->input('fecha_inicio', Carbon::today()->startOfMonth()->toDateString());
        $fechaFin = $request->input('fecha_fin', Carbon::today()->toDateString());
        $sucursalId = $request->input('sucursal_id');

        $puntosService = new PuntosService();
        $ranking = $puntosService->obtenerRankingConTiempoReal($fechaInicio, $fechaFin, $sucursalId);

        $csvContent = "Posicion,Nombre,Email,Sucursal,Puntos Totales,Ventas,Horneados,Notificaciones Atendidas,Check-ins,Horas Trabajadas\n";

        foreach ($ranking as $empleado) {
            $csvContent .= implode(',', [
                $empleado['posicion'],
                '"' . str_replace('"', '""', $empleado['nombre']) . '"',
                $empleado['email'],
                '"' . str_replace('"', '""', $empleado['sucursal']) . '"',
                $empleado['total_puntos'],
                $empleado['total_ventas'],
                $empleado['total_horneados'],
                $empleado['notificaciones_atendidas'],
                $empleado['total_check_ins'],
                $empleado['horas_trabajadas'],
            ]) . "\n";
        }

        $filename = 'evaluacion_empleados_' . $fechaInicio . '_' . $fechaFin . '.csv';

        return response($csvContent, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
