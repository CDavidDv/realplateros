<?php

use App\Http\Controllers\AlmacenController;
use App\Http\Controllers\CheckInCheckOutController;
use App\Http\Controllers\CorteCajaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EstimacionesController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\PrintController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SobrantesController;
use App\Http\Controllers\SucursalController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\VentaController;
use Illuminate\Support\Facades\Route;
use SebastianBergmann\CodeCoverage\Report\Html\Dashboard;

// NO AUTH ROUTES 
Route::get('/', [DashboardController::class, 'index']);


Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified', ])->group(function () {
    // AUTH ROUTES
    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name("dashboard");
    Route::post('/ventas', [VentaController::class, 'procesarVenta'])->name('ventas.procesar');
    Route::post('/ventas/editar', [VentaController::class, 'editarVenta'])->name('ventas.editar');
    Route::post('/asignar', [VentaController::class, 'procesarTicket'])->name('ticket.procesar');

    Route::get('/hornear', [DashboardController::class, 'hornear'])->name("hornear");
    Route::post('/hornear', [DashboardController::class, 'procesarPastesHorneados']);
    Route::post('/iniciar-horneado', [DashboardController::class, 'iniciar_horneado']);
    Route::post('/check-estado', [DashboardController::class, 'check_estado']);

    Route::get('/tickets', [InventarioController::class, 'tickets'])->name("tickets");
    Route::post('/tickets/cancelar', [InventarioController::class, 'ticketsCancel'])->name("tickets.cancelar");
    Route::post('/tickets/buscar', [InventarioController::class, 'ticketsBuscar'])->name("tickets.buscar");

    Route::get('/inventario', [InventarioController::class, 'inventario'])->name("inventario");
    Route::put('/tickets/{id}', [InventarioController::class, 'confirmTicket'])->name('confirmTicket'); 
    Route::post('/categorias', [InventarioController::class, 'addCategorias'])->name("addCategoria");

    Route::post('/inventario', [InventarioController::class, 'store'])->name('inventario.store');

    Route::post('/estimaciones', [EstimacionesController::class, 'store'])->name('estimaciones.store');
    Route::post('/verify-admin-password', [DashboardController::class, 'verifyAdminPassword'])->name('verify-admin-password');

    Route::post('/sobrantes', [SobrantesController::class, 'store'])->name('sobrantes.store');

    Route::post('/gastos', [InventarioController::class, 'gastos'])->name('inventario.gastos');
    Route::post('/registro', [InventarioController::class, 'registro'])->name('inventario.registro');
    Route::put('/inventario/{inventario}', [InventarioController::class, 'update'])->name('inventario.update');
    Route::delete('/inventario/{id}', [InventarioController::class, 'destroy'])->name('inventario.destroy');
    Route::delete('/categorias/{tipo}', [InventarioController::class, 'destroyCategoria'])->name('categoria.destroy');

    Route::get('/checador', [CheckInCheckOutController::class, 'index'])->name("checador");
    Route::post('/search-user-check-ins', [CheckInCheckOutController::class, 'searchCheckInsOuts'])->name("search.checador");
    Route::post('/checkInOut', [CheckInCheckOutController::class, 'checkInOut'])->name('checkInOut');

    Route::post('/checkout/{usuario}/{sucursal}', [CheckInCheckOutController::class, 'checkOut'])->name('checkout');

    Route::get('/personal', [UsuarioController::class, 'index'])->name("personal");
    Route::resource('users', UsuarioController::class);
    Route::resource('sucursales', SucursalController::class)->parameters([
        'sucursales' => 'sucursal',  // Esto asegura que el parámetro sea 'sucursal'
    ]);


    
    
    Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
    Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
    Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
    
    Route::get('/corte-caja/filtro', [CorteCajaController::class, 'corte'])->name('corte-caja');
    Route::get('/corte-caja', [CorteCajaController::class, 'corte'])->name("corte-caja");
    
    Route::post('/corte-caja/guardar-inicial', [CorteCajaController::class, 'guardarInicial'])->name('corte-caja.guardar-inicial');
    Route::post('/corte-caja/guardar-final', [CorteCajaController::class, 'guardarFinal'])->name('corte-caja.guardar-final');
    
    Route::post('/corte-caja', [CorteCajaController::class, 'filtro'])->name('corte-caja.filtro');


    Route::post('/print-ticket', [PrintController::class, 'printTicket']);
    Route::post('/search-check-ins', [CheckInCheckOutController::class, 'search'])->name('search-check-ins');

    
    Route::get('/almacen', [AlmacenController::class, 'almacen'])->name("almacen");
});
