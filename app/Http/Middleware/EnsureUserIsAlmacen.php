<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware para verificar que el usuario sea de almacén
 *
 * Uso en rutas:
 * Route::middleware(['auth', 'almacen'])->group(function () {
 *     Route::get('/almacen', [AlmacenController::class, 'index']);
 * });
 *
 * Registro en app/Http/Kernel.php:
 * protected $middlewareAliases = [
 *     'almacen' => \App\Http\Middleware\EnsureUserIsAlmacen::class,
 * ];
 */
class EnsureUserIsAlmacen
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verificar que el usuario esté autenticado
        if (!$request->user()) {
            return redirect()->route('login');
        }

        // Verificar que el usuario sea de almacén
        if (!$request->user()->esAlmacen()) {
            abort(403, 'Acceso denegado. Esta sección es solo para personal de almacén.');
        }

        return $next($request);
    }
}
