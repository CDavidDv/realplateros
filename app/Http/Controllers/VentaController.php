<?php

namespace App\Http\Controllers;

use App\Models\Inventario;
use App\Models\Venta;
use App\Models\VentaProducto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use PhpParser\Node\Stmt\Return_;

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

    public function procesarVenta(Request $request)
{
    try {
        // Recibe los productos seleccionados
        $productos = $request->input('productos');
        $metodoPago = $request->input('metodo_pago');
        $total = $request->input('total');

        // Crear el registro de la venta
        $venta = new Venta();
        $venta->usuario_id = auth()->id();
        $venta->sucursal_id = auth()->user()->sucursal_id; // Acceder al sucursal_id del usuario
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
                $detalleVenta->sucursal_id = auth()->user()->sucursal_id; // Acceder al sucursal_id del usuario
                $detalleVenta->cantidad = $producto['ticketQuantity'];
                $detalleVenta->precio_unitario = $item->precio;
                $detalleVenta->save();
            }
        }

        // Retornar una respuesta JSON
        return redirect()->route('dashboard')->with('mensaje', 'Venta procesada correctamente');
    } catch (\Exception $e) {
        // Redirigir a la vista con un mensaje de error
        return redirect()->route('dashboard')->with('mensaje', 'Venta procesada incorrectamente');
    }
}



}
