<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

const { props } = usePage();

const movimientos = ref(props.movimientos || []);
const resumen = ref(props.resumen || { total_ingresos: 0, total_salidas: 0, balance: 0 });
const graficas = ref(props.graficas || { por_dia: [], top_productos: [], por_sucursal: [] });
const sucursales = ref(props.sucursales || []);

const filtros = ref({
    fecha_inicio: props.filtros?.fecha_inicio || new Date().toISOString().split('T')[0],
    fecha_fin: props.filtros?.fecha_fin || new Date().toISOString().split('T')[0],
    sucursal_id: props.filtros?.sucursal_id || '',
});

const chartPorDia = ref(null);
const chartTopProductos = ref(null);
const chartPorSucursal = ref(null);
let chartInstances = {};

const aplicarFiltro = () => {
    router.get('/almacen/hoja-corte', filtros.value, { preserveScroll: true });
};

const descargarCSV = () => {
    const params = new URLSearchParams({
        fecha_inicio: filtros.value.fecha_inicio,
        fecha_fin: filtros.value.fecha_fin,
        ...(filtros.value.sucursal_id ? { sucursal_id: filtros.value.sucursal_id } : {}),
    });
    window.location.href = `/almacen/hoja-corte/export?${params.toString()}`;
};

const renderCharts = () => {
    // Destruir instancias previas
    Object.values(chartInstances).forEach(c => c.destroy());
    chartInstances = {};

    // Gráfica: movimientos por día (línea)
    if (chartPorDia.value && graficas.value.por_dia.length > 0) {
        chartInstances.porDia = new Chart(chartPorDia.value, {
            type: 'line',
            data: {
                labels: graficas.value.por_dia.map(d => d.fecha),
                datasets: [
                    {
                        label: 'Ingresos',
                        data: graficas.value.por_dia.map(d => d.ingresos),
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16,185,129,0.1)',
                        tension: 0.3,
                        fill: true,
                    },
                    {
                        label: 'Salidas',
                        data: graficas.value.por_dia.map(d => d.salidas),
                        borderColor: '#f59e0b',
                        backgroundColor: 'rgba(245,158,11,0.1)',
                        tension: 0.3,
                        fill: true,
                    },
                ],
            },
            options: { responsive: true, plugins: { legend: { position: 'top' } } },
        });
    }

    // Gráfica: top productos (barras)
    if (chartTopProductos.value && graficas.value.top_productos.length > 0) {
        chartInstances.topProductos = new Chart(chartTopProductos.value, {
            type: 'bar',
            data: {
                labels: graficas.value.top_productos.map(p => p.producto),
                datasets: [{
                    label: 'Cantidad total',
                    data: graficas.value.top_productos.map(p => p.total),
                    backgroundColor: '#6366f1',
                }],
            },
            options: {
                responsive: true,
                indexAxis: 'y',
                plugins: { legend: { display: false } },
            },
        });
    }

    // Gráfica: distribución por sucursal destino (barras)
    if (chartPorSucursal.value && graficas.value.por_sucursal.length > 0) {
        chartInstances.porSucursal = new Chart(chartPorSucursal.value, {
            type: 'bar',
            data: {
                labels: graficas.value.por_sucursal.map(s => s.sucursal),
                datasets: [{
                    label: 'Salidas',
                    data: graficas.value.por_sucursal.map(s => s.total),
                    backgroundColor: '#f59e0b',
                }],
            },
            options: { responsive: true, plugins: { legend: { display: false } } },
        });
    }
};

onMounted(() => {
    renderCharts();
});

watch(() => props.graficas, (val) => {
    graficas.value = val;
    renderCharts();
});
</script>

