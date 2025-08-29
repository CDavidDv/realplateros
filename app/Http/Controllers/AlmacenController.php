<?php

namespace App\Http\Controllers;

use App\Models\Inventario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AlmacenController extends Controller
{
    public function almacen()
    {
        // El middleware 'role:almacen' ya verifica que el usuario tenga el rol correcto
        //SELECT DISTINCT `tipo` FROM `inventarios`
        $categorias = Inventario::select('tipo')->distinct()->get();
        $inventarioAlmcane = Inventario::where('sucursal_id', 0)->get();
        return Inertia::render('Almacen/index', [
            'categorias' => $categorias
        ]);
    }
}
