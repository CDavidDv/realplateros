<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Venta;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function store(Request $request)
    {
        // Asume que ya tienes la venta registrada
        $venta = Venta::find($request->venta_id);

        if (!$venta) {
            return response()->json(['error' => 'Venta no encontrada'], 404);
        }

        // Generar el número de ticket (puedes personalizar esto)
        $numero_ticket = 'TICKET-' . time();

        // Crear el ticket
        $ticket = Ticket::create([
            'venta_id' => $venta->id,
            'numero_ticket' => $numero_ticket,
            'total' => $venta->total,
            'creado_por' => auth()->user()->id,
            'sucursal_id' => $venta->sucursal_id,
            'metodo_pago' => $venta->metodo_pago,
        ]);

        return response()->json(['ticket' => $ticket], 201);
    }
}
