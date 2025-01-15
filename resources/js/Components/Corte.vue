<template>
  <div class="min-h-screen py-6 flex rounded-2xl border-0 flex-col justify-center sm:py-12">
    <div class="relative py-3 sm:max-w-xl sm:mx-auto w-full px-4 sm:px-0">
      <div class="relative px-4 py-10 bg-white shadow-lg rounded-3xl sm:p-20">
        <h1 class="text-3xl font-bold mb-6 text-center ">Corte de Caja</h1>
        <span class="text-red-500 font-bold mt-2 print" v-if="mensajeNoAutorizado">No autorizado</span>
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
              <button class="no-print text-sm rounded-lg shadow-lg px-3 py-2 bg-orange-500 text-white hover:bg-orange-600" @click="fetchFilteredData">Aplicar Filtro</button>
              <button class="no-print text-sm rounded-lg shadow-lg px-3 py-2 bg-gray-500 text-white hover:bg-gray-600" @click="resetFilters">Limpiar Filtro</button>
            </div>
          </div>
        </div>

        <!-- Mensaje de error -->
        <div v-if="error" class="text-red-500 font-bold mt-2">{{ error }}</div>

        <!-- Cantidad inicial -->
        <div class="mb-6 no-print" v-if="isToday">
          <!-- Cantidad Inicial -->
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
                'px-4 py-2 rounded-md text-white hover:bg-orange-600 bg-orange-500': props?.corte === null && props?.corte?.dinero_inicio === null,
                'bg-gray-500 px-4 py-2 rounded-md text-gray-300 cursor-not-allowed': props?.corte !== null && props?.corte?.dinero_inicio !== null,
                'px-4 py-2 rounded-md text-white hover:bg-orange-600 bg-orange-500': props?.corte === null
              }" 
              :disabled="props?.corte !== null || props?.corte?.dinero_inicio  === null">
              Guardar
            </button>
          </div>
        </div>

        <!-- Cantidad Final -->
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
                'px-4 py-2 rounded-md text-white hover:bg-orange-600 bg-orange-500': props?.corte !== null && props?.corte?.dinero_final === null,
                'bg-gray-500 px-4 py-2 rounded-md text-gray-300 cursor-not-allowed': props?.corte !== null && props?.corte?.dinero_final !== null,
                'bg-orange-500': props?.corte !== null && props?.corte?.dinero_final === null,
                'px-4 py-2 rounded-md text-white hover:bg-orange-600 bg-orange-500': props?.corte === null
              }" 
              :disabled=" props?.corte !== null && props?.corte?.dinero_final !== null">
              Guardar
            </button>
          </div>
        </div>


        <!-- Resumen financiero -->
        <div class="bg-gray-50 p-4 rounded-lg mb-6">
          <h2 class="text-xl font-semibold mb-4">Resumen Financiero</h2>
          <div class="grid grid-cols-2 gap-4">
            <div v-if="selectedFilter === 'day'">
              <p class="text-sm text-gray-600">Dinero inicial:</p>
              <p class="font-medium">${{ safeToFixed(initialCash) }}</p>
            </div>
            <div v-if="selectedFilter === 'day'">
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
              <p v-if="!editSales" class="font-medium">${{ Number(cashPayments) + Number(cardPayments) }}</p>
              <p v-else>{{ totalventas  }}</p>
            </div>
          </div>
        </div>

        <!-- Productos utilizados -->
        <div class="mb-6">
          <div class="flex justify-between">
            <h2 class="text-xl font-semibold mb-4">Productos Utilizados</h2>
            <button v-if="!editSales && $page.props.user.roles[0] != 'trabajador'"  @click="handleEditSales" class="no-print size-fit py-1 px-2 mr-8 rounded-md text-white hover:bg-orange-600 bg-orange-500">Capturar datos</button>
            <button v-else="editSales"  @click="requestAdminPassword"  class="no-print size-fit py-1 px-2 mr-8 rounded-md text-white hover:bg-purple-600 bg-purple-500">Guardar</button>
          </div>
          <div v-if="productsUsed.length <= 0" class="text-gray-500">No se han vendido productos en este período.</div>
          <div v-else class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="product in productsUsed" :key="product.producto_id">
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ product.producto.nombre }} <small v-if="product.producto.detalle" class="text-gray-500 uppercase">- {{ product.producto.detalle }}</small></td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <input
                    v-if="editSales"
                    type="number"
                    v-model="product.total_vendido"
                    class="w-16 border border-gray-300 rounded-md px-2 py-1"
                    :class="[ filtrarProductoOriginal(product.producto_id) > product.total_vendido ? 'text-red-500' : 'text-black']"
                    :max="filtrarProductoOriginal(product.producto_id)"
                    min="0"
                    />
                    <span v-else class="print" >{{ product.total_vendido }}</span>
                    <!-- <div v-if="filtrarProductoOriginal(product.producto_id) > product.total_vendido">
                      Se modifico de {{ filtrarProductoOriginal(product.producto_id) }} a {{ product.total_vendido }}
                    </div> -->
                  </td>

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
import { ref, onMounted, computed, watch } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import Swal from 'sweetalert2'
import { route } from '../../../vendor/tightenco/ziggy/src/js'


