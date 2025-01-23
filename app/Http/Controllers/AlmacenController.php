<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AlmacenController extends Controller
{
    public function almacen()
    {
        if (Auth::user()->hasRole('almacen')) {
            return Inertia::render('Almacen/index', [
                
            ]);
        }else{
            
            return redirect()->route('dashboard');
        }

    }
}
