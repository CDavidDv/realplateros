<?php

namespace App\Http\Controllers;

use App\Models\Gastos;
use App\Models\Inventario;
use App\Models\Registros;
use App\Models\Sucursal;
use App\Models\VentaProducto;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class InventarioController extends Controller
{

    public function inventario()
    {
        $user = Auth::user();
        $sucursalId = $user->sucursal_id;
        $inventario = Inventario::where('sucursal_id', $sucursalId)->get();

        $ventas = VentaProducto::select('producto_id', DB::raw('SUM(cantidad) as total_vendido'))
            ->where('sucursal_id', $sucursalId)
            ->with('inventario:id,nombre')
            ->whereDate('created_at', Carbon::today())
            ->groupBy('producto_id')
            ->get();

        $registros = Registros::where('sucursal_id', $sucursalId)
            ->whereDate('created_at', Carbon::today())
            ->get();

        $gastos = Gastos::where('sucursal_id', $sucursalId)
            ->whereDate('created_at', Carbon::today())
            ->get();

        return Inertia::render('Inventario/index', [
            'inventario' => $inventario,
            'ventas' => $ventas,
            'registros' => $registros,
            'gastos' => $gastos
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

        // Buscar el registro relacionado
        $registro = Registros::where('inventario_id', $inventario->id)->first();

        if ($registro) {
            // Actualizar el campo `existe` si se encuentra el registro
            $registro->existe = $inventario->cantidad;
            $registro->entra = 0;
            $registro->total = 0;
            $registro->vende = 0;
            $registro->save();
        }

        // Actualizar el inventario
        $inventario->nombre = $request->nombre;
        $inventario->tipo = $request->tipo;
        $inventario->detalle = $request->detalle;
        $inventario->cantidad = $request->cantidad;
        $inventario->precio = $request->precio;

        $inventario->save();

        // Redireccionar de vuelta a la vista de inventario
        return redirect()->route('inventario')->with('success', 'Inventario actualizado correctamente');
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


    public function gastos(Request $request)
    {
        try {
            // Validar la entrada
            $validated = $request->validate([
                'gastos' => 'array',
                'gastos.*.id' => 'nullable|integer|exists:gastos,id', // Asegúrate de que se puede identificar registros existentes.
                'gastos.*.nombre' => 'required|string|max:255',
                'gastos.*.costo' => 'required|numeric|min:0|max:999999.99',
            ]);

            $user = Auth::user();
            $sucursalId = $user->sucursal_id;
            $today = Carbon::today();

            // Obtener los nuevos gastos enviados
            $gastosNuevos = $validated['gastos'] ?? [];

            // Obtener los gastos existentes para la sucursal y fecha actual
            $gastosExistentes = Gastos::where('sucursal_id', $sucursalId)
                ->whereDate('created_at', $today)
                ->get();

            // Si no se envía nada, eliminar todos los gastos del día
            if (empty($gastosNuevos)) {
                Gastos::where('sucursal_id', $sucursalId)
                    ->whereDate('created_at', $today)
                    ->delete();

                return redirect()->route('inventario')->with('success', 'Todos los gastos eliminados.');
            }

            // Determinar los IDs de los nuevos gastos
            $idNuevos = collect($gastosNuevos)->pluck('id')->filter()->toArray();

            // Filtrar los gastos a eliminar
            $gastosAEliminar = $gastosExistentes->filter(fn($gasto) => !in_array($gasto->id, $idNuevos));
            Gastos::destroy($gastosAEliminar->pluck('id'));

            // Insertar o actualizar los nuevos gastos
            $gastosAInsertar = collect($gastosNuevos)->map(fn($gasto) => [
                'id' => $gasto['id'] ?? null, // Esto permite que `upsert` lo trate como nuevo si no tiene ID.
                'nombre' => $gasto['nombre'],
                'sucursal_id' => $sucursalId,
                'created_at' => $today,
                'updated_at' => now(),
                'costo' => $gasto['costo'],
            ])->toArray();

            // Actualizar o insertar
            Gastos::upsert($gastosAInsertar, ['id'], ['nombre', 'costo', 'updated_at']);

            return redirect()->route('inventario')->with('success', 'Registros actualizados o creados correctamente.');
        } catch (\Exception $e) {
            Log::error('Error procesando los gastos', [
                'context' => 'Actualización de gastos',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
            ]);
        
            // Redirigir con mensajes de error
            return redirect()->back()->withErrors([
                'message' => 'Error al procesar los gastos.',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor',
            ]);
        }
        
    }




    public function registro(Request $request)
    { 
        $user = Auth::user();
        $sucursalId = $user->sucursal_id;
   

        // Validar la estructura del request
        $request->validate([
            'registros.productos' => 'required|array',
            'registros.productos.*.id' => 'required|integer|exists:inventarios,id',
            'registros.productos.*.existe' => 'nullable|integer|min:0',
            'registros.productos.*.entra' => 'nullable|integer|min:0',
            'registros.productos.*.total' => 'nullable|integer|min:0',
            'registros.productos.*.vende' => 'nullable|integer|min:0',
            'registros.productos.*.sobra' => 'nullable|integer|min:0',
            'registros.productos.*.precio' => 'nullable|numeric|min:0',
        ]);
        

        $registros = $request->registros['productos'];

        
        // Obtener registros existentes para esta sucursal y fecha
        $registrosExistentes = Registros::where('sucursal_id', $sucursalId)
            ->whereDate('created_at', Carbon::today())
            ->get();

        // Si no existen registros previos, se crean nuevos
        if ($registrosExistentes->isEmpty()) {
            foreach ($registros as $registro) {
                
                Registros::create([
                    'inventario_id' => $registro['id'], // ID del inventario
                    'sucursal_id' => $sucursalId,
                    'existe' => $registro['existe'] ?? 0,
                    'entra' => $registro['entra'] ?? 0,
                    'total' => $registro['total'] ?? 0,
                    'vende' => $registro['vende'] ?? 0,
                    'sobra' => $registro['sobra'] ?? 0,
                    'precio' => $registro['precio'] ?? 0,
                ]);
                $inventario = Inventario::find($registro['id']);
                
                if ($inventario) {
                    $inventario->cantidad = $registro['sobra'];
                    $inventario->update();
                }
            }
        } else {
            // Actualizar registros existentes
            foreach ($registros as $registro) {
                $registroExistente = $registrosExistentes->firstWhere('inventario_id', $registro['id']);
                if ($registroExistente) {
                    $registroExistente->update([
                        'existe' => $registro['existe'] ?? $registroExistente->existe,
                        'entra' => $registro['entra'] ?? $registroExistente->entra,
                        'total' => $registro['total'] ?? $registroExistente->total,
                        'vende' => $registro['vende'] ?? $registroExistente->vende,
                        'sobra' => $registro['sobra'] ?? $registroExistente->sobra,
                        'precio' => $registro['precio'] ?? $registroExistente->precio,
                    ]);
                }
                $inventario = Inventario::find($registro['id']);
                
                if ($inventario) {
                    $inventario->cantidad = $registro['sobra'];
                    $inventario->update();
                }
            }
        }

        // Redirigir con mensaje de éxito
        return redirect()->route('inventario')->with('success', 'Registros actualizados o creados correctamente.');
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
