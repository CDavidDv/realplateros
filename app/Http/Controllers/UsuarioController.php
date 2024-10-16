<?php

namespace App\Http\Controllers;

use App\Models\Sucursal;
use App\Models\User;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class UsuarioController extends Controller
{
    // Listar todos los usuarios
    public function index()
    {
        $users = User::role(['admin', 'trabajador'])
        ->with(['roles', 'sucursal'])
        ->get()
        ->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'sucursal_id' => $user->sucursal_id,
                'sucursal' => $user->sucursal->nombre ?? 'Sin sucursal',
                'roles' => $user->roles->pluck('name'), // Optimizamos la manera de retornar los roles
            ];
        });

    // Obtenemos la sesión de usuario para 'sucursal'
    $sucursalSession = User::role('sucursal')
        ->with('roles')
        ->get()
        ->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'sucursal_id' => $user->sucursal_id,
                'email' => $user->email,
                'roles' => $user->roles->pluck('name'),
            ];
        });

    // Listado de sucursales
    $sucursales = Sucursal::all()->map(function ($sucursal) {
        return [
            'id' => $sucursal->id,
            'nombre' => $sucursal->nombre,
            'direccion' => $sucursal->direccion,
            'telefono' => $sucursal->telefono,
        ];
    });

    // Roles disponibles
    $roles = Role::whereIn('name', ['admin', 'trabajador', 'sucursal'])->get(['id', 'name']);

    $allUsers = User::with('roles')->get();
    return Inertia::render('Personal/index', [
        'users' => $users,
        'sucursalSession' => $sucursalSession,
        'sucursales' => $sucursales,
        'roles' => $roles,
    ]);
    }

    // Mostrar formulario para crear un nuevo usuario
    public function create()
    {
        return view('personal.create');
    }

    // Guardar nuevo usuario en la base de datos
    

    // Mostrar un usuario específico
    public function show(User $usuario)
    {
        return view('personal.show', compact('usuario'));
    }

    // Mostrar formulario para editar un usuario
    public function edit(User $usuario)
    {
        return view('personal.edit', compact('usuario'));
    }

    // Actualizar usuario en la base de datos
   

    // Asignar usuario a sucursales (relación muchos a muchos)
    public function asignarSucursales(Request $request, User $usuario)
    {
        $request->validate([
            'sucursal_id' => 'required|exists:sucursales,id',
        ]);

        $usuario->sucursales()->attach($request->sucursal_id);
        return redirect()->route('personal.show', $usuario);
    }

    // Remover usuario de una sucursal
    public function removerSucursal(Request $request, User $usuario)
    {
        $request->validate([
            'sucursal_id' => 'required|exists:sucursales,id',
        ]);

        $usuario->sucursales()->detach($request->sucursal_id);
        return redirect()->route('personal.show', $usuario);
    }


    




    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|string|exists:roles,name', // Asegura que el rol existe
            'sucursal_id' => 'required|exists:sucursales,id',
        ]);

        // Crear el usuario (sin el campo 'role')
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'sucursal_id' => $request->sucursal_id,
        ]);

        // Asignar el rol directamente (verificando el guard)
        $user->assignRole($request->role); 

        return redirect()->route('users.index')->with('success', 'Usuario creado y rol asignado correctamente.');
    }

    public function update(Request $request, Usuario $user) {
        $request->validate([
            'name' => 'required',
            'email' => 'required|unique:users,email,' . $user->id,
            'role' => 'required',
            'sucursal_id' => 'required',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password ? bcrypt($request->password) : $user->password,
            'role' => $request->role,
            'sucursal_id' => $request->sucursal_id,
        ]);

        return redirect()->route('personal');
    }

    public function destroy(Usuario $user) {
        $user->delete();
        return redirect()->route('personal');
    }
}
