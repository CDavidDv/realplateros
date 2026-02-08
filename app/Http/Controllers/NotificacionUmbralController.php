<?php

namespace App\Http\Controllers;

use App\Models\NotificacionUmbral;
use App\Models\Estimaciones;
use App\Models\Inventario;
use App\Models\ControlProduccion;
use App\Models\NotificacionPersonal;
use App\Models\CheckInCheckOut;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NotificacionUmbralController extends Controller
{   
    public function registrarNotificacion(Request $request)
    {
        Log::info('Datos recibidos:', $request->all());

        $validated = $request->validate([
            'sucursal_id' => 'required|integer',
            'notificacion_id' => 'required|string',
            'cantidad' => 'required|integer'
        ]);

        $pasteId = explode('-', $validated['notificacion_id']);
        Log::info('Paste ID extraído:', ['pasteId' => $pasteId]);
if($pasteId[0] == 1){
    $controlProduccion = ControlProduccion::where('paste_id', $pasteId[0])
    ->where('hora_notificacion', $pasteId[1])
    ->where('estado', 'pendiente')
    ->first();

    if ($controlProduccion) {
        $controlProduccion->cantidad = $validated['cantidad'];
        $controlProduccion->save();
        Log::info('Notificación actualizada:', $controlProduccion->toArray());
    }
}else{
        $controlProduccion = new ControlProduccion();
        $controlProduccion->paste_id = $pasteId[0];
        $controlProduccion->sucursal_id = $validated['sucursal_id'];
        $controlProduccion->cantidad = $validated['cantidad'];
        $controlProduccion->hora_notificacion = $pasteId[1];
        $controlProduccion->dia_notificacion = Carbon::now()->locale('es')->dayName;
        $controlProduccion->estado = 'pendiente';
        $controlProduccion->save();
}
        Log::info('Notificación guardada:', $controlProduccion->toArray());

        $this->crearNotificacionPersonal($controlProduccion);

        return back()->with('notificaciones', $controlProduccion);
    }

    private function crearNotificacionPersonal(ControlProduccion $cp): void
    {
        try {
            $turno = CheckInCheckOut::where('sucursal_id', $cp->sucursal_id)
                ->whereDate('check_in', Carbon::today())
                ->whereNull('check_out')
                ->first();

            if (!$turno) {
                return;
            }

            $tipoMap = [
                'pendiente' => 'faltante',
                'desperdicio' => 'excedente',
                'horneando' => 'horneado',
                'en_espera' => 'horneado',
            ];

            $tipo = $tipoMap[$cp->estado] ?? null;
            if (!$tipo) {
                return;
            }

            $paste = $cp->paste;
            $nombrePaste = $paste?->nombre ?? 'Paste #' . $cp->paste_id;
            $descripcion = "Paste: {$nombrePaste} | Cantidad: {$cp->cantidad} | Estado: {$cp->estado}";

            NotificacionPersonal::create([
                'control_produccion_id' => $cp->id,
                'sucursal_id' => $cp->sucursal_id,
                'user_id' => $turno->user_id,
                'tipo' => $tipo,
                'descripcion' => $descripcion,
            ]);
        } catch (\Exception $e) {
            Log::error('Error al crear notificación personal:', ['error' => $e->getMessage()]);
        }
    }

    public function actualizarNotificaciones(Request $request)
    {
        Log::info('Actualizando notificación:', $request->all());

        $validated = $request->validate([
            'sucursal_id' => 'required|integer',
            'notificacion_id' => 'required|string',
            'cantidad' => 'required|integer'
        ]);

        $pasteId = explode('-', $validated['notificacion_id']);
        
        $controlProduccion = ControlProduccion::where('paste_id', $pasteId[0])
            ->where('hora_notificacion', $pasteId[1])
            ->where('estado', 'pendiente')
            ->first();

        if ($controlProduccion) {
            $controlProduccion->cantidad = $validated['cantidad'];
            $controlProduccion->save();
            Log::info('Notificación actualizada:', $controlProduccion->toArray());
        }

        return back()->with('success', 'Notificación actualizada correctamente');
    }

    public function obtenerNotificacionesFiltradas(Request $request)
    {
        try {
            $sucursalId = Auth::user()->sucursal_id;
            $fecha = $request->get('fecha');
            $hora = $request->get('hora');

            Log::info('Obteniendo notificaciones filtradas:', [
                'sucursal_id' => $sucursalId,
                'fecha' => $fecha,
                'hora' => $hora
            ]);

            $query = ControlProduccion::with(['paste', 'sucursal'])
                ->where('sucursal_id', $sucursalId);

            // Filtrar por fecha si está presente
            if ($fecha) {
                $fechaObj = Carbon::parse($fecha);
                $query->whereDate('created_at', $fechaObj->toDateString());
            }

            // Filtrar por hora si está presente
            if ($hora && $hora !== 'todas' && $hora !== 'actual') {
                // Convertir hora 12h a 24h
                $hora24 = $this->convertTo24Hour($hora);
                if ($hora24 !== null) {
                    $query->whereRaw('HOUR(created_at) = ?', [$hora24]);
                }
            }

            // Obtener notificaciones ordenadas por fecha de creación
            $notificaciones = $query->orderBy('created_at', 'desc')->get();

            Log::info('Notificaciones obtenidas:', [
                'total' => $notificaciones->count(),
                'filtros_aplicados' => [
                    'fecha' => $fecha,
                    'hora' => $hora
                ]
            ]);

            return response()->json([
                'success' => true,
                'notificaciones' => $notificaciones,
                'filtros' => [
                    'fecha' => $fecha,
                    'hora' => $hora
                ],
                'total' => $notificaciones->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener notificaciones filtradas:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Error al obtener notificaciones',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getNotificaciones()
    {
        try {
            $sucursalId = Auth::user()->sucursal_id;
            
            $notificaciones = ControlProduccion::with(['paste', 'sucursal'])
                ->where('sucursal_id', $sucursalId)
                ->whereIn('estado', ['pendiente', 'horneando', 'en_espera', 'vendido'])
                ->orderBy('created_at', 'desc')
                ->get();

            Log::info('Notificaciones obtenidas:', [
                'sucursal_id' => $sucursalId,
                'total' => $notificaciones->count()
            ]);

            return response()->json([
                'success' => true,
                'notificaciones' => $notificaciones,
                'total' => $notificaciones->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener notificaciones:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Error al obtener notificaciones',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Convertir hora de formato 12h a 24h
     */
    private function convertTo24Hour($time12h)
    {
        if (!$time12h || !is_string($time12h)) {
            return null;
        }

        try {
            // Manejar formato '7-am', '2-pm'
            if (strpos($time12h, '-') !== false) {
                $parts = explode('-', strtolower($time12h));
                if (count($parts) === 2) {
                    $hours = (int) $parts[0];
                    $modifier = $parts[1];

                    if ($hours === 12) {
                        $hours = $modifier === 'am' ? 0 : 12;
                    } elseif ($modifier === 'pm') {
                        $hours = $hours + 12;
                    }

                    return $hours;
                }
            }

            // Manejar formato '7:00 AM', '2:00 PM'
            if (strpos($time12h, ' ') !== false) {
                $parts = explode(' ', strtolower($time12h));
                if (count($parts) === 2) {
                    $timeParts = explode(':', $parts[0]);
                    if (count($timeParts) === 2) {
                        $hours = (int) $timeParts[0];
                        $modifier = $parts[1];

                        if ($hours === 12) {
                            $hours = $modifier === 'am' ? 0 : 12;
                        } elseif ($modifier === 'pm') {
                            $hours = $hours + 12;
                        }

                        return $hours;
                    }
                }
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Error al convertir hora:', [
                'hora_original' => $time12h,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
} 