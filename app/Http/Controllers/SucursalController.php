<?php

namespace App\Http\Controllers;

use App\Models\Inventario;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class SucursalController extends Controller
{
    // Mostrar formulario para crear una nueva sucursal
    public function create()
    {
        return view('sucursales.create');
    }

    // Guardar nueva sucursal en la base de datos
    

    // Mostrar una sucursal específica
    public function show(Sucursal $sucursal)
    {
        return view('sucursales.show', compact('sucursal'));
    }

    // Mostrar formulario para editar una sucursal
    public function edit(Sucursal $sucursal)
    {
        return view('sucursales.edit', compact('sucursal'));
    }

    // Actualizar sucursal en la base de datos
    

    // Eliminar una sucursal
    

    // Asignar usuario a sucursal (relación muchos a muchos)
    public function asignarUsuario(Request $request, Sucursal $sucursal)
    {
        $request->validate([
            'usuario_id' => 'required|exists:usuarios,id',
        ]);

        $sucursal->usuarios()->attach($request->usuario_id);
        return redirect()->route('sucursales.show', $sucursal);
    }

    // Remover usuario de una sucursal
    public function removerUsuario(Request $request, Sucursal $sucursal)
    {
        $request->validate([
            'usuario_id' => 'required|exists:usuarios,id',
        ]);

        $sucursal->usuarios()->detach($request->usuario_id);
        return redirect()->route('sucursales.show', $sucursal);
    }

    

  

    

    public function store(Request $request) {
        $request->validate([
            'nombre' => 'required',
            'direccion' => 'required',
            'telefono' => 'nullable',
            'email' => 'required|unique:users,email',
            'password' => 'required',
        ]);

        $sucursal = Sucursal::create($request->only('nombre', 'direccion', 'telefono'));

        $user = User::create([
            'name' => $request->nombre,
            'email' => $request->email,
            'sucursal_id' => $sucursal->id,
            'password' => Hash::make($request->password),
        ]);

        $user->assignRole('sucursal'); 

        return redirect()->route('users.index');
    }

    public function update(Request $request, Sucursal $sucursal) {
        $request->validate([
            'nombre' => 'required',
            'direccion' => 'required',
            'telefono' => 'nullable',
            'email' => 'required|unique:users,email,' . $request->id_user, // Ignora el email del usuario actual
            'password' => 'nullable', // O la regla que prefieras
        ]);
    
        // Actualiza la sucursal
        $sucursal->update($request->only('nombre', 'direccion', 'telefono'));
    
        // Encuentra al usuario relacionado utilizando el id_user de la solicitud
        $user = User::find($request->id_user);
    
        // Verifica que el usuario exista
        if (!$user) {
            return redirect()->route('personal')->withErrors(['error' => 'Usuario no encontrado']);
        }
    
        // Actualiza la información del usuario
        $user->update([
            'name' => $request->nombre,
            'email' => $request->email,
            'sucursal_id' => $request->id,
            'password' => $request->password ? Hash::make($request->password) : $user->password, // Solo actualiza la contraseña si se proporciona
        ]);
    
        return redirect()->route('personal')->with('success', 'Sucursal y usuario actualizados correctamente');
    }
    
    
    public function destroy(Sucursal $sucursal)
    {
        try {
            $loggedUser = auth()->user();

            if ($loggedUser->sucursal_id == $sucursal->id) {
                return back()->withErrors(['error' => 'No puedes eliminar la sucursal a la que has ingresado.']);
            }

            DB::transaction(function () use ($sucursal) {
                $sucursal->checkInCheckOuts()->delete();
                $sucursal->cocina()->delete();
                $sucursal->cortesDeCaja()->delete();
                $sucursal->ventas()->delete();
                $sucursal->pedidos()->delete();
                $sucursal->inventario()->delete();
                $sucursal->usuarios()->detach();

                User::where('sucursal_id', $sucursal->id)->role('sucursal')->delete();

                $sucursal->delete();
            });

            Log::info('Sucursal eliminada con todos sus registros: ' . $sucursal->id);
            return redirect()->route('personal')->with('success', 'Sucursal y todos sus datos eliminados correctamente.');

        } catch (\Exception $e) {
            Log::error('Error eliminando la sucursal: ' . $e->getMessage());
            return back()->withErrors(['error' => 'No se pudo eliminar la sucursal: ' . $e->getMessage()]);
        }
    }

    
    
}
