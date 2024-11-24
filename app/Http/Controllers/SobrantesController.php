<?php

namespace App\Http\Controllers;

use App\Models\Sobrantes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        
        if($sobrantesExistentes->count() > 0){
            foreach ($sobrantesExistentes as $sobraExistente) {
                $sobraExistente->cantidad = 0;
                $sobraExistente->save();
            }
        }else{
            foreach ($sobrantes as $sobra) {
                Sobrantes::create([
                    'cantidad' => $sobra['cantidad'],
                    'sucursal_id' => $sucursalId,
                    'inventario_id' => $sobra['inventario_id'],
                ]);
            }
        }

        return redirect()->route('inventario')->with('success', 'Sobrantes agregados correctamente');
    }

}
