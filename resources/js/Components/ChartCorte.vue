<template>
    <div class="w-full max-w-4xl mx-auto p-6 bg-white rounded-lg shadow space-y-6">
        <!-- Filtros -->
        <div class="flex flex-wrap gap-4 mb-6">
            <!-- Selección de Periodo -->
            <div>
                <label class="block text-sm font-medium">Periodo</label>
                <select 
                    v-model="timeFilter" 
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                >
                    <option value="day">Día</option>
                    <option value="week">Semana</option>
                    <option value="month">Mes</option>
                </select>
            </div>

            <!-- Dinámico: Filtro por Fecha -->
            <div v-if="timeFilter === 'day'">
                <label class="block text-sm font-medium">Fecha</label>
                <input 
                    type="date" 
                    v-model="startDate" 
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                />
            </div>
            <div v-if="timeFilter === 'customRange'">
            <label class="block text-sm font-medium">Fecha Inicial</label>
            <input 
                type="date" 
                v-model="startDate" 
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
            />
            <label class="block text-sm font-medium mt-4">Fecha Final</label>
            <input 
                type="date" 
                v-model="endDate" 
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
            />
        </div>


            <!-- Dinámico: Filtro por Semana -->
            <div v-if="timeFilter === 'week'">
                <label class="block text-sm font-medium">Semana</label>
                <input 
                    type="week" 
                    v-model="weekFilter" 
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                />
            </div>

            <!-- Dinámico: Filtro por Mes -->
            <div v-if="timeFilter === 'month'">
                <label class="block text-sm font-medium">Mes</label>
                <input 
                    type="month" 
                    v-model="monthFilter" 
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                />
            </div>

            <!-- Botones de Exportación -->
            <div class="flex gap-2">
                <button 
                    @click="exportData" 
                    class="px-4 py-2 text-white bg-blue-500 rounded shadow size-fit"
                >
                    Exportar Datos
                </button>
                <button 
                    @click="exportCharts" 
                    class="px-4 py-2 text-white bg-green-500 rounded shadow size-fit "
                >
                    Exportar Gráficos
                </button>
            </div>
        </div>


        <!-- Gráfico: Ventas por Producto -->
        <div>
            <h2 class="text-xl font-bold mb-4">Ventas por Producto</h2>
            <canvas ref="productChartRef"></canvas>
        </div>

        <!-- Gráfico: Pico de Ventas por Hora -->
        <div>
            <h2 class="text-xl font-bold mb-4">Ventas por Hora</h2>
            <canvas ref="hourChartRef"></canvas>
        </div>

        <!-- Gráfico: Días con Más Ventas -->
        <div>
            <h2 class="text-xl font-bold mb-4">Días con Más Ventas</h2>
            <canvas ref="topDaysChartRef"></canvas>
        </div>

        <!-- Gráfico: Ventas por Hora de Producto -->
        <div>
            <h2 class="text-xl font-bold mb-4">Ventas por Hora de Producto</h2>
            <div>
                <label class="block text-sm font-medium">Selecciona un Producto</label>
                <select 
                    v-model="selectedProduct" 
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                >
                    <option 
                        v-for="product in productosVenta" 
                        :key="product.id" 
                        :value="product.id"
                    >
                        {{ product.nombre }}
                    </option>
                </select>
            </div>
            <canvas ref="productByHourChartRef"></canvas>
        </div>
    </div>
</template>
<script setup>
import { usePage } from '@inertiajs/vue3';
import { ref, onMounted, computed, watch } from 'vue';
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);


const { props } = usePage();
const ventasProductos = props.ventasProductos;
const inventario = props.inventario;

// Obtener el día actual
const today = new Date().toISOString().split('T')[0];

// Estados para filtros
const startDate = ref(today);
const endDate = ref(today);
const timeFilter = ref('day'); // Predeterminado: Día
const selectedCategories = ref(['pastes', 'empanadas saladas', 'empanadas dulces', 'bebida']);
const sortOrder = ref('desc');

// Todas las categorías disponibles
const allCategories = ['pastes', 'empanadas saladas', 'empanadas dulces', 'bebida'];

// Producto seleccionado para ventas por hora
const selectedProduct = ref(null);

