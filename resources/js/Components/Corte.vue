<template>
  <div class="print min-h-screen py-6 flex rounded-2xl border-0 flex-col justify-center sm:py-12">
    <div class="relative py-3 w-full px-4 sm:px-0">
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
              <button class="no-print text-sm rounded-lg shadow-lg px-3 py-2 bg-orange-500 text-white hover:bg-orange-600" @click="fetchFilteredData">Aplicar Filtro</button>
              <button class="no-print text-sm rounded-lg shadow-lg px-3 py-2 bg-gray-500 text-white hover:bg-gray-600" @click="resetFilters">Limpiar Filtro</button>
            </div>
          </div>
        </div>

        <!-- Mensaje de error -->
        <div v-if="error" class="text-red-500 font-bold mt-2">{{ error }}</div>

        <!-- Cantidad inicial y final -->
        <div class="mb-6 no-print" v-if="isToday">
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

          <label for="finalCash" class="block text-sm font-medium text-gray-700 mt-4">Cantidad Final en Caja</label>
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
              <p class="font-medium">${{ Number(cashPayments) + Number(cardPayments) }}</p>
            </div>
          </div>
        </div>

        <!-- Ventas -->
        <div class="mb-6">
          <div class="flex justify-between">
            <h2 class="text-xl font-semibold mb-4">Ventas</h2>
            <button @click="imprimir" class="no-print size-fit py-1 px-2 mr-8 rounded-md text-white hover:bg-purple-600 bg-purple-500">
              Imprimir
            </button>
          </div>
          <div v-if="ventas.length <= 0" class="text-gray-500">No se han vendido productos en este período.</div>
          <div v-else class="overflow-x-auto">
            <table class="tabla min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th v-for="tab in tabTitles" :key="tab" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    {{ tab }}
                  </th>
                  <th v-if="$page.props.user.roles[0] != 'trabajador'" class="no-print px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Acciones
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr class="odd:bg-white even:bg-gray-100" v-for="venta in ventas" :key="venta.id">
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-center">
                    {{ venta.id }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap overflow-auto text-sm text-gray-500">
                    <span class="print">
                      {{ `${venta?.usuario?.name} 
                          ${venta?.usuario?.apellido_p ? venta?.usuario?.apellido_p : ''} 
                          ${venta?.usuario?.apellido_m ? venta?.usuario?.apellido_m : ''}` }}
                    </span> 
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                    {{ venta.created_at.split('T')[1].split('.')[0] }}
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-500">
                    <div class="flex flex-col space-y-1">
                      <div v-for="producto in venta.detalles" :key="producto.id" class="flex justify-between">
                        <span>
                          {{ producto.cantidad }} × {{ producto.producto?.nombre || 'Producto desconocido' }}
                        </span>
                        <span 
                          :class="[
                            'font-semibold', 
                            (!producto?.cantidadEditado || producto?.cantidadEditado === producto?.cantidad )
                              ? 'text-gray-700'  
                              : 'text-red-700'
                          ]">
                          ${{ (producto.producto?.precio ?? 0) * (producto.cantidad ?? 0) }}
                        </span>

                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-500 capitalize text-center">
                    {{ venta.metodo_pago }}
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-900 font-semibold text-right">
                    ${{ venta.total }}
                  </td>
                  <td v-if="$page.props.user.roles[0] != 'trabajador'" class="no-print px-6 py-4 text-center">
                    <div class="flex justify-center gap-2">
                      <button @click="editVenta(venta.id)" class="text-white rounded-md px-2 py-1 bg-orange-500 hover:bg-orange-700">
                        Editar
                      </button>
                      <!-- <button @click="deleteVenta(venta.id)" class="text-white rounded-md px-2 py-1 bg-red-500 hover:bg-red-700">
                        Eliminar
                      </button> -->
                    </div>
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

  <ChartCorte 
      v-if="$page.props.user.roles[0] != 'trabajador'"
      :ventasProductos="ventasProductos"
      :inventario="inventario"
  />

  <div v-if="isEditing" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
    <div class="bg-white p-6 rounded-lg overflow-auto w-1/2 max-h-96">
      <h2 class="text-xl font-bold mb-4">Editar Venta</h2>
      <div class="">

        <div v-for="(producto, index) in editedProducts" :key="producto.id" class="mb-4">
          <label class="block text-sm font-medium text-gray-700">{{ producto.nombre }}</label>
          <input
            type="number"
            v-model="producto.cantidad"
            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-orange-500 focus:border-orange-500 sm:text-sm rounded-md"
            min="1"
          />
        </div>
        <div class="flex justify-end">
          <button @click="saveEditedVenta" class="bg-orange-500 text-white px-4 py-2 rounded-md hover:bg-orange-600">Guardar</button>
          <button @click="cancelEdit" class="ml-2 bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">Cancelar</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import Swal from 'sweetalert2'
