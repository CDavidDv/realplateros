<template>
  <div>
    <h1 class="pb-4 pt-10 font-semibold text-xl">Pastes/Empanadas horneados de hoy</h1>
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Paste/Empanada</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hora</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Día</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Piezas Horneadas</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total del día</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="(item, index) in bakedGoodsWithTotals" :key="index">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ item.relleno }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ item.created_at.split('T')[1].split('.')[0] }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatDate(item.created_at) }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ item.piezas }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                <span v-if="item.isFirst">{{ item.totalPiezas }}</span>
              </td>
            </tr>
          </tbody>
          <tfoot class="bg-gray-50">
            <tr>
              <td colspan="3" class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Total de piezas horneadas:</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ grandTotalPiezas }}</td>
              <td></td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import { computed } from 'vue'

const { props } = usePage();
const pastesHorneados = ref(props.pastesHorneados);

console.log("horneados: ", pastesHorneados.value)

// Group items by relleno and add a total field for each type
const bakedGoodsWithTotals = computed(() => {
  const grouped = {}
  
  // Group by relleno and sum piezas for each type
  props.pastesHorneados.forEach(item => {
    if (!grouped[item.relleno]) {
      grouped[item.relleno] = { totalPiezas: 0, items: [] }
    }
    grouped[item.relleno].totalPiezas += Number(item.piezas)
    grouped[item.relleno].items.push(item)
  })

  // Flatten the grouped data and mark the first item of each relleno to display the total
  const result = []
  Object.keys(grouped).forEach(relleno => {
    grouped[relleno].items.forEach((item, index) => {
      result.push({
        ...item,
        totalPiezas: grouped[relleno].totalPiezas,
        isFirst: index === 0 // Mark the first item to display the total
      })
    })
  })

  return result
})

// Calcula el total general de piezas horneadas como número
const grandTotalPiezas = computed(() => {
  return bakedGoodsWithTotals.value.reduce((sum, item) => Number(sum) + (item.isFirst ? Number(item.totalPiezas) : 0), 0)
})



// Format date to display in dd/mm/yyyy format
const formatDate = (dateString) => {
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
