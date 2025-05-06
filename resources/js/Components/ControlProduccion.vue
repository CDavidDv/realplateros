<template>
  <div class="mt-8 bg-white shadow rounded-lg p-6">
    <h2 class="text-xl font-semibold mb-4">Control de Producción</h2>
    
    <!-- Tiempo de Reposición -->
    <div class="mb-6">
      <h3 class="text-lg font-medium mb-2">Tiempo de Reposición</h3>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div v-for="(tiempo, index) in tiemposReposicion" :key="index" class="bg-gray-50 p-4 rounded-lg">
          <p class="font-medium">{{ tiempo.paste }}</p>
          <p>Tiempo promedio: {{ tiempo.promedio }} minutos</p>
          <p>Última reposición: {{ tiempo.ultima }}</p>
        </div>
      </div>
    </div>

    <!-- Control de Producción -->
    <div class="mb-6">
      <h3 class="text-lg font-medium mb-2">Estado de Producción</h3>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Paste</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cantidad</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Inicio Horneado</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fin Horneado</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Retiro</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vendidos</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">% Vendido</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tiempo sin Venta</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="produccion in produccionActual" :key="produccion.id">
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ produccion.paste.nombre }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ produccion.cantidad }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatDateTime(produccion.tiempo_inicio_horneado) }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatDateTime(produccion.tiempo_fin_horneado) }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatDateTime(produccion.tiempo_retiro_horno) }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ produccion.cantidad_vendida }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ calcularPorcentajeVenta(produccion) }}%
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getEstadoClass(produccion.estado)">
                  {{ produccion.estado }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ calcularTiempoSinVenta(produccion) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Tabla de Control de Tiempo (Solo para Administrador) -->
    <div v-if="isAdmin" class="mt-8">
      <h3 class="text-lg font-medium mb-2">Control de Tiempo de Producción</h3>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Paste</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Hora de Producción</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tiempo de Horneado</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tiempo de Venta</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tiempo Total</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="tiempo in tiemposProduccion" :key="tiempo.id">
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ tiempo.paste }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatDateTime(tiempo.hora_produccion) }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ tiempo.tiempo_horneado }} min</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ tiempo.tiempo_venta }} min</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ tiempo.tiempo_total }} min</td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getEstadoClass(tiempo.estado)">
                  {{ tiempo.estado }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Recomendaciones de Producción -->
    <div class="mt-6">
      <h3 class="text-lg font-medium mb-2">Recomendaciones de Producción</h3>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div v-for="recomendacion in recomendaciones" :key="recomendacion.paste" 
             class="bg-gray-50 p-4 rounded-lg">
          <p class="font-medium">{{ recomendacion.paste }}</p>
          <p :class="recomendacion.recomendacion === 'Producir' ? 'text-green-600' : 'text-red-600'">
            {{ recomendacion.recomendacion }}
          </p>
          <p class="text-sm text-gray-600">{{ recomendacion.razon }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';

const { props } = usePage();
const produccionActual = ref([]);
const tiemposReposicion = ref([]);
const recomendaciones = ref([]);
const tiemposProduccion = ref([]);

const isAdmin = computed(() => props.auth.user.roles[0].name === 'admin');

const calcularPorcentajeVenta = (produccion) => {
  if (!produccion.cantidad) return 0;
  return Math.round((produccion.cantidad_vendida / produccion.cantidad) * 100);
};

const calcularTiempoSinVenta = (produccion) => {
  if (!produccion.tiempo_ultima_venta) return '-';
  const ultimaVenta = new Date(produccion.tiempo_ultima_venta);
  const ahora = new Date();
  const minutos = Math.floor((ahora - ultimaVenta) / (1000 * 60));
  
  if (minutos < 60) {
    return `${minutos} min`;
  } else {
    const horas = Math.floor(minutos / 60);
    const minutosRestantes = minutos % 60;
    return `${horas}h ${minutosRestantes}min`;
  }
};

const formatDateTime = (date) => {
  if (!date) return '-';
  return new Date(date).toLocaleString('es-MX', {
    hour: '2-digit',
    minute: '2-digit',
    timeZone: 'America/Mexico_City'
  });
};

const getEstadoClass = (estado) => {
  const classes = {
    horneando: 'bg-yellow-100 text-yellow-800',
    retirado: 'bg-blue-100 text-blue-800',
    vendido: 'bg-green-100 text-green-800',
    desperdicio: 'bg-red-100 text-red-800'
  };
  return `px-2 py-1 rounded-full text-xs font-medium ${classes[estado]}`;
};

const actualizarDatos = async () => {
  try {
    const response = await axios.get('/api/control-produccion');
    produccionActual.value = response.data.produccion;
    tiemposReposicion.value = response.data.tiemposReposicion;
    recomendaciones.value = response.data.recomendaciones;
    tiemposProduccion.value = response.data.tiemposProduccion;
  } catch (error) {
    console.error('Error al obtener datos:', error);
  }
};

onMounted(() => {
  actualizarDatos();
  // Actualizar cada 5 minutos
  setInterval(actualizarDatos, 5 * 60 * 1000);
});
</script> 