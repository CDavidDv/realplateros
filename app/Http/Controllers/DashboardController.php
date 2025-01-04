<?php

namespace App\Http\Controllers;

use App\Models\Estimaciones;
use App\Models\Horneados;
use App\Models\Inventario;
use App\Models\User;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        return Inertia::render('Auth/Login', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'laravelVersion' => Application::VERSION,
            'phpVersion' => PHP_VERSION,
        ]);
    }

    public function dashboard()
    {
        // Obtén el usuario autenticado
        $user = Auth::user();

        // Asume que el usuario tiene una sucursal_id
        $sucursalId = $user->sucursal_id;

        // Filtra el inventario por sucursal_id
        $inventario = Inventario::where('sucursal_id', $sucursalId)->get();
        $ticketId = session('ticket_id');
        

        return Inertia::render('Dashboard/index', [
            'inventario' => $inventario,
            'ticket_id' => $ticketId
        ]);
    }


    public function verifyAdminPassword(Request $request)
    {
        $request->validate([
            'admin_password' => 'required|string',
        ]);

        $admins = User::whereHas('roles', function ($query) {
            $query->where('name', 'admin');
        })->get();

        foreach ($admins as $admin) {
            if (Hash::check($request->admin_password, $admin->password)) {
                return response()->json(['correct' => true], 200);
            }
        }

        return response()->json(['correct' => false], 403);
    }


    



    

    public function hornear()
    {
        // Obtén el usuario autenticado
        $user = Auth::user();

        // Asume que el usuario tiene una sucursal_id
        $sucursalId = $user->sucursal_id;

        // Filtra el inventario por sucursal_id
        $inventario = Inventario::where('sucursal_id', $sucursalId)->get();

        $pastesHorneados = Horneados::where('sucursal_id', $sucursalId)
            ->whereDate('created_at', now()->toDateString())
            ->get();

        $estimaciones = Estimaciones::where('sucursal_id', $sucursalId)
            ->with('inventario') // Carga la relación Inventario
            ->get();
        

        return Inertia::render('Hornear/index', [
            'inventario' => $inventario,
            'pastesHorneados' => $pastesHorneados,
            'estimaciones' => $estimaciones
        ]);
    }

    
    public function procesarPastesHorneados(Request $request)
    {
        // Obtén el usuario autenticado
        $user = Auth::user();
        $sucursalId = $user->sucursal_id;

        // Obtener los pastes horneados desde la solicitud
        $pastesHorneados = $request->input('pastes'); // Array de pastes que contiene nombre, cantidad, masa y relleno

        foreach ($pastesHorneados as $paste) {

            Horneados::create([
                'sucursal_id' => $sucursalId,
                'relleno' => $paste['nombre'],
                'piezas' => $paste['cantidad'],
                'created_at' => now()->subHours(6)
            ]);

            // 1. Aumentar la cantidad de pastes en el inventario
            $inventarioPaste = Inventario::where('nombre', $paste['nombre'])
                ->where('tipo', 'pastes')
                ->where('sucursal_id', $sucursalId)
                ->first();

            $inventarioEmpanadaSalada = Inventario::where('nombre', $paste['nombre'])
                ->where('tipo', 'empanadas saladas')
                ->where('sucursal_id', $sucursalId)
                ->first();

            $inventarioEmpanadaDulce = Inventario::where('nombre', $paste['nombre'])
                ->where('tipo', 'empanadas dulces')
                ->where('sucursal_id', $sucursalId)
                ->first();

            if ($inventarioPaste) {
                $inventarioPaste->cantidad += $paste['cantidad']; // Aumenta la cantidad de pastes horneados
                $inventarioPaste->save();
            } else if ($inventarioEmpanadaSalada){
                $inventarioEmpanadaSalada->cantidad += $paste['cantidad']; // Aumenta la cantidad de pastes horneados
                $inventarioEmpanadaSalada->save();
            } else if ($inventarioEmpanadaDulce){
                $inventarioEmpanadaDulce->cantidad += $paste['cantidad']; // Aumenta la cantidad de pastes horneados
                $inventarioEmpanadaDulce->save();
            }else{
                Inventario::create([
                    'sucursal_id' => $sucursalId,
                    'nombre' => $paste['nombre'],
                    'tipo' => 'pastes',
                    'cantidad' => $paste['cantidad'],
                ]);
            }

            // 2. Restar la cantidad de masa utilizada
            $inventarioMasa = Inventario::where('nombre', $paste['masa'])
                ->where('tipo', 'masa')
                ->where('sucursal_id', $sucursalId)
                ->first();

            if ($inventarioMasa) {
                $inventarioMasa->cantidad -= ($paste['cantidad']); // Suponemos que cada paste usa 0.1 kg de masa
                if ($inventarioMasa->cantidad < 0) {
                    $inventarioMasa->cantidad = 0; // Evitar cantidades negativas
                }
                $inventarioMasa->save();
            }

            // 3. Restar la cantidad de relleno utilizado
            $inventarioRelleno = Inventario::where('nombre', $paste['nombre'])
                ->where('tipo', 'relleno')
                ->where('sucursal_id', $sucursalId)
                ->first();

            if ($inventarioRelleno) {
                $inventarioRelleno->cantidad -= ($paste['cantidad']); // Suponemos que cada paste usa 0.2 kg de relleno
                if ($inventarioRelleno->cantidad < 0) {
                    $inventarioRelleno->cantidad = 0; // Evitar cantidades negativas
                }
                $inventarioRelleno->save();
            }
        }

        $inventario = Inventario::where('sucursal_id', $sucursalId)->get();
        $pastesHorneados = Horneados::where('sucursal_id', $sucursalId)
        ->whereDate('created_at', now()->toDateString())
        ->get();

        return Inertia::render('Hornear/index', [
            'inventario' => $inventario,
            'pastesHorneados' => $pastesHorneados
        ]);
        
    }


    
}
