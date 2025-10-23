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



use Spatie\Permission\Models\Permission;

class UsuarioController extends Controller
{
    // Listar todos los usuarios
    public function index()
    {
        // Si está logeado como almacén solo mostrar los usuarios de almacén
        $user = auth()->user();
        $user->assignRole('almacen');

        
        if($user->esAlmacen()) {
            // Usuario de almacén: solo ver trabajadores de almacén
            $users = User::role(['admin', 'trabajador', 'supervisor', 'almacen'])
                ->where('es_almacen', true)
                ->with(['roles', 'sucursal'])
                ->orderBy('active', 'desc')
                ->orderBy('name', 'asc')
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
                        'es_almacen' => $user->es_almacen,
                        'roles' => $user->roles->pluck('name'),
                    ];
                });
        } else {
            // Usuario admin/sucursal: ver todos los usuarios
            $users = User::role(['admin', 'trabajador', 'supervisor', 'almacen'])
                ->with(['roles', 'sucursal'])
                ->where('es_almacen', false)
                ->orderBy('es_almacen', 'desc')
                ->orderBy('active', 'desc')
                ->orderBy('name', 'asc')
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
                        'es_almacen' => $user->es_almacen,
                        'roles' => $user->roles->pluck('name'),
                    ];
                });
        }

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

    // Listado de sucursales (incluye almacén con id = 0)
    $sucursales = Sucursal::where('id', '!=', 1000)
        ->orderByRaw('CASE WHEN id = 0 THEN 0 ELSE 1 END') // Almacén primero
        ->orderBy('nombre')
        ->get()
        ->map(function ($sucursal) {
            return [
                'id' => $sucursal->id,
                'nombre' => $sucursal->nombre,
                'direccion' => $sucursal->direccion,
                'telefono' => $sucursal->telefono,
                'es_almacen' => $sucursal->id == 0,
            ];
        });

    // Roles disponibles
    $roles = Role::whereIn('name', ['admin', 'trabajador', 'sucursal', 'supervisor', 'almacen'])
        ->get(['id', 'name']);

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

        // Determinar si es usuario de almacén basado en sucursal_id
        $esAlmacen = $request->sucursal_id == 0;

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
            'es_almacen' => $esAlmacen,
        ]);

        // Asignar rol
        if($request->role) {
            $user->assignRole($request->role);
        } else {
            // Si es almacén y no se especifica rol, asignar 'almacen'
            $user->assignRole($esAlmacen ? 'almacen' : 'trabajador');
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

        // Determinar si es usuario de almacén basado en sucursal_id
        $esAlmacen = $request->sucursal_id == 0;

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
            'es_almacen' => $esAlmacen,
        ]);

        // Actualizar rol
        if($request->role) {
            $user->syncRoles($request->role);
        } else {
            // Si es almacén y no se especifica rol, asignar 'almacen'
            $user->syncRoles($esAlmacen ? 'almacen' : 'trabajador');
        }

        // Actualizar contraseña si se proporciona
        if ($request->password) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        return redirect()->route('users.index')->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(Usuario $user) {
        $user->delete();
        return redirect()->route('personal');
    }

    /**
     * Obtener solo trabajadores de almacén
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function trabajadoresAlmacen()
    {
        $trabajadores = User::almacen()
            ->where('active', true)
            ->with(['roles', 'sucursal'])
            ->orderBy('name')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'apellido_p' => $user->apellido_p,
                    'apellido_m' => $user->apellido_m,
                    'nombre_completo' => trim("{$user->name} {$user->apellido_p} {$user->apellido_m}"),
                    'tel' => $user->tel,
                    'email' => $user->email,
                    'sucursal' => $user->sucursal->nombre ?? 'Almacén',
                    'roles' => $user->roles->pluck('name'),
                    'active' => $user->active,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $trabajadores,
            'total' => $trabajadores->count()
        ]);
    }

    /**
     * Obtener estadísticas de trabajadores
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function estadisticasPersonal()
    {
        $stats = [
            'total_usuarios' => User::count(),
            'total_almacen' => User::almacen()->count(),
            'total_sucursales' => User::sucursales()->count(),
            'activos_almacen' => User::almacen()->where('active', true)->count(),
            'inactivos_almacen' => User::almacen()->where('active', false)->count(),
            'activos_sucursales' => User::sucursales()->where('active', true)->count(),
            'inactivos_sucursales' => User::sucursales()->where('active', false)->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}
