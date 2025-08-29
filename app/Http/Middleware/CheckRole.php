<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        
        if (!$user->hasRole($role)) {
            // Redirigir según el rol del usuario
            if ($user->hasRole('almacen')) {
                return redirect()->route('almacen');
            }
            
            if ($user->hasRole('gestor')) {
                return redirect()->route('gestor-ventas');
            }
            
            // Para otros roles, ir al dashboard
            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}
