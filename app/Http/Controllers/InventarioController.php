<?php

namespace App\Http\Controllers;

use App\Models\Inventario;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class InventarioController extends Controller
{

    public function inventario()
    {
        $user = Auth::user();
        $sucursalId = $user->sucursal_id;
        $inventario = Inventario::where('sucursal_id', $sucursalId)->get();
        return Inertia::render('Inventario/index', [
            'inventario' => $inventario
        ]);
    }

    // Actualizar producto en el inventario
    public function update(Request $request, Inventario $inventario)
{
    // Validar los datos del formulario
    $request->validate([
        'nombre' => 'required',
        'tipo' => 'required',
        'detalle' => 'nullable|string',
        'cantidad' => 'required|integer',
        'precio' => 'required|numeric',
    ]);

    // Asignar los campos desde el request, pero excluyendo sucursal_id
    $inventario->nombre = $request->nombre;
    $inventario->tipo = $request->tipo;
    $inventario->detalle = $request->detalle;
    $inventario->cantidad = $request->cantidad;
    $inventario->precio = $request->precio;

    $inventario->save();

    // Redireccionar de vuelta a la vista de inventario

    return redirect()->route('inventario')->with('success', 'Ítem eliminado correctamente');

    

}


    public function index()
    {
        $inventario = Inventario::all();
        return Inertia::render('Inventory', [
            'inventario' => $inventario
        ]);
    }

    // Agrega un nuevo ítem al inventario
    public function store(Request $request)
    {
        // Validar los datos del formulario
        $request->validate([
            'nombre' => 'required|string|max:255',
            'detalle' => 'string|nullable',
            'tipo' => 'required|string',
            'cantidad' => 'required|integer|min:0',
            'precio' => 'required|numeric|min:0',
        ]);

        // Crear el nuevo inventario con el sucursal_id del usuario autenticado
        $inventario = new Inventario();
        $inventario->nombre = $request->nombre;
        $inventario->detalle = $request->detalle;
        $inventario->tipo = $request->tipo;
        $inventario->cantidad = $request->cantidad;
        $inventario->precio = $request->precio;
        $inventario->sucursal_id = Auth::user()->sucursal_id; // Asignar sucursal_id del usuario autenticado
        $inventario->save();

        return redirect()->route('inventario')->with('success', 'Ítem eliminado correctamente');

    }

    // Elimina un ítem del inventario
    public function destroy($id)
    {
        $item = Inventario::findOrFail($id);
        $item->delete();
        $user = Auth::user();
        $sucursalId = $user->sucursal_id;

        // Filtra el inventario por sucursal_id
        return redirect()->route('inventario')->with('success', 'Ítem eliminado correctamente');

    }
}
