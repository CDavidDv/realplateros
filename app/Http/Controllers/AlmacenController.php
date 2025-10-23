<?php

namespace App\Http\Controllers;

use App\Models\Inventario;
use App\Models\User;
use App\Models\TicketAsignacion;
use App\Models\TicketProductosAsignacion;
use App\Models\EntradasInventario;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AlmacenController extends Controller
{
    /**
     * Constructor - Verificar que el usuario sea de almacén
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!Auth::user()->esAlmacen()) {
                abort(403, 'Acceso denegado. Solo para personal de almacén.');
            }
            return $next($request);
        });
    }

    /**
     * Vista principal del almacén
     */
    public function almacen()
    {
        $user = Auth::user();

        // Obtener ID del almacén del usuario
        $almacenId = $user->sucursal_id;

        // Obtener categorías únicas
        $categorias = Inventario::select('tipo')->distinct()->get();

        // Obtener inventario del almacén
        $inventarioAlmacen = Inventario::where('sucursal_id', $almacenId)
            ->orderBy('tipo')
            ->orderBy('nombre')
            ->get();

        // Obtener personal del almacén
        $personalAlmacen = User::almacen()
            ->where('active', true)
            ->select('id', 'name', 'email', 'tel')
            ->get();

        return Inertia::render('Almacen/index', [
            'categorias' => $categorias,
            'inventario' => $inventarioAlmacen,
            'personal' => $personalAlmacen,
            'usuario' => [
                'nombre' => $user->name,
                'es_almacen' => $user->es_almacen,
                'sucursal_id' => $user->sucursal_id,
            ]
        ]);
    }

    /**
     * Obtener estadísticas del almacén
     */
    public function estadisticas()
    {
        $user = Auth::user();
        $almacenId = $user->sucursal_id;

        $stats = [
            'total_productos' => Inventario::where('sucursal_id', $almacenId)->count(),
            'productos_bajo_stock' => Inventario::where('sucursal_id', $almacenId)
                ->where('cantidad', '<', 10)
                ->count(),
            'total_empleados' => User::almacen()->where('active', true)->count(),
            'categorias' => Inventario::where('sucursal_id', $almacenId)
                ->select('tipo')
                ->distinct()
                ->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Gráfica de productos enviados por sucursal
     * Solo accesible para admin de almacén
     */
    public function productosPorSucursal(Request $request)
    {
        // Verificar que sea admin de almacén
        $user = Auth::user();
        if (!$user->esAlmacen() || !$user->hasRole('admin')) {
            return response()->json(['error' => 'Acceso denegado'], 403);
        }

        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');

        // Query base
        $query = TicketAsignacion::select(
            'tickets_asignacion.sucursal_id',
            'sucursales.nombre as sucursal_nombre',
            DB::raw('COUNT(DISTINCT tickets_asignacion.id) as total_tickets'),
            DB::raw('COALESCE(SUM(ticket_productos_asignacion.cantidad), 0) as total_productos')
        )
        ->leftJoin('ticket_productos_asignacion', 'tickets_asignacion.id', '=', 'ticket_productos_asignacion.ticket_asignacion_id')
        ->leftJoin('sucursales', 'tickets_asignacion.sucursal_id', '=', 'sucursales.id')
        ->whereNotNull('tickets_asignacion.sucursal_id')
        ->where('tickets_asignacion.sucursal_id', '!=', 0); // Excluir almacén mismo

        // Aplicar filtros de fecha si existen
        if ($fechaInicio) {
            $query->whereDate('tickets_asignacion.created_at', '>=', $fechaInicio);
        }
        if ($fechaFin) {
            $query->whereDate('tickets_asignacion.created_at', '<=', $fechaFin);
        }

        $datos = $query->groupBy('tickets_asignacion.sucursal_id', 'sucursales.nombre')
            ->orderBy('total_productos', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $datos,
            'periodo' => [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
            ]
        ]);
    }

    /**
     * Gráfica de movimientos de inventario (ingresos vs salidas)
     * Solo accesible para admin de almacén
     */
    public function movimientosInventario(Request $request)
    {
        // Verificar que sea admin de almacén
        $user = Auth::user();
        if (!$user->esAlmacen() || !$user->hasRole('admin')) {
            return response()->json(['error' => 'Acceso denegado'], 403);
        }

        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');

        // Ingresos por fecha
        $ingresosQuery = EntradasInventario::select(
            DB::raw('DATE(created_at) as fecha'),
            DB::raw('SUM(cantidad) as total')
        )
        ->where('sucursal_id', 0); // Solo almacén

        if ($fechaInicio) {
            $ingresosQuery->whereDate('created_at', '>=', $fechaInicio);
        }
        if ($fechaFin) {
            $ingresosQuery->whereDate('created_at', '<=', $fechaFin);
        }

        $ingresos = $ingresosQuery->groupBy('fecha')
            ->orderBy('fecha')
            ->get()
            ->keyBy('fecha');

        // Salidas por fecha (productos enviados a sucursales)
        $salidasQuery = TicketAsignacion::select(
            DB::raw('DATE(tickets_asignacion.created_at) as fecha'),
            DB::raw('COALESCE(SUM(ticket_productos_asignacion.cantidad), 0) as total')
        )
        ->leftJoin('ticket_productos_asignacion', 'tickets_asignacion.id', '=', 'ticket_productos_asignacion.ticket_asignacion_id')
        ->whereNotNull('tickets_asignacion.sucursal_id')
        ->where('tickets_asignacion.sucursal_id', '!=', 0); // Enviados a sucursales

        if ($fechaInicio) {
            $salidasQuery->whereDate('tickets_asignacion.created_at', '>=', $fechaInicio);
        }
        if ($fechaFin) {
            $salidasQuery->whereDate('tickets_asignacion.created_at', '<=', $fechaFin);
        }

        $salidas = $salidasQuery->groupBy('fecha')
            ->orderBy('fecha')
            ->get()
            ->keyBy('fecha');

        // Combinar fechas de ingresos y salidas
        $todasFechas = collect($ingresos->keys())
            ->merge($salidas->keys())
            ->unique()
            ->sort()
            ->values();

        // Preparar datos para la gráfica
        $datos = $todasFechas->map(function ($fecha) use ($ingresos, $salidas) {
            return [
                'fecha' => $fecha,
                'ingresos' => $ingresos->get($fecha)?->total ?? 0,
                'salidas' => $salidas->get($fecha)?->total ?? 0,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $datos,
            'periodo' => [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
            ]
        ]);
    }

    /**
     * Gráfica de productos individuales más enviados a una sucursal específica
     * Solo accesible para admin de almacén
     */
    public function productosIndividualesPorSucursal(Request $request)
    {
        // Verificar que sea admin de almacén
        $user = Auth::user();
        if (!$user->esAlmacen() || !$user->hasRole('admin')) {
            return response()->json(['error' => 'Acceso denegado'], 403);
        }

        $sucursalId = $request->input('sucursal_id');
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');

        if (!$sucursalId) {
            return response()->json(['error' => 'Sucursal requerida'], 400);
        }

        // Query para obtener productos individuales enviados a la sucursal
        $query = TicketProductosAsignacion::select(
            'ticket_productos_asignacion.producto_id',
            'inventarios.nombre as producto_nombre',
            DB::raw('SUM(ticket_productos_asignacion.cantidad) as total_cantidad')
        )
        ->join('tickets_asignacion', 'ticket_productos_asignacion.ticket_asignacion_id', '=', 'tickets_asignacion.id')
        ->join('inventarios', 'ticket_productos_asignacion.producto_id', '=', 'inventarios.id')
        ->where('tickets_asignacion.sucursal_id', $sucursalId);

        // Aplicar filtros de fecha
        if ($fechaInicio) {
            $query->whereDate('tickets_asignacion.created_at', '>=', $fechaInicio);
        }
        if ($fechaFin) {
            $query->whereDate('tickets_asignacion.created_at', '<=', $fechaFin);
        }

        $datos = $query->groupBy('ticket_productos_asignacion.producto_id', 'inventarios.nombre')
            ->orderBy('total_cantidad', 'desc')
            ->limit(15) // Top 15 productos
            ->get();

        return response()->json([
            'success' => true,
            'data' => $datos,
            'sucursal_id' => $sucursalId,
            'periodo' => [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
            ]
        ]);
    }

    /**
     * Gráfica de movimientos (ingresos vs salidas) de productos por sucursal
     * Solo accesible para admin de almacén
     */
    public function movimientosProductosPorSucursal(Request $request)
    {
        // Verificar que sea admin de almacén
        $user = Auth::user();
        if (!$user->esAlmacen() || !$user->hasRole('admin')) {
            return response()->json(['error' => 'Acceso denegado'], 403);
        }

        $sucursalId = $request->input('sucursal_id');
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');

        if (!$sucursalId) {
            return response()->json(['error' => 'Sucursal requerida'], 400);
        }

        // Ingresos de productos (entradas al inventario de la sucursal)
        $ingresosQuery = EntradasInventario::select(
            'entradas_inventario.inventario_id',
            'inventarios.nombre as producto_nombre',
            DB::raw('SUM(entradas_inventario.cantidad) as total_ingresos')
        )
        ->join('inventarios', 'entradas_inventario.inventario_id', '=', 'inventarios.id')
        ->where('entradas_inventario.sucursal_id', $sucursalId);

        if ($fechaInicio) {
            $ingresosQuery->whereDate('entradas_inventario.created_at', '>=', $fechaInicio);
        }
        if ($fechaFin) {
            $ingresosQuery->whereDate('entradas_inventario.created_at', '<=', $fechaFin);
        }

        $ingresos = $ingresosQuery->groupBy('entradas_inventario.inventario_id', 'inventarios.nombre')
            ->get()
            ->keyBy('inventario_id');

        // Salidas de productos (productos enviados desde almacén a la sucursal)
        $salidasQuery = TicketProductosAsignacion::select(
            'ticket_productos_asignacion.producto_id',
            'inventarios.nombre as producto_nombre',
            DB::raw('SUM(ticket_productos_asignacion.cantidad) as total_salidas')
        )
        ->join('tickets_asignacion', 'ticket_productos_asignacion.ticket_asignacion_id', '=', 'tickets_asignacion.id')
        ->join('inventarios', 'ticket_productos_asignacion.producto_id', '=', 'inventarios.id')
        ->where('tickets_asignacion.sucursal_id', $sucursalId);

        if ($fechaInicio) {
            $salidasQuery->whereDate('tickets_asignacion.created_at', '>=', $fechaInicio);
        }
        if ($fechaFin) {
            $salidasQuery->whereDate('tickets_asignacion.created_at', '<=', $fechaFin);
        }

        $salidas = $salidasQuery->groupBy('ticket_productos_asignacion.producto_id', 'inventarios.nombre')
            ->get()
            ->keyBy('producto_id');

        // Combinar productos de ingresos y salidas
        $todosProductos = collect($ingresos->keys())
            ->merge($salidas->keys())
            ->unique()
            ->values();

        // Preparar datos combinados
        $datos = $todosProductos->map(function ($productoId) use ($ingresos, $salidas) {
            $ingreso = $ingresos->get($productoId);
            $salida = $salidas->get($productoId);

            return [
                'producto_id' => $productoId,
                'producto_nombre' => $ingreso?->producto_nombre ?? $salida?->producto_nombre ?? 'Desconocido',
                'total_ingresos' => $ingreso?->total_ingresos ?? 0,
                'total_salidas' => $salida?->total_salidas ?? 0,
            ];
        })->sortByDesc(function ($item) {
            return $item['total_ingresos'] + $item['total_salidas'];
        })->take(15)->values(); // Top 15 productos con más movimientos

        return response()->json([
            'success' => true,
            'data' => $datos,
            'sucursal_id' => $sucursalId,
            'periodo' => [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
            ]
        ]);
    }
}
