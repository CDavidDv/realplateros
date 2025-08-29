<?php

namespace App\Http\Controllers;

use App\Models\ControlProduccion;
use App\Models\Hornos;
use App\Models\Inventario;
use App\Models\Horneados;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class HornoController extends Controller
{
    public function check_estado(Request $request)
    {
        try {
            $request->validate([
                'horno_id' => 'required|exists:horno,id',
                'pastes' => 'nullable|array'
            ]);

            $hornoId = $request->input('horno_id');
            $pastes = $request->input('pastes');
            $sucursalId = null;

            if ($pastes && is_array($pastes) && !empty($pastes)) {
                foreach ($pastes as $paste) {
                    if (isset($paste['sucursal_id'])) {
                        $sucursalId = $paste['sucursal_id'];
                        break;
                    }
                }
            }

            if (!$sucursalId) {
                $sucursalId = Auth::user()->sucursal_id;
            }

            $horno = Hornos::where('id', $hornoId)
                ->where('sucursal_id', $sucursalId)
                ->first();

            if ($horno) {
                return response()->json([
                    'estado' => $horno->estado,
                    'sucursalId' => $sucursalId
                ]);
            }

            return response()->json([
                'estado' => 0,
                'sucursalId' => $sucursalId
            ]);

        } catch (\Exception $e) {
            Log::error('Error en check_estado: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error al verificar el estado del horno',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function iniciar_horneado(Request $request)
    {
        $request->validate([
            'horno_id' => 'required|exists:horno,id',
            'pastes_horneando' => 'required|array',
            'tiempo_inicio' => 'required|numeric',
            'tiempo_fin' => 'required|numeric',
            'estado' => 'required|boolean'
        ]);

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

        $control_produccion = [];

        foreach ($pastesHorneados as $paste) {
           

            $control = ControlProduccion::where('sucursal_id', $sucursalId)
                ->where('paste_id', $paste['paste_id'])
                ->whereDate('created_at', Carbon::today())
                ->orderBy('created_at', 'desc')
                ->first();

            $fechaHorneado = Carbon::parse(Carbon::now()); // Usar la hora actual
            
            //si ya hay tiempo de inicio horneado no se asigna la hora actual
            if(!isset($control->tiempo_inicio_horneado)){
                $control->tiempo_inicio_horneado = $fechaHorneado;
                $control->save();
            }
            
            if ($control) {
                $control->estado = 'horneando';
                
                // Obtener valores actuales
                $cantidadActual = is_numeric($control->cantidad_horneada) ? (int)$control->cantidad_horneada : 0;
                $cantidadNueva = is_numeric($paste['cantidad']) ? (int)$paste['cantidad'] : 0;
                
                // Realizar la suma
                $control->cantidad_horneada = $cantidadActual + $cantidadNueva;
                
                
                try {
                    $control->save();
                    Log::info('Control guardado exitosamente');
                } catch (\Exception $e) {
                    Log::error('Error al guardar el control:', [
                        'error' => $e->getMessage(),
                        'control_id' => $control->id
                    ]);
                }
                
                $control_produccion[] = $control;
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Horneado iniciado correctamente',
            'request' => $request,
            'pastesHorneados' => $pastesHorneados,
            'control_produccion' => $control_produccion,
            'pasteCantidad' => $paste['cantidad']
        ]);
    }

    public function crear_horno(Request $request)
    {
        $request->validate([
            'estado' => 'required|boolean',
            'pastesHorneando' => 'nullable|array'
        ]);

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
        $request->validate([
            'horno_id' => 'required|exists:horno,id'
        ]);

        $hornoId = $request->input('horno_id');
        $horno = Hornos::where('id', $hornoId)->first();
        $horno->delete();
        return back()->with('success', 'Horno eliminado correctamente');
    }

    public function procesarPastesHorneados(Request $request)
    {
        $request->validate([
            'horno_id' => 'required|exists:horno,id',
            'pastes' => 'required|array'
        ]);

        $user = Auth::user();
        $sucursalId = $user->sucursal_id;
        $pastesHorneados = $request->input('pastes');
        $hornoId = $request->input('horno_id');

        // Usar un bloqueo de base de datos para este horno específico
        return DB::transaction(function () use ($hornoId, $sucursalId, $pastesHorneados, $user) {
            // Obtener el horno con bloqueo
            $horno = Hornos::where('id', $hornoId)
                ->where('sucursal_id', $sucursalId)
                ->lockForUpdate()
                ->first();

            // Verificar si el horno existe y está en estado de horneado
            if (!$horno || !$horno->estado) {
                return redirect()->route('hornear');
            }

            // Verificar si ya existe un registro de horneado para este grupo
            $horneadoExistente = Horneados::where('sucursal_id', $sucursalId)
                ->where('created_at', '>=', now()->subMinutes(5))
                ->where('responsable_id', $user->id)
                ->whereIn('relleno', collect($pastesHorneados)->pluck('nombre'))
                ->exists();

            if ($horneadoExistente) {
                // Si ya existe un registro reciente, solo actualizar el estado del horno
                $horno->estado = 0;
                $horno->save();
                return redirect()->route('hornear');
            }

            foreach ($pastesHorneados as $paste) {
                // Verificar si ya existe un registro de horneado para este paste específico
                $pasteHorneadoExistente = Horneados::where('sucursal_id', $sucursalId)
                    ->where('created_at', '>=', now()->subMinutes(5))
                    ->where('relleno', $paste['nombre'])
                    ->where('piezas', $paste['cantidad'])
                    ->exists();

                if (!$pasteHorneadoExistente) {
                    Horneados::create([
                        'sucursal_id' => $sucursalId,
                        'relleno' => $paste['nombre'],
                        'created_at' => Carbon::now(),
                        'responsable_id' => $user->id,
                        'piezas' => $paste['cantidad']
                    ]);

                    $control = ControlProduccion::where('sucursal_id', $sucursalId)
                        ->where('paste_id', $paste['paste_id'])
                        ->orderBy('created_at', 'desc')
                        ->where('estado', 'horneando')
                        ->first();

                    if ($control) {
                        $control->estado = 'en_espera';
                        $control->save();
                    }

                    // Actualizar inventario con bloqueo
                    $inventarioPaste = Inventario::where('nombre', $paste['nombre'])
                        ->where('tipo', 'pastes')
                        ->where('sucursal_id', $sucursalId)
                        ->lockForUpdate()
                        ->first();

                    $inventarioEmpanadaSalada = Inventario::where('nombre', $paste['nombre'])
                        ->where('tipo', 'empanadas saladas')
                        ->where('sucursal_id', $sucursalId)
                        ->lockForUpdate()
                        ->first();

                    $inventarioEmpanadaDulce = Inventario::where('nombre', $paste['nombre'])
                        ->where('tipo', 'empanadas dulces')
                        ->where('sucursal_id', $sucursalId)
                        ->lockForUpdate()
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

                    // Restar masa y relleno con bloqueo
                    $inventarioMasa = Inventario::where('nombre', $paste['masa'])
                        ->where('tipo', 'masa')
                        ->where('sucursal_id', $sucursalId)
                        ->lockForUpdate()
                        ->first();

                    if ($inventarioMasa) {
                        $inventarioMasa->cantidad -= $paste['cantidad'];
                        if ($inventarioMasa->cantidad < 0) {
                            $inventarioMasa->cantidad = 0;
                        }
                        $inventarioMasa->save();
                    }

                    $inventarioRelleno = Inventario::where('nombre', $paste['nombre'])
                        ->where('tipo', 'relleno')
                        ->where('sucursal_id', $sucursalId)
                        ->lockForUpdate()
                        ->first();

                    if ($inventarioRelleno) {
                        $inventarioRelleno->cantidad -= $paste['cantidad'];
                        if ($inventarioRelleno->cantidad < 0) {
                            $inventarioRelleno->cantidad = 0;
                        }
                        $inventarioRelleno->save();
                    }
                }
            }

            $horno->estado = 0;
            $horno->save();

            return redirect()->route('hornear');
        });
    }
} 