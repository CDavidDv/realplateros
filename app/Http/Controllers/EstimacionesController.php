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

        foreach ($validated['estimaciones'] as $hora => $productos) {
            foreach ($productos as $productoNombre => $cantidad) {
                // Buscar el inventario_id basado en el nombre del producto
                
                $inventario = Inventario::where('nombre', $productoNombre)
                    ->whereIn('tipo', ['pastes', 'empanadas saladas', 'empanadas dulces'])
                    ->first();

                if ($inventario) {
                    Estimaciones::updateOrCreate(
                        [
                            'sucursal_id' => $sucursalId,
                            'inventario_id' => $inventario->id,
                            'dia' => $dia,
                            'hora' => $hora,
                        ],
                        [
                            'cantidad' => $cantidad,
                        ]
                    );
                }
            }
        }

        return back()->with('success', 'Datos guardados correctamente.');
    }
}
