# Backend del Control de Producción

## Descripción
Este documento describe la implementación del backend para el sistema de filtros del control de producción.

## Rutas Implementadas

### 1. Control de Producción
- **GET** `/control-produccion/test` - Prueba del backend
- **GET** `/control-produccion/notificaciones` - Obtener notificaciones filtradas
- **POST** `/control-produccion/iniciar-horneado` - Iniciar horneado
- **POST** `/control-produccion/finalizar-horneado` - Finalizar horneado
- **POST** `/control-produccion/registrar-venta` - Registrar venta

### 2. Notificaciones
- **GET** `/api/notificaciones/obtener` - Obtener notificaciones filtradas (NotificacionUmbralController)
- **POST** `/api/notificaciones/registrar` - Registrar nueva notificación
- **POST** `/api/notificaciones/actualizar` - Actualizar notificación existente

## Funcionalidad de Filtros

### Filtro por Fecha
- **Parámetro**: `fecha` (formato: YYYY-MM-DD)
- **Ejemplo**: `?fecha=2024-01-15`
- **Comportamiento**: Filtra notificaciones por fecha de creación

### Filtro por Hora
- **Parámetro**: `hora` (formato: 7-am, 2-pm, etc.)
- **Ejemplo**: `?hora=7-am`
- **Comportamiento**: Filtra notificaciones por hora de creación (convertida a 24h)

### Filtros Combinados
- **Ejemplo**: `?fecha=2024-01-15&hora=7-am`
- **Comportamiento**: Aplica ambos filtros simultáneamente

## Estructura de Respuesta

### Respuesta Exitosa
```json
{
    "success": true,
    "notificaciones": [...],
    "filtros": {
        "fecha": "2024-01-15",
        "hora": "7-am"
    },
    "total": 5
}
```

### Respuesta de Error
```json
{
    "success": false,
    "error": "Error al obtener notificaciones",
    "message": "Detalle del error"
}
```

## Lógica de Filtrado

### 1. Comportamiento por Defecto
- **Sin filtros**: Muestra notificaciones calculadas en tiempo real
- **Con filtros**: Busca en la base de datos (backend)

### 2. Conversión de Hora
- **Formato 12h**: `7-am`, `2-pm`, `12:00 PM`
- **Conversión a 24h**: `7-am` → `7`, `2-pm` → `14`, `12:00 PM` → `12`

### 3. Filtros de Base de Datos
- **Fecha**: `WHERE DATE(created_at) = ?`
- **Hora**: `WHERE HOUR(created_at) = ?`

## Middleware y Autenticación

### Autenticación
- Todas las rutas requieren autenticación
- Usa el middleware `auth:sanctum` para web
- Usa el middleware `auth` para API

### Manejo de Respuestas
- **Peticiones JSON**: Respuestas JSON directas
- **Peticiones Web**: Procesadas por Inertia.js

## Logs y Debugging

### Logs Implementados
- **Info**: Consultas exitosas, filtros aplicados
- **Error**: Errores de base de datos, conversión de hora
- **Debug**: Parámetros recibidos, resultados de consultas

### Niveles de Log
- `Log::info()` - Operaciones exitosas
- `Log::error()` - Errores y excepciones
- `Log::warning()` - Advertencias (si aplica)

## Uso del Frontend

### 1. Inicialización
```javascript
// Por defecto: datos en tiempo real
fechaSeleccionada.value = '';
horaSeleccionada.value = 'actual';
```

### 2. Aplicar Filtros
```javascript
// Filtro por fecha
fechaSeleccionada.value = '2024-01-15';

// Filtro por hora
horaSeleccionada.value = '7-am';

// Se ejecuta automáticamente obtenerNotificacionesBackend()
```

### 3. Limpiar Filtros
```javascript
limpiarFiltros(); // Restablece a fecha actual + hora siguiente
mostrarTodas();   // Muestra todas las notificaciones
```

## Pruebas

### 1. Probar Backend
```bash
curl -X GET "http://localhost:8000/control-produccion/test" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer {token}"
```

### 2. Probar Filtros
```bash
# Filtro por fecha
curl -X GET "http://localhost:8000/control-produccion/notificaciones?fecha=2024-01-15" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer {token}"

# Filtro por hora
curl -X GET "http://localhost:8000/control-produccion/notificaciones?hora=7-am" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer {token}"
```

## Consideraciones de Rendimiento

### 1. Índices de Base de Datos
- `sucursal_id` - Para filtrar por sucursal
- `created_at` - Para filtros de fecha y hora
- `estado` - Para filtrar por estado

### 2. Consultas Optimizadas
- Uso de `with()` para eager loading de relaciones
- Filtros aplicados en la base de datos, no en PHP
- Ordenamiento por `created_at` con índice

### 3. Caché (Futuro)
- Implementar Redis para notificaciones frecuentes
- Cache de filtros comunes
- Invalidación automática al actualizar datos

## Mantenimiento

### 1. Logs
- Revisar logs diariamente
- Monitorear errores de conversión de hora
- Verificar rendimiento de consultas

### 2. Base de Datos
- Optimizar índices según uso
- Limpiar notificaciones antiguas
- Monitorear tamaño de tablas

### 3. Actualizaciones
- Mantener compatibilidad con frontend
- Versionar cambios en API
- Documentar cambios en filtros
