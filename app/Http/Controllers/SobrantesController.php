<?php

namespace App\Http\Controllers;

use App\Models\Sobrantes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\PuntosService;

class SobrantesController extends Controller
{
    public function store(Request $request)
    {
        // Asegúrate de que los datos estén bajo la clave correcta
        $sobrantes = $request->input('sobrantes', []);
        $user = Auth::user();
        $sucursalId = $user->sucursal_id;

        // Validar los datos
        $validated = $request->validate([
            'sobrantes.*.id' => 'nullable|integer', // Permitir ID opcional para updateOrCreate
            'sobrantes.*.inventario_id' => 'required|integer',
            'sobrantes.*.nombre' => 'required|string',
            'sobrantes.*.cantidad' => 'required|integer',
        ]);
        //buscar los existentes de hoy
        $sobrantesExistentes = Sobrantes::where('sucursal_id', $sucursalId)->where('created_at', '>=', now()->startOfDay())->where('created_at', '<=', now()->endOfDay())->get();
        //si existe actualizar si no crear
        
        $puntosService = new PuntosService();

        if($sobrantesExistentes->count() > 0){
            foreach ($sobrantesExistentes as $sobraExistente) {
                $sobraExistente->cantidad = 0;
                $sobraExistente->trabajador_id = $user->id;
                $sobraExistente->save();
            }
        }else{
            foreach ($sobrantes as $sobra) {
                $sobrante = Sobrantes::create([
                    'cantidad' => $sobra['cantidad'],
                    'sucursal_id' => $sucursalId,
                    'trabajador_id' => $user->id,
                    'inventario_id' => $sobra['inventario_id'],
                ]);

                // Registrar puntos negativos por sobrante (solo si hay cantidad > 0)
                if ($sobra['cantidad'] > 0) {
                    $puntosService->registrar(
                        $user->id,
                        $sucursalId,
                        'sobrante',
                        $sobrante->id,
                        'sobrante',
                        'Sobrante: ' . ($sobra['nombre'] ?? 'Producto') . ' - Cantidad: ' . $sobra['cantidad']
                    );
                }
            }
        }

        return redirect()->route('inventario')->with('success', 'Sobrantes agregados correctamente');
    }

}
