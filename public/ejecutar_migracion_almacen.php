<?php
/**
 * Script para ejecutar la migración de es_almacen
 *
 * ⚠️ IMPORTANTE: ELIMINA ESTE ARCHIVO DESPUÉS DE EJECUTARLO
 *
 * Uso:
 * 1. Sube este archivo a la carpeta public/
 * 2. Accede a: https://tudominio.com/ejecutar_migracion_almacen.php
 * 3. Verifica que se ejecutó correctamente
 * 4. ELIMINA este archivo inmediatamente por seguridad
 */

// Verificar que no esté en producción sin protección
// Descomenta y configura una contraseña si lo prefieres:
// $password_proteccion = 'tu_contraseña_secreta';
// if (!isset($_GET['password']) || $_GET['password'] !== $password_proteccion) {
//     die('⛔ Acceso denegado');
// }

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "<html><head><meta charset='UTF-8'><title>Migración es_almacen</title>";
echo "<style>
    body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
    .success { color: green; background: #d4edda; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .error { color: red; background: #f8d7da; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .warning { color: orange; background: #fff3cd; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .info { color: blue; background: #d1ecf1; padding: 10px; border-radius: 5px; margin: 10px 0; }
    pre { background: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto; }
</style></head><body>";

echo "<h1>🚀 Migración: Campo es_almacen en Users</h1>";
echo "<hr>";

try {
    // Verificar si la columna ya existe
    if (Schema::hasColumn('users', 'es_almacen')) {
        echo "<div class='warning'>⚠️ La columna 'es_almacen' ya existe en la tabla users. No es necesario ejecutar la migración.</div>";

        // Mostrar información actual
        $usuariosAlmacen = DB::table('users')->where('es_almacen', true)->count();
        echo "<div class='info'>ℹ️ Usuarios de almacén actuales: <strong>{$usuariosAlmacen}</strong></div>";

        $usuarios = DB::table('users')
            ->select('id', 'name', 'email', 'sucursal_id', 'es_almacen')
            ->where('es_almacen', true)
            ->get();

        if ($usuarios->count() > 0) {
            echo "<h3>Usuarios de almacén:</h3>";
            echo "<pre>";
            foreach ($usuarios as $usuario) {
                echo "ID: {$usuario->id} | Nombre: {$usuario->name} | Email: {$usuario->email} | Sucursal: {$usuario->sucursal_id}\n";
            }
            echo "</pre>";
        }
    } else {
        echo "<div class='info'>🔄 Ejecutando migración...</div>";

        // Paso 1: Agregar la columna
        DB::statement("ALTER TABLE users ADD COLUMN es_almacen TINYINT(1) NOT NULL DEFAULT 0 AFTER active");
        echo "<div class='success'>✅ Paso 1: Columna 'es_almacen' agregada correctamente</div>";

        // Paso 2: Actualizar usuarios con sucursal_id = 0
        $updated1 = DB::table('users')
            ->where('sucursal_id', 0)
            ->update(['es_almacen' => true]);
        echo "<div class='success'>✅ Paso 2: Actualizados {$updated1} usuario(s) con sucursal_id = 0</div>";

        // Paso 3: Actualizar usuarios con rol 'almacen'
        $usersWithAlmacenRole = DB::table('model_has_roles')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('roles.name', 'almacen')
            ->where('model_has_roles.model_type', 'App\\Models\\User')
            ->pluck('model_has_roles.model_id');

        if ($usersWithAlmacenRole->isNotEmpty()) {
            $updated2 = DB::table('users')
                ->whereIn('id', $usersWithAlmacenRole)
                ->update(['es_almacen' => true]);
            echo "<div class='success'>✅ Paso 3: Actualizados {$updated2} usuario(s) con rol 'almacen'</div>";
        } else {
            echo "<div class='info'>ℹ️ Paso 3: No se encontraron usuarios con rol 'almacen'</div>";
        }

        // Verificación final
        $totalAlmacen = DB::table('users')->where('es_almacen', true)->count();
        echo "<div class='success'>✅ Migración completada exitosamente</div>";
        echo "<div class='info'>ℹ️ Total de usuarios de almacén: <strong>{$totalAlmacen}</strong></div>";

        // Mostrar usuarios actualizados
        $usuarios = DB::table('users')
            ->select('id', 'name', 'email', 'sucursal_id', 'es_almacen')
            ->where('es_almacen', true)
            ->get();

        if ($usuarios->count() > 0) {
            echo "<h3>Usuarios actualizados:</h3>";
            echo "<pre>";
            foreach ($usuarios as $usuario) {
                echo "ID: {$usuario->id} | Nombre: {$usuario->name} | Email: {$usuario->email} | Sucursal: {$usuario->sucursal_id}\n";
            }
            echo "</pre>";
        }
    }

    echo "<hr>";
    echo "<div class='warning'><strong>⚠️ IMPORTANTE: ELIMINA ESTE ARCHIVO AHORA POR SEGURIDAD</strong></div>";
    echo "<div class='info'>Archivo a eliminar: <code>/public/ejecutar_migracion_almacen.php</code></div>";

} catch (Exception $e) {
    echo "<div class='error'>❌ Error al ejecutar la migración:</div>";
    echo "<div class='error'><strong>Mensaje:</strong> " . $e->getMessage() . "</div>";
    echo "<div class='error'><strong>Archivo:</strong> " . $e->getFile() . "</div>";
    echo "<div class='error'><strong>Línea:</strong> " . $e->getLine() . "</div>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "</body></html>";