// Filtros de datos
const filteredVentas = computed(() => {
    return ventasProductos.filter(venta => {
        const productoEnInventario = inventario.find(item => item.id === venta.producto_id);
        const salesDate = new Date(venta.created_at);

        // Manejo de fechas
        const start = startDate.value ? new Date(startDate.value) : null;
        const end = endDate.value ? new Date(endDate.value) : null;
        const dateInRange = (!start || salesDate >= start) && (!end || salesDate <= end);

        // Validación de categorías
        const productInCategory =
            selectedCategories.value.length === 0 || 
            (productoEnInventario && selectedCategories.value.includes(productoEnInventario.tipo));

        return dateInRange && productInCategory;
    });
});

// Preparar datos de productos
const productosVenta = computed(() => 
    inventario.filter(item => selectedCategories.value.includes(item.tipo))
);

// Actualización de ventas por producto con filtros
const salesByProduct = computed(() => {
    const sales = productosVenta.value.map(product => {
        const productSales = filteredVentas.value
            .filter(venta => venta.producto_id === product.id)
            .reduce((total, venta) => total + venta.cantidad, 0);
        const totalRevenue = filteredVentas.value
            .filter(venta => venta.producto_id === product.id)
            .reduce((total, venta) => total + (venta.cantidad * product.precio), 0);
        return {
            nombre: product.nombre,
            ventas: productSales,
            revenue: totalRevenue
        };
    }).sort((a, b) => 
        sortOrder.value === 'desc' ? 
        b.ventas - a.ventas : 
        a.ventas - b.ventas
    );

    return sales;
});

const totalSalesAndRevenue = computed(() => {
    return salesByProduct.value.reduce((acc, product) => ({
        totalItems: acc.totalItems + product.ventas,
        totalRevenue: acc.totalRevenue + product.revenue
    }), { totalItems: 0, totalRevenue: 0 });
});

// Ventas por hora
const salesByHour = computed(() => {
    const hourSales = new Array(24).fill(0);

    filteredVentas.value.forEach(venta => {
        const date = new Date(venta.created_at);
        const hour = date.getHours();
        hourSales[hour] += venta.cantidad;
    });

    return hourSales;
});

// Días con más ventas
const salesByDay = computed(() => {
    const daySales = {};

    filteredVentas.value.forEach(venta => {
        const day = new Date(venta.created_at).toISOString().split('T')[0];
        daySales[day] = (daySales[day] || 0) + venta.cantidad;
    });

    return Object.entries(daySales)
        .map(([day, ventas]) => ({ day, ventas }))
        .sort((a, b) => b.ventas - a.ventas);
});

// Ventas por hora de producto específico
const salesByHourForProduct = computed(() => {
    if (!selectedProduct.value) return new Array(24).fill(0);

    const hourSales = new Array(24).fill(0);

    filteredVentas.value
        .filter(venta => venta.producto_id === selectedProduct.value)
        .forEach(venta => {
            const hour = new Date(venta.created_at).getHours();
            hourSales[hour] += venta.cantidad;
        });

    return hourSales;
});

// Referencias para gráficos
const productChartRef = ref(null);
const hourChartRef = ref(null);
const topDaysChartRef = ref(null);
const productByHourChartRef = ref(null);

let productChart = null;
let hourChart = null;
let topDaysChart = null;
let productByHourChart = null;

