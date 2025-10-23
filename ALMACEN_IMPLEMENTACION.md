# Implementación del Campo `es_almacen` en Users

## 📋 Descripción

Se ha agregado un campo booleano `es_almacen` a la tabla `users` para identificar fácilmente qué usuarios pertenecen al almacén.

## 🚀 Instalación

### Para Desarrollo (con acceso a consola)

```bash
php artisan migrate
```

### Para Producción (sin acceso a consola)

1. **Sube el archivo** `ejecutar_migracion_almacen.php` a la carpeta `public/`
2. **Accede vía navegador**: `https://tudominio.com/ejecutar_migracion_almacen.php`
3. **Verifica** que la migración se ejecutó correctamente
4. **ELIMINA** el archivo `ejecutar_migracion_almacen.php` inmediatamente por seguridad

#### Opcional: Protección con contraseña

Si prefieres proteger el script con contraseña, edita el archivo `ejecutar_migracion_almacen.php` y descomenta estas líneas:

```php
$password_proteccion = 'tu_contraseña_secreta';
if (!isset($_GET['password']) || $_GET['password'] !== $password_proteccion) {
    die('⛔ Acceso denegado');
}
```

Luego accede con: `https://tudominio.com/ejecutar_migracion_almacen.php?password=tu_contraseña_secreta`

## 📊 Estructura de Base de Datos

### Tabla: `users`

```sql
ALTER TABLE users ADD COLUMN es_almacen TINYINT(1) NOT NULL DEFAULT 0 AFTER role;
```

| Campo | Tipo | Default | Descripción |
|-------|------|---------|-------------|
| es_almacen | BOOLEAN | false | Indica si el usuario pertenece al almacén |

## 🔧 Uso en el Código

### 1. Modelo User

El modelo `User` ahora incluye:

#### Métodos Disponibles

```php
// Verificar si un usuario es de almacén
if ($user->esAlmacen()) {
    // Usuario es de almacén
}

// Verificar si un usuario es de sucursal normal
if ($user->esSucursal()) {
    // Usuario es de sucursal (no almacén)
}
```

#### Scopes (consultas)

```php
// Obtener solo usuarios de almacén
$usuariosAlmacen = User::almacen()->get();

// Obtener solo usuarios de sucursales normales
$usuariosSucursales = User::sucursales()->get();

// Combinar con otros filtros
$usuariosAlmacenActivos = User::almacen()
    ->where('active', true)
    ->orderBy('name')
    ->get();
```

#### Ejemplo en Controlador

```php
use App\Models\User;

class AlmacenController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Verificar si el usuario es de almacén
        if (!$user->esAlmacen()) {
            abort(403, 'Acceso denegado');
        }

        // Obtener todos los empleados de almacén
        $empleadosAlmacen = User::almacen()
            ->where('active', true)
            ->get();

        return view('almacen.index', compact('empleadosAlmacen'));
    }

    public function asignarEmpleado(Request $request)
    {
        // Crear un nuevo empleado de almacén
        $empleado = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'sucursal_id' => 0, // ID del almacén
            'es_almacen' => true, // Marcar como almacén
            'active' => true,
        ]);

        // Asignar rol
        $empleado->assignRole('almacen');

        return redirect()->back()->with('success', 'Empleado asignado al almacén');
    }
}
```

### 2. Middleware

Se creó el middleware `EnsureUserIsAlmacen` para proteger rutas.

#### Registrar el Middleware

Edita `app/Http/Kernel.php`:

```php
protected $middlewareAliases = [
    // ... otros middlewares
    'almacen' => \App\Http\Middleware\EnsureUserIsAlmacen::class,
];
```

#### Uso en Rutas

**Opción 1: Ruta individual**

```php
Route::get('/almacen', [AlmacenController::class, 'index'])
    ->middleware(['auth', 'almacen']);
```

**Opción 2: Grupo de rutas**

```php
Route::middleware(['auth', 'almacen'])->prefix('almacen')->group(function () {
    Route::get('/', [AlmacenController::class, 'index'])->name('almacen.index');
    Route::get('/inventario', [AlmacenController::class, 'inventario'])->name('almacen.inventario');
    Route::post('/transferencia', [AlmacenController::class, 'transferencia'])->name('almacen.transferencia');
});
```

### 3. Vistas Blade/Inertia

**En Blade:**

```blade
@if(Auth::user()->esAlmacen())
    <a href="{{ route('almacen.index') }}">Panel de Almacén</a>
@endif
```

**En Inertia (Vue):**

```vue
<template>
  <div v-if="$page.props.auth.user.es_almacen">
    <Link :href="route('almacen.index')">Panel de Almacén</Link>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
</script>
```

### 4. Seeders

Actualizar el seeder de almacén:

