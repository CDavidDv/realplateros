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
            $sucursal_id = $request->user()->sucursal_id;
            $horno = Hornos::where('sucursal_id', $sucursal_id)->first();
            
            // Obtener inventario y estimaciones para las notificaciones globales
            $inventario = Inventario::where('sucursal_id', $sucursal_id)->get();
            $diaHoy = Carbon::now()->locale('es')->dayName;
            $estimacionesHoy = Estimaciones::where('sucursal_id', $sucursal_id)
                ->where('dia', $diaHoy)
                ->get();
        } else {
            $horno = null;
            $inventario = collect([]);
            $estimacionesHoy = collect([]);
        }

        return array_merge(parent::share($request), [
            'user.roles' => $request->user() ? $request->user()->roles->pluck('name') : [],
            'user.permissions' => $request->user() ? $request->user()->getPermissionsViaRoles()->pluck('name') : [],
            'flash' => [
                'success' => session('success'),
                'error' => session('error'),
                'message' => fn () => $request->session()->get('message')
            ],
            'horno' => $horno ? $horno->id : null,
            'auth' => [
                'user' => $request->user(),
            ],
            'notificaciones' => [
                'faltantes' => $request->user() ? ControlProduccion::with(['horno', 'paste'])
                    ->where('sucursal_id', $request->user()->sucursal_id)
                    ->where('dia_notificacion', Carbon::now()->locale('es')->dayName)                    
                    ->get() : [],
                'horneados' => $request->user() ? ControlProduccion::with(['horno', 'paste'])
                    ->where('sucursal_id', $request->user()->sucursal_id)
                    ->whereIn('estado', ['horneando', 'en_espera'])
                    ->where('dia_notificacion', Carbon::now()->locale('es')->dayName)                    
                    ->get() : []
            ],
            'inventario' => $inventario,
            'estimacionesHoy' => $estimacionesHoy
        ]);
    }
}