const renderCharts = () => {
    // Gráfico de Ventas por Producto
    if (productChart) productChart.destroy();
    if (productChartRef.value) {
        productChart = new Chart(productChartRef.value, {
            type: 'bar',
            data: {
                labels: salesByProduct.value.map(p => `${p.nombre}: ${p.ventas} ($${p.revenue.toLocaleString()})`),
                datasets: [
                    {
                        label: 'Ventas por Producto',
                        data: salesByProduct.value.map(p => p.ventas),
                        backgroundColor: 'rgba(75,192,192,0.6)',
                        borderColor: 'rgba(75,192,192,1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true },
                    x: { 
                        title: { display: true, text: 'Productos' },
                        ticks: {
                            maxRotation: 45,
                            minRotation: 45
                        }
                    }
                }
            }
        });
    }

    // Gráfico de Ventas por Hora
    if (hourChart) hourChart.destroy();
    if (hourChartRef.value) {
        const hourlyRevenue = Array(24).fill(0);
        filteredVentas.value.forEach(venta => {
            const hour = new Date(venta.created_at).getHours();
            const producto = inventario.find(item => item.id === venta.producto_id);
            hourlyRevenue[hour] += venta.cantidad * (producto?.precio || 0);
        });

        hourChart = new Chart(hourChartRef.value, {
            type: 'line',
            data: {
                labels: Array.from({ length: 24 }, (_, i) => 
                    `${i}:00 (${salesByHour.value[i]} - $${hourlyRevenue[i].toLocaleString()})`
                ),
                datasets: [
                    {
                        label: 'Ventas por Hora',
                        data: salesByHour.value,
                        backgroundColor: 'rgba(255,99,132,0.2)',
                        borderColor: 'rgba(255,99,132,1)',
                        borderWidth: 2,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true },
                    x: { 
                        title: { display: true, text: 'Hora del Día' },
                        ticks: {
                            maxRotation: 45,
                            minRotation: 45
                        }
                    }
                }
            }
        });
    }

    // Gráfico de Días con Más Ventas
    if (topDaysChart) topDaysChart.destroy();
    if (topDaysChartRef.value) {
        const dailyRevenue = {};
        filteredVentas.value.forEach(venta => {
            const day = new Date(venta.created_at).toISOString().split('T')[0];
            const producto = inventario.find(item => item.id === venta.producto_id);
            dailyRevenue[day] = (dailyRevenue[day] || 0) + (venta.cantidad * (producto?.precio || 0));
        });

        topDaysChart = new Chart(topDaysChartRef.value, {
            type: 'bar',
            data: {
                labels: salesByDay.value.map(d => 
                    `${d.day}: ${d.ventas} ($${dailyRevenue[d.day].toLocaleString()})`
                ),
                datasets: [
                    {
                        label: 'Ventas por Día',
                        data: salesByDay.value.map(d => d.ventas),
                        backgroundColor: 'rgba(54,162,235,0.6)',
                        borderColor: 'rgba(54,162,235,1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true },
                    x: { 
                        title: { display: true, text: 'Días' },
                        ticks: {
                            maxRotation: 45,
                            minRotation: 45
                        }
                    }
                }
            }
        });
    }

    // Gráfico de Ventas por Hora de Producto
    if (productByHourChart) productByHourChart.destroy();
    if (productByHourChartRef.value) {
        const selectedProductInfo = inventario.find(item => item.id === selectedProduct.value);
        const productHourlyRevenue = Array(24).fill(0);
        
        if (selectedProductInfo) {
            filteredVentas.value
                .filter(venta => venta.producto_id === selectedProduct.value)
                .forEach(venta => {
                    const hour = new Date(venta.created_at).getHours();
                    productHourlyRevenue[hour] += venta.cantidad * selectedProductInfo.precio;
                });
        }

        productByHourChart = new Chart(productByHourChartRef.value, {
            type: 'line',
            data: {
                labels: Array.from({ length: 24 }, (_, i) => 
                    `${i}:00 (${salesByHourForProduct.value[i]} - $${productHourlyRevenue[i].toLocaleString()})`
                ),
                datasets: [
                    {
                        label: selectedProductInfo?.nombre || 'Producto',
                        data: salesByHourForProduct.value,
                        backgroundColor: 'rgba(153,102,255,0.2)',
                        borderColor: 'rgba(153,102,255,1)',
                        borderWidth: 2,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true },
                    x: { 
                        title: { display: true, text: 'Hora del Día' },
                        ticks: {
                            maxRotation: 45,
                            minRotation: 45
                        }
                    }
                }
            }
        });
    }
};

watch([filteredVentas, salesByProduct, salesByHour, salesByDay, salesByHourForProduct], renderCharts, { immediate: true });

onMounted(() => {
    renderCharts();
});


const exportData = () => {
    const data = filteredVentas.value.map(venta => ({
        Producto: inventario.find(item => item.id === venta.producto_id)?.nombre || 'Desconocido',
        Fecha: venta.created_at,
        Cantidad: venta.cantidad
    }));
    const csvContent = "data:text/csv;charset=utf-8," +
        ["Producto,Fecha,Cantidad", ...data.map(row => Object.values(row).join(','))].join('\n');
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement('a');
    link.setAttribute('href', encodedUri);
    link.setAttribute('download', 'ventas.csv');
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
};

// Exportar gráficos como imágenes
const exportCharts = () => {
    const charts = [productChart, hourChart, topDaysChart, productByHourChart];
    charts.forEach((chart, index) => {
        if (chart) {
            const link = document.createElement('a');
            link.download = `chart_${index + 1}.png`;
            link.href = chart.toBase64Image();
            link.click();
        }
    });
};
</script>
