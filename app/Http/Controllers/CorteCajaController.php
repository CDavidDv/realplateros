<?php

namespace App\Http\Controllers;

use App\Models\CorteCaja;
use App\Models\EntradasInventario;
use App\Models\Estimaciones;
use App\Models\Gastos;
use App\Models\Inventario;
use App\Models\Venta;
use App\Models\VentaProducto;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CorteCajaController extends Controller
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

    

    public function guardarInicial(Request $request)
    {
        $usuario = auth()->user();

        $request->validate([
            'fecha' => 'required|date',
            'dinero_inicio' => 'required|numeric|min:0',
        ]);

        $ultimoCorte = CorteCaja::where('sucursal_id', $usuario->sucursal_id)
            ->where('fecha', $request->fecha)
            ->latest('created_at')
            ->first();

        if ($ultimoCorte) {
            if (!is_null($ultimoCorte->dinero_inicio)) {
                return back()->with('error', 'Ya existe un dinero de inicio para esta fecha y corte de caja.');
            }

            // Actualizar el dinero_inicio del último corte de caja
            $ultimoCorte->dinero_inicio = $request->dinero_inicio;
            $ultimoCorte->save();
        } else {
            // Crear un nuevo corte de caja
            CorteCaja::create([
                'sucursal_id' => $usuario->sucursal_id,
                'fecha' => $request->fecha,
                'dinero_inicio' => $request->dinero_inicio,
                'usuario_id' => $usuario->id,
            ]);
        }

        return redirect()->route('corte-caja')->with('success', 'Cantidad inicial guardada correctamente.');
    }


    public function guardarFinal(Request $request)
    {
        $usuario = auth()->user();
        
        $request->validate([
            'fecha' => 'required|date',
            'dinero_final' => 'required|numeric|min:0',
        ]);
        // Obtener el último corte de caja para esa sucursal y fecha
        $ultimoCorte = CorteCaja::where('sucursal_id', $usuario->sucursal_id)
            ->where('fecha', $request->fecha)
            ->latest('created_at')
            ->first();

        if (!$ultimoCorte) {
            return back()->with('error', 'No existe un corte de caja para esta fecha. Debe guardar la cantidad inicial primero.');
        }

        if ($ultimoCorte->dinero_final !== null) {
            return back()->with('error', 'Ya existe un dinero final para esta fecha y corte de caja.');
        }

        // Actualiza el dinero final del último corte
        $ultimoCorte->dinero_final = $request->dinero_final;
        $ultimoCorte->save();

        return back()->with('success', 'Cantidad final guardada correctamente.');
    }


    public function filtro(Request $request)
    {
        $user = Auth::user();
        $sucursalId = $user->sucursal_id;

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

        // Filtrar ventas según el rango de fechas
        $ventas = Venta::where('sucursal_id', $sucursalId)
            ->with(['detalles.producto', 'usuario'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        //sumar 6 horas a cada venta
        $ventas = $ventas->map(function ($venta) {
            $venta->created_at = $venta->created_at->addHours(6);
            $venta->updated_at = $venta->updated_at->addHours(6);
            return $venta;
        });

        // Cálculos adicionales
        $cashPayments = $ventas->where('metodo_pago', 'efectivo')->sum('total');
        
        $cardPayments = $ventas->where('metodo_pago', 'tarjeta')->sum('total');
    
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
        $inventario = Inventario::where('sucursal_id', $sucursalId)->get();

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
        //sumar 6 horas a cada corte
        $cortes = $cortes->map(function ($corte) {
            $corte->created_at = $corte->created_at->addHours(6);
            $corte->updated_at = $corte->updated_at->addHours(6);
            return $corte;
        });

        $cantidadCortes = $cortes->count();

        // Registros de inventario
        $registrosInventario = EntradasInventario::where('sucursal_id', $sucursalId)
            ->with(['inventario', 'trabajador'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        //sumar 6 horas a cada registro de inventario
        $registrosInventario = $registrosInventario->map(function ($registroInventario) {
            $registroInventario->created_at = $registroInventario->created_at->addHours(6);
            $registroInventario->updated_at = $registroInventario->updated_at->addHours(6);
            return $registroInventario;
        });

        $gastos = Gastos::where('sucursal_id', $sucursalId)
            ->with('trabajador')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $totalGastos = $gastos->sum('costo');
        

        return Inertia::render('Corte/index', [
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
            'cantidadCortes' => $cantidadCortes
        ]);
    }


    public function corte()
    {   
        // Obtener usuario autenticado
        $user = Auth::user();
        $sucursalId = $user->sucursal_id;

        // Obtener todas las ventas de la sucursal
        $ventas = Venta::where('sucursal_id', $sucursalId)
            ->with(['detalles.producto', 'usuario'])
            ->whereDate('created_at', Carbon::today())
            ->get();

        //sumar 6 horas a cada venta
        $ventas = $ventas->map(function ($venta) {
            $venta->created_at = $venta->created_at->addHours(6);
            $venta->updated_at = $venta->updated_at->addHours(6);
            return $venta;
        });

        $ventasEfectivo = Venta::where('sucursal_id', $sucursalId)
            ->whereDate('created_at', Carbon::today())
            ->where('metodo_pago', 'efectivo')
            ->get();

        //sumar 6 horas a cada venta
        $ventasEfectivo = $ventasEfectivo->map(function ($venta) {
            $venta->created_at = $venta->created_at->addHours(6);
            $venta->updated_at = $venta->updated_at->addHours(6);
            return $venta;
        });

        $ventasTarjeta = Venta::where('sucursal_id', $sucursalId)
            ->whereDate('created_at', Carbon::today())
            ->where('metodo_pago', 'tarjeta')
            ->get();

        //sumar 6 horas a cada venta
        $ventasTarjeta = $ventasTarjeta->map(function ($venta) {
            $venta->created_at = $venta->created_at->addHours(6);
            $venta->updated_at = $venta->updated_at->addHours(6);
            return $venta;
        });

        // Obtener los productos vendidos de la sucursal, sumando las cantidades por producto
        $productosVendidos = VentaProducto::select('producto_id', DB::raw('SUM(cantidad) as total_vendido'))
            ->whereHas('venta', function ($query) use ($sucursalId) {
                $query->where('sucursal_id', $sucursalId);
            })
            ->groupBy('producto_id')
            ->with('producto')
            ->whereDate('created_at', Carbon::today()) // Cambiar el nombre del modelo relacionado si es necesario
            ->get();  

        //sumar 6 horas a cada producto vendido
        $productosVendidos = $productosVendidos->map(function ($producto) {
            if($producto->created_at){
                $producto->created_at = $producto->created_at->addHours(6);
            }
            if($producto->updated_at){
                $producto->updated_at = $producto->updated_at->addHours(6);
            }
            return $producto;
        });

        // Obtener inventario de la sucursal
        $inventario = Inventario::where('sucursal_id', $sucursalId)->get();
        //obtener el ultimo corte de caja de la sucursal
        
        $corte = CorteCaja::where('sucursal_id', $sucursalId)
           ->whereDate('created_at', Carbon::today())
           ->latest('created_at')
           ->first();

        //sumar 6 horas al corte si no es null 
        if ($corte) {
            $corte->created_at = $corte->created_at->addHours(6);
            $corte->updated_at = $corte->updated_at->addHours(6);
        }

        //contar los cortes de caja de la sucursal
        $cantidadDeCortes = CorteCaja::where('sucursal_id', $sucursalId)
            ->whereDate('created_at', Carbon::today())  
            ->count();

        //obtener todos los cortes de caja de la sucursal de hoy
        $cortes = CorteCaja::where('sucursal_id', $sucursalId)
            ->whereDate('created_at', Carbon::today())
            ->get();

        //sumar 6 horas a cada corte
        $cortes = $cortes->map(function ($corte) {
            $corte->created_at = $corte->created_at->addHours(6);
            $corte->updated_at = $corte->updated_at->addHours(6);
            return $corte;
        });

        $estimaciones = Estimaciones::where('sucursal_id', $sucursalId)
           ->with('inventario') // Carga la relación Inventario
           ->get();

           
        $ventaProductos = VentaProducto::with('producto')
            ->where('sucursal_id', $sucursalId)
            ->whereDate('created_at', Carbon::today())
            ->get();

        //sumar 6 horas a cada venta producto
        $ventaProductos = $ventaProductos->map(function ($ventaProducto) {
            $ventaProducto->created_at = $ventaProducto->created_at->addHours(6);
            $ventaProducto->updated_at = $ventaProducto->updated_at->addHours(6);
            return $ventaProducto;
        }); 
           
        $registrosInventario = EntradasInventario::where('sucursal_id', $sucursalId)
            ->with(['inventario', 'trabajador' ])
            ->whereDate('created_at', Carbon::today())
            ->get(); 

        //sumar 6 horas a cada registro de inventario
        $registrosInventario = $registrosInventario->map(function ($registroInventario) {
            $registroInventario->created_at = $registroInventario->created_at->addHours(6);
            $registroInventario->updated_at = $registroInventario->updated_at->addHours(6);
            return $registroInventario;
        });

        $gastos = Gastos::where('sucursal_id', $sucursalId)
            ->with('trabajador')
            ->whereDate('created_at', Carbon::today())
            ->get();

        //sumar 6 horas a cada gasto
        $gastos = $gastos->map(function ($gasto) {
            $gasto->created_at = $gasto->created_at->addHours(6);
            $gasto->updated_at = $gasto->updated_at->addHours(6);
            return $gasto;
        });
        $totalGastos = $gastos->sum('costo');
    
        $sobrantes = Inventario::where('sucursal_id', $sucursalId)
            ->get();

        //obtener las categorias de los inventarios en su columna tipo
        $categoriasInventario = Inventario::where('sucursal_id', $sucursalId)
            ->select('tipo')
            ->distinct()
            ->get();

        return Inertia::render('Corte/index', [
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
            'categoriasInventario' => $categoriasInventario,
            'cantidadDeCortes' => $cantidadDeCortes,
            'cortes' => $cortes
        ]);
    }

    public function crearCorte(Request $request)
    {
        $user = Auth::user();
        $sucursalId = $user->sucursal_id;
        
        $corteNuevo = CorteCaja::create([
            'sucursal_id' => $user->sucursal_id,
            'fecha' => Carbon::today(),
            'usuario_id' => $user->id,
        ]);

        return response()->json(['corte' => $corteNuevo]);
    }
}

