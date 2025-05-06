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

class HornoController extends Controller
{
    public function check_estado(Request $request)
    {
        try {
            $request->validate([
                'horno_id' => 'required|integer',
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

        //crear registro de control de horneado
        ControlProduccion::create([
            'sucursal_id' => $sucursalId,
            'pastesHorneando' => $pastesHorneados,
            'estado' => $estado,
            'responsable_id' => $user->id,
            'created_at' => Carbon::now(),
        ]);

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
                Horneados::create([
                    'sucursal_id' => $sucursalId,
                    'relleno' => $paste['nombre'],
                    'created_at' => Carbon::now(),
                    'responsable_id' => $user->id,
                    'piezas' => $paste['cantidad']
                ]);

                // Actualizar inventario
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

                // Restar masa y relleno
                $inventarioMasa = Inventario::where('nombre', $paste['masa'])
                    ->where('tipo', 'masa')
                    ->where('sucursal_id', $sucursalId)
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
    }
} 