import { route } from '../../../vendor/tightenco/ziggy/src/js'
import ChartCorte from './ChartCorte.vue'

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
const isLoading = ref(false)
const error = ref('')
const ventasProductos = ref(props.ventasProductos)
const inventario = ref(props.inventario)
const ventas = ref(props.ventas)

const tabTitles = ['ID Venta', 'Creado por', 'Hora', 'Productos vendidos', 'Metodo de pago', 'Total']

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

  router.post('/corte-caja', {
    filter: filter, 
    value: value 
  }, {
    preserveScroll: true,
    onSuccess(response) {
      cashPayments.value = response.props.cashPayments
      cardPayments.value = response.props.cardPayments
      productsUsed.value = response.props.productsUsed
      ventas.value = response.props.ventas
      initialCash.value = response.props.initialCash || 0
      finalCash.value = response.props.finalCash || 0
      isLoading.value = false
      ventasProductos.value = response.props.ventasProductos || []
      inventario.value = response.props.inventario || []
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
  selectedDate.value = today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0') + '-' + String(today.getDate()).padStart(2, '0');
  selectedWeek.value = null
  selectedMonth.value = null
  fetchFilteredData()
}

const imprimir = () => {
  window.print()
}

const calculatePayments = () => {
  cashPayments.value = props?.ventas?.reduce((total, venta) => venta.metodo_pago === 'efectivo' ? Number(total) + Number(venta.total) : Number(total), 0)
  cardPayments.value = props?.ventas?.reduce((total, venta) => venta.metodo_pago === 'tarjeta' ? Number(total) + Number(venta.total) : Number(total), 0)
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

const handleSaveInitialCash = async () => {
  const data = {
    sucursal_id: props?.sucursal_id,
    usuario_id: props?.usuario_id,
    fecha: selectedDate.value,
    dinero_inicio: initialCash.value
  };

  try {
    await router.post(route('corte-caja.guardar-inicial'), data, { preserveScroll: true });
    showToast("success", "Cantidad inicial guardada correctamente");
  } catch (e) {
    showToast("error", e.error || "Error al guardar la cantidad inicial");
  }
};

const handleSaveFinalCash = async () => {
  const data = {
    sucursal_id: props?.sucursal_id,
    usuario_id: props?.usuario_id,
    fecha: selectedDate.value,
    dinero_final: finalCash.value
  };

  try {
    await router.post('/corte-caja/guardar-final', data, { preserveScroll: true });
    showToast("success", "Cantidad final guardada correctamente");
  } catch (e) {
    showToast("error", e.error || "Error al guardar la cantidad final");
  }
};

onMounted(() => {
  calculatePayments()
})

const safeToFixed = (value) => {
  return parseFloat(value).toFixed(2)
}

const isEditing = ref(false)
const editedProducts = ref([])
const editedVentaId = ref(null)

const editVenta = (ventaId) => {
  const venta = ventas.value.find(v => v.id === ventaId);
  editedProducts.value = venta.detalles.map(detalle => ({
    id: detalle.producto.id,
    nombre: detalle.producto.nombre,
    cantidad: detalle.cantidad,
    oldQuantity:  detalle.producto.cantidad,
    precio: detalle.producto.precio
  }));
  editedVentaId.value = ventaId;
  isEditing.value = true;
};

const saveEditedVenta = async () => {
  
  const data = {
    venta_id: editedVentaId.value,
    productos: editedProducts.value.map(producto => ({
      id: producto.id,
      ticketQuantity: producto.cantidad,
      oldQuantity: producto.oldQuantity // Guardar la cantidad anterior para ajustar el inventario
    })),
  };

  console.log(data);
  try {
    const response = await axios.post('/ventas/editar', data);
    if (response.status === 200) {
      showToast("success", "Venta actualizada correctamente");
      fetchFilteredData(); // Recargar los datos para reflejar los cambios
      isEditing.value = false;
    } else {
      showToast("error", response.data.error || "Error al actualizar la venta");
    }
  } catch (error) {
    showToast("error", error.response.data.error || "Error al actualizar la venta");
  }
};

const cancelEdit = () => {
  isEditing.value = false
}
</script>

<style>
.print {
    display: block !important;
}

@media print {
    .no-print {
        display: none !important;
    }

    .print {
        display: block !important;
    }

    body {
        overflow: scroll !important;
    }

    ::-webkit-scrollbar {
        display: none;
    }

    astro-dev-toolbar {
        display: none !important;
    }

    article {
        break-inside: avoid;
    }

    @page {
        size: A4 landscape;
        margin: 0;
    }

    .tabla {
        width: 100%;
        table-layout: fixed;
    }

    .tabla th, .tabla td {
        word-wrap: break-word;
    }
}

@media print {
  .text-red-700 {
    color: #6b7280 !important; /* Cambiar a gris al imprimir */
  }
}

</style>