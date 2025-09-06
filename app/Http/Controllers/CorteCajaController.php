<?php

namespace App\Http\Controllers;

use App\Models\CorteCaja;
use App\Models\EntradasInventario;
use App\Models\Estimaciones;
use App\Models\Gastos;
use App\Models\Inventario;
use App\Models\Sobrantes;
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

        //guardar los productos sobrantes
        $productosSobrantes = Inventario::where('sucursal_id', $usuario->sucursal_id)
            ->get();

        foreach ($productosSobrantes as $producto) {
            Sobrantes::create([
                'cantidad' => $producto->cantidad,
                'sucursal_id' => $usuario->sucursal_id,
                'inventario_id' => $producto->id,
                'corte_caja_id' => $ultimoCorte->id,
            ]);
        }

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
            ->where('estado', '!=', 'eliminada') 
            ->where('visible', true)
            ->orderBy('created_at', 'asc')
            ->get();

        // Cálculos adicionales
        $cashPayments = $ventas->where('metodo_pago', 'efectivo')->sum('total');
        
        $cardPayments = $ventas->where('metodo_pago', 'tarjeta')->sum('total');
    
        // Productos vendidos
        $productosVendidos = VentaProducto::select('producto_id', DB::raw('SUM(cantidad) as total_vendido'))
            ->whereHas('venta', function ($query) use ($sucursalId, $startDate, $endDate) {
                $query->where('sucursal_id', $sucursalId)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->where('estado', '!=', 'eliminada') 
                    ->where('visible', true);
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
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->where('estado', '!=', 'eliminada') 
                    ->where('visible', true);
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
        

        $sobrantes = Sobrantes::where('sucursal_id', $sucursalId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $sobrantesInventario = Inventario::where('sucursal_id', $sucursalId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();
            
        $cantidadDeCortes = CorteCaja::where('sucursal_id', $sucursalId)
            ->whereBetween('created_at', [$startDate, $endDate])  
            ->count();

        //obtener las categorias de los inventarios en su columna tipo
        $categoriasInventario = Inventario::where('sucursal_id', $sucursalId)
            ->select('tipo')
            ->distinct()
            ->get();

        //ventaProdcutos
        $ventaProductos = VentaProducto::with('producto')
            ->whereHas('venta', function ($query) use ($sucursalId, $startDate, $endDate) {
                $query->where('sucursal_id', $sucursalId)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->where('estado', '!=', 'eliminada') 
                    ->where('visible', true);
            })
            ->get();
            

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
            'cantidadCortes' => $cantidadCortes,
            'sobrantes' => $sobrantes,
            'sobrantesInventario' => $sobrantesInventario,
            'categoriasInventario' => $categoriasInventario,
            'cantidadDeCortes' => $cantidadDeCortes,
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
            ->where('estado', '!=', 'eliminada') 
            ->where('visible', true)
            ->orderBy('created_at', 'asc')
            ->get();

        
        $ventasEfectivo = Venta::where('sucursal_id', $sucursalId)
            ->whereDate('created_at', Carbon::today())
            ->where('metodo_pago', 'efectivo')
            ->where('visible', true)
            ->get();

      
        $ventasTarjeta = Venta::where('sucursal_id', $sucursalId)
            ->whereDate('created_at', Carbon::today())
            ->where('metodo_pago', 'tarjeta')
            ->where('visible', true)
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
        $inventario = Inventario::where('sucursal_id', $sucursalId)->get();
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
            'sobrantesInventario' => $sobrantesInventario,
            'categoriasInventario' => $categoriasInventario,
            'cantidadDeCortes' => $cantidadDeCortes,
            'cortes' => $cortes,
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

