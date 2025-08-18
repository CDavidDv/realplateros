<?php

namespace App\Http\Controllers;

use App\Models\NotificacionUmbral;
use App\Models\Estimaciones;
use App\Models\Inventario;
use App\Models\ControlProduccion;
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

        $controlProduccion = new ControlProduccion();
        $controlProduccion->paste_id = $pasteId[0];
        $controlProduccion->sucursal_id = $validated['sucursal_id'];
        $controlProduccion->cantidad = $validated['cantidad'];
        $controlProduccion->hora_notificacion = $pasteId[1];
        $controlProduccion->dia_notificacion = Carbon::now()->locale('es')->dayName;
        $controlProduccion->estado = 'pendiente';
        $controlProduccion->save();

        Log::info('Notificación guardada:', $controlProduccion->toArray());

        return back()->with('notificaciones', $controlProduccion);
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

    public function getNotificaciones()
    {
        $user = Auth::user();
        $sucursalId = $user->sucursal_id;

        // Obtener notificaciones faltantes (pendientes, horneando, en_espera)
        $faltantes = ControlProduccion::with('paste')
            ->where('sucursal_id', $sucursalId)
            ->whereIn('estado', ['pendiente', 'horneando', 'en_espera'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Obtener notificaciones horneadas (vendidas, desperdicio)
        $horneados = ControlProduccion::with('paste')
            ->where('sucursal_id', $sucursalId)
            ->whereIn('estado', ['vendido', 'desperdicio'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'faltantes' => $faltantes,
            'horneados' => $horneados
        ]);
    }
} 