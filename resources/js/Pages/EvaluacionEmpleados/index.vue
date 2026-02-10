<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

const props = defineProps({
    ranking: Array,
    mejores: Object,
    estadisticas: Object,
    configuracion: Array,
    sucursales: Array,
    filtros: Object,
});

const fechaInicio = ref(props.filtros?.fecha_inicio || '');
const fechaFin = ref(props.filtros?.fecha_fin || '');
const sucursalId = ref(props.filtros?.sucursal_id || '');

const barChart = ref(null);
const pieChart = ref(null);
let barChartInstance = null;
let pieChartInstance = null;

const empleadoDetalle = ref(null);
const mostrarDetalle = ref(false);

const aplicarFiltros = () => {
    router.get(route('evaluacion-empleados'), {
        fecha_inicio: fechaInicio.value,
        fecha_fin: fechaFin.value,
        sucursal_id: sucursalId.value || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const verDetalle = async (userId) => {
    try {
        const response = await fetch(`/evaluacion-empleados/${userId}?fecha_inicio=${fechaInicio.value}&fecha_fin=${fechaFin.value}`);
        const data = await response.json();
        if (data.success) {
            empleadoDetalle.value = data;
            mostrarDetalle.value = true;
        }
    } catch (error) {
        console.error('Error al obtener detalle:', error);
    }
};

const cerrarDetalle = () => {
    mostrarDetalle.value = false;
    empleadoDetalle.value = null;
};

const exportarCSV = () => {
    const params = new URLSearchParams({
        fecha_inicio: fechaInicio.value,
        fecha_fin: fechaFin.value,
    });
    if (sucursalId.value) {
        params.append('sucursal_id', sucursalId.value);
    }
    window.location.href = `/evaluacion-empleados/exportar?${params.toString()}`;
};

const crearGraficaBarras = () => {
    if (barChartInstance) {
        barChartInstance.destroy();
    }

    const ctx = barChart.value?.getContext('2d');
    if (!ctx || !props.ranking?.length) return;

    const top10 = props.ranking.slice(0, 10);

    barChartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: top10.map(e => e.nombre.split(' ')[0]),
            datasets: [{
                label: 'Puntos Totales',
                data: top10.map(e => e.total_puntos),
                backgroundColor: [
                    '#FFD700', '#C0C0C0', '#CD7F32',
                    '#4F46E5', '#4F46E5', '#4F46E5',
                    '#4F46E5', '#4F46E5', '#4F46E5', '#4F46E5'
                ],
                borderRadius: 8,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                title: {
                    display: true,
                    text: 'Top 10 Empleados',
                    font: { size: 16, weight: 'bold' }
                }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
};

const crearGraficaPie = () => {
    if (pieChartInstance) {
        pieChartInstance.destroy();
    }

    const ctx = pieChart.value?.getContext('2d');
    if (!ctx || !props.estadisticas?.puntos_por_concepto) return;

    const conceptos = Object.entries(props.estadisticas.puntos_por_concepto);
    const labels = conceptos.map(([key]) => formatConcepto(key));
    const data = conceptos.map(([, value]) => Math.abs(value.total));
    const colors = [
        '#10B981', '#3B82F6', '#F59E0B', '#8B5CF6', '#EC4899', '#EF4444', '#6366F1'
    ];

    pieChartInstance = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels,
            datasets: [{
                data,
                backgroundColor: colors.slice(0, labels.length),
                borderWidth: 2,
                borderColor: '#fff',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { padding: 15 }
                },
                title: {
                    display: true,
                    text: 'Distribucion por Concepto',
                    font: { size: 16, weight: 'bold' }
                }
            }
        }
    });
};

const formatConcepto = (concepto) => {
    const map = {
        'check_in': 'Check-in',
        'venta': 'Ventas',
        'horneado': 'Horneados',
        'notificacion_atendida': 'Notif. Atendidas',
        'notificacion_no_atendida': 'Notif. No Atendidas',
        'sobrante': 'Sobrantes',
        'corte_caja': 'Corte Caja'
    };
    return map[concepto] || concepto;
};

onMounted(() => {
    crearGraficaBarras();
    crearGraficaPie();
});

watch(() => props.ranking, () => {
    crearGraficaBarras();
}, { deep: true });

watch(() => props.estadisticas, () => {
    crearGraficaPie();
}, { deep: true });
</script>

<template>
    <AppLayout title="Evaluacion de Empleados">
        <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Evaluacion de Empleados</h1>
                <p class="text-gray-600">Sistema de puntos para medir desempeno del personal</p>
            </div>

            <!-- Filtros -->
            <div class="bg-white rounded-lg shadow p-4 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Inicio</label>
                        <input
                            type="date"
                            v-model="fechaInicio"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Fin</label>
                        <input
                            type="date"
                            v-model="fechaFin"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Sucursal</label>
                        <select
                            v-model="sucursalId"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                        >
                            <option value="">Todas las sucursales</option>
                            <option v-for="s in sucursales" :key="s.id" :value="s.id">
                                {{ s.nombre }}
                            </option>
                        </select>
                    </div>
                    <div class="flex items-end gap-2">
                        <button
                            @click="aplicarFiltros"
                            class="flex-1 bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition"
                        >
                            Filtrar
                        </button>
                        <button
                            @click="exportarCSV"
                            class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition"
                        >
                            CSV
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tarjetas Resumen -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <!-- Mejor Empleado General -->
                <div class="bg-gradient-to-br from-yellow-400 to-orange-500 rounded-lg shadow p-4 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm opacity-90">Mejor Empleado</p>
                            <p class="text-xl font-bold truncate">
                                {{ mejores?.mejor_general?.nombre || '-' }}
                            </p>
                            <p class="text-sm opacity-90">
                                {{ mejores?.mejor_general?.total_puntos || 0 }} puntos
                            </p>
                        </div>
                        <div class="text-4xl">&#127942;</div>
                    </div>
                </div>

                <!-- Mas Ventas -->
                <div class="bg-gradient-to-br from-blue-400 to-blue-600 rounded-lg shadow p-4 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm opacity-90">Mas Ventas</p>
                            <p class="text-xl font-bold truncate">
                                {{ mejores?.mas_ventas?.nombre || '-' }}
                            </p>
                            <p class="text-sm opacity-90">
                                {{ mejores?.mas_ventas?.total_ventas || 0 }} ventas
                            </p>
                        </div>
                        <div class="text-4xl">&#128176;</div>
                    </div>
                </div>

                <!-- Mas Horneados -->
                <div class="bg-gradient-to-br from-red-400 to-red-600 rounded-lg shadow p-4 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm opacity-90">Mas Horneados</p>
                            <p class="text-xl font-bold truncate">
                                {{ mejores?.mas_horneados?.nombre || '-' }}
                            </p>
                            <p class="text-sm opacity-90">
                                {{ mejores?.mas_horneados?.total_horneados || 0 }} lotes
                            </p>
                        </div>
                        <div class="text-4xl">&#128293;</div>
                    </div>
                </div>

                <!-- Promedio Horas -->
                <div class="bg-gradient-to-br from-purple-400 to-purple-600 rounded-lg shadow p-4 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm opacity-90">Promedio Horas</p>
                            <p class="text-xl font-bold">
                                {{ estadisticas?.promedio_horas_trabajadas || 0 }}h
                            </p>
                            <p class="text-sm opacity-90">
                                {{ estadisticas?.total_empleados || 0 }} empleados
                            </p>
                        </div>
                        <div class="text-4xl">&#128337;</div>
                    </div>
                </div>
            </div>

            <!-- Graficas -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="h-80">
                        <canvas ref="barChart"></canvas>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="h-80">
                        <canvas ref="pieChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Tabla Ranking -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Ranking de Empleados</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Empleado</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sucursal</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Puntos</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Ventas</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Horneados</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Notif.</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Horas</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr
                                v-for="empleado in ranking"
                                :key="empleado.user_id"
                                :class="{
                                    'bg-yellow-50': empleado.posicion === 1,
                                    'bg-gray-50': empleado.posicion === 2,
                                    'bg-orange-50': empleado.posicion === 3,
                                }"
                            >
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span
                                        :class="{
                                            'text-yellow-600 font-bold text-lg': empleado.posicion === 1,
                                            'text-gray-500 font-semibold': empleado.posicion === 2,
                                            'text-orange-600 font-semibold': empleado.posicion === 3,
                                            'text-gray-700': empleado.posicion > 3,
                                        }"
                                    >
                                        {{ empleado.posicion === 1 ? '&#127942;' : empleado.posicion === 2 ? '&#129352;' : empleado.posicion === 3 ? '&#129353;' : empleado.posicion }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ empleado.nombre }}</div>
                                    <div class="text-xs text-gray-500">{{ empleado.email }}</div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">
                                    {{ empleado.sucursal }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-center">
                                    <span class="px-2 py-1 text-sm font-semibold rounded-full"
                                        :class="empleado.total_puntos >= 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                                        {{ empleado.total_puntos }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-center text-sm text-gray-700">
                                    {{ empleado.total_ventas }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-center text-sm text-gray-700">
                                    {{ empleado.total_horneados }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-center text-sm text-gray-700">
                                    {{ empleado.notificaciones_atendidas }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-center text-sm text-gray-700">
                                    {{ empleado.horas_trabajadas }}h
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-center">
                                    <button
                                        @click="verDetalle(empleado.user_id)"
                                        class="text-indigo-600 hover:text-indigo-900 text-sm font-medium"
                                    >
                                        Ver detalle
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="!ranking?.length">
                                <td colspan="9" class="px-4 py-8 text-center text-gray-500">
                                    No hay datos para mostrar en el rango de fechas seleccionado.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Modal Detalle -->
            <div
                v-if="mostrarDetalle"
                class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
                @click.self="cerrarDetalle"
            >
                <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Detalle de {{ empleadoDetalle?.empleado?.nombre }}
                        </h3>
                        <button @click="cerrarDetalle" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="p-6" v-if="empleadoDetalle">
                        <!-- Info Empleado -->
                        <div class="mb-6">
                            <p class="text-gray-600">{{ empleadoDetalle.empleado?.email }}</p>
                            <p class="text-gray-600">Sucursal: {{ empleadoDetalle.empleado?.sucursal }}</p>
                        </div>

                        <!-- Resumen -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                            <div class="bg-gray-50 rounded-lg p-3 text-center">
                                <p class="text-2xl font-bold text-indigo-600">{{ empleadoDetalle.metricas?.total_puntos || 0 }}</p>
                                <p class="text-xs text-gray-500">Puntos Totales</p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-3 text-center">
                                <p class="text-2xl font-bold text-blue-600">{{ empleadoDetalle.metricas?.resumen?.ventas || 0 }}</p>
                                <p class="text-xs text-gray-500">Ventas</p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-3 text-center">
                                <p class="text-2xl font-bold text-red-600">{{ empleadoDetalle.metricas?.resumen?.horneados || 0 }}</p>
                                <p class="text-xs text-gray-500">Horneados</p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-3 text-center">
                                <p class="text-2xl font-bold text-purple-600">{{ empleadoDetalle.metricas?.horas_trabajadas || 0 }}h</p>
                                <p class="text-xs text-gray-500">Horas</p>
                            </div>
                        </div>

                        <!-- Historial -->
                        <div>
                            <h4 class="font-semibold text-gray-900 mb-3">Historial de Puntos</h4>
                            <div class="max-h-64 overflow-y-auto">
                                <div
                                    v-for="punto in empleadoDetalle.metricas?.historial"
                                    :key="punto.id"
                                    class="flex justify-between items-center py-2 border-b border-gray-100"
                                >
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ formatConcepto(punto.concepto) }}</p>
                                        <p class="text-xs text-gray-500">{{ punto.descripcion || '-' }}</p>
                                        <p class="text-xs text-gray-400">{{ new Date(punto.created_at).toLocaleString() }}</p>
                                    </div>
                                    <span
                                        class="px-2 py-1 text-sm font-semibold rounded-full"
                                        :class="punto.puntos >= 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                    >
                                        {{ punto.puntos >= 0 ? '+' : '' }}{{ punto.puntos }}
                                    </span>
                                </div>
                                <p v-if="!empleadoDetalle.metricas?.historial?.length" class="text-gray-500 text-center py-4">
                                    Sin historial de puntos
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
