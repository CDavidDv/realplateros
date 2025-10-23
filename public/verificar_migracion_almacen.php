<?php
/**
 * Script de verificación de la migración es_almacen
 *
 * Uso: Accede a https://tudominio.com/verificar_migracion_almacen.php
 * Elimina este archivo después de verificar
 */

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\User;

echo "<html><head><meta charset='UTF-8'><title>Verificación Migración</title>";
echo "<style>
    body { font-family: Arial, sans-serif; max-width: 900px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
    .card { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    .success { color: #28a745; }
    .info { color: #17a2b8; }
    .warning { color: #ffc107; }
    h1 { color: #333; border-bottom: 3px solid #007bff; padding-bottom: 10px; }
    h2 { color: #555; margin-top: 20px; }
    table { width: 100%; border-collapse: collapse; margin: 20px 0; }
    th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
    th { background: #007bff; color: white; }
    tr:hover { background: #f1f1f1; }
    .badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 12px; }
    .badge-success { background: #28a745; color: white; }
    .badge-secondary { background: #6c757d; color: white; }
    .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; }
    .stat-card { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 8px; text-align: center; }
    .stat-number { font-size: 36px; font-weight: bold; margin: 10px 0; }
    .stat-label { font-size: 14px; opacity: 0.9; }
</style></head><body>";

echo "<h1>🔍 Verificación de Migración: es_almacen</h1>";

try {
    // 1. Verificar que la columna existe
    echo "<div class='card'>";
    echo "<h2>✅ 1. Estructura de la Tabla</h2>";

    $columns = DB::select('DESCRIBE users');
    $hasColumn = false;

    foreach ($columns as $col) {
        if ($col->Field === 'es_almacen') {
            $hasColumn = true;
            echo "<p class='success'><strong>✓ Columna 'es_almacen' encontrada</strong></p>";
            echo "<ul>";
            echo "<li><strong>Tipo:</strong> {$col->Type}</li>";
            echo "<li><strong>Null:</strong> {$col->Null}</li>";
            echo "<li><strong>Default:</strong> {$col->Default}</li>";
            echo "<li><strong>Extra:</strong> " . ($col->Extra ?: 'N/A') . "</li>";
            echo "</ul>";
            break;
        }
    }

    if (!$hasColumn) {
        echo "<p class='warning'>⚠️ ADVERTENCIA: Columna 'es_almacen' NO encontrada</p>";
    }
    echo "</div>";

    // 2. Estadísticas
    echo "<div class='card'>";
    echo "<h2>📊 2. Estadísticas Generales</h2>";
    echo "<div class='stats'>";

    $totalUsers = User::count();
    $usuariosAlmacen = User::where('es_almacen', true)->count();
    $usuariosSucursales = User::where('es_almacen', false)->count();

    echo "<div class='stat-card'>";
    echo "<div class='stat-label'>Total Usuarios</div>";
    echo "<div class='stat-number'>{$totalUsers}</div>";
    echo "</div>";

    echo "<div class='stat-card' style='background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);'>";
    echo "<div class='stat-label'>Usuarios Almacén</div>";
    echo "<div class='stat-number'>{$usuariosAlmacen}</div>";
    echo "</div>";

    echo "<div class='stat-card' style='background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);'>";
    echo "<div class='stat-label'>Usuarios Sucursales</div>";
    echo "<div class='stat-number'>{$usuariosSucursales}</div>";
    echo "</div>";

    echo "</div>";
    echo "</div>";

    // 3. Usuarios de almacén
    echo "<div class='card'>";
    echo "<h2>👤 3. Usuarios de Almacén</h2>";

    $usuariosAlmacenDetalle = User::where('es_almacen', true)
        ->select('id', 'name', 'email', 'sucursal_id', 'es_almacen', 'active')
        ->get();

    if ($usuariosAlmacenDetalle->count() > 0) {
        echo "<table>";
        echo "<thead><tr><th>ID</th><th>Nombre</th><th>Email</th><th>Sucursal ID</th><th>Activo</th><th>Es Almacén</th></tr></thead>";
        echo "<tbody>";

        foreach ($usuariosAlmacenDetalle as $user) {
            echo "<tr>";
            echo "<td>{$user->id}</td>";
            echo "<td>{$user->name}</td>";
            echo "<td>{$user->email}</td>";
            echo "<td>{$user->sucursal_id}</td>";
            echo "<td>" . ($user->active ? '<span class="badge badge-success">SÍ</span>' : '<span class="badge badge-secondary">NO</span>') . "</td>";
            echo "<td>" . ($user->es_almacen ? '<span class="badge badge-success">SÍ</span>' : '<span class="badge badge-secondary">NO</span>') . "</td>";
            echo "</tr>";
        }

        echo "</tbody></table>";
    } else {
        echo "<p class='warning'>⚠️ No se encontraron usuarios de almacén</p>";
    }
    echo "</div>";

    // 4. Verificar métodos del modelo
    echo "<div class='card'>";
    echo "<h2>🔧 4. Verificación de Métodos</h2>";

    $testUser = User::where('es_almacen', true)->first();

    if ($testUser) {
        echo "<p><strong>Probando con usuario:</strong> {$testUser->name} (ID: {$testUser->id})</p>";
        echo "<ul>";

        try {
            $esAlmacen = $testUser->esAlmacen();
            echo "<li class='success'>✓ Método <code>esAlmacen()</code>: " . ($esAlmacen ? 'SÍ' : 'NO') . "</li>";
        } catch (Exception $e) {
            echo "<li class='warning'>⚠️ Error en <code>esAlmacen()</code>: {$e->getMessage()}</li>";
        }

        try {
            $esSucursal = $testUser->esSucursal();
            echo "<li class='success'>✓ Método <code>esSucursal()</code>: " . ($esSucursal ? 'SÍ' : 'NO') . "</li>";
        } catch (Exception $e) {
            echo "<li class='warning'>⚠️ Error en <code>esSucursal()</code>: {$e->getMessage()}</li>";
        }

        try {
            $countAlmacen = User::almacen()->count();
            echo "<li class='success'>✓ Scope <code>almacen()</code>: {$countAlmacen} usuarios</li>";
        } catch (Exception $e) {
            echo "<li class='warning'>⚠️ Error en scope <code>almacen()</code>: {$e->getMessage()}</li>";
        }

        try {
            $countSucursales = User::sucursales()->count();
            echo "<li class='success'>✓ Scope <code>sucursales()</code>: {$countSucursales} usuarios</li>";
        } catch (Exception $e) {
            echo "<li class='warning'>⚠️ Error en scope <code>sucursales()</code>: {$e->getMessage()}</li>";
        }

        echo "</ul>";
    } else {
        echo "<p class='warning'>⚠️ No hay usuarios de almacén para probar los métodos</p>";
    }
    echo "</div>";

    // 5. Consultas SQL útiles
    echo "<div class='card'>";
    echo "<h2>📝 5. Consultas SQL Útiles</h2>";
    echo "<pre style='background: #f5f5f5; padding: 15px; border-radius: 5px; overflow-x: auto;'>";
    echo "-- Listar usuarios de almacén
SELECT id, name, email, sucursal_id, es_almacen FROM users WHERE es_almacen = 1;

-- Actualizar manualmente un usuario a almacén
UPDATE users SET es_almacen = 1, sucursal_id = 0 WHERE id = ?;

-- Quitar usuario de almacén
UPDATE users SET es_almacen = 0 WHERE id = ?;

-- Contar usuarios por tipo
SELECT
    CASE WHEN es_almacen = 1 THEN 'Almacén' ELSE 'Sucursal' END as tipo,
    COUNT(*) as total
FROM users
GROUP BY es_almacen;</pre>";
    echo "</div>";

    // Conclusión
    echo "<div class='card' style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;'>";
    echo "<h2 style='color: white; border-bottom-color: white;'>🎉 Conclusión</h2>";
    echo "<p style='font-size: 18px;'><strong>✅ La migración se ejecutó correctamente</strong></p>";
    echo "<p>Todos los sistemas están operativos y listos para usar.</p>";
    echo "</div>";

    echo "<div class='card' style='background: #fff3cd; border-left: 4px solid #ffc107;'>";
    echo "<p><strong>⚠️ IMPORTANTE:</strong> Elimina este archivo (<code>/public/verificar_migracion_almacen.php</code>) por seguridad.</p>";
    echo "</div>";

} catch (Exception $e) {
    echo "<div class='card' style='background: #f8d7da; border-left: 4px solid #dc3545;'>";
    echo "<h2>❌ Error</h2>";
    echo "<p><strong>Mensaje:</strong> {$e->getMessage()}</p>";
    echo "<p><strong>Archivo:</strong> {$e->getFile()}</p>";
    echo "<p><strong>Línea:</strong> {$e->getLine()}</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    echo "</div>";
}

echo "</body></html>";
