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
        
        try {
            $user = Auth::user();
            $sucursalId = $user->sucursal_id;

            $validated = $request->validate([
                'estimaciones' => 'required|array',
                'dia' => 'required|string',
            ]);

            $dia = $validated['dia'];

            $inventarios = Inventario::whereIn('tipo', ['pastes', 'empanadas saladas', 'empanadas dulces'])
                ->where('sucursal_id', $sucursalId)
                ->get()
                ->keyBy('nombre');

            foreach ($validated['estimaciones'] as $hora => $productos) {
                foreach ($productos as $productoNombre => $cantidad) {

                    $inventarioId = $inventarios->get($productoNombre)->id ?? null;
                    
                   
                    
                    if (!$inventarioId) {
                        continue;
                    }

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

                    
                }
            }

            return back()->with('success', 'Datos guardados correctamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al guardar los datos: ' . $e->getMessage());
        }
    }
}
