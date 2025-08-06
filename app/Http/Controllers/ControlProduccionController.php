<?php

namespace App\Http\Controllers;

use App\Models\ControlProduccion;
use App\Models\Hornos;
use App\Models\Inventario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ControlProduccionController extends Controller
{
    public function index()
    {
        $sucursalId = Auth::user()->sucursal_id;
        
        // Obtener producción actual
        $produccion = ControlProduccion::with('paste')
            ->where('sucursal_id', $sucursalId)
            ->whereIn('estado', ['horneando', 'retirado'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Calcular tiempos de reposición
        $tiemposReposicion = $this->calcularTiemposReposicion($sucursalId);

        // Generar recomendaciones
        $recomendaciones = $this->generarRecomendaciones($sucursalId);

        // Calcular tiempos de producción
        $tiemposProduccion = $this->calcularTiemposProduccion($sucursalId);

        return response()->json([
            'produccion' => $produccion,
            'tiemposReposicion' => $tiemposReposicion,
            'recomendaciones' => $recomendaciones,
            'tiemposProduccion' => $tiemposProduccion
        ]);
    }

    private function calcularTiemposReposicion($sucursalId)
    {
        $tiempos = [];
        $pastes = Inventario::where('sucursal_id', $sucursalId)
            ->whereIn('tipo', ['pastes', 'empanadas saladas', 'empanadas dulces'])
            ->get();

        foreach ($pastes as $paste) {
            $ultimasProducciones = ControlProduccion::where('paste_id', $paste->id)
                ->where('sucursal_id', $sucursalId)
                ->whereNotNull('tiempo_retiro_horno')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            if ($ultimasProducciones->count() > 0) {
                $tiempoPromedio = $ultimasProducciones->avg(function ($produccion) {
                    return Carbon::parse($produccion->tiempo_fin_horneado)
                        ->diffInMinutes(Carbon::parse($produccion->tiempo_retiro_horno));
                });

                $tiempos[] = [
                    'paste' => $paste->nombre,
                    'promedio' => round($tiempoPromedio, 1),
                    'ultima' => $ultimasProducciones->first()->tiempo_retiro_horno
                ];
            }
        }

        return $tiempos;
    }

    private function calcularTiemposProduccion($sucursalId)
    {
        $tiempos = [];
        $produccion = ControlProduccion::with('paste')
            ->where('sucursal_id', $sucursalId)
            ->whereNotNull('tiempo_retiro_horno')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        foreach ($produccion as $item) {
            $tiempoHorneado = Carbon::parse($item->tiempo_inicio_horneado)
                ->diffInMinutes(Carbon::parse($item->tiempo_fin_horneado));

            $tiempoVenta = null;
            if ($item->tiempo_ultima_venta) {
                $tiempoVenta = Carbon::parse($item->tiempo_retiro_horno)
                    ->diffInMinutes(Carbon::parse($item->tiempo_ultima_venta));
            }

            $tiempos[] = [
                'id' => $item->id,
                'paste' => $item->paste->nombre,
                'hora_produccion' => $item->tiempo_inicio_horneado,
                'tiempo_horneado' => $tiempoHorneado,
                'tiempo_venta' => $tiempoVenta ?? '-',
                'tiempo_total' => $tiempoVenta ? $tiempoHorneado + $tiempoVenta : $tiempoHorneado,
                'estado' => $item->estado
            ];
        }

        return $tiempos;
    }

    private function generarRecomendaciones($sucursalId)
    {
        $recomendaciones = [];
        $pastes = Inventario::where('sucursal_id', $sucursalId)
            ->whereIn('tipo', ['pastes', 'empanadas saladas', 'empanadas dulces'])
            ->get();

        foreach ($pastes as $paste) {
            $produccionActual = ControlProduccion::where('paste_id', $paste->id)
                ->where('sucursal_id', $sucursalId)
                ->whereIn('estado', ['retirado'])
                ->orderBy('created_at', 'desc')
                ->first();

            if ($produccionActual) {
                $porcentajeVendido = ($produccionActual->cantidad_vendida / $produccionActual->cantidad) * 100;
                $tiempoSinVenta = Carbon::parse($produccionActual->tiempo_retiro_horno)
                    ->diffInMinutes(now());

                $recomendacion = [
                    'paste' => $paste->nombre,
                    'recomendacion' => $porcentajeVendido < 70 ? 'Producir' : 'No Producir',
                    'razon' => $porcentajeVendido < 70 
                        ? "Se ha vendido el {$porcentajeVendido}% del lote actual"
                        : "Se ha vendido el {$porcentajeVendido}% del lote actual"
                ];

                if ($tiempoSinVenta > 120) { // 2 horas sin ventas
                    $recomendacion['recomendacion'] = 'No Producir';
                    $recomendacion['razon'] = "No hay ventas en las últimas 2 horas";
                }

                $recomendaciones[] = $recomendacion;
            }
        }

        return $recomendaciones;
    }

    public function registrarHorneado(Request $request)
    {
        $request->validate([
            'horno_id' => 'required|exists:horno,id',
            'paste_id' => 'required|exists:inventarios,id',
            'cantidad' => 'required|integer|min:1'
        ]);

        $sucursalId = Auth::user()->sucursal_id;

        $produccion = ControlProduccion::where('sucursal_id', $sucursalId)
            ->where('paste_id', $request->paste_id)
            ->orderBy('created_at', 'desc')
            ->first();

        $produccion->estado = 'en_espera';
        

        return response()->json([
            'message' => 'Horneado registrado correctamente',
            'produccion' => $produccion
        ]);
    }

    public function registrarRetiro(Request $request)
    {
        try {
            $request->validate([
                'horno_id' => 'required|exists:horno,id',
                'paste_id' => 'required|exists:inventarios,id',
                'cantidad' => 'required|integer|min:1'
            ]);

            // Crear nuevo registro de producción
            $produccion = ControlProduccion::create([
                'sucursal_id' => Auth::user()->sucursal_id,
                'paste_id' => $request->paste_id,
                'cantidad' => $request->cantidad,
                'estado' => 'retirado',
                'tiempo_retiro_horno' => now()
            ]);

            // Actualizar el estado del horno
            $horno = Hornos::findOrFail($request->horno_id);
            $horno->estado = 0;
            $horno->save();

            return response()->json([
                'message' => 'Retiro registrado correctamente',
                'estado' => 'retirado',
                'produccion' => $produccion
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Error en registrarRetiro - Registro no encontrado: ' . $e->getMessage());
            return response()->json([
                'error' => 'No se encontró el registro de producción o el horno',
                'message' => $e->getMessage()
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Error en registrarRetiro - Validación fallida: ' . $e->getMessage());
            return response()->json([
                'error' => 'Datos de entrada inválidos',
                'message' => $e->getMessage(),
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error en registrarRetiro: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error al registrar el retiro',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function registrarVenta(Request $request)
    {
        $request->validate([
            'control_produccion_id' => 'required|exists:control_produccion,id',
            'cantidad_vendida' => 'required|integer|min:1'
        ]);

        $controlProduccion = ControlProduccion::findOrFail($request->control_produccion_id);
        
        // Actualizar cantidad vendida
        $controlProduccion->cantidad_vendida += $request->cantidad_vendida;
        $controlProduccion->hora_ultima_venta = now();
        
        // Verificar si se vendió todo
        if ($controlProduccion->cantidad_vendida >= $controlProduccion->cantidad) {
            $controlProduccion->estado = 'vendido';
        } else if ($controlProduccion->estado === 'horneando') {
            $controlProduccion->estado = 'en_espera';
        }
        
        $controlProduccion->save();

        return response()->json([
            'success' => true,
            'message' => 'Venta registrada correctamente',
            'control_produccion' => $controlProduccion
        ]);
    }

    public function iniciarHorneado(Request $request)
    {
        $request->validate([
            'control_produccion_id' => 'required|exists:control_produccion,id',
            'tiempo_inicio_horneado' => 'required|date',
            'cantidad_horneada' => 'required|integer|min:1'
        ]);

        $controlProduccion = ControlProduccion::findOrFail($request->control_produccion_id);
        
        // Actualizar estado y tiempos
        $controlProduccion->estado = 'horneando';
        $controlProduccion->tiempo_inicio_horneado = $request->tiempo_inicio_horneado;
        $controlProduccion->cantidad_horneada = $request->cantidad_horneada;
        $controlProduccion->diferencia_notificacion_inicio = now();
        
        $controlProduccion->save();

        return response()->json([
            'success' => true,
            'message' => 'Horneado iniciado correctamente',
            'control_produccion' => $controlProduccion
        ]);
    }

    public function finalizarHorneado(Request $request)
    {
        $request->validate([
            'control_produccion_id' => 'required|exists:control_produccion,id',
            'tiempo_fin_horneado' => 'required|date'
        ]);

        $controlProduccion = ControlProduccion::findOrFail($request->control_produccion_id);
        
        // Actualizar estado y tiempos
        $controlProduccion->estado = 'en_espera';
        $controlProduccion->tiempo_fin_horneado = $request->tiempo_fin_horneado;
        
        $controlProduccion->save();

        return response()->json([
            'success' => true,
            'message' => 'Horneado finalizado correctamente',
            'control_produccion' => $controlProduccion
        ]);
    }
} 