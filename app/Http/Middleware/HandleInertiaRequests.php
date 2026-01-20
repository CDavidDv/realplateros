<?php

namespace App\Http\Middleware;

use App\Models\Hornos;
use App\Models\ControlProduccion;
use App\Models\Inventario;
use App\Models\Estimaciones;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Defines the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function share(Request $request): array
    {
        //si el usuario esta autenticado
        if ($request->user()) {
            $sucursalId = $request->user()->sucursal_id;
            
            // Obtener inventario de la sucursal
            $inventario = Inventario::select(['id', 'nombre', 'tipo', 'cantidad', 'sucursal_id'])
                ->where('sucursal_id', $sucursalId)
                ->get();
            
            // Obtener estimaciones del día actual
            $estimacionesHoy = Estimaciones::select(['id', 'inventario_id', 'cantidad', 'hora', 'dia', 'sucursal_id'])
                ->with(['inventario:id,nombre,tipo,cantidad'])
                ->where('sucursal_id', $sucursalId)
                ->where('dia', Carbon::now()->locale('es')->dayName)
                ->get();

            // Obtener notificaciones faltantes
            $notificacionesFaltantes = ControlProduccion::select([
                    'id', 'paste_id', 'sucursal_id', 'estado', 'created_at',
                    'cantidad', 'cantidad_horneada', 'cantidad_vendida',
                    'tiempo_inicio_horneado', 'hora_ultima_venta',
                    'hora_notificacion', 'dia_notificacion'
                ])
                ->with(['paste:id,nombre,tipo', 'sucursal:id,nombre'])
                ->where('sucursal_id', $sucursalId)
                ->whereIn('estado', ['pendiente', 'horneando', 'en_espera', 'vendido'])
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get();

            // Obtener notificaciones horneados
            $notificacionesHorneados = ControlProduccion::select([
                    'id', 'paste_id', 'sucursal_id', 'estado', 'created_at',
                    'cantidad_horneada', 'tiempo_inicio_horneado',
                    'hora_notificacion', 'dia_notificacion'
                ])
                ->with(['paste:id,nombre,tipo', 'sucursal:id,nombre'])
                ->where('sucursal_id', $sucursalId)
                ->whereIn('estado', ['horneando', 'en_espera', 'vendido'])
                ->whereNotNull('tiempo_inicio_horneado')
                ->orderBy('created_at', 'desc')
                ->get();

            return array_merge(parent::share($request), [
                // Flash messages para que back()->with() funcione con Inertia
                'flash' => [
                    'success' => fn () => $request->session()->get('success'),
                    'error' => fn () => $request->session()->get('error'),
                ],

                'auth' => [
                    'user' => $request->user() ? [
                        'id' => $request->user()->id,
                        'name' => $request->user()->name,
                        'email' => $request->user()->email,
                        'sucursal_id' => $request->user()->sucursal_id,
                        'es_almacen' => $request->user()->es_almacen,
                        'roles' => $request->user()->roles->map(function($role) {
                            return ['name' => $role->name];
                        })
                    ] : null,
                ],
                'user.roles' => $request->user() ? $request->user()->roles->pluck('name') : [],
                'notificaciones' => [
                    'faltantes' => $notificacionesFaltantes,
                    'horneados' => $notificacionesHorneados
                ],
                
                'inventario' => $inventario,
                'estimacionesHoy' => $estimacionesHoy
            ]);
        }

        return array_merge(parent::share($request), [
            // Flash messages para que back()->with() funcione con Inertia
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'auth' => [
                'user' => null,
            ],
            'notificaciones' => [
                'faltantes' => collect([]),
                'horneados' => collect([])
            ],
            'inventario' => collect([]),
            'estimacionesHoy' => collect([])
        ]);
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, \Closure $next)
    {
        // Si la petición espera JSON, no procesar con Inertia
        if ($request->expectsJson() || $request->header('Accept') === 'application/json') {
            return $next($request);
        }

        return parent::handle($request, $next);
    }
}
