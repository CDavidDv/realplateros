<?php

namespace App\Http\Controllers;

use App\Models\Estimaciones;
use App\Models\Horneados;
use App\Models\Hornos;
use App\Models\Inventario;
use App\Models\Sucursal;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {

        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        $sucursales = Sucursal::where('id', '!=', 0)->get();

        return Inertia::render('Auth/Login', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'laravelVersion' => Application::VERSION,
            'phpVersion' => PHP_VERSION,
            'sucursales' => $sucursales
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
        
        $categorias = Inventario::select('tipo')->distinct()->get();
        $sucursales = Sucursal::all(); 
        
        $trabajadores = User::whereHas('roles', function ($query) {
            $query->where('name', 'trabajador');
        })->get();

        $hornos = Hornos::where('sucursal_id', $sucursalId);

        return Inertia::render('Dashboard/index', [
            'inventario' => $inventario,
            'ticket_id' => $ticketId,
            'categorias' => $categorias,
            'sucursales' => $sucursales,
            'trabajadores' => $trabajadores,
            'hornos' => $hornos,
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
            ->whereDate('created_at', Carbon::now()->toDateString())
            ->get();

        $diaHoy = Carbon::now()->locale('es')->dayName; // Obtiene el nombre del día actual en español

        $estimacionesHoy = Estimaciones::where('sucursal_id', $sucursalId)
            ->where('dia', $diaHoy)
            ->get();

        $estimaciones = Estimaciones::
            where('sucursal_id', $sucursalId)
            ->with('inventario') // Carga la relación Inventario
            ->get();

        $horno = Hornos::where('sucursal_id', $sucursalId)->first();
    

        return Inertia::render('Hornear/index', [
            'inventario' => $inventario,
            'pastesHorneados' => $pastesHorneados,
            'estimaciones' => $estimaciones,
            'estimacionesHoy' => $estimacionesHoy,
            'horno' => $horno
        ]);
    }

    
    public function procesarPastesHorneados(Request $request)
    {
        // Obtén el usuario autenticado
        $user = Auth::user();
        $sucursalId = $user->sucursal_id;
        // Obtener los pastes horneados desde la solicitud
        $pastesHorneados = $request->input('pastes'); // Array de pastes que contiene nombre, cantidad, masa y relleno

        $horno = Hornos::where('sucursal_id', $sucursalId)->first();
        
        if($horno->estado){

            foreach ($pastesHorneados as $paste) {
                
                // Si no existe, crea un nuevo registro
                $existingHorneado = Horneados::where('sucursal_id', $sucursalId)
                    ->where('relleno', $paste['nombre'])
                    ->where('piezas', $paste['cantidad'])
                    ->whereDate('created_at', '>=', Carbon::now()->subHours(6))
                    ->first();

                
                if (!$existingHorneado) {
                    // Si no existe, crea un nuevo registro
                    Horneados::create([
                        'sucursal_id' => $sucursalId,
                        'relleno' => $paste['nombre'],
                        'piezas' => $paste['cantidad'],
                        'created_at' => Carbon::now()->subHours(6)
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
                }else{
                    dd(Carbon::now()->subHours(6));
                }

            
            }
        }
            
        $horno = Hornos::where('sucursal_id', $sucursalId)->first();
        $horno->estado = 0;
        
        $horno->save();

        return redirect()->route('hornear');
        
    }

    public function check_estado(Request $request)
    {
        $pastes = $request->input('pastes');
        $sucursalId = null;
        if($pastes){
            foreach ($pastes as $paste) {
                $sucursalId = $paste['sucursal_id'];
            }
    
            $horno = Hornos::where('sucursal_id', $sucursalId)->first();
    
            if ($horno) {
                return response()->json(['estado' => $horno->estado, 'sucursalId' => $sucursalId]);
            } else {
                return response()->json(['estado' => 0, 'sucursalId' => $sucursalId]);
            }
        }else{
            return response()->json(['estado' => 0, 'sucursalId' => $sucursalId]);
        }
    }
    
    public function iniciar_horneado(Request $request)
    {
        // Obtén el usuario autenticado
        $user = Auth::user();
        $sucursalId = $user->sucursal_id;
        // Obtener los pastes horneados desde la solicitud
        $pastesHorneados = $request->input('pastes_horneando');
        // El tiempo llega en unix como pasarlo a timestamp
        $tiempo_inicio = date('Y-m-d H:i:s', $request->input('tiempo_inicio') / 1000);
        $tiempo_fin = date('Y-m-d H:i:s', $request->input('tiempo_fin') / 1000);

        
        $estado = $request->input('estado');

        $horno = Hornos::where('sucursal_id', $sucursalId)->first();

        if ($horno) {
            $horno->tiempo_inicio = $tiempo_inicio;
            $horno->tiempo_fin = $tiempo_fin;
            $horno->estado = $estado;
            $horno->pastesHorneando = $pastesHorneados;
            $horno->save();
        } else {
            Hornos::create([
                'sucursal_id' => $sucursalId,
                'tiempo_inicio' => $tiempo_inicio,
                'tiempo_fin' => $tiempo_fin,
                'pastesHorneando' => $pastesHorneados,
                'estado' => $estado,
            ]);
        }
        
        return redirect()->route('hornear')->with('success', 'Horno iniciado correctamente');
        
    }

    
    
}
