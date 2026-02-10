<?php

namespace App\Http\Controllers;

use App\Models\PuntosConfiguracion;
use App\Models\PuntosEmpleado;
use App\Models\Sucursal;
use App\Models\User;
use App\Services\PuntosService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class EvaluacionEmpleadoController extends Controller
{
    protected PuntosService $puntosService;

    public function __construct(PuntosService $puntosService)
    {
        $this->puntosService = $puntosService;
    }

    /**
     * Vista principal con dashboard de métricas
     */
    public function index(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio', Carbon::today()->startOfMonth()->toDateString());
        $fechaFin = $request->input('fecha_fin', Carbon::today()->toDateString());
        $sucursalId = $request->input('sucursal_id');

        // Obtener ranking
        $ranking = $this->puntosService->obtenerRanking($fechaInicio, $fechaFin, $sucursalId);

        // Obtener mejores por categoría
        $mejores = $this->puntosService->obtenerMejoresPorCategoria($fechaInicio, $fechaFin, $sucursalId);

        // Obtener estadísticas generales
        $estadisticas = $this->puntosService->obtenerEstadisticasGenerales($fechaInicio, $fechaFin, $sucursalId);

        // Obtener configuración de puntos
        $configuracion = PuntosConfiguracion::activos()->get();

        // Obtener sucursales para el filtro
        $sucursales = Sucursal::all();

        return Inertia::render('EvaluacionEmpleados/index', [
            'ranking' => $ranking,
            'mejores' => $mejores,
            'estadisticas' => $estadisticas,
            'configuracion' => $configuracion,
            'sucursales' => $sucursales,
            'filtros' => [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'sucursal_id' => $sucursalId,
            ],
        ]);
    }

    /**
     * Obtener ranking de empleados (API)
     */
    public function ranking(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio', Carbon::today()->startOfMonth()->toDateString());
        $fechaFin = $request->input('fecha_fin', Carbon::today()->toDateString());
        $sucursalId = $request->input('sucursal_id');

        $ranking = $this->puntosService->obtenerRanking($fechaInicio, $fechaFin, $sucursalId);

        return response()->json([
            'success' => true,
            'ranking' => $ranking,
        ]);
    }

    /**
     * Detalle de un empleado específico
     */
    public function detalle(Request $request, int $userId)
    {
        $fechaInicio = $request->input('fecha_inicio', Carbon::today()->startOfMonth()->toDateString());
        $fechaFin = $request->input('fecha_fin', Carbon::today()->toDateString());

        $user = User::with('sucursal')->findOrFail($userId);
        $metricas = $this->puntosService->obtenerMetricas($userId, $fechaInicio, $fechaFin);

        return response()->json([
            'success' => true,
            'empleado' => [
                'id' => $user->id,
                'nombre' => $user->name,
                'email' => $user->email,
                'sucursal' => $user->sucursal?->nombre ?? '-',
            ],
            'metricas' => $metricas,
            'filtros' => [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
            ],
        ]);
    }

    /**
     * Exportar ranking a CSV
     */
    public function exportar(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio', Carbon::today()->startOfMonth()->toDateString());
        $fechaFin = $request->input('fecha_fin', Carbon::today()->toDateString());
        $sucursalId = $request->input('sucursal_id');

        $ranking = $this->puntosService->obtenerRanking($fechaInicio, $fechaFin, $sucursalId);

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

    /**
     * Obtener configuración de puntos
     */
    public function configuracion()
    {
        $configuracion = PuntosConfiguracion::all();

        return response()->json([
            'success' => true,
            'configuracion' => $configuracion,
        ]);
    }

    /**
     * Actualizar configuración de puntos
     */
    public function actualizarConfiguracion(Request $request, int $id)
    {
        $request->validate([
            'puntos' => 'required|integer',
            'descripcion' => 'nullable|string|max:255',
            'activo' => 'required|boolean',
        ]);

        $config = PuntosConfiguracion::findOrFail($id);
        $config->update([
            'puntos' => $request->puntos,
            'descripcion' => $request->descripcion,
            'activo' => $request->activo,
        ]);

        return response()->json([
            'success' => true,
            'configuracion' => $config,
        ]);
    }
}