const { props } = usePage()
const selectedFilter = ref('day')
const today = new Date();
const selectedDate = ref(today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0') + '-' + String(today.getDate()).padStart(2, '0'))
const selectedWeek = ref(null)
const selectedMonth = ref(null)
const initialCash = ref(props?.corte?.dinero_inicio || 0)
const finalCash = ref(props?.corte?.dinero_final || 0)
const cashPayments = ref(0)
const cardPayments = ref(0)
const productsUsed = ref(props.productosVendidos || [])
let productsUsedOriginal = ref(props.productosVendidos || [])
const isLoading = ref(false)
const error = ref('')
const mensajeNoAutorizado = ref(false)



const totalventas = computed(() => {
  return productsUsed.value.reduce((total, product) => {
    return total + (product.total_vendido * product.producto.precio);
  }, 0);
})

const filtrarProductoOriginal = (productoId) => {
  const productoOriginal = productsUsedOriginal.value.find(producto => producto.producto_id === productoId);
  return productoOriginal ? productoOriginal.total_vendido : 0;
}

const editSales = ref(false)

const handleEditSales = () => {
  editSales.value = !editSales.value
}

const hasChanges = computed(() => {
  return productsUsed.value.some(product =>
    product.total_vendido !== filtrarProductoOriginal(product.producto_id)
  );
});


watch(hasChanges, () => {
  mensajeNoAutorizado.value = !mensajeNoAutorizado.value;
});


const requestAdminPassword = () => {
  if (!hasChanges.value) {
    mensajeNoAutorizado.value = !mensajeNoAutorizado.value;
    handleEditSales();
    window.print();
  } else {  
    Swal.fire({
      title: 'Contraseña de administrador',
      input: 'password',
      inputLabel: 'Ingrese la contraseña',
      inputPlaceholder: 'Contraseña',
      showCancelButton: true,
      confirmButtonText: 'Aceptar',
      preConfirm: (password) => {
        if (!password) {
          Swal.showValidationMessage('La contraseña no puede estar vacía');
          return;
        }
        return axios
          .post(
            route('verify-admin-password'),
            { admin_password: password },
            {
              headers: { 'X-Inertia': false }, // Deshabilita Inertia
            }
          )
          .then((response) => response.data.correct)
          .catch(() => {
            Swal.showValidationMessage('Contraseña incorrecta o error en la verificación');
          });
      },
    }).then((result) => {
      if (result.isConfirmed) {
        if (result.value) {
          mensajeNoAutorizado.value = !mensajeNoAutorizado.value;
            setTimeout(() => {
              showToast('success', 'Contraseña correcta');
              window.print();
              handleEditSales();
            }, 1000);
        } else {
          showToast('error', 'Contraseña incorrecta');
          handleEditSales();
        }
      }
    }).catch((error) => {
      showToast('error', error.message || 'Error inesperado');
    });
  }
};
 



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
      cashPayments.value = response.props.cashPayments
      cardPayments.value = response.props.cardPayments
      productsUsed.value = response.props.productsUsed
      productsUsedOriginal.value = JSON.parse(JSON.stringify(response.props.productsUsed)); // Clona los originales
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
    customClass: "no-print",
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


  router.post(route('corte-caja.guardar-inicial'), data, {
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
        showToast("success", "Cantidad final guardada correctamente");
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

