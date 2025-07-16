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

            // Crear el registro de la venta
            $venta = new Venta();
            $venta->usuario_id = auth()->id();
            $venta->sucursal_id = auth()->user()->sucursal_id;
            $venta->total = $total;
            $venta->metodo_pago = $metodoPago;
            $venta->save();

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
                        ->whereIn('estado', ['en_espera'])
                        ->orderBy('created_at', 'desc')
                        ->first();

                    if ($control) {
                        if($control->cantidad_vendida + $producto['ticketQuantity'] >= $control->cantidad_horneada){
                            $control->estado = 'vendido';
                            $control->cantidad_vendida += $producto['ticketQuantity'];
                            $control->hora_ultima_venta = Carbon::now();
                            $control->save();
                        }else{
                            $control->cantidad_vendida += $producto['ticketQuantity'];
                            $control->hora_ultima_venta = Carbon::now();
                            $control->save();
                        }
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



}
