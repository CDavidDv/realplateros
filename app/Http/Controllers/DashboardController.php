<?php

namespace App\Http\Controllers;

use App\Models\ControlProduccion;
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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        if (Auth::check()) {
            $user = Auth::user();
            return redirect()->route('dashboard');
        }

        $sucursales = Sucursal::where('id', '!=', 0)->where('id', '!=', 1000)->get();

        return Inertia::render('Auth/Login', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'laravelVersion' => Application::VERSION,
            'phpVersion' => PHP_VERSION,
            'sucursales' => $sucursales
        ]);
    }

    public function dashboard(Request $request)
    {
        $user = Auth::user();
        $sucursalId = $user->sucursal_id;

        if (Auth::user()->hasRole('gestor')) {
            return redirect()->route('gestor-ventas');
        }

        if ($user->esAlmacen()) {
            return redirect()->route('almacen');
        }

        // Obtener fecha seleccionada o usar fecha actual
        $fechaSeleccionada = $request->input('fecha', null);
        $fechaActual = Carbon::now()->setTimezone('America/Mexico_City');
        


        if ($fechaSeleccionada) {
            try {
                $fechaFiltro = Carbon::parse($fechaSeleccionada)->setTimezone('America/Mexico_City');
            } catch (\Exception $e) {
                $fechaFiltro = $fechaActual;
            }
        } else {
            $fechaFiltro = $fechaActual;
        }
        
        $fechaHoy = $fechaFiltro->toDateString();

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

        // Cargar notificaciones de control de producción FILTRADAS POR FECHA
        $fechaActual = Carbon::now()->setTimezone('America/Mexico_City');
        $fechaHoy = $fechaActual->toDateString();
        
        // Notificaciones del día actual
        $notificacionesFaltantes = \App\Models\ControlProduccion::select('*')
            ->with(['paste', 'sucursal'])
            ->where('sucursal_id', $sucursalId)
            ->whereIn('estado', ['pendiente', 'horneando', 'en_espera', 'vendido'])
            ->where('created_at', '>=', $fechaHoy . ' 00:00:00')
            ->where('created_at', '<=', $fechaHoy . ' 23:59:59')
            ->orderBy('created_at', 'desc')
            ->get();

        $notificacionesHorneados = \App\Models\ControlProduccion::select('*')
            ->with(['paste', 'sucursal'])
            ->where('sucursal_id', $sucursalId)
            ->whereIn('estado', ['horneando', 'en_espera', 'vendido'])
            ->whereNotNull('tiempo_inicio_horneado')
            ->where('created_at', '>=', $fechaHoy . ' 00:00:00')
            ->where('created_at', '<=', $fechaHoy . ' 23:59:59')
            ->orderBy('created_at', 'desc')
            ->get();

        // Obtener hornos activos de la sucursal
        $hornosActivos = Hornos::where('sucursal_id', $sucursalId)
            ->where('estado', 1)
            ->get();

        $datosHornos = [];
        $totalPastesEnHornos = 0;

        foreach ($hornosActivos as $horno) {
            $pastesHorno = $horno->pastesHorneando ?? [];
            $totalPastesHorno = 0;

            foreach ($pastesHorno as $paste) {
                $totalPastesHorno += $paste['cantidad'] ?? 0;
            }

            if (count($pastesHorno) > 0) {
                $datosHornos[] = [
                    'horno_id' => $horno->id,
                    'pastes' => $pastesHorno,
                    'total_pastes' => $totalPastesHorno,
                    'tiempo_fin' => $horno->tiempo_fin
                ];
                $totalPastesEnHornos += $totalPastesHorno;
            }
        }

        // Nueva estructura simplificada
        $contadorEstados = [
            'hornos_activos' => $datosHornos,
            'total_en_hornos' => $totalPastesEnHornos,
            'cantidad_hornos' => count($datosHornos)
        ];

        // Obtener datos de la sesión flash (cuando viene de una venta procesada)
        $venta = session('venta');
        $ticketIdFromSession = session('ticket_id');


        return Inertia::render('Dashboard/index', [
            'inventario' => $inventario,
            'ticket_id' => $ticketId,
            'venta' => $venta,
            'categorias' => $categorias,
            'sucursales' => $sucursales,
            'trabajadores' => $trabajadores,
            'hornos' => $hornos,
            'estimacionesHoy' => $estimacionesHoy,
            'notificaciones' => [
                'faltantes' => $notificacionesFaltantes,
                'horneados' => $notificacionesHorneados
            ],
            'contadorEstados' => $contadorEstados,
            'fechaActual' => $fechaHoy,
            'fechaSeleccionada' => $fechaSeleccionada,
            'fechaFiltro' => $fechaHoy
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

    public function hornear(Request $request)
    {
        $user = Auth::user();
        $sucursalId = $user->sucursal_id;
        
        // Obtener fecha seleccionada o usar fecha actual
        $fechaSeleccionada = $request->input('fecha', null);
        $fechaActual = Carbon::now()->setTimezone('America/Mexico_City');
        
        Log::info('Debug hornear - parámetros recibidos:', [
            'fecha_seleccionada' => $fechaSeleccionada,
            'fecha_actual_mexico' => $fechaActual->toDateString(),
            'request_all' => $request->all()
        ]);
        
        if ($fechaSeleccionada) {
            try {
                $fechaFiltro = Carbon::parse($fechaSeleccionada)->setTimezone('America/Mexico_City');
                Log::info('Debug hornear - fecha parseada correctamente:', [
                    'fecha_recibida' => $fechaSeleccionada,
                    'fecha_parseada' => $fechaFiltro->toDateString()
                ]);
            } catch (\Exception $e) {
                Log::error('Error al parsear fecha en hornear:', ['fecha' => $fechaSeleccionada, 'error' => $e->getMessage()]);
                $fechaFiltro = $fechaActual;
            }
        } else {
            $fechaFiltro = $fechaActual;
            Log::info('Debug hornear - usando fecha por defecto:', ['fecha_por_defecto' => $fechaFiltro->toDateString()]);
        }
        
        $fechaHoy = $fechaFiltro->toDateString();
        
        $pastesHorneados = Horneados::where('sucursal_id', $sucursalId)
            ->with('responsable')
            ->whereDate('created_at', $fechaHoy)
            ->get();
        
        $inventario = Inventario::where('sucursal_id', $sucursalId)->get();
        $diaHoy = $fechaFiltro->locale('es')->dayName;

        $estimacionesHoy = Estimaciones::where('sucursal_id', $sucursalId)
            ->where('dia', $diaHoy)
            ->get();

        $estimaciones = Estimaciones::where('sucursal_id', $sucursalId)
            ->with('inventario')
            ->get();

        $hornos = Hornos::where('sucursal_id', $sucursalId)->get();

        // Cargar notificaciones de control de producción FILTRADAS POR FECHA
        $notificacionesFaltantes = \App\Models\ControlProduccion::select('*')
            ->with(['paste', 'sucursal'])
            ->where('sucursal_id', $sucursalId)
            ->whereIn('estado', ['pendiente', 'horneando', 'en_espera', 'vendido'])
            ->whereDate('created_at', $fechaHoy) // Usar whereDate que es más confiable
            ->orderBy('created_at', 'desc')
            ->get();
            
        // Debug: probar filtro más permisivo
        Log::info('Debug filtro permisivo:', [
            'fecha_hoy' => $fechaHoy,
            'registros_con_filtro_permisivo' => \App\Models\ControlProduccion::select('*')
                ->where('sucursal_id', $sucursalId)
                ->whereIn('estado', ['pendiente', 'horneando', 'en_espera', 'vendido'])
                ->whereDate('created_at', $fechaHoy)
                ->count()
        ]);

        $notificacionesHorneados = \App\Models\ControlProduccion::select('*')
            ->with(['paste', 'sucursal'])
            ->where('sucursal_id', $sucursalId)
            ->whereIn('estado', ['horneando', 'en_espera', 'vendido'])
            ->whereNotNull('tiempo_inicio_horneado')
            ->where('created_at', '>=', $fechaHoy . ' 00:00:00')
            ->where('created_at', '<=', $fechaHoy . ' 23:59:59')
            ->orderBy('created_at', 'desc')
            ->get();
            
        // Debug: verificar todos los registros sin filtros
        $todasLasNotificaciones = \App\Models\ControlProduccion::select('*')
            ->where('sucursal_id', $sucursalId)
            ->whereDate('created_at', $fechaHoy)
            ->get();
            
        $notificacionesSinFiltroFecha = \App\Models\ControlProduccion::select('*')
            ->where('sucursal_id', $sucursalId)
            ->whereIn('estado', ['pendiente', 'horneando', 'en_espera', 'vendido'])
            ->whereDate('created_at', $fechaHoy)
            ->get();

        return Inertia::render('Hornear/index', [
            'inventario' => $inventario,
            'pastesHorneados' => $pastesHorneados,
            'estimaciones' => $estimaciones,
            'estimacionesHoy' => $estimacionesHoy,
            'hornos' => $hornos,
            'notificaciones' => [
                'faltantes' => $notificacionesFaltantes,
                'horneados' => $notificacionesHorneados
            ],
            'fechaActual' => $fechaHoy,
            'fechaSeleccionada' => $fechaSeleccionada,
            'fechaFiltro' => $fechaHoy
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
                // Buscar si ya existe un registro para hoy
                $existingHorneado = Horneados::where('sucursal_id', $sucursalId)
                    ->where('relleno', $paste['nombre'])
                    ->whereDate('created_at', Carbon::today())
                    ->first();

                if ($existingHorneado) {
                    // Si existe, actualizar la cantidad
                    $existingHorneado->piezas += $paste['cantidad'];
                    $existingHorneado->save();
                } else {
                    // Si no existe, crear nuevo registro
                    Horneados::create([
                        'sucursal_id' => $sucursalId,
                        'relleno' => $paste['nombre'],
                        'responsable_id' => $user->id,
                        'piezas' => $paste['cantidad']
                    ]);
                }

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
                    $inventarioPaste->cantidad += $paste['cantidad'];
                    $inventarioPaste->save();
                } else if ($inventarioEmpanadaSalada) {
                    $inventarioEmpanadaSalada->cantidad += $paste['cantidad'];
                    $inventarioEmpanadaSalada->save();
                } else if ($inventarioEmpanadaDulce) {
                    $inventarioEmpanadaDulce->cantidad += $paste['cantidad'];
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
                    $inventarioMasa->cantidad -= ($paste['cantidad']);
                    if ($inventarioMasa->cantidad < 0) {
                        $inventarioMasa->cantidad = 0;
                    }
                    $inventarioMasa->save();
                }

                    // 3. Restar la cantidad de relleno utilizado
                    $inventarioRelleno = Inventario::where('nombre', $paste['nombre'])
                        ->where('tipo', 'relleno')
                        ->where('sucursal_id', $sucursalId)
                        ->first();

                if ($inventarioRelleno) {
                    $inventarioRelleno->cantidad -= ($paste['cantidad']);
                    if ($inventarioRelleno->cantidad < 0) {
                        $inventarioRelleno->cantidad = 0;
                    }
                    $inventarioRelleno->save();
                }
            }
        }

        $horno->estado = 0;
        $horno->save();

        return back()->with('success', 'Pastes horneados procesados correctamente');
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

    /**
     * Obtener contador de estados de producción en tiempo real (para polling)
     */
    public function obtenerContadorEstados(Request $request)
    {
        $user = Auth::user();
        $sucursalId = $user->sucursal_id;

        // Obtener hornos activos de la sucursal
        $hornosActivos = Hornos::where('sucursal_id', $sucursalId)
            ->where('estado', 1)
            ->get();

        $datosHornos = [];
        $totalPastesEnHornos = 0;

        foreach ($hornosActivos as $horno) {
            $pastesHorno = $horno->pastesHorneando ?? [];
            $totalPastesHorno = 0;

            foreach ($pastesHorno as $paste) {
                $totalPastesHorno += $paste['cantidad'] ?? 0;
            }

            if (count($pastesHorno) > 0) {
                $datosHornos[] = [
                    'horno_id' => $horno->id,
                    'pastes' => $pastesHorno,
                    'total_pastes' => $totalPastesHorno,
                    'tiempo_fin' => $horno->tiempo_fin ? $horno->tiempo_fin->toIso8601String() : null
                ];
                $totalPastesEnHornos += $totalPastesHorno;
            }
        }

        // Estructura de respuesta simplificada
        $contadorEstados = [
            'hornos_activos' => $datosHornos,
            'total_en_hornos' => $totalPastesEnHornos,
            'cantidad_hornos' => count($datosHornos)
        ];

        return response()->json($contadorEstados);
    }
}
