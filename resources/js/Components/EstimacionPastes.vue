<template>
  <div class="pt-16">
    <div class="max-w-7xl mx-auto space-y-6">
      <!-- Header -->
      <header class="bg-white rounded-lg shadow p-6">
        <h1 class="text-2xl font-bold text-gray-900">Sistema de Control de Producción - Día: {{ timeFrame }}</h1>
        <div class="mt-4">
          <select 
            v-model="timeFrame"
            class="rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500"
          >
            <option v-for="day in days" :key="day.value" :value="day.value">
              {{ day.label }}
            </option>
          </select>
        </div>
      </header>

      <!-- Production Table -->
      <div v-if="isDataReady" class="bg-white rounded-lg shadow overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tiempo</th>
              <th 
                v-for="product in products" 
                :key="product"
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
              >
                {{ product }}
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="hour in hours" :key="hour">
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ hour }}</td>
              <td 
                v-for="product in products" 
                :key="product" 
                class="px-6 py-4 whitespace-nowrap"
              >
                <input 
                  type="number"
                  v-model="productionData[hour][product]"
                  class="w-20 rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500"
                  min="0"
                >
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="w-full flex justify-end">
        <button 
          @click="saveProduction" 
          class="px-3 py-2 bg-orange-500 rounded-lg text-white hover:bg-orange-400"
        >
          Guardar
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import Swal from 'sweetalert2'

const { props } = usePage()
const daysOfWeek = ['Domingo', 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sábado'];
const today = new Date().getDay(); // Obtiene el índice del día actual (0 = Domingo, 1 = Lunes, etc.)
const timeFrame = ref(daysOfWeek[today]); // Asigna el día actual basado en el índice

const isDataReady = ref(false)

// Constantes
const days = [
  { value: 'Lunes', label: 'Lunes' },
  { value: 'Martes', label: 'Martes' },
  { value: 'Miercoles', label: 'Miércoles' },
  { value: 'Jueves', label: 'Jueves' },
  { value: 'Viernes', label: 'Viernes' },
  { value: 'Sabado', label: 'Sábado' },
  { value: 'Domingo', label: 'Domingo' }
]
const hours = ['9:00 am', '11:00 am', '1:00 pm', '3:00 pm', '6:00 pm', '9:00 pm']
const products = computed(() => 
  props.inventario
    .filter(item => ['pastes', 'empanadas dulces', 'empanadas saladas'].includes(item.tipo))
    .map(item => item.nombre)
)

// Estado
const productionData = ref({})

// Toast notification
const Toast = Swal.mixin({
  toast: true,
  position: "top-end",
  showConfirmButton: false,
  timer: 1500,
  timerProgressBar: true,
  didOpen: (toast) => {
    toast.onmouseenter = Swal.stopTimer
    toast.onmouseleave = Swal.resumeTimer
  }
})

// Inicializar estructura de datos
const initializeProductionData = () => {
  const newData = {}
  hours.forEach(hour => {
    newData[hour] = {}
    products.value.forEach(product => {
      newData[hour][product] = 0
    })
  })
  return newData
}

// Cargar datos de producción
const loadProductionData = () => {
  isDataReady.value = false
  
  // Inicializar con estructura base
  productionData.value = initializeProductionData()
  
  // Obtener estimaciones del día seleccionado
  const estimacionesDia = props.estimaciones.filter(item => item.dia === timeFrame.value)
  
  // Rellenar con datos existentes
  estimacionesDia.forEach(({ hora, cantidad, inventario_id }) => {
    const product = props.inventario.find(i => i.id === inventario_id)?.nombre
    if (product && productionData.value[hora]) {
      productionData.value[hora][product] = cantidad
    }
  })
  
  isDataReady.value = true
}

// Guardar datos de producción
const saveProduction = () => {
    router.post("/estimaciones", {
    estimaciones: productionData.value,
    dia: timeFrame.value
  }, {
    preserveState: false,
    preserveScroll: true,
    onSuccess: () => Toast.fire({ icon: "success", title: "Sobrantes guardados exitosamente" }),
    onError: () => Toast.fire({ icon: "error", title: "Error al guardar los sobrantes" })
  })
}

// Lifecycle hooks y watchers
onMounted(() => {
  loadProductionData()
})

watch(timeFrame, loadProductionData)


</script>