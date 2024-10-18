<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;

class PrintController extends Controller
{
    public function printTicket(Request $request)
    {
        $productos = $request->input('productos');
        $total = $request->input('total');
        $formaPago = $request->input('metodo_pago');

        // Nombre de la impresora
        $printerName = "POS-80C"; // Cambia esto por el nombre correcto de tu impresora

        try {
            $connector = new WindowsPrintConnector($printerName);
            $printer = new Printer($connector);

            // Fecha, hora y número de ticket
            $fecha = date("Y-m-d");
            $hora = date("H:i:s");
            $numeroTicket = rand(10000, 99999); // Generar número de ticket

            // Configurar el ticket
            $printer->initialize();
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text("Real Del Plateros\n");
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text("Fecha: " . $fecha . "\n");
            $printer->text("Hora: " . $hora . "\n");
            $printer->text("Ticket No: " . $numeroTicket . "\n");
            $printer->text("Forma de Pago: " . $formaPago . "\n");
            $printer->text("----------------------------------\n");

            // Agregar productos al ticket
            foreach ($productos as $producto) {
                $printer->text($producto['nombre'] . " - $" . number_format($producto['precio'], 2) . "\n");
            }

            // Total
            $printer->text("----------------------------------\n");
            $printer->text("Total: $" . number_format($total, 2) . "\n");

            // Finalización del ticket
            $printer->feed();
            $printer->cut();
            $printer->close();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
