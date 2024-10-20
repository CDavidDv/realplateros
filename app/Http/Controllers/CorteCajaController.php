<?php

namespace App\Http\Controllers;

use App\Models\CorteCaja;
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
        
        // Verifica si ya existe un corte de caja para esa sucursal y fecha
        if (CorteCaja::where('sucursal_id', $usuario->sucursal_id)
                    ->where('fecha', $request->fecha)
                    ->exists()) {
            return back()->with('error', 'Ya existe un corte de caja para esta fecha.');
        } else {
            // Crea un nuevo corte de caja
            CorteCaja::create([
                'sucursal_id' => $usuario->sucursal_id,
                'fecha' => $request->fecha,
                'dinero_inicio' => $request->dinero_inicio,
                'usuario_id' => $usuario->id,
            ]);
            return redirect()->route('corte-caja')->with('success', 'Cantidad inicial guardada correctamente.');
            
        }
    }


    public function guardarFinal(Request $request)
    {
        $usuario = auth()->user();
        
        $request->validate([
            'fecha' => 'required|date',
            'dinero_final' => 'required|numeric|min:0',
        ]);

        // Verifica si ya existe un corte de caja para esa sucursal y fecha
        $corteCaja = CorteCaja::where('sucursal_id', $usuario->sucursal_id)
                            ->where('fecha', $request->fecha)
                            ->first();

        // Si no existe el corte de caja, devuelve un error
        if (!$corteCaja) {
            return back()->with('error', 'No existe un corte de caja para esta fecha. Debe guardar la cantidad inicial primero.');
        } 
        
        // Si ya existe un corte final, muestra un mensaje de error
        if (!is_null($corteCaja->dinero_final)) {
            return back()->with('error', 'Ya existe un corte final registrado para esta fecha.');
        }

        // Si existe el corte de caja y no tiene corte final, actualiza el registro
        $corteCaja->dinero_final = $request->dinero_final;
        $corteCaja->usuario_id = $usuario->id; // Actualiza el usuario si es necesario
        $corteCaja->save();

        return back()->with('success', 'Cantidad final guardada correctamente.');
    }



    public function filtro(Request $request)
    {
        $user = Auth::user();
        $sucursalId = $user->sucursal_id;
        
        $filter = $request->input('filter');
        $value = $request->input('value');

        // Filtrado según el tipo (día, semana, mes)
        if ($filter === 'day') {
            $ventas = Venta::where('sucursal_id', $sucursalId)
                ->whereDate('created_at', $value)
                ->get();
        } elseif ($filter === 'week') {
            $ventas = Venta::where('sucursal_id', $sucursalId)
                ->whereBetween('created_at', [Carbon::parse($value)->startOfWeek(), Carbon::parse($value)->endOfWeek()])
                ->get();
        } elseif ($filter === 'month') {
            $ventas = Venta::where('sucursal_id', $sucursalId)
                ->whereMonth('created_at', Carbon::parse($value)->month)
                ->get();
        } else {
            return response()->json(['error' => 'Filtro no válido.'], 400);
        }

        // Aquí podrías incluir cálculos adicionales como los pagos en efectivo o tarjeta
        $cashPayments = $ventas->where('metodo_pago', 'efectivo')->sum('total');
        $cardPayments = $ventas->where('metodo_pago', 'tarjeta')->sum('total');
        
        // También podrías obtener los productos usados y la caja inicial y final
        $productosVendidos = VentaProducto::select('producto_id', DB::raw('SUM(cantidad) as total_vendido'))
            ->whereHas('venta', function ($query) use ($sucursalId, $filter, $value) {
                $query->where('sucursal_id', $sucursalId);
                
                if ($filter === 'day') {
                    $query->whereDate('created_at', $value);
                } elseif ($filter === 'week') {
                    $query->whereBetween('created_at', [Carbon::parse($value)->startOfWeek(), Carbon::parse($value)->endOfWeek()]);
                } elseif ($filter === 'month') {
                    $query->whereMonth('created_at', Carbon::parse($value)->month);
                }
            })
            ->groupBy('producto_id')
            ->with('producto')
            ->get();
        
        $initialCash = CorteCaja::where('sucursal_id', $sucursalId)
            ->whereDate('created_at', $value)
            ->value('dinero_inicio');
        
        $finalCash = CorteCaja::where('sucursal_id', $sucursalId)
            ->whereDate('created_at', $value)
            ->value('dinero_final');

            

        return Inertia::render('Corte/index', [
            'cashPayments' => $cashPayments,
            'cardPayments' => $cardPayments,
            'productsUsed' => $productosVendidos,
            'initialCash' => $initialCash,
            'finalCash' => $finalCash
        ]);
        
    }


    public function corte()
    {   
        // Obtener usuario autenticado
        $user = Auth::user();
        $sucursalId = $user->sucursal_id;

        // Obtener todas las ventas de la sucursal
        $ventas = Venta::where('sucursal_id', $sucursalId)
            ->whereDate('created_at', Carbon::today())
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
        $corte = CorteCaja::where('sucursal_id', $sucursalId)
           ->whereDate('created_at', Carbon::today())
           ->first();

        
            return Inertia::render('Corte/index', [
                'inventario' => $inventario,
                'ventas' => $ventas,
                'productosVendidos' => $productosVendidos,
                'corte' => $corte
            ]);
    }
}
