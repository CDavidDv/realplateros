<?php

namespace App\Http\Controllers;

use App\Models\NotificacionPersonal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificacionPersonalController extends Controller
{
    public function index(Request $request)
    {
        $query = NotificacionPersonal::with(['user', 'sucursal', 'atendidaPor'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('sucursal_id')) {
            $query->where('sucursal_id', $request->sucursal_id);
        }

        if ($request->filled('fecha_inicio')) {
            $query->whereDate('created_at', '>=', $request->fecha_inicio);
        }

        if ($request->filled('fecha_fin')) {
            $query->whereDate('created_at', '<=', $request->fecha_fin);
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->filled('atendida')) {
            $query->where('atendida', $request->atendida === '1' || $request->atendida === true);
        }

        $notificaciones = $query->get()->map(function ($n) {
            return [
                'id' => $n->id,
                'fecha' => $n->created_at->format('Y-m-d'),
                'hora' => $n->created_at->format('H:i'),
                'trabajador' => $n->user?->name ?? 'Sin asignar',
                'sucursal' => $n->sucursal?->nombre ?? '-',
                'tipo' => $n->tipo,
                'descripcion' => $n->descripcion,
                'atendida' => $n->atendida,
                'atendida_at' => $n->atendida_at?->format('Y-m-d H:i'),
                'atendida_por' => $n->atendidaPor?->name,
            ];
        });

        return response()->json(['success' => true, 'notificaciones' => $notificaciones]);
    }

    public function atender(NotificacionPersonal $notificacion)
    {
        $notificacion->update([
            'atendida' => true,
            'atendida_at' => now(),
            'atendida_por' => Auth::id(),
        ]);

        return response()->json(['success' => true]);
    }
}
