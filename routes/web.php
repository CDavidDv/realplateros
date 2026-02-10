<?php

use App\Http\Controllers\ActividadPersonalController;
use App\Http\Controllers\AlmacenController;
use App\Http\Controllers\CheckInCheckOutController;
use App\Http\Controllers\CorteCajaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EstimacionesController;
use App\Http\Controllers\HornoController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\PrintController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SobrantesController;
use App\Http\Controllers\SucursalController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\ControlProduccionController;
use App\Http\Controllers\GestorVentasController;
use App\Http\Controllers\NotificacionUmbralController;
use App\Http\Controllers\NotificacionPersonalController;
use Illuminate\Support\Facades\Route;
use SebastianBergmann\CodeCoverage\Report\Html\Dashboard;

// NO AUTH ROUTES 
Route::get('/', [DashboardController::class, 'index']);


Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified', ])->group(function () {
    // AUTH ROUTES
    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name("dashboard");
    Route::get('/api/contador-estados', [DashboardController::class, 'obtenerContadorEstados'])->name('api.contador.estados');
    Route::post('/ventas', [VentaController::class, 'procesarVenta'])->name('ventas.procesar');
    Route::post('/ventas/editar', [VentaController::class, 'editarVenta'])->name('ventas.editar');
    Route::post('/asignar', [VentaController::class, 'procesarTicket'])->name('ticket.procesar');

    // Rutas para consultar ventas por día
    Route::get('/ventas/dia', [VentaController::class, 'obtenerVentasDelDia'])->name('ventas.dia');
    Route::get('/ventas/resumen-dia', [VentaController::class, 'obtenerResumenVentasPorDia'])->name('ventas.resumen-dia');

    Route::get('/hornear', [DashboardController::class, 'hornear'])->name("hornear");
    Route::post('/hornear', [HornoController::class, 'procesarPastesHorneados']);
    Route::post('/iniciar-horneado', [HornoController::class, 'iniciar_horneado']);
    Route::post('/check-estado', [HornoController::class, 'check_estado']);
    Route::post('/crear-horno', [HornoController::class, 'crear_horno']);
    Route::post('/eliminar-horno', [HornoController::class, 'eliminar_horno']);
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
    Route::post('/inventario/filtro', [InventarioController::class, 'filtroInventario'])->name('inventario.filtro');
    Route::get('/inventario/filtro', function () {
        return redirect('/inventario');
    });


    Route::post('/control-produccion', [ControlProduccionController::class, 'obtenerNotificacionesFiltradas'])->name('control.produccion.notificaciones');
    
    
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

    // Rutas específicas para trabajadores de almacén
    Route::get('/personal/almacen/trabajadores', [UsuarioController::class, 'trabajadoresAlmacen'])->name('personal.almacen.trabajadores');
    Route::get('/personal/estadisticas', [UsuarioController::class, 'estadisticasPersonal'])->name('personal.estadisticas');

    // Rutas para actividad del personal
    Route::get('/actividad-personal', [ActividadPersonalController::class, 'index'])->name('actividad-personal');
    Route::post('/actividad-personal/filtro', [ActividadPersonalController::class, 'filtro'])->name('actividad-personal.filtro');

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
    Route::post('/corte-caja/crear-corte', [CorteCajaController::class, 'crearCorte'])->name('corte-caja.crear-corte');

    Route::post('/corte-caja', [CorteCajaController::class, 'filtro'])->name('corte-caja.filtro');


    Route::post('/print-ticket', [PrintController::class, 'printTicket']);
    Route::post('/search-check-ins', [CheckInCheckOutController::class, 'search'])->name('search-check-ins');


    Route::get('/almacen', [AlmacenController::class, 'almacen'])->name("almacen")->middleware('role:almacen');

    // Rutas para gráficas de almacén (solo admin de almacén)
    Route::get('/almacen/graficas/productos-por-sucursal', [AlmacenController::class, 'productosPorSucursal'])->middleware('role:almacen');
    Route::get('/almacen/graficas/movimientos-inventario', [AlmacenController::class, 'movimientosInventario'])->middleware('role:almacen');
    Route::get('/almacen/graficas/productos-individuales-por-sucursal', [AlmacenController::class, 'productosIndividualesPorSucursal'])->middleware('role:almacen');
    Route::get('/almacen/graficas/movimientos-productos-por-sucursal', [AlmacenController::class, 'movimientosProductosPorSucursal'])->middleware('role:almacen');

    // Hoja de corte almacén
    Route::get('/almacen/hoja-corte', [AlmacenController::class, 'hojaCorte'])->name('almacen.hoja-corte')->middleware('role:almacen');
    Route::post('/almacen/hoja-corte', [AlmacenController::class, 'hojaCorte'])->middleware('role:almacen');
    Route::get('/almacen/hoja-corte/export', [AlmacenController::class, 'hojaCorteExport'])->name('almacen.hoja-corte.export')->middleware('role:almacen');

    // Notificaciones de turno (personal) - basado en control_produccion
    Route::get('/api/notificaciones-personal', [NotificacionPersonalController::class, 'index'])->name('notificaciones-personal.index');
    Route::post('/api/notificaciones-personal/{id}/atender', [NotificacionPersonalController::class, 'atender'])->name('notificaciones-personal.atender');

    Route::get('/api/control-produccion', [ControlProduccionController::class, 'index']);
    
    // Rutas para control de producción
    Route::post('/control-produccion/iniciar-horneado', [ControlProduccionController::class, 'iniciarHorneado'])->name('control-produccion.iniciar-horneado');
    Route::post('/control-produccion/finalizar-horneado', [ControlProduccionController::class, 'finalizarHorneado'])->name('control-produccion.finalizar-horneado');
    Route::post('/control-produccion/registrar-venta', [ControlProduccionController::class, 'registrarVenta'])->name('control-produccion.registrar-venta');
    Route::get('/control-produccion/notificaciones', [ControlProduccionController::class, 'obtenerNotificacionesFiltradas'])->name('control-produccion.notificaciones');
    Route::get('/control-produccion/test', [ControlProduccionController::class, 'test'])->name('control-produccion.test');

    Route::post('/api/notificaciones/registrar', [NotificacionUmbralController::class, 'registrarNotificacion'])->name('notificaciones.registrar');
    Route::post('/api/notificaciones/actualizar', [NotificacionUmbralController::class, 'actualizarNotificaciones'])->name('notificaciones.actualizar');
    Route::get('/api/notificaciones/obtener', [NotificacionUmbralController::class, 'obtenerNotificacionesFiltradas'])->name('notificaciones.obtener');


    Route::get('/gestor-ventas', [GestorVentasController::class, 'index'])->name('gestor-ventas')->middleware('role:gestor');
    Route::post('/gestor-ventas/filtro', [GestorVentasController::class, 'filtro'])->name('gestor-ventas.filtro')->middleware('role:gestor');

    Route::get('/ventas', [GestorVentasController::class, 'ventas'])->name('ventas');
    Route::post('/ventas/eliminar', [GestorVentasController::class, 'eliminarVenta'])->name('ventas.eliminar');
    Route::post('/ventas/renumerar', [GestorVentasController::class, 'renumerarVentas'])->name('ventas.renumerar');
    Route::post('/ventas/renumerar-normales', [GestorVentasController::class, 'renumerarVentasNormales'])->name('ventas.renumerar-normales');
    Route::post('/ventas/renumerar-folios', [GestorVentasController::class, 'renumerarFolios'])->name('ventas.renumerar-folios');
    
    Route::post('/ventas/restaurar', [GestorVentasController::class, 'restaurarVenta'])->name('ventas.restaurar');
    Route::post('/ventas/crear', [GestorVentasController::class, 'crearVenta'])->name('ventas.crear');
    Route::put('/ventas/{id}', [GestorVentasController::class, 'actualizarVenta'])->name('ventas.actualizar');
    Route::get('/api/usuarios-sucursal/{sucursalId}', [GestorVentasController::class, 'getUsuariosSucursal']);
    Route::post('/api/ventas-filtradas', [GestorVentasController::class, 'getVentasFiltradas'])->name('ventas.filtradas');
    Route::post('/ventas/actualizar-visible', [GestorVentasController::class, 'actualizarVisible'])->name('ventas.actualizar-visible');
});

// Rutas para notificaciones de umbral
Route::middleware(['auth'])->group(function () {
    Route::get('/api/notificaciones', [NotificacionUmbralController::class, 'getNotificaciones']);
    Route::post('/api/notificaciones/actualizar-tiempo-horneado', [NotificacionUmbralController::class, 'actualizarTiempoHorneado']);
    Route::get('/api/inventario', [InventarioController::class, 'getInventario']);
});

//si no se encuentra el link regresar a la pagina principal
Route::fallback(function () {
    return redirect('/');
});