```php
// database/seeders/AlmacenRolSeeder.php

$user = User::create([
    'name' => 'almacen',
    'sucursal_id' => 0,
    'email' => 'almacen@example.com',
    'password' => Hash::make('password'),
    'es_almacen' => true, // ← Agregar esta línea
]);

$user->assignRole('almacen');
```

### 5. Políticas (Policies)

Crear una política para almacén:

```php
// app/Policies/AlmacenPolicy.php
namespace App\Policies;

use App\Models\User;

class AlmacenPolicy
{
    public function verInventario(User $user): bool
    {
        return $user->esAlmacen();
    }

    public function crearTransferencia(User $user): bool
    {
        return $user->esAlmacen();
    }

    public function aprobarTransferencia(User $user): bool
    {
        // Solo administradores pueden aprobar
        return $user->hasRole('admin');
    }
}
```

Registrar en `AuthServiceProvider`:

```php
protected $policies = [
    Almacen::class => AlmacenPolicy::class,
];
```

Uso en controlador:

```php
public function crearTransferencia(Request $request)
{
    $this->authorize('crearTransferencia', Almacen::class);

    // Lógica de transferencia...
}
```

## 📝 Ejemplos de Consultas SQL

### Consultas Útiles

```sql
-- Listar todos los usuarios de almacén
SELECT * FROM users WHERE es_almacen = 1;

-- Contar usuarios de almacén activos
SELECT COUNT(*) FROM users WHERE es_almacen = 1 AND active = 1;

-- Listar usuarios de almacén con sus roles
SELECT u.id, u.name, u.email, r.name as role
FROM users u
JOIN model_has_roles mhr ON u.id = mhr.model_id
JOIN roles r ON mhr.role_id = r.id
WHERE u.es_almacen = 1;

-- Actualizar manualmente un usuario para ser de almacén
UPDATE users SET es_almacen = 1 WHERE id = 123;

-- Quitar a un usuario del almacén
UPDATE users SET es_almacen = 0 WHERE id = 123;
```

## 🔄 Migración Automática

La migración actualiza automáticamente:

1. ✅ Usuarios con `sucursal_id = 0` → `es_almacen = true`
2. ✅ Usuarios con rol `'almacen'` → `es_almacen = true`

## ⚠️ Consideraciones

### Consistencia de Datos

Asegúrate de mantener la consistencia:

```php
// Al crear usuario de almacén
$user = User::create([
    'sucursal_id' => 0,      // ID de almacén
    'es_almacen' => true,    // Marcar como almacén
    // ...
]);
$user->assignRole('almacen'); // Rol de almacén

// Al crear usuario de sucursal
$user = User::create([
    'sucursal_id' => 1,      // ID de sucursal normal
    'es_almacen' => false,   // NO es almacén
    // ...
]);
$user->assignRole('trabajador'); // Rol de trabajador
```

### Performance

El campo `es_almacen` mejora el rendimiento porque:

- ✅ No requiere JOIN con tabla de roles
- ✅ Consultas más rápidas (índice en columna booleana)
- ✅ Fácil de cachear

Si tienes muchas consultas, considera agregar un índice:

```sql
ALTER TABLE users ADD INDEX idx_es_almacen (es_almacen);
```

## 🧪 Testing

Ejemplo de test:

```php
use Tests\TestCase;
use App\Models\User;

class AlmacenTest extends TestCase
{
    public function test_usuario_almacen_puede_acceder()
    {
        $user = User::factory()->create([
            'es_almacen' => true,
            'sucursal_id' => 0,
        ]);

        $this->actingAs($user)
            ->get(route('almacen.index'))
            ->assertStatus(200);
    }

    public function test_usuario_sucursal_no_puede_acceder()
    {
        $user = User::factory()->create([
            'es_almacen' => false,
            'sucursal_id' => 1,
        ]);

        $this->actingAs($user)
            ->get(route('almacen.index'))
            ->assertStatus(403);
    }
}
```

## 📚 Recursos Adicionales

- [Laravel Migrations](https://laravel.com/docs/10.x/migrations)
- [Eloquent Scopes](https://laravel.com/docs/10.x/eloquent#local-scopes)
- [Middleware](https://laravel.com/docs/10.x/middleware)
- [Spatie Permissions](https://spatie.be/docs/laravel-permission/v5/introduction)

## 🆘 Soporte

Si tienes problemas:

1. Verifica que la migración se ejecutó correctamente
2. Revisa los logs en `storage/logs/laravel.log`
3. Ejecuta `php artisan cache:clear` (o usa el script web si no tienes consola)
4. Verifica que el modelo User incluye el campo en `$fillable`

## 🔐 Seguridad

- ❌ NUNCA dejes archivos de migración web en producción
- ✅ Protege las rutas de almacén con middleware
- ✅ Valida siempre los permisos en el backend
- ✅ No confíes solo en la validación del frontend
