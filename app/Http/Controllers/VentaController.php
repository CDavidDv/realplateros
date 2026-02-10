<?php

namespace App\Http\Controllers;

use App\Models\ControlProduccion;
use App\Models\CorteCaja;
use App\Models\Inventario;
use App\Models\Ticket;
use App\Models\TicketAsignacion;
use App\Models\TicketProductosAsignacion;
use App\Models\Venta;
use App\Models\VentaProducto;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use PhpParser\Node\Stmt\Return_;
use App\Models\Estimaciones;
use Illuminate\Support\Facades\DB;
use App\Services\PuntosService;

class VentaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function editarVenta(Request $request)
    {
        try {
            // Obtener los datos de la solicitud
            $ventaId = $request->input('venta_id');
            $productos = $request->input('productos');
    
            // Buscar la venta existente
            $venta = Venta::findOrFail($ventaId);
            $venta->total = 0; // Reiniciar el total para recalcularlo
    
            // Obtener los detalles de la venta actual
            $detallesVenta = VentaProducto::where('venta_id', $ventaId)->get();
            
            // Registrar cada detalle de la venta actualizado
            foreach ($productos as $producto) {
                $item = Inventario::find($producto['id']);
                if ($item) {
                    // Buscar el detalle de la venta existente para este producto
                    $detalleVenta = $detallesVenta->where('producto_id', $producto['id'])->first();
                    
                    if ($detalleVenta) {
                        // Ajustar el inventario: revertir la cantidad anterior y aplicar la nueva
                        // $item->cantidad += $detalleVenta->cantidad; // Revertir la cantidad anterior
                        // $item->cantidad -= $producto['ticketQuantity']; // Aplicar la nueva cantidad
                        // $item->save();
                        
                        // Actualizar el detalle de la venta existente
                        
                        if(!$detalleVenta->cantidadEditado&& $detalleVenta->cantidad != $producto['ticketQuantity']){
                            $detalleVenta->cantidadEditado = $detalleVenta->cantidad;
                        }
                            
                        $detalleVenta->cantidad = $producto['ticketQuantity'];
                        

                        $detalleVenta->precio_unitario = $item->precio;
                        $detalleVenta->save();
                    } else {
                        // Si no existe un detalle para este producto, crear uno nuevo
                        $detalleVenta = new VentaProducto();
                        $detalleVenta->venta_id = $venta->id;
                        $detalleVenta->producto_id = $item->id;
                        $detalleVenta->sucursal_id = auth()->user()->sucursal_id;
                        $detalleVenta->cantidad = $producto['ticketQuantity'];
                        $detalleVenta->precio_unitario = $item->precio;
                        $detalleVenta->save();
    
                        // Ajustar el inventario para el nuevo producto
                        // $item->cantidad -= $producto['ticketQuantity'];
                        // $item->save();
                    }
    
                    // Sumar al total de la venta
                    $venta->total += $item->precio * $producto['ticketQuantity'];
                }
            }
    
            // Eliminar detalles de la venta que ya no están en la lista de productos
            foreach ($detallesVenta as $detalle) {
                if (!collect($productos)->contains('id', $detalle->producto_id)) {
                    // Revertir la cantidad en el inventario
                    // $item = Inventario::find($detalle->producto_id);
                    // if ($item) {
                    //     $item->cantidad += $detalle->cantidad;
                    //     $item->save();
                    // }
    
                    // Eliminar el detalle de la venta
                    $detalle->delete();
                }
            }
    
            // Guardar la venta actualizada
            $venta->save();

            // Renumerar ventas del día actual
            \App\Models\Venta::renumerarVentasDelDia($venta->sucursal_id);
            
            // Renumerar ventas normales del día
            \App\Models\Venta::renumerarVentasNormales($venta->sucursal_id);
    
            // Actualizar el ticket si es necesario
            $ticket = Ticket::where('venta_id', $ventaId)->first();
            if ($ticket) {
                $ticket->total = $venta->total;
                $ticket->save();
            }
    
            // Retornar una respuesta JSON
            return response()->json([
                'mensaje' => 'Venta actualizada correctamente',
                'ticket_id' => $ticket->id,
            ]);
        } catch (\Exception $e) {
            // Retornar un mensaje de error en caso de excepción
            return response()->json([
                'error' => 'Error al actualizar la venta: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function procesarVenta(Request $request)
    {
        try {
            // Recibe los productos seleccionados
            $productos = $request->input('productos');
            $metodoPago = $request->input('metodo_pago');
            $total = $request->input('total');
            $factura = $request->input('factura');

            // Validar existencias antes de procesar la venta
            $errores = [];
            foreach ($productos as $producto) {
                $item = Inventario::find($producto['id']);
                if ($item) {
                    if ($item->cantidad < $producto['ticketQuantity']) {
                        $errores[] = "No hay suficientes existencias de {$item->nombre}. Disponible: {$item->cantidad}, Solicitado: {$producto['ticketQuantity']}";
                    }
                } else {
                    $errores[] = "Producto no encontrado con ID: {$producto['id']}";
                }
            }

            // Si hay errores, retornar sin procesar la venta
            if (!empty($errores)) {
                return redirect()->route('dashboard')->with('error', implode(', ', $errores));
            }

            // Resolver vendedor: usar email proporcionado o el usuario logueado
            $vendedorEmail = $request->input('vendedor_email');
            $vendedorId = auth()->id();
            if ($vendedorEmail) {
                $vendedor = \App\Models\User::where('email', $vendedorEmail)->where('active', true)->first();
                if ($vendedor) {
                    $vendedorId = $vendedor->id;
                }
            }

            // Crear el registro de la venta
            $venta = new Venta();
            $venta->usuario_id = $vendedorId;
            $venta->sucursal_id = auth()->user()->sucursal_id;
            $venta->total = $total;
            $venta->metodo_pago = $metodoPago;
            $venta->factura = $factura == 'true' ? true : false;

            // Generar folio consecutivo para TODAS las ventas
            $venta->folio = \App\Models\Venta::generarFolioConsecutivo(auth()->user()->sucursal_id);

            $idVenta = $venta->idVentaDia;
            $venta->save();

            // Renumerar ventas del día actual 
            \App\Models\Venta::renumerarVentasDelDia(auth()->user()->sucursal_id);
            
            // Renumerar ventas normales del día
            \App\Models\Venta::renumerarVentasNormales(auth()->user()->sucursal_id);

            // Registrar cada detalle de la venta
            foreach ($productos as $producto) {
                $item = Inventario::find($producto['id']);
                if ($item) {
                    $item->cantidad -= $producto['ticketQuantity'];
                    $item->save();

                    $detalleVenta = new VentaProducto();
                    $detalleVenta->venta_id = $venta->id;
                    $detalleVenta->producto_id = $item->id;
                    $detalleVenta->sucursal_id = auth()->user()->sucursal_id;
                    $detalleVenta->cantidad = $producto['ticketQuantity'];
                    $detalleVenta->precio_unitario = $item->precio;
                    $detalleVenta->save();

                    $control = ControlProduccion::where('sucursal_id', auth()->user()->sucursal_id)
                        ->where('paste_id', $item->id)
                        ->whereIn('estado', ['en_espera', 'horneando', 'pendiente'])
                        ->whereDate('created_at', Carbon::today()) // Solo del día actual
                        ->orderBy('created_at', 'desc')
                        ->first();

                    if ($control) {
                        // Verificar que la notificación sea del día actual
                        $fechaNotificacion = Carbon::parse($control->created_at)->toDateString();
                        $fechaActual = Carbon::today()->toDateString();
                        
                        Log::info('Control de producción encontrado para venta:', [
                            'producto_id' => $item->id,
                            'producto_nombre' => $item->nombre,
                            'control_id' => $control->id,
                            'estado' => $control->estado,
                            'fecha_notificacion' => $fechaNotificacion,
                            'fecha_actual' => $fechaActual,
                            'es_mismo_dia' => $fechaNotificacion === $fechaActual,
                            'cantidad_vendida_actual' => $control->cantidad_vendida,
                            'cantidad_nueva' => $producto['ticketQuantity']
                        ]);
                        
                        if ($fechaNotificacion === $fechaActual) {
                            if($control->cantidad_vendida + $producto['ticketQuantity'] >= $control->cantidad_horneada){
                                $control->estado = 'vendido';
                                $control->cantidad_vendida += $producto['ticketQuantity'];
                                $control->hora_ultima_venta = Carbon::now();
                                $control->save();
                                
                                Log::info('Control marcado como vendido:', [
                                    'control_id' => $control->id,
                                    'cantidad_total_vendida' => $control->cantidad_vendida,
                                    'hora_ultima_venta' => $control->hora_ultima_venta
                                ]);
                            }else{
                                $control->cantidad_vendida += $producto['ticketQuantity'];
                                $control->hora_ultima_venta = Carbon::now();
                                $control->save();
                                
                                Log::info('Control actualizado con nueva venta:', [
                                    'control_id' => $control->id,
                                    'cantidad_total_vendida' => $control->cantidad_vendida,
                                    'hora_ultima_venta' => $control->hora_ultima_venta
                                ]);
                            }
                        } else {
                            Log::info('Control ignorado por ser de otro día:', [
                                'control_id' => $control->id,
                                'fecha_notificacion' => $fechaNotificacion,
                                'fecha_actual' => $fechaActual
                            ]);
                        }
                    } else {
                        Log::info('No se encontró control de producción para el producto:', [
                            'producto_id' => $item->id,
                            'producto_nombre' => $item->nombre,
                            'sucursal_id' => auth()->user()->sucursal_id
                        ]);
                    }

                    
                }
            }

            $ticket = Ticket::create([
                'venta_id' => $venta->id,
                'numero_ticket' => time(),
                'total' => $venta->total,
                'creado_por' => auth()->user()->id,
                'sucursal_id' => auth()->user()->sucursal_id,
                'metodo_pago' => $request->metodo_pago,
            ]);

            // Registrar puntos por la venta
            $puntosService = new PuntosService();
            $puntosService->registrar(
                $vendedorId,
                auth()->user()->sucursal_id,
                'venta',
                $venta->id,
                'venta',
                'Venta #' . $venta->folio
            );

            // Evaluar notificaciones después de la venta
            $sucursalId = auth()->user()->sucursal_id;
            $now = Carbon::now();
            $currentDay = strtolower($now->locale('es')->dayName);
            $currentHour = $now->hour;

            // Obtener estimaciones para la hora actual y la siguiente hora
            $estimaciones = Estimaciones::where('sucursal_id', $sucursalId)
                ->where('dia', $currentDay)
                ->where(function($query) use ($currentHour) {
                    $query->where('hora', 'like', $currentHour . ':%')
                          ->orWhere('hora', 'like', ($currentHour + 1) . ':%');
                })
                ->with('inventario')
                ->get();

            // Obtener inventario actual
            $inventario = Inventario::where('sucursal_id', $sucursalId)
                ->whereIn('tipo', ['pastes', 'empanadas dulces', 'empanadas saladas'])
                ->get();

            // Evaluar faltantes y excedentes
            $notificaciones = [];
            foreach ($estimaciones as $estimacion) {
                $producto = $inventario->firstWhere('id', $estimacion->inventario_id);
                if ($producto) {
                    $diferencia = $producto->cantidad - $estimacion->cantidad;
                    
                    if ($diferencia < 0) {
                        $notificaciones[] = [
                            'tipo' => 'warning',
                            'mensaje' => "Faltan " . abs($diferencia) . " unidades de " . $producto->nombre . " para las " . $estimacion->hora
                        ];
                    } elseif ($diferencia > 0) {
                        $notificaciones[] = [
                            'tipo' => 'info',
                            'mensaje' => "Hay " . $diferencia . " unidades extra de " . $producto->nombre . " para las " . $estimacion->hora
                        ];
                    }
                }
            }

            // Asegurarnos de que las notificaciones se envíen en la sesión
            session()->flash('notificaciones', $notificaciones);
        

            // Retornar una respuesta JSON con las notificaciones
            return redirect()->route('dashboard')->with([
                'mensaje' => 'Venta procesada correctamente',
                'ticket_id' => $ticket->id,
                'venta' => $venta,
                'notificaciones' => $notificaciones
            ]);
        } catch (\Exception $e) {
            // Redirigir a la vista con un mensaje de error
            return redirect()->route('dashboard')->with('mensaje', 'Venta procesada incorrectamente');
        }
    }


    public function procesarTicket(Request $request)
    {
        try {
            
            // Recibe los productos seleccionados
            $productos = $request->input('productos');
            $sucursal_id = $request->input('sucursal_id');
            $trabajador_id = $request->input('trabajador_id');

            // Validar existencias antes de procesar la asignación
            $errores = [];
            foreach ($productos as $producto) {
                $item = Inventario::find($producto['id']);
                if ($item) {
                    if ($item->cantidad < $producto['ticketQuantity']) {
                        $errores[] = "No hay suficientes existencias de {$item->nombre}. Disponible: {$item->cantidad}, Solicitado: {$producto['ticketQuantity']}";
                    }
                } else {
                    $errores[] = "Producto no encontrado con ID: {$producto['id']}";
                }
            }

            // Si hay errores, retornar sin procesar la asignación
            if (!empty($errores)) {
                return redirect()->route('dashboard')->with('error', implode(', ', $errores));
            }

            // Crear el registro de la venta
            $ticket = new TicketAsignacion();
            $ticket->empleado_id = $trabajador_id;
            $ticket->sucursal_id = $sucursal_id; 
            $ticket->hora_salida = Carbon::now();
            $ticket->save();

            // Registrar cada detalle de la venta
            foreach ($productos as $producto) {
                $item = Inventario::find($producto['id']);
                if ($item) {
                    $item->cantidad -= $producto['ticketQuantity'];
                    $item->save();

                    $detalleticket = new TicketProductosAsignacion();
                    $detalleticket->ticket_asignacion_id = $ticket->id;
                    $detalleticket->producto_id = $item->id;
                    $detalleticket->cantidad = $producto['ticketQuantity'];
                    $detalleticket->precio_unitario = $item->precio;
                    $detalleticket->save();
                }
            }
            
            // Retornar una respuesta JSON
            return redirect()->route('dashboard')->with([
                'mensaje' => 'Venta procesada correctamente',
                'ticket_id' => $ticket->id,
            ]);
        } catch (\Exception $e) {
            // Redirigir a la vista con un mensaje de error
            return redirect()->route('dashboard')->with('mensaje', 'Venta procesada incorrectamente');
        }
    }

    /**
     * Obtiene las ventas de un día específico para una sucursal
     */
    public function obtenerVentasDelDia(Request $request)
    {
        $request->validate([
            'sucursal_id' => 'required|integer|exists:sucursales,id',
            'fecha' => 'nullable|date'
        ]);

        $sucursalId = $request->input('sucursal_id');
        $fecha = $request->input('fecha', now()->format('Y-m-d'));

        $ventas = Venta::obtenerVentasDelDia($sucursalId, $fecha);
        $totalVentas = Venta::contarVentasDelDia($sucursalId, $fecha);

        return response()->json([
            'ventas' => $ventas,
            'total_ventas' => $totalVentas,
            'fecha' => $fecha,
            'sucursal_id' => $sucursalId
        ]);
    }

    /**
     * Obtiene un resumen de ventas por día para todas las sucursales
     */
    public function obtenerResumenVentasPorDia(Request $request)
    {
        $request->validate([
            'fecha' => 'nullable|date'
        ]);

        $fecha = $request->input('fecha', now()->format('Y-m-d'));

        $resumen = DB::table('ventas')
            ->join('sucursales', 'ventas.sucursal_id', '=', 'sucursales.id')
            ->select(
                'ventas.sucursal_id',
                'sucursales.nombre as sucursal_nombre',
                DB::raw('COUNT(*) as total_ventas'),
                DB::raw('SUM(ventas.total) as total_ventas_monto'),
                DB::raw('MIN(ventas.idVentaDia) as primera_venta_dia'),
                DB::raw('MAX(ventas.idVentaDia) as ultima_venta_dia')
            )
            ->whereDate('ventas.created_at', $fecha)
            ->groupBy('ventas.sucursal_id', 'sucursales.nombre')
            ->orderBy('ventas.sucursal_id')
            ->get();

        return response()->json([
            'resumen' => $resumen,
            'fecha' => $fecha
        ]);
    }

}
