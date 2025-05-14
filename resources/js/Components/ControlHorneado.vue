<template>
  <div class="bg-white shadow rounded-lg p-6">
    <h2 class="text-xl font-semibold mb-4">Control de Horneado</h2>

    <!-- Notificaciones de Umbral -->
    <div class="mb-8">
      <h3 class="text-lg font-medium mb-3">Notificaciones de Umbral</h3>
      <div v-if="notificaciones.length === 0" class="text-gray-500 text-center py-4">
        No hay notificaciones activas
      </div>
      <div v-else class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estimado</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Existente</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Diferencia</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tiempo Notificación</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tiempo Horneado</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tiempo Espera</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="notificacion in notificaciones" :key="notificacion.id">
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ notificacion.inventario.nombre }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ notificacion.cantidad_estimada }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ notificacion.cantidad_existente }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ notificacion.diferencia }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm">
                <span :class="[
                  'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                  notificacion.tipo === 'faltante' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'
                ]">
                  {{ notificacion.tipo }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ formatDate(notificacion.tiempo_notificacion) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ notificacion.tiempo_horneado ? formatDate(notificacion.tiempo_horneado) : 'Pendiente' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ notificacion.tiempo_espera_minutos ? `${notificacion.tiempo_espera_minutos} min` : '-' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                <button 
                  v-if="!notificacion.tiempo_horneado"
                  @click="registrarHorneado(notificacion)"
                  class="text-orange-600 hover:text-orange-900"
                >
                  Registrar Horneado
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Control de Inventario -->
    <div>
      <h3 class="text-lg font-medium mb-3">Control de Inventario</h3>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="item in inventario" :key="item.id">
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ item.nombre }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ item.cantidad }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm">
                <span :class="[
                  'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                  getEstadoClass(item)
                ]">
                  {{ getEstadoText(item) }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const notificaciones = ref([]);
const inventario = ref([]);

const formatDate = (date) => {
  return new Date(date).toLocaleString('es-MX', {
    hour: '2-digit',
    minute: '2-digit',
    timeZone: 'America/Mexico_City'
  });
};

const getEstadoClass = (item) => {
  const estimacion = item.estimacion || 0;
  const porcentaje = (item.cantidad / estimacion) * 100;
  
  if (porcentaje < 70) return 'bg-red-100 text-red-800';
  if (porcentaje > 100) return 'bg-green-100 text-green-800';
  return 'bg-yellow-100 text-yellow-800';
};

const getEstadoText = (item) => {
  const estimacion = item.estimacion || 0;
  const porcentaje = (item.cantidad / estimacion) * 100;
  
  if (porcentaje < 70) return 'Bajo';
  if (porcentaje > 100) return 'Sobrante';
  return 'Normal';
};

const registrarHorneado = async (notificacion) => {
  try {
    await axios.post('/api/notificaciones/actualizar-tiempo-horneado', {
      notificacion_id: notificacion.id,
      tiempo_horneado: new Date().toISOString()
    });
    
    await cargarNotificaciones();
  } catch (error) {
    console.error('Error al registrar horneado:', error);
  }
};

const cargarNotificaciones = async () => {
  try {
    const response = await axios.get('/api/notificaciones');
    notificaciones.value = response.data;
  } catch (error) {
    console.error('Error al cargar notificaciones:', error);
  }
};

const cargarInventario = async () => {
  try {
    const response = await axios.get('/api/inventario');
    inventario.value = response.data;
  } catch (error) {
    console.error('Error al cargar inventario:', error);
  }
};

onMounted(() => {
  cargarNotificaciones();
  cargarInventario();
  
  // Actualizar cada minuto
  setInterval(() => {
    cargarNotificaciones();
    cargarInventario();
  }, 60000);
});
</script> 