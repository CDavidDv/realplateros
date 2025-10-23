<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        

        Fortify::loginView(function () {
            $sucursales = Sucursal::where('id', '!=', 0)->get();
            
            return Inertia::render('Auth/Login', [
                'sucursales' => $sucursales
            ]);
        });

        RateLimiter::for('login', function (Request $request) {
            
            $user = User::where('email', $request->input(Fortify::username()))->first();

            // Verifica que el usuario existe y la contraseña es correcta
            if ($request->input('sucursal_id') && $user && Hash::check($request->input('password'), $user->password)) {
                if($user->sucursal_id !== 0){
                    $user->sucursal_id = $request->input('sucursal_id');
                    $user->save();
                }
            } 


            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        // Redirección personalizada después del login según el rol
        Fortify::redirects('login', function () {
            $user = Auth::user();

            if ($user->hasRole('almacen')) {
                return route('almacen');
            }

            if ($user->hasRole('gestor')) {
                return route('gestor-ventas');
            }

            // Para otros roles, ir al dashboard por defecto
            return route('dashboard');
        });
    }
}
