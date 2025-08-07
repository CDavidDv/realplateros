# Funcionalidad idVentaDia

## Descripción
Se ha implementado un nuevo campo `idVentaDia` en la tabla de ventas que permite identificar únicamente cada venta del día por sucursal. Este campo se reinicia automáticamente cada día y comienza desde 1.

## Características

- **Automático**: El campo se genera automáticamente al crear una nueva venta
- **Por sucursal**: Cada sucursal tiene su propia numeración diaria
- **Por día**: Se reinicia cada día a las 00:00
- **Secuencial**: Comienza desde 1 y se incrementa secuencialmente

## Estructura de la Base de Datos

### Tabla `ventas`
```sql
ALTER TABLE ventas ADD COLUMN idVentaDia INT UNSIGNED NULL AFTER sucursal_id;
```

### Campos agregados:
- `idVentaDia`: Identificador único de la venta del día (1, 2, 3, ...)

## Uso en el Código

### Modelo Venta
El modelo `Venta` ahora incluye métodos para manejar el `idVentaDia`:

```php
// Generar automáticamente el idVentaDia
$venta = new Venta();
$venta->usuario_id = auth()->id();
$venta->sucursal_id = auth()->user()->sucursal_id;
$venta->total = $total;
$venta->metodo_pago = $metodoPago;
$venta->save(); // El idVentaDia se genera automáticamente

// Obtener ventas de un día específico
$ventas = Venta::obtenerVentasDelDia($sucursalId, $fecha);

// Contar ventas de un día
$totalVentas = Venta::contarVentasDelDia($sucursalId, $fecha);
```

### API Endpoints

#### 1. Obtener ventas de un día específico
```
GET /ventas/dia?sucursal_id=1&fecha=2025-08-06
```

Respuesta:
```json
{
    "ventas": [
        {
            "id": 1,
            "idVentaDia": 1,
            "total": 150.00,
            "metodo_pago": "efectivo",
            "created_at": "2025-08-06 10:30:00",
            "usuario_nombre": "Juan Pérez",
            "sucursal_nombre": "Sucursal Centro"
        }
    ],
    "total_ventas": 1,
    "fecha": "2025-08-06",
    "sucursal_id": 1
}
```

#### 2. Obtener resumen de ventas por día
```
GET /ventas/resumen-dia?fecha=2025-08-06
```

Respuesta:
```json
{
    "resumen": [
        {
            "sucursal_id": 1,
            "sucursal_nombre": "Sucursal Centro",
            "total_ventas": 30,
            "total_ventas_monto": 4500.00,
            "primera_venta_dia": 1,
            "ultima_venta_dia": 30
        },
        {
            "sucursal_id": 2,
            "sucursal_nombre": "Sucursal Norte",
            "total_ventas": 40,
            "total_ventas_monto": 6000.00,
            "primera_venta_dia": 1,
            "ultima_venta_dia": 40
        }
    ],
    "fecha": "2025-08-06"
}
```

## Consultas SQL Útiles

### 1. Obtener todas las ventas de un día para una sucursal
```sql
SELECT 
    v.id,
    v.idVentaDia,
    v.total,
    v.metodo_pago,
    v.created_at,
    u.name as usuario_nombre,
    s.nombre as sucursal_nombre
FROM ventas v
JOIN users u ON v.usuario_id = u.id
JOIN sucursales s ON v.sucursal_id = s.id
WHERE v.sucursal_id = 1 
    AND DATE(v.created_at) = '2025-08-06'
ORDER BY v.idVentaDia ASC;
```

### 2. Obtener resumen de ventas por día para todas las sucursales
```sql
SELECT 
    v.sucursal_id,
    s.nombre as sucursal_nombre,
    COUNT(*) as total_ventas,
    SUM(v.total) as total_ventas_monto,
    MIN(v.idVentaDia) as primera_venta_dia,
    MAX(v.idVentaDia) as ultima_venta_dia
FROM ventas v
JOIN sucursales s ON v.sucursal_id = s.id
WHERE DATE(v.created_at) = '2025-08-06'
GROUP BY v.sucursal_id, s.nombre
ORDER BY v.sucursal_id;
```

### 3. Obtener tickets asociados a las ventas del día
```sql
SELECT 
    v.idVentaDia,
    v.total as total_venta,
    v.metodo_pago,
    t.numero_ticket,
    t.created_at as hora_ticket,
    u.name as usuario_nombre
FROM ventas v
JOIN tickets t ON v.id = t.venta_id
JOIN users u ON v.usuario_id = u.id
WHERE v.sucursal_id = 1 
    AND DATE(v.created_at) = '2025-08-06'
ORDER BY v.idVentaDia ASC;
```

## Migraciones Ejecutadas

1. **2025_08_06_222654_add_id_venta_dia_to_ventas_table.php**
   - Agrega el campo `idVentaDia` a la tabla `ventas`

2. **2025_08_06_222738_update_existing_ventas_with_id_venta_dia.php**
   - Actualiza las ventas existentes con el `idVentaDia` correspondiente

## Ejemplos de Uso

### En el Frontend (Vue.js)
```javascript
// Obtener ventas del día actual
const obtenerVentasDelDia = async (sucursalId) => {
    const response = await axios.get(`/ventas/dia?sucursal_id=${sucursalId}&fecha=${new Date().toISOString().split('T')[0]}`);
    return response.data;
};

// Obtener resumen de ventas del día
const obtenerResumenVentas = async () => {
    const response = await axios.get(`/ventas/resumen-dia?fecha=${new Date().toISOString().split('T')[0]}`);
    return response.data;
};
```

### En el Backend (Laravel)
```php
// En un controlador
public function mostrarVentasDelDia(Request $request)
{
    $sucursalId = auth()->user()->sucursal_id;
    $fecha = $request->input('fecha', now()->format('Y-m-d'));
    
    $ventas = Venta::obtenerVentasDelDia($sucursalId, $fecha);
    $totalVentas = Venta::contarVentasDelDia($sucursalId, $fecha);
    
    return view('ventas.dia', compact('ventas', 'totalVentas', 'fecha'));
}
```

## Notas Importantes

1. **Compatibilidad**: Las ventas existentes han sido actualizadas automáticamente con el `idVentaDia` correspondiente
2. **Automatización**: El campo se genera automáticamente al crear nuevas ventas
3. **Unicidad**: Cada sucursal tiene su propia numeración diaria
4. **Reinicio**: La numeración se reinicia cada día a las 00:00
5. **Ordenamiento**: Las ventas se pueden ordenar por `idVentaDia` para obtener el orden cronológico del día

