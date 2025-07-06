<template>
  <div>
    <h1 class="pb-4 pt-10 font-semibold text-xl">Pastes/Empanadas horneados de hoy</h1>
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Responsable</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Paste/Empanada</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hora</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Día</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Piezas Horneadas</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="(item, index) in pastesHorneados" :key="index">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                {{ item?.responsable?.name || 'N/A' }} {{ item?.responsable?.apellido_p || '' }} {{ item?.responsable?.apellido_m || '' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ item.relleno }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatDate(item.updated_at) }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatDay(item.updated_at) }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ item.piezas }}</td>
            </tr>
          </tbody>
          <tfoot class="bg-gray-50">
            <tr>
              <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Total de piezas horneadas:</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ totalPiezasHorneadas }}</td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const { props } = usePage();
const pastesHorneados = ref(props.pastesHorneados);

// Calcula el total general de piezas horneadas
const totalPiezasHorneadas = computed(() => {
  return pastesHorneados.value.reduce((total, item) => {
    return total + Number(item.piezas || 0);
  }, 0);
});

const formatDate = (dateString) => {
  const date = new Date(dateString);
  return date.toLocaleString('es-MX', {
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit',
    timeZone: 'America/Mexico_City' 
  });
}

// Format date to display in dd/mm/yyyy format
const formatDay = (dateString) => {
  const date = new Date(dateString)
  return date.toLocaleDateString('es-ES', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    timeZone: 'America/Mexico_City'
  })
}
</script>

<style scoped>
.table-container {
  @apply overflow-x-auto;
}

@media (max-width: 640px) {
  table {
    @apply text-xs;
  }

  th, td {
    @apply px-2 py-2;
  }
}
</style>
