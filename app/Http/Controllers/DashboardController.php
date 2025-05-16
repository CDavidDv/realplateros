<?php

namespace App\Http\Controllers;

use App\Models\Estimaciones;
use App\Models\Horneados;
use App\Models\Hornos;
use App\Models\Inventario;
use App\Models\Sucursal;
use App\Models\User;
use App\Models\Venta;
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
        $user = Auth::user();
        $sucursalId = $user->sucursal_id;

        $inventario = Inventario::where('sucursal_id', $sucursalId)->get();
        $numeroTiketsPorSucursal = Venta::where('sucursal_id', $sucursalId)->count();
        $ticketId = $numeroTiketsPorSucursal + 1;

        $categorias = Inventario::select('tipo')->distinct()->get();
        $sucursales = Sucursal::all();

        $trabajadores = User::whereHas('roles', function ($query) {
            $query->where('name', 'trabajador');
        })->get();

        $hornos = Hornos::where('sucursal_id', $sucursalId)->get();

        $estimacionesHoy = Estimaciones::with('inventario')
            ->where('sucursal_id', $sucursalId)
            ->where('dia', Carbon::now()->locale('es')->dayName)
            ->get();    

        return Inertia::render('Dashboard/index', [
            'inventario' => $inventario,
            'ticket_id' => $ticketId,
            'categorias' => $categorias,
            'sucursales' => $sucursales,
            'trabajadores' => $trabajadores,
            'hornos' => $hornos,
            'estimacionesHoy' => $estimacionesHoy
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
        $user = Auth::user();
        $sucursalId = $user->sucursal_id;

        
        $pastesHorneados = Horneados::where('sucursal_id', $sucursalId)
            ->with('responsable')
            ->whereDate('created_at', Carbon::now()->toDateString())
            ->get();
        
        $inventario = Inventario::where('sucursal_id', $sucursalId)->get();
        $diaHoy = Carbon::now()->locale('es')->dayName;

        $estimacionesHoy = Estimaciones::where('sucursal_id', $sucursalId)
            ->where('dia', $diaHoy)
            ->get();

        $estimaciones = Estimaciones::where('sucursal_id', $sucursalId)
            ->with('inventario')
            ->get();

        $hornos = Hornos::where('sucursal_id', $sucursalId)->get();

        return Inertia::render('Hornear/index', [
            'inventario' => $inventario,
            'pastesHorneados' => $pastesHorneados,
            'estimaciones' => $estimaciones,
            'estimacionesHoy' => $estimacionesHoy,
            'hornos' => $hornos 
        ]);
    }

    public function procesarPastesHorneados(Request $request)
    {
        $user = Auth::user();
        $sucursalId = $user->sucursal_id;
        $pastesHorneados = $request->input('pastes');
        $hornoId = $request->input('horno_id');

        $horno = Hornos::where('id', $hornoId)
            ->where('sucursal_id', $sucursalId)
            ->first();

        if ($horno && $horno->estado) {
            foreach ($pastesHorneados as $paste) {
                $existe = Horneados::firstOrCreate([
                    'sucursal_id' => $sucursalId,
                    'relleno' => $paste['nombre'],
                    'created_at' => Carbon::now(),
                ], [
                    'responsable_id' => $user->id,
                    'piezas' => $paste['cantidad']
                ]);

                if ($existe) {


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
                    } else if ($inventarioEmpanadaSalada) {
                        $inventarioEmpanadaSalada->cantidad += $paste['cantidad']; // Aumenta la cantidad de pastes horneados
                        $inventarioEmpanadaSalada->save();
                    } else if ($inventarioEmpanadaDulce) {
                        $inventarioEmpanadaDulce->cantidad += $paste['cantidad']; // Aumenta la cantidad de pastes horneados
                        $inventarioEmpanadaDulce->save();
                    } else {
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
            }
        }

        $horno->estado = 0;
        $horno->save();

        return redirect()->route('hornear');
    }

    public function check_estado(Request $request)
    {
        $pastes = $request->input('pastes');
        $hornoId = $request->input('horno_id');
        $sucursalId = null;

        if ($pastes) {
            foreach ($pastes as $paste) {
                $sucursalId = $paste['sucursal_id'];
            }

            $horno = Hornos::where('id', $hornoId)
                ->where('sucursal_id', $sucursalId)
                ->first();

            if ($horno) {
                return response()->json(['estado' => $horno->estado, 'sucursalId' => $sucursalId]);
            }
        }

        return response()->json(['estado' => 0, 'sucursalId' => $sucursalId]);
    }

    public function iniciar_horneado(Request $request)
    {
        $user = Auth::user();
        $sucursalId = $user->sucursal_id;
        $pastesHorneados = $request->input('pastes_horneando');
        $hornoId = $request->input('horno_id');
        $tiempo_inicio = date('Y-m-d H:i:s', $request->input('tiempo_inicio') / 1000);
        $tiempo_fin = date('Y-m-d H:i:s', $request->input('tiempo_fin') / 1000);
        $estado = $request->input('estado');

        $horno = Hornos::where('id', $hornoId)
            ->where('sucursal_id', $sucursalId)
            ->first();

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

    public function crear_horno(Request $request)
    {
        $user = Auth::user();
        $sucursalId = $user->sucursal_id;

        $horno = Hornos::create([
            'sucursal_id' => $sucursalId,
            'estado' => $request->input('estado'),
            'pastesHorneando' => $request->input('pastesHorneando'),
        ]);

        return back()->with('success', 'Horno creado correctamente');
    }

    public function eliminar_horno(Request $request)
    {
        $hornoId = $request->input('horno_id');
        $horno = Hornos::where('id', $hornoId)->first();
        $horno->delete();
        return back()->with('success', 'Horno eliminado correctamente');
    }
}
