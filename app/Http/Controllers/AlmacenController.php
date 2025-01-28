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
        if (Auth::user()->hasRole('almacen')) {
            //SELECT DISTINCT `tipo` FROM `inventarios`
            $categorias = Inventario::select('tipo')->distinct()->get();
            $inventarioAlmcane = Inventario::where('sucursal_id', 0)->get();
            return Inertia::render('Almacen/index', [
                'categorias' => $categorias
            ]);
        }else{
            
            return redirect()->route('dashboard');
        }

    }
}
