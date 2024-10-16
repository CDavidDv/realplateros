<template>
  <div class="min-h-screen py-6 flex rounded-2xl border-0 flex-col justify-center sm:py-12">
    <div class="relative py-3 sm:max-w-xl sm:mx-auto w-full px-4 sm:px-0">
      <div class="relative px-4 py-10 bg-white shadow-lg rounded-3xl sm:p-20">
        <h1 class="text-3xl font-bold mb-6 text-center">Corte de Caja</h1>
        
        <!-- Filtro por día, semana o mes -->
        <div class="flex flex-col md:flex-row items-center gap-4 mb-8">
          <div class="w-full gap-2 flex flex-col items-center">
            <label for="filter" class="font-bold">Filtrar por:</label>
            <div class="flex gap-2">
              <select class="ml-1 py-0 rounded" id="filter" v-model="selectedFilter">
                <option value="day">Día</option>
                <option value="week">Semana</option>
                <option value="month">Mes</option>
              </select>

              <input class="w-fit h-fit p-1 rounded" id="valueSelect" type="date" v-if="selectedFilter === 'day'" v-model="selectedDate" />
              <input class="w-fit h-fit p-1 rounded" id="valueSelect" type="week" v-if="selectedFilter === 'week'" v-model="selectedWeek" />
              <input class="w-fit h-fit p-1 rounded" id="valueSelect" type="month" v-if="selectedFilter === 'month'" v-model="selectedMonth" />
            </div>
            <div class="flex gap-1">
              <button class="text-sm rounded-lg shadow-lg px-3 py-2 bg-orange-500 text-white hover:bg-orange-600" @click="fetchFilteredData">Aplicar Filtro</button>
              <button class="text-sm rounded-lg shadow-lg px-3 py-2 bg-gray-500 text-white hover:bg-gray-600" @click="resetFilters">Limpiar Filtro</button>
            </div>
          </div>
        </div>

        <!-- Mensaje de error -->
        <div v-if="error" class="text-red-500 font-bold mt-2">{{ error }}</div>

        <!-- Cantidad inicial -->
        <div class="mb-6" v-if="isToday">
          <label for="initialCash" class="block text-sm font-medium text-gray-700">Cantidad Inicial en Caja</label>
          <div class="flex space-x-2">
            <input
              type="number"
              id="initialCash"
              v-model="initialCash"
              class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-orange-500 focus:border-orange-500 sm:text-sm rounded-md"
              min="0"
              step="0.01"
              placeholder="Ingrese cantidad"
            />
            <button @click="handleSaveInitialCash" 
              :class="{
                  'px-4 py-2 rounded-md text-white hover:bg-orange-600': Number(props?.corte[0]?.dinero_inicio || 0) <= 0,
                  'bg-gray-500 px-4 py-2 rounded-md text-gray-300 cursor-not-allowed': Number(props?.corte[0]?.dinero_inicio || 0) > 0,
                  'bg-orange-500': Number(props?.corte[0]?.dinero_inicio || 0) <= 0
              }" 
              :disabled="Number(props?.corte[0]?.dinero_inicio || 0) > 0">Guardar</button>
          </div>
        </div>

        <!-- Cantidad final -->
        <div class="mb-6" v-if="isToday">
          <label for="finalCash" class="block text-sm font-medium text-gray-700">Cantidad Final en Caja</label>
          <div class="flex space-x-2">
            <input
              type="number"
              id="finalCash"
              v-model="finalCash"
              class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-orange-500 focus:border-orange-500 sm:text-sm rounded-md"
              min="0"
              step="0.01"
              placeholder="Ingrese cantidad"
            />
            <button @click="handleSaveFinalCash" 
              :class="{
                  'px-4 py-2 rounded-md text-white hover:bg-orange-600': Number(props?.corte[0]?.dinero_final || 0 ) <= 0,
                  'bg-gray-500 px-4 py-2 rounded-md text-gray-300 cursor-not-allowed': Number(props?.corte[0]?.dinero_final || 0 ) > 0,
                  'bg-orange-500': Number(props?.corte[0]?.dinero_final || 0 ) <= 0
              }" 
              :disabled="Number(props?.corte[0]?.dinero_final || 0 ) > 0">Guardar</button>
          </div>
        </div>

        <!-- Resumen financiero -->
        <div class="bg-gray-50 p-4 rounded-lg mb-6">
          <h2 class="text-xl font-semibold mb-4">Resumen Financiero</h2>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <p class="text-sm text-gray-600">Dinero inicial:</p>
              <p class="font-medium">${{ safeToFixed(initialCash) }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-600">Dinero final:</p>
              <p class="font-medium">${{ safeToFixed(finalCash) }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-600">Efectivo:</p>
              <p class="font-medium">${{ safeToFixed(cashPayments) }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-600">Tarjetas:</p>
              <p class="font-medium">${{ cardPayments }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-600">Total ventas:</p>
              <p class="font-medium">${{ Number(cashPayments) + Number(cardPayments) }}</p>
            </div>
          </div>
        </div>

        <!-- Productos utilizados -->
        <div class="mb-6">
          <h2 class="text-xl font-semibold mb-4">Productos Utilizados</h2>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="product in productsUsed" :key="product.producto_id">
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ product.producto.nombre }} - <small class="text-gray-500 uppercase">{{ product.producto.detalle }}</small></td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ product.total_vendido }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Mensaje de carga -->
        <div v-if="isLoading" class="text-center text-gray-500">Cargando...</div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import Swal from 'sweetalert2'

const { props } = usePage()
const selectedFilter = ref('day')
const today = new Date();
const selectedDate = ref(today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0') + '-' + String(today.getDate()).padStart(2, '0'))
const selectedWeek = ref(null)
const selectedMonth = ref(null)
const initialCash = ref(props?.corte[0]?.dinero_inicio || 0)
const finalCash = ref(props?.corte[0]?.dinero_final || 0)
const cashPayments = ref(0)
const cardPayments = ref(0)
const productsUsed = ref(props.productosVendidos || [])
const isLoading = ref(false)
const error = ref('')

const isToday = computed(() => {
  const today = new Date();
  const formattedToday = today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0') + '-' + String(today.getDate()).padStart(2, '0');
  
  return selectedFilter.value === 'day' && selectedDate.value === formattedToday;
});

const fetchFilteredData = () => {
  isLoading.value = true
  error.value = ''

  let filter = selectedFilter.value
  let value = null

  if (filter === 'day') {
    value = selectedDate.value
  } else if (filter === 'week') {
    value = selectedWeek.value
  } else if (filter === 'month') {
    value = selectedMonth.value
  }

  router.post('/corte-caja/filtro', {
    filter: filter, 
    value: value 
  }, {
    preserveScroll: true,
    onSuccess(response) {
      console.log(response)
      cashPayments.value = response.props.cashPayments
      cardPayments.value = response.props.cardPayments
      productsUsed.value = response.props.productsUsed
      initialCash.value = response.props.initialCash || 0
      finalCash.value = response.props.finalCash || 0
      isLoading.value = false
      showToast("success", "Filtro actualizado correctamente");
    },
    onError(e) {
      showToast("error", e.props.flash.error || "Error al obtener datos con este filtro");
      error.value = 'Ocurrió un error al obtener los datos. Inténtalo de nuevo.'
      isLoading.value = false
    }
  })
}


const resetFilters = () => {
  selectedFilter.value = 'day'
  const today = new Date();
  selectedDate.value = today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0') + '-' + String(today.getDate()).padStart(2, '0');
  selectedWeek.value = null
  selectedMonth.value = null
  fetchFilteredData()
}

const calculatePayments = () => {
  cashPayments.value = props.ventas.reduce((total, venta) => venta.metodo_pago === 'efectivo' ? Number(total) + Number(venta.total) : Number(total), 0)
  cardPayments.value = props.ventas.reduce((total, venta) => venta.metodo_pago === 'tarjeta' ? Number(total) + Number(venta.total) : Number(total), 0)
}

const showToast = (icon, title) => {
  Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    didOpen: (toast) => {
      toast.addEventListener('mouseenter', Swal.stopTimer)
      toast.addEventListener('mouseleave', Swal.resumeTimer)
    }
  }).fire({
    icon,
    title
  })
}

