<?php

namespace App\Http\Controllers;

use App\Models\Estimaciones;
use App\Models\Inventario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EstimacionesController extends Controller
{
    public function store(Request $request)
    {
        $user = Auth::user();
        $sucursalId = $user->sucursal_id;

        // Validar el request
        $validated = $request->validate([
            'estimaciones' => 'required|array',
            'dia' => 'required|string',
        ]);

        $dia = $validated['dia'];

        // Cargar inventarios una sola vez para reducir consultas
        $inventarios = Inventario::whereIn('tipo', ['pastes', 'empanadas saladas', 'empanadas dulces'])
            ->where('sucursal_id', $sucursalId)
            ->get()
            ->keyBy('nombre');

        foreach ($validated['estimaciones'] as $hora => $productos) {
            foreach ($productos as $productoNombre => $cantidad) {
                $inventarioId = $inventarios->get($productoNombre)->id ?? null;

                if (!$inventarioId) {
                    continue;
                    Estimaciones::updateOrCreate(
                        [
                            'sucursal_id' => $sucursalId,
                            'inventario_id' => $inventarioId,
                            'dia' => $dia,
                            'hora' => $hora,
                        ],
                        [
                            'cantidad' => $cantidad,
                        ]
                    );
                }else{
                    Estimaciones::Create(
                        [
                            'sucursal_id' => $sucursalId,
                            'inventario_id' => $inventarioId,
                            'dia' => $dia,
                            'hora' => $hora,
                            'cantidad' => $cantidad,
                        ]
                    );
                }
            }
        }

        return back()->with('success', 'Datos guardados correctamente.');
    }
}
