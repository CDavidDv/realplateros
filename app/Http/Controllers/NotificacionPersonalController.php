<?php

namespace App\Http\Controllers;

use App\Models\ControlProduccion;
use App\Models\NotificacionPersonal;
use App\Models\CheckInCheckOut;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class NotificacionPersonalController extends Controller
{
    /**
     * Obtener notificaciones basadas en control_produccion
     * con estado de "atendida" desde notificaciones_personal
     */
    public function index(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio', Carbon::today()->toDateString());
        $fechaFin = $request->input('fecha_fin', Carbon::today()->toDateString());

        // Mapeo de estados a tipos de notificación
        $tipoMap = [
            'pendiente' => 'faltante',
            'desperdicio' => 'excedente',
            'horneando' => 'horneado',
            'en_espera' => 'horneado',
        ];

        // Estados que generan notificaciones
        $estadosNotificables = ['pendiente', 'desperdicio', 'horneando', 'en_espera'];

        $query = ControlProduccion::with(['paste', 'sucursal'])
            ->whereIn('estado', $estadosNotificables)
            ->whereDate('created_at', '>=', $fechaInicio)
            ->whereDate('created_at', '<=', $fechaFin)
            ->orderBy('created_at', 'desc');

        if ($request->filled('sucursal_id')) {
            $query->where('sucursal_id', $request->sucursal_id);
        }

        // Filtrar por tipo (mapeado desde estado)
        if ($request->filled('tipo')) {
            $tipoSolicitado = $request->tipo;
            $estadosFiltro = array_keys(array_filter($tipoMap, fn($t) => $t === $tipoSolicitado));
            $query->whereIn('estado', $estadosFiltro);
        }

        $controlProducciones = $query->get();

        // Obtener IDs de notificaciones atendidas
        $atendidas = NotificacionPersonal::whereIn('control_produccion_id', $controlProducciones->pluck('id'))
            ->where('atendida', true)
            ->get()
            ->keyBy('control_produccion_id');

        // Filtrar por estado atendida si se solicita
        $filtroAtendida = $request->input('atendida');

        $notificaciones = $controlProducciones->map(function ($cp) use ($tipoMap, $atendidas) {
            $atendidaRecord = $atendidas->get($cp->id);
            $estaAtendida = $atendidaRecord !== null;

            // Buscar el trabajador de turno en esa sucursal en esa fecha
            $turno = CheckInCheckOut::where('sucursal_id', $cp->sucursal_id)
                ->whereDate('check_in', $cp->created_at->toDateString())
                ->first();

            return [
                'id' => $cp->id,
                'control_produccion_id' => $cp->id,
                'fecha' => $cp->created_at->format('Y-m-d'),
                'hora' => $cp->created_at->format('H:i'),
                'trabajador' => $turno?->user?->name ?? 'Sin turno registrado',
                'sucursal' => $cp->sucursal?->nombre ?? '-',
                'tipo' => $tipoMap[$cp->estado] ?? $cp->estado,
                'estado_original' => $cp->estado,
                'descripcion' => ($cp->paste?->nombre ?? 'Producto') . ' | Cantidad: ' . $cp->cantidad . ' | Estado: ' . $cp->estado,
                'atendida' => $estaAtendida,
                'atendida_at' => $atendidaRecord?->atendida_at?->format('Y-m-d H:i'),
                'atendida_por' => $atendidaRecord?->atendidaPor?->name,
            ];
        });

        // Filtrar por atendida después del mapeo
        if ($filtroAtendida !== null && $filtroAtendida !== '') {
            $valorAtendida = $filtroAtendida === '1' || $filtroAtendida === true;
            $notificaciones = $notificaciones->filter(fn($n) => $n['atendida'] === $valorAtendida)->values();
        }

        return response()->json(['success' => true, 'notificaciones' => $notificaciones]);
    }

    /**
     * Marcar una notificación como atendida
     * Recibe el ID de control_produccion
     */
    public function atender($id)
    {
        $controlProduccion = ControlProduccion::findOrFail($id);

        // Buscar o crear el registro de notificación personal
        $notificacion = NotificacionPersonal::firstOrCreate(
            ['control_produccion_id' => $controlProduccion->id],
            [
                'sucursal_id' => $controlProduccion->sucursal_id,
                'user_id' => Auth::id(),
                'tipo' => $this->mapearEstadoATipo($controlProduccion->estado),
                'descripcion' => ($controlProduccion->paste?->nombre ?? 'Producto') . ' | Cantidad: ' . $controlProduccion->cantidad,
            ]
        );

        $notificacion->update([
            'atendida' => true,
            'atendida_at' => now(),
            'atendida_por' => Auth::id(),
        ]);

        return response()->json(['success' => true]);
    }

    private function mapearEstadoATipo($estado): string
    {
        return match($estado) {
            'pendiente' => 'faltante',
            'desperdicio' => 'excedente',
            'horneando', 'en_espera' => 'horneado',
            default => 'horneado',
        };
    }
}