const handleSaveInitialCash = () => {
  
  const data = {
    sucursal_id: props?.sucursal_id,
    usuario_id: props?.usuario_id,
    fecha: selectedDate.value,
    dinero_inicio: initialCash.value
  };


  router.post('/corte-caja/guardar-inicial', data, {
    preserveScroll: true,
    onSuccess(e) {
      if(e.props.flash.error){
        showToast("error", e.props.flash.error || "Error al guardar la cantidad inicial");
      }else{
        showToast("success", "Cantidad inicial guardada correctamente");
      }
    },
    onError(e) {
      showToast("error", e.error || "Error al guardar la cantidad inicial");
    }
  });
};

const handleSaveFinalCash = () => {
  
  const data = {
    sucursal_id: props?.sucursal_id,
    usuario_id: props?.usuario_id,
    fecha: selectedDate.value,
    dinero_final: finalCash.value
  };

  router.post('/corte-caja/guardar-final', data, {
    preserveScroll: true,
    onSuccess(e) {
      if(e.props.flash.error){
        showToast("error", e.props.flash.error || "Error al guardar la cantidad inicial");
      }else{
        showToast("success", "Cantidad inicial guardada correctamente");
      }
    },
    onError(e) {
      showToast("error", e.error || "Error al guardar la cantidad inicial");
    }
  });
};


onMounted(() => {
  calculatePayments()
})

const safeToFixed = (value) => {
  return parseFloat(value).toFixed(2)
}
</script>

