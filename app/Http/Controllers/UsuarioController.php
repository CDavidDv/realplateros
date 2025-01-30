<?php

namespace App\Http\Controllers;

use App\Models\Sucursal;
use App\Models\User;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
                'apellido_p' => $user->apellido_p,
                'apellido_m' => $user->apellido_m,
                'tel' => $user->tel,
                'inicio_contrato' => $user->inicio_contrato,
                'fin_contrato' => $user->fin_contrato,
                'active' => $user->active,
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
            'apellido_p' => 'nullable|string|max:255',
            'apellido_m' => 'nullable|string|max:255',
            'tel' => 'nullable|string|max:20',
            'email' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:8',
            'sucursal_id' => 'required|exists:sucursales,id',
            'inicio_contrato' => 'nullable|date',
            'fin_contrato' => 'nullable|date',
            'active' => 'boolean',
            'role' => 'nullable|string|max:255',
        ]);

        $user = User::create([
            'name' => $request->name,
            'apellido_p' => $request->apellido_p,
            'apellido_m' => $request->apellido_m,
            'tel' => $request->tel,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'sucursal_id' => $request->sucursal_id,
            'inicio_contrato' => $request->inicio_contrato,
            'fin_contrato' => $request->fin_contrato,
            'active' => $request->active ?? true,
        ]);

        if($request->role) {
            $user->assignRole($request->role);
        } else {
            $user->assignRole('trabajador');
        }

        return redirect()->route('users.index')->with('success', 'Usuario creado correctamente.');
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'apellido_p' => 'nullable|string|max:255',
            'apellido_m' => 'nullable|string|max:255',
            'tel' => 'nullable|string|max:20',
            'email' => 'required|string|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8',
            'sucursal_id' => 'required|exists:sucursales,id',
            'inicio_contrato' => 'nullable|date',
            'fin_contrato' => 'nullable|date',
            'active' => 'boolean',
            'role' => 'nullable|string|max:255',
        ]);

        $user->update([
            'name' => $request->name,
            'apellido_p' => $request->apellido_p,
            'apellido_m' => $request->apellido_m,
            'tel' => $request->tel,
            'email' => $request->email,
            'sucursal_id' => $request->sucursal_id,
            'inicio_contrato' => $request->inicio_contrato,
            'fin_contrato' => $request->fin_contrato,
            'active' => $request->active ?? true,
        ]);

        if($request->role) {
            $user->syncRoles($request->role);
        } else {
            $user->syncRoles('trabajador');
        }

        if ($request->password) {
            $user->update(['password' => Hash::make($request->password)]);
        }



        return redirect()->route('users.index')->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(Usuario $user) {
        $user->delete();
        return redirect()->route('personal');
    }
}
