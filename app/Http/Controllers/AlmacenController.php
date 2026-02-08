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
     * Hoja de corte: ingresos vs salidas de inventario
     */
    public function hojaCorte(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio', now()->toDateString());
        $fechaFin = $request->input('fecha_fin', now()->toDateString());
        $sucursalId = $request->input('sucursal_id');

        $sucursales = Sucursal::orderBy('nombre')->get(['id', 'nombre']);

        $ingresos = EntradasInventario::select(
            DB::raw('DATE(entradas_inventario.created_at) as fecha'),
            'entradas_inventario.inventario_id',
            'inventarios.nombre as producto',
            'entradas_inventario.cantidad',
            'entradas_inventario.trabajador_id',
            DB::raw("'Ingreso' as tipo"),
            'entradas_inventario.sucursal_id'
        )
        ->join('inventarios', 'entradas_inventario.inventario_id', '=', 'inventarios.id')
        ->whereDate('entradas_inventario.created_at', '>=', $fechaInicio)
        ->whereDate('entradas_inventario.created_at', '<=', $fechaFin)
        ->when($sucursalId, fn($q) => $q->where('entradas_inventario.sucursal_id', $sucursalId))
        ->get()
        ->map(function ($row) {
            $trabajador = User::find($row->trabajador_id);
            return [
                'fecha' => $row->fecha,
                'tipo' => 'Ingreso',
                'producto' => $row->producto,
                'cantidad' => $row->cantidad,
                'origen_destino' => 'Almacén',
                'trabajador' => $trabajador?->name ?? '-',
            ];
        });

        $salidas = TicketProductosAsignacion::select(
            DB::raw('DATE(tickets_asignacion.created_at) as fecha'),
            'ticket_productos_asignacion.producto_id',
            'inventarios.nombre as producto',
            'ticket_productos_asignacion.cantidad',
            'tickets_asignacion.sucursal_id',
            'tickets_asignacion.empleado_id'
        )
        ->join('tickets_asignacion', 'ticket_productos_asignacion.ticket_asignacion_id', '=', 'tickets_asignacion.id')
        ->join('inventarios', 'ticket_productos_asignacion.producto_id', '=', 'inventarios.id')
        ->whereDate('tickets_asignacion.created_at', '>=', $fechaInicio)
        ->whereDate('tickets_asignacion.created_at', '<=', $fechaFin)
        ->whereNotNull('tickets_asignacion.sucursal_id')
        ->when($sucursalId, fn($q) => $q->where('tickets_asignacion.sucursal_id', $sucursalId))
        ->get()
        ->map(function ($row) {
            $sucursal = Sucursal::find($row->sucursal_id);
            $empleado = User::find($row->empleado_id);
            return [
                'fecha' => $row->fecha,
                'tipo' => 'Salida',
                'producto' => $row->producto,
                'cantidad' => $row->cantidad,
                'origen_destino' => $sucursal?->nombre ?? '-',
                'trabajador' => $empleado?->name ?? '-',
            ];
        });

        $movimientos = $ingresos->concat($salidas)->sortBy('fecha')->values();

        $totalIngresos = $ingresos->sum('cantidad');
        $totalSalidas = $salidas->sum('cantidad');

        // Agrupaciones para gráficas
        $porDia = $movimientos->groupBy('fecha')->map(function ($grupo, $fecha) {
            return [
                'fecha' => $fecha,
                'ingresos' => $grupo->where('tipo', 'Ingreso')->sum('cantidad'),
                'salidas' => $grupo->where('tipo', 'Salida')->sum('cantidad'),
            ];
        })->values();

        $topProductos = $movimientos->groupBy('producto')->map(function ($grupo, $nombre) {
            return ['producto' => $nombre, 'total' => $grupo->sum('cantidad')];
        })->sortByDesc('total')->take(10)->values();

        $porSucursal = $salidas->groupBy('origen_destino')->map(function ($grupo, $nombre) {
            return ['sucursal' => $nombre, 'total' => $grupo->sum('cantidad')];
        })->sortByDesc('total')->values();

        return Inertia::render('Almacen/HojaCorte', [
            'movimientos' => $movimientos,
            'resumen' => [
                'total_ingresos' => $totalIngresos,
                'total_salidas' => $totalSalidas,
                'balance' => $totalIngresos - $totalSalidas,
            ],
            'graficas' => [
                'por_dia' => $porDia,
                'top_productos' => $topProductos,
                'por_sucursal' => $porSucursal,
            ],
            'sucursales' => $sucursales,
            'filtros' => [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'sucursal_id' => $sucursalId,
            ],
        ]);
    }

    /**
     * Exportar hoja de corte como CSV
     */
    public function hojaCorteExport(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio', now()->toDateString());
        $fechaFin = $request->input('fecha_fin', now()->toDateString());
        $sucursalId = $request->input('sucursal_id');

        $ingresos = EntradasInventario::select(
            DB::raw('DATE(entradas_inventario.created_at) as fecha'),
            'inventarios.nombre as producto',
            'entradas_inventario.cantidad',
            'entradas_inventario.trabajador_id',
            'entradas_inventario.sucursal_id'
        )
        ->join('inventarios', 'entradas_inventario.inventario_id', '=', 'inventarios.id')
        ->whereDate('entradas_inventario.created_at', '>=', $fechaInicio)
        ->whereDate('entradas_inventario.created_at', '<=', $fechaFin)
        ->when($sucursalId, fn($q) => $q->where('entradas_inventario.sucursal_id', $sucursalId))
        ->get()
        ->map(fn($row) => [
            $row->fecha, 'Ingreso', $row->producto, $row->cantidad,
            'Almacén', User::find($row->trabajador_id)?->name ?? '-',
        ]);

        $salidas = TicketProductosAsignacion::select(
            DB::raw('DATE(tickets_asignacion.created_at) as fecha'),
            'inventarios.nombre as producto',
            'ticket_productos_asignacion.cantidad',
            'tickets_asignacion.sucursal_id',
            'tickets_asignacion.empleado_id'
        )
        ->join('tickets_asignacion', 'ticket_productos_asignacion.ticket_asignacion_id', '=', 'tickets_asignacion.id')
        ->join('inventarios', 'ticket_productos_asignacion.producto_id', '=', 'inventarios.id')
        ->whereDate('tickets_asignacion.created_at', '>=', $fechaInicio)
        ->whereDate('tickets_asignacion.created_at', '<=', $fechaFin)
        ->whereNotNull('tickets_asignacion.sucursal_id')
        ->when($sucursalId, fn($q) => $q->where('tickets_asignacion.sucursal_id', $sucursalId))
        ->get()
        ->map(fn($row) => [
            $row->fecha, 'Salida', $row->producto, $row->cantidad,
            Sucursal::find($row->sucursal_id)?->nombre ?? '-',
            User::find($row->empleado_id)?->name ?? '-',
        ]);

        $filas = $ingresos->concat($salidas)->sortBy(fn($r) => $r[0])->values();

        $nombre = "hoja_corte_{$fechaInicio}_{$fechaFin}.csv";

        return response()->streamDownload(function () use ($filas) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Fecha', 'Tipo', 'Producto', 'Cantidad', 'Origen/Destino', 'Trabajador']);
            foreach ($filas as $fila) {
                fputcsv($handle, $fila);
            }
            fclose($handle);
        }, $nombre, ['Content-Type' => 'text/csv']);
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
