<?php

namespace App\Http\Controllers;

use App\Models\CorteCaja;
use App\Models\EntradasInventario;
use App\Models\Estimaciones;
use App\Models\Gastos;
use App\Models\Hornos;
use App\Models\Inventario;
use App\Models\Sobrantes;
use App\Models\Sucursal;
use App\Models\User;
use App\Models\Venta;
use App\Models\VentaProducto;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class GestorVentasController extends Controller
{
    public function index()
    {
        // Obtener usuario autenticado
        $user = Auth::user();
        $sucursalId = $user->sucursal_id;

        // Obtener todas las ventas de la sucursal ordenadas cronológicamente
        $ventas = Venta::where('sucursal_id', $sucursalId)
            ->with(['detalles.producto', 'usuario'])
            ->whereDate('created_at', Carbon::today())
            ->orderBy('created_at', 'asc')
            ->get();

        // Cálculos adicionales para el resumen financiero
        $cashPayments = $ventas->where('metodo_pago', 'efectivo')->sum('total');
        $cardPayments = $ventas->where('metodo_pago', 'tarjeta')->sum('total');

        // Caja inicial y final
        $initialCash = CorteCaja::where('sucursal_id', $sucursalId)
            ->whereDate('created_at', Carbon::today())
            ->value('dinero_inicio');

        $finalCash = CorteCaja::where('sucursal_id', $sucursalId)
            ->whereDate('created_at', Carbon::today())
            ->value('dinero_final');

        
        $ventasEfectivo = Venta::where('sucursal_id', $sucursalId)
            ->whereDate('created_at', Carbon::today())
            ->where('metodo_pago', 'efectivo')
            ->get();

      
        $ventasTarjeta = Venta::where('sucursal_id', $sucursalId)
            ->whereDate('created_at', Carbon::today())
            ->where('metodo_pago', 'tarjeta')
            ->get();


        // Obtener los productos vendidos de la sucursal, sumando las cantidades por producto
        $productosVendidos = VentaProducto::select('producto_id', DB::raw('SUM(cantidad) as total_vendido'))
            ->whereHas('venta', function ($query) use ($sucursalId) {
                $query->where('sucursal_id', $sucursalId);
            })
            ->groupBy('producto_id')
            ->with('producto')
            ->whereDate('created_at', Carbon::today()) // Cambiar el nombre del modelo relacionado si es necesario
            ->get();  

      
        // Obtener inventario de la sucursal
        $inventario = Inventario::where('sucursal_id', $sucursalId)
            ->with('producto')
            ->where('tipo', '=', 'bebida')
            ->where('tipo', '=', 'pastes')
            ->where('tipo', '=', 'empanadas_saladas')
            ->where('tipo', '=', 'empanadas_dulces')
            ->get();
        //obtener el ultimo corte de caja de la sucursal
        
        $corte = CorteCaja::where('sucursal_id', $sucursalId)
           ->whereDate('created_at', Carbon::today())
           ->latest('created_at')
           ->first();
 
        //contar los cortes de caja de la sucursal
        $cantidadDeCortes = CorteCaja::where('sucursal_id', $sucursalId)
            ->whereDate('created_at', Carbon::today())  
            ->count();
        
        //obtener todos los cortes de caja de la sucursal de hoy
        $cortes = CorteCaja::where('sucursal_id', $sucursalId)
            ->whereDate('created_at', Carbon::today())
            ->get();

       

        $estimaciones = Estimaciones::where('sucursal_id', $sucursalId)
           ->with('inventario') // Carga la relación Inventario
           ->get();

           
        $ventaProductos = VentaProducto::with('producto')
            ->where('sucursal_id', $sucursalId)
            ->whereDate('created_at', Carbon::today())
            ->get();

       
           
        $registrosInventario = EntradasInventario::where('sucursal_id', $sucursalId)
            ->with(['inventario', 'trabajador' ])
            ->whereDate('created_at', Carbon::today())
            ->get(); 

       

        $gastos = Gastos::where('sucursal_id', $sucursalId)
            ->with('trabajador')
            ->whereDate('created_at', Carbon::today())
            ->get();

        
        $totalGastos = $gastos->sum('costo');
    
        $sobrantesInventario = Inventario::where('sucursal_id', $sucursalId)
            ->get();

        $sobrantes = Sobrantes::where('sucursal_id', $sucursalId)
            ->whereDate('created_at', Carbon::today())
            ->with('inventario')
            ->with('corteCaja')
            ->get();

        //obtener las categorias de los inventarios en su columna tipo
        $categoriasInventario = Inventario::where('sucursal_id', $sucursalId)
            ->select('tipo')
            ->distinct()
            ->get();

            //ventaProdcutos
        $ventaProductos = VentaProducto::with('producto')
            ->where('sucursal_id', $sucursalId)
            ->whereDate('created_at', Carbon::today())
            ->get();

        $sucursales = Sucursal::where('id', '!=', 0)->where('id', '!=', 1000)->get();
        
        

        return Inertia::render('GestorVentas/index', [
            'cashPayments' => $cashPayments,
            'cardPayments' => $cardPayments,
            'initialCash' => $initialCash,
            'finalCash' => $finalCash,
            'inventario' => $inventario,
            'ventas' => $ventas,
            'productosVendidos' => $productosVendidos,
            'corte' => $corte,
            'estimaciones' => $estimaciones,
            'ventasProductos' => $ventaProductos,
            'ventasEfectivo' => $ventasEfectivo,
            'ventasTarjeta' => $ventasTarjeta,
            'registrosInventario' => $registrosInventario,
            'gastos' => $gastos,
            'totalGastos' => $totalGastos,
            'sobrantes' => $sobrantes,
            'sobrantesInventario' => $sobrantesInventario,
            'categoriasInventario' => $categoriasInventario,
            'cantidadDeCortes' => $cantidadDeCortes,
            'cortes' => $cortes,
            'sucursales' => $sucursales,
            'sucursal_id' => $sucursalId,
            'usuario_id' => $user->id
        ]);
        
    }


    public function ventas(Request $request)
    {
        $user = Auth::user();
        $sucursalId = $user->sucursal_id;
        
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


        

        return Inertia::render('Dashboard/index', [
            'inventario' => $inventario,
            'ticket_id' => $ticketId,
            'categorias' => $categorias,
            'sucursales' => $sucursales,
            'trabajadores' => $trabajadores,
            'hornos' => $hornos,
            'estimacionesHoy' => $estimacionesHoy,
            'notificaciones' => [
                'faltantes' => $notificacionesFaltantes,
                'horneados' => $notificacionesHorneados
            ],
            'fechaActual' => $fechaHoy,
            'fechaSeleccionada' => $fechaSeleccionada,
            'fechaFiltro' => $fechaHoy
        ]);
    }

    public function filtro(Request $request)
    {
        $user = Auth::user();
        $sucursalId = $request->input('sucursal_id', $user->sucursal_id);

        $filter = $request->input('filter');
        $value = $request->input('value');

        // Definir el rango de fechas según el filtro
        if ($filter === 'day') {
            $startDate = Carbon::parse($value)->startOfDay();
            $endDate = Carbon::parse($value)->endOfDay();
        } elseif ($filter === 'week') {
            $startDate = Carbon::parse($value)->startOfWeek();
            $endDate = Carbon::parse($value)->endOfWeek();
        } elseif ($filter === 'month') {
            $startDate = Carbon::parse($value)->startOfMonth();
            $endDate = Carbon::parse($value)->endOfMonth();
        } else {
            return response()->json(['error' => 'Filtro no válido.'], 400);
        }

        // Filtrar ventas según el rango de fechas y sucursal
        $ventas = Venta::where('sucursal_id', $sucursalId)
            ->with(['detalles.producto', 'usuario'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'asc') 
            ->get();

        // ventas en efectivo y con tarjeta no eliminados y visibles
        $cashPayments = $ventas->where('metodo_pago', 'efectivo')
            ->where('estado', '!=', 'eliminada')
            ->where('visible', true)
            ->sum('total');
        $cardPayments = $ventas->where('metodo_pago', 'tarjeta')
            ->where('estado', '!=', 'eliminada')
            ->where('visible', true)
            ->sum('total');

        // todas las ventas no eliminadas y visibles
        $cashPaymentsTotal = $ventas->where('metodo_pago', 'efectivo')
            ->sum('total');
        $cardPaymentsTotal = $ventas->where('metodo_pago', 'tarjeta')
            ->sum('total');
    
        // Productos vendidos
        $productosVendidos = VentaProducto::select('producto_id', DB::raw('SUM(cantidad) as total_vendido'))
            ->whereHas('venta', function ($query) use ($sucursalId, $startDate, $endDate) {
                $query->where('sucursal_id', $sucursalId)
                    ->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->groupBy('producto_id')
            ->with('producto')
            ->get();

        // Caja inicial y final
        $initialCash = CorteCaja::where('sucursal_id', $sucursalId)
            ->whereDate('created_at', $startDate)
            ->value('dinero_inicio');

        $finalCash = CorteCaja::where('sucursal_id', $sucursalId)
            ->whereDate('created_at', $endDate)
            ->value('dinero_final');

        // Inventario
        $inventario = Inventario::where('sucursal_id', $sucursalId)
            ->whereIn('tipo', ['bebida', 'pastes', 'empanadas saladas', 'empanadas dulces'])
            ->get();

        // Ventas de productos
        $ventaProductos = VentaProducto::with('producto')
            ->whereHas('venta', function ($query) use ($sucursalId, $startDate, $endDate) {
                $query->where('sucursal_id', $sucursalId)
                    ->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->get();

        $cortes = CorteCaja::where('sucursal_id', $sucursalId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();
       
        $cantidadCortes = $cortes->count();

        // Registros de inventario
        $registrosInventario = EntradasInventario::where('sucursal_id', $sucursalId)
            ->with(['inventario', 'trabajador'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $gastos = Gastos::where('sucursal_id', $sucursalId)
            ->with('trabajador')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $totalGastos = $gastos->sum('costo');
        
        $sobrantes = Sobrantes::with('inventario')->where('sucursal_id', $sucursalId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $sobrantesInventario = Inventario::where('sucursal_id', $sucursalId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();
            
        $cantidadDeCortes = CorteCaja::where('sucursal_id', $sucursalId)
            ->whereBetween('created_at', [$startDate, $endDate])  
            ->count();

        // Obtener las categorias de los inventarios en su columna tipo
        $categoriasInventario = Inventario::where('sucursal_id', $sucursalId)
            ->select('tipo')
            ->distinct()
            ->get();

        // Obtener todas las sucursales para el selector
        $sucursales = Sucursal::where('id', '!=', 0)->where('id', '!=', 1000)->get();

        // Obtener usuarios de la sucursal filtrada para el formulario de ventas
        $usuarios = User::where('sucursal_id', $sucursalId)
            ->whereHas('roles', function($query) {
                $query->whereIn('name', ['trabajador', 'admin']);
            })
            ->get();

        return Inertia::render('GestorVentas/index', [
            'cashPayments' => $cashPayments,
            'cardPayments' => $cardPayments,
            'productsUsed' => $productosVendidos,
            'initialCash' => $initialCash,
            'finalCash' => $finalCash,
            'inventario' => $inventario,
            'ventasProductos' => $ventaProductos,
            'ventas' => $ventas,
            'registrosInventario' => $registrosInventario,
            'gastos' => $gastos,
            'totalGastos' => $totalGastos,
            'cortes' => $cortes,
            'cantidadCortes' => $cantidadCortes,
            'sobrantes' => $sobrantes,
            'sobrantesInventario' => $sobrantesInventario,
            'categoriasInventario' => $categoriasInventario,
            'cantidadDeCortes' => $cantidadDeCortes,
            'sucursales' => $sucursales,
            'sucursal_id' => $sucursalId,
            'usuario_id' => $user->id,
            'users' => $usuarios,
            'cashPaymentsTotal' => $cashPaymentsTotal,
            'cardPaymentsTotal' => $cardPaymentsTotal,
        ]);
    }


    public function eliminarVenta(Request $request)
    {
        try {
            $ventaId = $request->input('venta_id');
            $venta = Venta::find($ventaId);
            
            if (!$venta) {
                return response()->json(['error' => 'Venta no encontrada'], 404);
            }
            
            $sucursalId = $venta->sucursal_id;
            $fechaVenta = $venta->created_at;
            
            // Guardar datos originales antes de eliminar
            $venta->datos_originales = $venta->toJson();
            $venta->visible = false;
            $venta->estado = 'eliminada';
            $venta->save();

            // Renumerar ventas del día actual
            \App\Models\Venta::renumerarVentasDelDia($sucursalId, $fechaVenta);
            
            // Renumerar ventas normales del día
            \App\Models\Venta::renumerarVentasNormales($sucursalId, $fechaVenta);
            
            // Reanudar numeración de folios para todas las ventas del día
            // Esto asegura que los folios estén en orden secuencial después de cualquier eliminación
            $this->reanudarNumeracionFolios($sucursalId, $fechaVenta);
            
            // Obtener el conteo de ventas activas para la respuesta
            $ventasActivas = Venta::where('sucursal_id', $sucursalId)
                ->where('estado', '!=', 'eliminada')
                ->where('visible', true)
                ->whereDate('created_at', $fechaVenta)
                ->count();
            
            return response()->json([
                'message' => 'Venta eliminada correctamente',
                'renumeracion' => 'Se renumeraron ' . $ventasActivas . ' ventas'
            ]);
            
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al eliminar la venta: ' . $e->getMessage()], 500);
        }
    }

        /**
     * Renumerar manualmente todas las ventas de una sucursal de manera consecutiva desde el 1 de septiembre
     * Asigna números secuenciales basados en el orden cronológico (created_at)
     */
    public function renumerarVentas(Request $request)
    {
        try {
            $sucursalId = $request->input('sucursal_id');
            
            Log::info("Iniciando renumeración consecutiva desde 1 de septiembre para sucursal {$sucursalId}");

            // Usar el método del modelo para renumerar del día
            $ventasRenumeradas = \App\Models\Venta::renumerarVentasDelDia($sucursalId);
            
            Log::info("Ventas renumeradas para sucursal {$sucursalId}: {$ventasRenumeradas} ventas procesadas");
            
            return response()->json([
                'message' => 'Ventas renumeradas correctamente de manera consecutiva desde el 1 de septiembre',
                'ventas_renumeradas' => $ventasRenumeradas,
                'orden_cronologico' => 'Las ventas están numeradas consecutivamente desde el 1 de septiembre por orden cronológico (created_at)',
            ]);
            
        } catch (\Exception $e) {
            Log::error("Error al renumerar ventas: " . $e->getMessage());
            return response()->json(['error' => 'Error al renumerar ventas: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Restaurar una venta eliminada
     */
    public function restaurarVenta(Request $request)
    {
        try {
            $ventaId = $request->input('venta_id');
            $venta = Venta::find($ventaId);
            
            if (!$venta) {
                return response()->json(['error' => 'Venta no encontrada'], 404);
            }
            
            if ($venta->estado !== 'eliminada') {
                return response()->json(['error' => 'La venta no está eliminada'], 400);
            }
            
            // Restaurar la venta
            $venta->estado = 'sin cambios';
            $venta->visible = true;
            $venta->datos_originales = null;
            $venta->save();
            
            // Renumerar ventas del día actual
            \App\Models\Venta::renumerarVentasDelDia($venta->sucursal_id);
            
            // Renumerar ventas normales del día
            \App\Models\Venta::renumerarVentasNormales($venta->sucursal_id);

            // Reanudar numeración de folios para todas las ventas del día
            // Esto asegura que los folios estén en orden secuencial después de cualquier eliminación
            $this->reanudarNumeracionFolios($venta->sucursal_id, $venta->created_at);
            
            return response()->json([
                'message' => 'Venta restaurada correctamente'
            ]);
            
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al restaurar la venta: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Crear una nueva venta
     */
        public function crearVenta(Request $request)
    {
        try {
                        
            $request->validate([
                'sucursal_id' => 'required|exists:sucursales,id',
                'fecha_hora' => 'required|date',
                'usuario_id' => 'required|exists:users,id',
                'metodo_pago' => 'required|in:efectivo,tarjeta',
                'factura' => 'required|boolean',
                'folio' => 'nullable|string',
                'productos' => 'required|array|min:1',
                'productos.*.producto_id' => 'required|exists:inventarios,id',
                'productos.*.cantidad' => 'required|integer|min:1',
                'productos.*.precio_unitario' => 'required|numeric|min:0'
            ]);
            
            /* Verificar que el usuario pertenezca a la misma sucursal
            $usuarioVenta = User::find($request->usuario_id);
            if ($usuarioVenta->sucursal_id !== $request->sucursal_id) {
                return response()->json(['error' => 'El usuario seleccionado no pertenece a esta sucursal', 'usuario_id' => $usuarioVenta->id, 'sucursal_id' => $request->sucursal_id], 400);
            }*/

            // Crear la venta
            $venta = new Venta();
            $venta->created_at = $request->fecha_hora;
            $venta->usuario_id = $request->usuario_id;
            $venta->metodo_pago = $request->metodo_pago;
            $venta->factura = $request->factura;
            $venta->folio = $request->folio;
            $usuario = User::find($request->usuario_id);
            $venta->sucursal_id = $usuario->sucursal_id; // Usar la sucursal filtrada
            $venta->estado = 'creada';
            $venta->visible = true;

            // Generar folio consecutivo solo para ventas con tarjeta O facturadas
            if ($request->factura || $request->metodo_pago == 'tarjeta') {
                $venta->folio = \App\Models\Venta::generarFolioConsecutivo($usuario->sucursal_id);
            } else {
                $venta->folio = null;
            }
            
            // Calcular total
            $total = 0;
            foreach ($request->productos as $producto) {
                $total += $producto['cantidad'] * $producto['precio_unitario'];
            }
            $venta->total = $total;
            
            $venta->save();

            // Crear los productos de la venta
            foreach ($request->productos as $producto) {
                $ventaProducto = new VentaProducto();
                $ventaProducto->venta_id = $venta->id;
                $ventaProducto->producto_id = $producto['producto_id'];
                $ventaProducto->cantidad = $producto['cantidad'];
                $ventaProducto->precio_unitario = $producto['precio_unitario'];
                $ventaProducto->sucursal_id = $request->sucursal_id; // Usar la sucursal filtrada
                $ventaProducto->save();
            }

            // Guardar la venta primero para que tenga un ID
            $venta->save();

            // Crear los productos de la venta
            foreach ($request->productos as $producto) {
                $ventaProducto = new VentaProducto();
                $ventaProducto->venta_id = $venta->id;
                $ventaProducto->producto_id = $producto['producto_id'];
                $ventaProducto->cantidad = $producto['cantidad'];
                $ventaProducto->precio_unitario = $producto['precio_unitario'];
                $ventaProducto->sucursal_id = $request->sucursal_id; // Usar la sucursal filtrada
                $ventaProducto->save();
            }

            // Renumerar todas las ventas de la sucursal de manera consecutiva desde el 1 de septiembre
            \App\Models\Venta::renumerarVentasConsecutivas($request->sucursal_id);

            return response()->json([
                'message' => 'Venta creada correctamente',
                'venta_id' => $venta->id,
                'idVentaDia' => $venta->idVentaDia
            ]);
            
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al crear la venta: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Actualizar una venta existente
     */
    public function actualizarVenta(Request $request, $id)
    {
        try {
            $venta = Venta::find($id);
            if (!$venta) {
                return response()->json(['error' => 'Venta no encontrada'], 404);
            }

            $request->validate([
                'sucursal_id' => 'required|exists:sucursales,id',
                'fecha_hora' => 'required|date',
                'usuario_id' => 'required|exists:users,id',
                'metodo_pago' => 'required|in:efectivo,tarjeta',
                'factura' => 'required|boolean',
                'folio' => 'nullable|string',
                'productos' => 'required|array|min:1',
                'productos.*.producto_id' => 'required|exists:inventarios,id',
                'productos.*.cantidad' => 'required|integer|min:1',
                'productos.*.precio_unitario' => 'required|numeric|min:0'
            ]);

            //acutalizar fecha de creacion
            $venta->created_at = $request->fecha_hora;
            
            // Verificar que el usuario pertenezca a la sucursal seleccionada
            $usuarioVenta = User::find($request->usuario_id);
            if ($usuarioVenta->sucursal_id !== $request->sucursal_id) {
                return response()->json(['error' => 'El usuario seleccionado no pertenece a la sucursal seleccionada'], 400);
            }
            
            // Guardar valores originales antes de actualizar para detectar cambios
            $facturaOriginal = $venta->factura;
            $metodoPagoOriginal = $venta->metodo_pago;
            
            // Actualizar la venta
            $venta->created_at = $request->fecha_hora;
            $venta->usuario_id = $request->usuario_id;
            $venta->metodo_pago = $request->metodo_pago;
            $venta->factura = $request->factura;
            
            // Detectar cambios en factura y método de pago para mantener numeración correcta
            $facturaCambio = $facturaOriginal !== $request->factura;
            $metodoPagoCambio = $metodoPagoOriginal !== $request->metodo_pago;
            $eraFacturaTarjeta = $facturaOriginal || $metodoPagoOriginal == 'tarjeta';
            $seraFacturaTarjeta = $request->factura || $request->metodo_pago == 'tarjeta';
            
            // Generar folio consecutivo solo para ventas con tarjeta O facturadas
            if ($request->factura || $request->metodo_pago == 'tarjeta') {
                $venta->folio = \App\Models\Venta::generarFolioConsecutivo($request->sucursal_id);
            } else {
                $venta->folio = null;
            }
            
            $venta->sucursal_id = $request->sucursal_id; // Actualizar la sucursal si cambió
            $venta->estado = 'editada';
            
            // Calcular total
            $total = 0;
            foreach ($request->productos as $producto) {
                $total += $producto['cantidad'] * $producto['precio_unitario'];
            }
            $venta->total = $total;
            
            $venta->save();

            // Eliminar productos anteriores
            VentaProducto::where('venta_id', $venta->id)->delete();

            // Crear los nuevos productos de la venta
            foreach ($request->productos as $producto) {
                $ventaProducto = new VentaProducto();
                $ventaProducto->venta_id = $venta->id;
                $ventaProducto->producto_id = $producto['producto_id'];
                $ventaProducto->cantidad = $producto['cantidad'];
                $ventaProducto->precio_unitario = $producto['precio_unitario'];
                $ventaProducto->sucursal_id = $request->sucursal_id;
                $ventaProducto->save();
            }

            // Renumerar todas las ventas de la sucursal de manera consecutiva desde el 1 de septiembre
            \App\Models\Venta::renumerarVentasConsecutivas($request->sucursal_id);
            
            // Reanudar numeración de folios si hay cambios que afectan la numeración
            if (($facturaCambio || $metodoPagoCambio) && ($eraFacturaTarjeta || $seraFacturaTarjeta)) {
                $this->reanudarNumeracionFolios($request->sucursal_id, $fechaActualizada);
            }

            return response()->json([
                'message' => 'Venta actualizada correctamente'
            ]);
            
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al actualizar la venta: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Obtener usuarios de una sucursal específica
     */
    public function getUsuariosSucursal($sucursalId)
    {
        try {
            $usuarios = User::where('sucursal_id', $sucursalId)
                ->whereHas('roles', function($query) {
                    $query->whereIn('name', ['trabajador', 'admin']);
                })
                ->get();

            return response()->json([
                'usuarios' => $usuarios
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener usuarios: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Obtener ventas filtradas ordenadas cronológicamente
     */
    public function getVentasFiltradas(Request $request)
    {
        try {
            $sucursalId = $request->input('sucursal_id');
            $fecha = $request->input('fecha', Carbon::today());
            
            // Obtener ventas con filtros aplicados, siempre ordenadas cronológicamente
            $ventas = Venta::where('sucursal_id', $sucursalId)
                ->with(['detalles.producto', 'usuario'])
                ->whereDate('created_at', $fecha)
                ->orderBy('created_at', 'asc') // Siempre ordenar por fecha de creación
                ->get();

            return response()->json([
                'ventas' => $ventas,
                'ordenamiento' => 'Las ventas están ordenadas cronológicamente por fecha de creación (created_at)',
                'total_ventas' => $ventas->count()
            ]);
            
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener ventas filtradas: ' . $e->getMessage()], 500);
        }
    }

    public function actualizarVisible(Request $request)
    {
        try {
            $venta = Venta::find($request->id);
            if (!$venta) {
                return response()->json(['error' => 'Venta no encontrada'], 404);
            }
            
            // Guardar el estado anterior para detectar cambios
            $visibleAnterior = $venta->visible;
            
            // Actualizar la visibilidad
            $venta->visible = $request->visible;
            $venta->save();
            
            // Renumerar ventas del día actual
            \App\Models\Venta::renumerarVentasDelDia($venta->sucursal_id);
            
            // Renumerar ventas normales del día
            \App\Models\Venta::renumerarVentasNormales($venta->sucursal_id);
            
            // Reanudar numeración de folios para todas las ventas del día
            // Esto asegura que los folios estén en orden secuencial después de cualquier cambio de visibilidad
            $this->reanudarNumeracionFolios($venta->sucursal_id, $venta->created_at);
            
            return response()->json(['message' => 'Venta actualizada correctamente']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al actualizar la venta: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Genera un folio automático en formato para facturas con tarjeta
     * El número se incrementa cronológicamente por día y sucursal
     */
    private function generarFolioAutomatico($sucursalId, $fechaHora)
    {
        try {
            // Convertir la fecha a formato Y-m-d para obtener solo el día
            $fecha = Carbon::parse($fechaHora)->format('Y-m-d');

            // Poner null todos los folios que no sean de factura con tarjeta
            $ventas = Venta::where('sucursal_id', $sucursalId)
                ->where('factura', false)
                ->get();

            // Poner null todos los folios que no sean de factura 
            foreach ($ventas as $venta) {
                $venta->folio = null;
                $venta->save();
            }
            
            // Obtener el último folio del día para esta sucursal
            $ultimoFolio = Venta::where('sucursal_id', $sucursalId)
                ->whereDate('created_at', $fecha)
                ->where('factura', true)
                ->where('visible', true)
                ->whereNotNull('folio')
                ->where('folio', '>', 0) // Solo números mayores a 0
                ->orderBy('folio', 'desc')
                ->value('folio');
            
            

            if ($ultimoFolio) {
                // Incrementar el número del último folio
                $nuevoNumero = (int) $ultimoFolio + 1;
            } else {
                // Si no hay folios previos, empezar con 1
                $nuevoNumero = 1;
            }

            
            
            // Formato: solo el número secuencial
            return $nuevoNumero;
            
        } catch (\Exception $e) {
            // En caso de error, generar un folio con timestamp como fallback
            return time();
        }
    }

    /**
     * Reanuda la numeración de folios para todas las ventas que requieren folio desde el 1 de septiembre 2025
     * Se ejecuta automáticamente en todas las operaciones CRUD para mantener consistencia
     */
    private function reanudarNumeracionFolios($sucursalId, $fechaHora)
    {
        try {
            // Fecha de inicio: 1 de septiembre de 2025
            $fechaInicio = '2025-09-01';
            
            // PRIMERO: Poner null todos los folios que no cumplen las condiciones (no factura Y no tarjeta)
            $ventasSinFolio = Venta::where('sucursal_id', $sucursalId)
                ->where('created_at', '>=', $fechaInicio)
                ->where('visible', false)
                ->get();
            
            foreach ($ventasSinFolio as $venta) {
                $venta->folio = null;
                $venta->save();
            }
            
            // SEGUNDO: Obtener TODAS las ventas que requieren folio (factura O tarjeta) desde el 1 de septiembre 2025 ordenadas cronológicamente
            $ventasConFolio = Venta::where('sucursal_id', $sucursalId)
                ->where('created_at', '>=', $fechaInicio)
                ->where(function($query) {
                    $query->where('factura', true)
                          ->orWhere('metodo_pago', 'tarjeta');
                })
                ->where('estado', '!=', 'eliminada')
                ->where('visible', true)
                ->orderBy('created_at', 'asc')
                ->get();
            
            // Renumerar secuencialmente desde 1 (numeración consecutiva desde el 1 de septiembre)
            $contador = 1;
            $foliosRenumerados = [];
            
            foreach ($ventasConFolio as $venta) {
                $folioAnterior = $venta->folio;
                $nuevoFolio = $contador;
                $venta->folio = $nuevoFolio;
                $venta->save();
                
                $foliosRenumerados[] = [
                    'id' => $venta->id,
                    'folio_anterior' => $folioAnterior,
                    'folio_nuevo' => $nuevoFolio,
                    'created_at' => $venta->created_at->format('Y-m-d H:i:s'),
                    'factura' => $venta->factura,
                    'metodo_pago' => $venta->metodo_pago
                ];
                
                $contador++;
            }
            
            // Log de la reanudación de numeración
            if (count($foliosRenumerados) > 0) {
                Log::info("Numeración de folios reanudada para sucursal {$sucursalId} desde el 1 de septiembre 2025: " . count($foliosRenumerados) . " folios procesados");
            }
            
            return $foliosRenumerados;
            
        } catch (\Exception $e) {
            Log::error("Error al reanudar numeración de folios: " . $e->getMessage());
            return [];
        }
    }

    public function renumerarVentasNormales(Request $request)
    {
        try {
            $sucursalId = $request->input('sucursal_id');
            $fecha = $request->input('fecha', Carbon::today());

            // Usar el nuevo método del modelo
            $ventasRenumeradas = \App\Models\Venta::renumerarVentasNormales($sucursalId, $fecha);

            return response()->json([
                'message' => 'Ventas normales renumeradas correctamente',
                'ventas_renumeradas' => $ventasRenumeradas
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al renumerar ventas normales: ' . $e->getMessage()], 500);
        }
    }
}
