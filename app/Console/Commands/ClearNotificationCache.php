<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use App\Models\User;

class ClearNotificationCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:clear-notifications {--sucursal-id= : ID específico de sucursal}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Limpiar caché de notificaciones para optimizar rendimiento';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $sucursalId = $this->option('sucursal-id');
        
        if ($sucursalId) {
            // Limpiar caché de una sucursal específica
            $this->clearSucursalCache($sucursalId);
            $this->info("Caché de notificaciones limpiado para sucursal ID: {$sucursalId}");
        } else {
            // Limpiar caché de todas las sucursales
            $sucursales = User::distinct()->pluck('sucursal_id')->filter();
            
            foreach ($sucursales as $sucId) {
                $this->clearSucursalCache($sucId);
            }
            
            $this->info("Caché de notificaciones limpiado para todas las sucursales");
        }
        
        return Command::SUCCESS;
    }
    
    /**
     * Limpiar caché de una sucursal específica
     */
    private function clearSucursalCache($sucursalId)
    {
        $patterns = [
            "notificaciones_faltantes_sucursal_{$sucursalId}",
            "notificaciones_horneados_sucursal_{$sucursalId}",
            "inventario_sucursal_{$sucursalId}",
            "estimaciones_hoy_sucursal_{$sucursalId}"
        ];
        
        foreach ($patterns as $pattern) {
            Cache::forget($pattern);
        }
    }
}
