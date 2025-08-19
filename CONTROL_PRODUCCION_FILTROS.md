# Sistema de Filtros - Control de Producción

## Descripción General

El componente `ControlProduccion.vue` implementa un sistema completo de filtros para gestionar y visualizar el control de producción de la panadería. Utiliza el modelo `ControlProduccion.php` para obtener datos reales del backend.

## Características Principales

### 1. Filtros Disponibles

#### Filtro por Fecha
- **Selector de fecha**: Permite seleccionar una fecha específica
- **Formato**: YYYY-MM-DD
- **Comportamiento**: Filtra registros creados en la fecha seleccionada (usa `created_at`)
- **Alcance**: Todo el día completo (00:00:00 a 23:59:59)

#### Filtro por Hora
- **Hora Siguiente**: Muestra productos faltantes para la próxima hora
- **Todas las horas**: Muestra todos los registros sin filtrar por hora
- **Horas específicas**: 7:00 AM a 10:00 PM en intervalos de 1 hora
- **Campo usado**: `hora_notificacion` (formato: "7:00 am", "3:00 pm")

### 2. Lógica de Filtrado

#### Filtro de Fecha
```php
// Filtra por fecha completa usando created_at
if ($fecha) {
    $fechaObj = Carbon::parse($fecha);
    $queryBase->whereDate('created_at', $fechaObj->toDateString());
}
```

#### Filtro de Hora
```php
// Filtra por hora específica usando hora_notificacion
if ($hora && $hora !== 'todas' && $hora !== 'actual') {
    $horaFormateada = $this->formatearHoraParaBD($hora);
    $queryBase->where('hora_notificacion', $horaFormateada);
}
```

### 3. Estados de Producción

El sistema maneja los siguientes estados:

- **pendiente**: Productos que aún no han sido horneados
- **horneando**: Productos que están siendo horneados actualmente
- **en_espera**: Productos horneados y listos para venta
- **vendido**: Productos que han sido vendidos completamente
- **desperdicio**: Productos que se han desperdiciado
- **retirado**: Productos retirados del horno

### 4. Funcionalidades

#### Tabla de Productos Faltantes
- Muestra productos que requieren producción
- Calcula cantidades faltantes vs. horneadas vs. vendidas
- Muestra tiempos de horneado y venta
- Indica estado actual de cada producto

#### Tabla de Control de Tiempo
- Registra tiempos de producción
- Calcula eficiencia en el proceso
- Muestra estado de productos en producción

#### Resumen Estadístico
- Total de productos filtrados
- Conteo por estado
- Métricas de rendimiento

## Estructura de Datos

### Modelo ControlProduccion

```php
protected $fillable = [
    'horno_id',
    'paste_id', 
    'sucursal_id',
    'cantidad',
    'tiempo_inicio_horneado',
    'tiempo_fin_horneado',
    'hora_ultima_venta',
    'cantidad_vendida',
    'cantidad_horneada',
    'estado',
    'hora_notificacion',    // Formato: "7:00 am", "3:00 pm"
    'dia_notificacion',     // Formato: "lunes", "martes", etc.
    'created_at',           // Usado para filtro de fecha completa
    'updated_at'
];
```

### Campos de Filtrado

| Campo | Uso | Formato | Ejemplo |
|-------|-----|---------|---------|
| `created_at` | Filtro de fecha completa | YYYY-MM-DD | 2025-08-19 |
| `hora_notificacion` | Filtro de hora específica | "g:i A" | "3:00 pm" |
| `dia_notificacion` | Información de día | Día en español | "lunes" |

## API Endpoints

### Obtener Notificaciones Filtradas
```
POST /control-produccion
```

**Parámetros:**
- `fecha`: Fecha en formato YYYY-MM-DD (filtra por todo el día)
- `hora`: Hora específica o 'actual'/'todas'

**Respuesta:**
```json
{
    "success": true,
    "notificaciones": [...],
    "notificacionesHorneando": [...],
    "filtros": {...},
    "total": 25,
    "estadisticas": {...},
    "debug": {
        "filtros_aplicados": {
            "fecha": "2025-08-19",
            "hora": "3:00 PM",
            "fecha_parseada": "2025-08-19",
            "hora_formateada": "3:00 pm"
        }
    }
}
```

## Uso del Componente

### 1. Inicialización
```javascript
onMounted(() => {
    fechaSeleccionada.value = new Date().toISOString().split('T')[0];
    horaSeleccionada.value = 'actual';
    filtrarNotificaciones();
});
```

### 2. Filtrado Automático
```javascript
watch([fechaSeleccionada, horaSeleccionada], () => {
    filtrarNotificaciones();
});
```

### 3. Limpieza de Filtros
```javascript
const limpiarFiltros = () => {
    fechaSeleccionada.value = new Date().toISOString().split('T')[0];
    horaSeleccionada.value = 'actual';
    filtrarNotificaciones();
};
```

## Cálculos de Tiempo

### Tiempo de Producción
- Calcula el tiempo entre notificación y inicio de horneado
- Maneja casos de diferentes días
- Formatea en minutos u horas

### Tiempo de Horneado
- Calcula duración del proceso de horneado
- Valida fechas y tiempos
- Maneja errores de cálculo

### Tiempo de Venta
- Calcula tiempo desde notificación hasta primera venta
- Considera productos sin ventas
- Maneja diferentes zonas horarias

## Comandos de Prueba

### Probar Filtros desde Consola
```bash
# Probar filtro de fecha
php artisan test:control-produccion-filtros --fecha=2025-08-19

# Probar filtro de hora
php artisan test:control-produccion-filtros --hora="3:00 PM"

# Probar ambos filtros
php artisan test:control-produccion-filtros --fecha=2025-08-19 --hora="3:00 PM"

# Probar sin filtros (todos los registros)
php artisan test:control-produccion-filtros
```

### Generar Datos de Prueba
```bash
# Ejecutar seeder de prueba
php artisan db:seed --class=ControlProduccionTestSeeder
```

## Manejo de Errores

### Estados de Carga
- **loading**: Indica cuando se están cargando datos
- **error**: Almacena mensajes de error
- **Reintentar**: Botón para reintentar en caso de fallo

### Validaciones
- Verificación de fechas válidas
- Manejo de campos opcionales
- Fallbacks para datos faltantes

### Logging
- Logs detallados de filtros aplicados
- Información de consultas SQL generadas
- Trazabilidad de errores

## Estilos y UI

### Tailwind CSS
- Diseño responsivo
- Colores semánticos por estado
- Hover effects y transiciones
- Grid system para estadísticas

### Componentes Visuales
- Indicador de carga animado
- Mensajes de error estilizados
- Badges de estado con colores
- Tablas responsivas

## Optimizaciones

### Performance
- Filtrado reactivo con watchers
- Lazy loading de datos
- Debouncing de filtros
- Caché de respuestas

### UX
- Feedback visual inmediato
- Estados de carga claros
- Manejo de errores amigable
- Filtros intuitivos

## Mantenimiento

### Logs
- Errores de cálculo de tiempo
- Fallos de conversión de hora
- Problemas de validación de fecha
- Filtros aplicados y resultados

### Debugging
- Console logs para desarrollo
- Validación de datos del backend
- Verificación de relaciones
- Testing de filtros con comandos Artisan

## Consideraciones Futuras

### Mejoras Propuestas
- Filtros adicionales por tipo de producto
- Exportación de datos filtrados
- Gráficos de rendimiento
- Notificaciones en tiempo real
- Historial de cambios de estado
- Filtros por rango de fechas
- Búsqueda por texto en nombres de productos
