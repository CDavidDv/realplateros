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
            $user = $request->user();
            $sucursalId = $user->sucursal_id;

            // Cargar roles una sola vez
            if (!$user->relationLoaded('roles')) {
                $user->load('roles');
            }
            $rolesCollection = $user->roles;

            return array_merge(parent::share($request), [
                // Flash messages para que back()->with() funcione con Inertia
                'flash' => [
                    'success' => fn () => $request->session()->get('success'),
                    'error' => fn () => $request->session()->get('error'),
                ],

                'auth' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'sucursal_id' => $user->sucursal_id,
                        'es_almacen' => $user->es_almacen,
                        'roles' => $rolesCollection->map(function($role) {
                            return ['name' => $role->name];
                        })
                    ],
                ],
                'user.roles' => $rolesCollection->pluck('name'),
                'notificaciones' => fn () => [
                    'faltantes' => ControlProduccion::select([
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
                        ->get(),
                    'horneados' => ControlProduccion::select([
                            'id', 'paste_id', 'sucursal_id', 'estado', 'created_at',
                            'cantidad_horneada', 'tiempo_inicio_horneado',
                            'hora_notificacion', 'dia_notificacion'
                        ])
                        ->with(['paste:id,nombre,tipo', 'sucursal:id,nombre'])
                        ->where('sucursal_id', $sucursalId)
                        ->whereIn('estado', ['horneando', 'en_espera', 'vendido'])
                        ->whereNotNull('tiempo_inicio_horneado')
                        ->orderBy('created_at', 'desc')
                        ->limit(100)
                        ->get(),
                ],

                'inventario' => fn () => Inventario::select(['id', 'nombre', 'tipo', 'cantidad', 'sucursal_id'])
                    ->where('sucursal_id', $sucursalId)
                    ->get(),
                'estimacionesHoy' => fn () => Estimaciones::select(['id', 'inventario_id', 'cantidad', 'hora', 'dia', 'sucursal_id'])
                    ->with(['inventario:id,nombre,tipo,cantidad'])
                    ->where('sucursal_id', $sucursalId)
                    ->where('dia', Carbon::now()->locale('es')->dayName)
                    ->get(),
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