<template>
    <AppLayout title="Hoja de Corte Almacén">
        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 p-2 md:p-8">
                <div class="bg-white overflow-hidden shadow-xl rounded-xl p-6">
                    <h1 class="text-2xl font-bold mb-6 text-gray-800">Hoja de Corte - Almacén</h1>

                    <!-- Filtros -->
                    <div class="bg-gray-50 p-4 rounded-lg mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Inicio</label>
                                <input type="date" v-model="filtros.fecha_inicio"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Fin</label>
                                <input type="date" v-model="filtros.fecha_fin"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Sucursal</label>
                                <select v-model="filtros.sucursal_id"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500">
                                    <option value="">Todas las sucursales</option>
                                    <option v-for="s in sucursales" :key="s.id" :value="s.id">{{ s.nombre }}</option>
                                </select>
                            </div>
                            <div class="flex items-end gap-2">
                                <button @click="aplicarFiltro"
                                    class="flex-1 bg-orange-500 text-white px-4 py-2 rounded-md hover:bg-orange-600 transition-colors">
                                    Filtrar
                                </button>
                                <button @click="descargarCSV"
                                    class="flex-1 bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition-colors">
                                    Descargar CSV
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Tarjetas resumen -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                            <div class="text-sm text-green-600 font-medium">Total Ingresos</div>
                            <div class="text-3xl font-bold text-green-800">{{ resumen.total_ingresos }}</div>
                        </div>
                        <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                            <div class="text-sm text-amber-600 font-medium">Total Salidas</div>
                            <div class="text-3xl font-bold text-amber-800">{{ resumen.total_salidas }}</div>
                        </div>
                        <div :class="resumen.balance >= 0 ? 'bg-blue-50 border-blue-200' : 'bg-red-50 border-red-200'"
                            class="border rounded-lg p-4">
                            <div :class="resumen.balance >= 0 ? 'text-blue-600' : 'text-red-600'"
                                class="text-sm font-medium">Balance</div>
                            <div :class="resumen.balance >= 0 ? 'text-blue-800' : 'text-red-800'"
                                class="text-3xl font-bold">{{ resumen.balance }}</div>
                        </div>
                    </div>

                    <!-- Gráficas -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div class="bg-white border border-gray-200 rounded-lg p-4">
                            <h3 class="font-semibold text-gray-700 mb-2">Movimientos por Día</h3>
                            <canvas ref="chartPorDia"></canvas>
                            <p v-if="graficas.por_dia.length === 0" class="text-center text-gray-400 py-4 text-sm">Sin datos</p>
                        </div>
                        <div class="bg-white border border-gray-200 rounded-lg p-4">
                            <h3 class="font-semibold text-gray-700 mb-2">Top Productos</h3>
                            <canvas ref="chartTopProductos"></canvas>
                            <p v-if="graficas.top_productos.length === 0" class="text-center text-gray-400 py-4 text-sm">Sin datos</p>
                        </div>
                    </div>
                    <div class="bg-white border border-gray-200 rounded-lg p-4 mb-6">
                        <h3 class="font-semibold text-gray-700 mb-2">Distribución por Sucursal Destino</h3>
                        <canvas ref="chartPorSucursal" style="max-height: 250px;"></canvas>
                        <p v-if="graficas.por_sucursal.length === 0" class="text-center text-gray-400 py-4 text-sm">Sin datos</p>
                    </div>

                    <!-- Tabla de movimientos -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white border border-gray-200 rounded-lg">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 border-b">Fecha</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 border-b">Tipo</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 border-b">Producto</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 border-b">Cantidad</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 border-b">Origen/Destino</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 border-b">Trabajador</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(mov, i) in movimientos" :key="i" class="hover:bg-gray-50 transition-colors">
                                    <td class="px-4 py-3 border-b text-sm">{{ mov.fecha }}</td>
                                    <td class="px-4 py-3 border-b">
                                        <span :class="mov.tipo === 'Ingreso' ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800'"
                                            class="px-2 py-1 text-xs font-medium rounded-full">
                                            {{ mov.tipo }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 border-b text-sm">{{ mov.producto }}</td>
                                    <td class="px-4 py-3 border-b text-sm font-medium">{{ mov.cantidad }}</td>
                                    <td class="px-4 py-3 border-b text-sm">{{ mov.origen_destino }}</td>
                                    <td class="px-4 py-3 border-b text-sm">{{ mov.trabajador }}</td>
                                </tr>
                                <tr v-if="movimientos.length === 0">
                                    <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                        No se encontraron movimientos para el periodo seleccionado
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
