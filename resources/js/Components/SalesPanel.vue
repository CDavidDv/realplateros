<template>
  <div class="container mx-auto">
    <div class=" flex justify-between">
      <div class="flex-1">
        <h1 class="text-2xl font-bold mb-4" v-if="isAlmacen">Tablero de Asignación</h1>
        <h1 class="text-2xl font-bold mb-4" v-else>Tablero de Ventas</h1>
      </div>
      <EstadoPastes class="flex-1"
              v-if="$page.props.contadorEstados && !isAlmacen"
              :contadores="$page.props.contadorEstados"
          />
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <!-- Inventario -->
      <div class="bg-white shadow rounded-lg p-6">
        <div class="flex gap-8 justify-between">
          <h2 class="text-xl font-semibold mb-4">Inventario</h2>
          <input
            v-model="searchTerm"
            :placeholder="[isAlmacen ? 'Buscar en almacén...' : 'Buscar en inventario...']"
            class="w-6/12 p-2 mb-4 border rounded"
          />
        </div>
        
        <div v-for="category in categories" :key="category" class="mb-6 overflow-scroll md:overflow-hidden">
          <h3 class="text-lg font-semibold mb-2 text-gray-700 capitalize">{{ category }}</h3>
          <table class="min-w-full">
            <thead>
              <tr>
                <th class="text-left">Nombre</th>
                <th class="text-left" v-if="!isAlmacen">Precio</th>
                <th class="text-left hidden sm:block">Disponible</th>
                <th class="text-left" v-if="props.user.roles[0] !== 'supervisor'">Acción</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in filteredInventory(category)" :key="item.id">
                <td class="flex flex-col w-full">
                  <div class="flex items-center gap-2" >
                    <p>{{ item.nombre }}</p>
                    <!-- Indicador de horneado -->
                    <span
                      v-if="getCantidadEnHorno(item.nombre) > 0"
                      class="inline-flex items-center gap-1 px-2 py-0.5 bg-orange-100 text-orange-700 text-xs font-semibold rounded-full border border-orange-200"
                    >
                      <svg class="h-3 w-3 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z" />
                      </svg>
                      {{ getCantidadEnHorno(item.nombre) }} en horno
                    </span>
                  </div>
                  <p class="pl-6 text-sm text-gray-500">{{ item.detalle }}</p>
                </td>
                <td v-if="!isAlmacen">${{ item.precio }}</td>
                <td class="hidden sm:block">{{ item.cantidad }}</td>
                <td v-if="props.user.roles[0] !== 'supervisor'">
                  <button
                    @click="addToTicket(item)"
                    :disabled="item.cantidad === 0"
                    class="px-3 py-1 bg-orange-500 text-white rounded hover:bg-orange-600 disabled:opacity-50"
                    v-if="isAlmacen"
                  >
                    Asignar
                  </button>
                  <button
                  v-else
                    @click="addToTicket(item)"
                    :disabled="item.cantidad === 0"
                    class="px-3 py-1 bg-orange-500 text-white rounded hover:bg-orange-600 disabled:opacity-50"
                  >
                    Agregar
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <!-- Ticket de Venta -->
      <div class="bg-white w-full shadow rounded-lg p-6" v-if="props.user.roles[0] !== 'supervisor'">
        <h2 class="text-xl font-semibold mb-4" v-if="isAlmacen">Ticket de Asignación</h2>
        <h2 class="text-xl font-semibold mb-4" v-else>Ticket de Venta</h2>
        <div class="w-full flex flex-col gap-2 py-5" v-if="isAlmacen">
          <div class="w-full flex justify-between">
            <span class="text-lg font-bold text-gray-600 " v-if="isAlmacen">
              Sucursal a la que se asignará
            </span>
            <select class="w-fit" v-model="sucursal_id">
              <option v-for="sucursal  in sucursales" :key="sucursal.id"  :value="sucursal.id">
                {{ sucursal.nombre }}
              </option>
            </select>
          </div>
          <div class="w-full flex justify-between">
            <span class="text-lg w-full font-bold text-gray-600 " v-if="isAlmacen">
              Asignar responsable
            </span>
            <select class="w-fit" v-model="trabajador_id">
              <option value="0">No asignar</option>
              <option v-for="trabajador in trabajadores" :key="trabajador.id" :value="trabajador.id">
                {{ trabajador.name }}
              </option>
            </select>
          </div>
        </div>
        <table class="min-w-full">
          <thead>
            <tr>
              <th class="text-left">Producto</th>
              <th class="text-left">Cantidad</th>
              <th class="text-left" v-if="!isAlmacen">Precio</th>
              <th class="text-left">Acción</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in ticket" :key="item.id">
              <td>
                <p>{{ item.nombre }}</p>
                <p class="pl-6 text-sm text-gray-500">{{ item.detalle }}</p>
              </td>
              <td>{{ item.ticketQuantity }}</td>
              <td v-if="!isAlmacen">${{ (item.precio * item.ticketQuantity).toFixed(2) }}</td>
              <td>
                <button
                  @click="removeFromTicket(item.id)"
                  class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600"
                >
                  Quitar
                </button>
              </td>
            </tr>
          </tbody>
        </table>
        <div class="flex justify-end mt-4 gap-2" v-if="!isAlmacen">
          <select v-model="facturado" class="rounded">
            <option value="false">No facturado</option>
            <option value="true">Facturado</option>
          </select>
          <select v-model="metodoPago" class="rounded">
            <option value="efectivo">Efectivo</option>
            <option value="tarjeta">Tarjeta</option>
          </select>
        </div>
        <div class="mt-2" v-if="!isAlmacen">
          <label class="text-xs text-gray-500">Vendedor (matrícula)</label>
          <input
            v-model="vendedorEmail"
            type="text"
            placeholder="Vacío = usuario actual"
            class="w-full px-2 py-1 text-sm border border-gray-300 rounded"
          />
        </div>
        <div :class="['mt-4 flex  items-center', isAlmacen ? 'justify-end' : 'justify-between']">
          <span class="text-lg font-bold" v-if="!isAlmacen">Total: ${{ totalAmount.toFixed(2) }}</span>
          <button
            @click="completeSale"
            :disabled="ticket.length === 0 || loading"
            class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 disabled:opacity-50"
            v-if="!isAlmacen"
          >
            {{ loading ? 'Procesando...' : 'Completar Venta' }}
          </button>
          <button v-else 
              @click="completeAsignacion"
              :disabled="ticket.length === 0 || loading"
              class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 disabled:opacity-50"
            >
              {{ loading ? 'Procesando...' : 'Completar Asignacin' }}
            
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import Swal from 'sweetalert2'
import EstadoPastes from './EstadoPastes.vue';


const { props } = usePage();
const isAlmacen = ref(props.auth.user.es_almacen);
const inventario = props.inventario;


const ticket = ref([]);
const sucursal_id = ref(0);
const trabajador_id = ref(0);
const searchTerm = ref('');
const metodoPago = ref('efectivo');
const facturado = ref(false);
const loading = ref(false);
const vendedorEmail = ref('');
const categories = ref()
const sucursales = ref(
  props.sucursales.filter(item => item.nombre !== 'almacen')
);

const trabajadores = ref(
  props.trabajadores
);

const estimacionesHoy = ref(
  props.estimacionesHoy
);



if(isAlmacen.value){
  categories.value = props.categorias.map(item => item.tipo)
}else{
  categories.value = ['pastes', 'empanadas saladas', 'empanadas dulces', 'bebida', 'extras']
}


const filteredInventory = (category) =>
  inventario.filter(item =>
    item.nombre === '-' ? '' :
    item.tipo === category &&
    item.nombre.toLowerCase().includes(searchTerm.value.toLowerCase())
  );

// Funcion para verificar si un paste esta siendo horneado y obtener la cantidad total
const getCantidadEnHorno = (pasteNombre) => {
  if (!props.contadorEstados || !props.contadorEstados.hornos_activos) {
    return 0;
  }

  let cantidadTotal = 0;

  // Iterar sobre todos los hornos activos
  props.contadorEstados.hornos_activos.forEach(horno => {
    if (horno.pastes && Array.isArray(horno.pastes)) {
      // Buscar el paste en este horno
      horno.pastes.forEach(paste => {
        if (paste.nombre === pasteNombre) {
          cantidadTotal += paste.cantidad || 0;
        }
      });
    }
  });

  return cantidadTotal;
};

const addToTicket = (item) => {
  if (item.cantidad <= 0) return;
  const existingItem = ticket.value.find(i => i.id === item.id);
  existingItem ? existingItem.ticketQuantity++ : ticket.value.push({ ...item, ticketQuantity: 1 });
  item.cantidad--;
};

const removeFromTicket = (itemId) => {
  const index = ticket.value.findIndex(item => item.id === itemId);
  if (index !== -1) {
    const item = ticket.value[index];
    item.ticketQuantity > 1 ? item.ticketQuantity-- : ticket.value.splice(index, 1);
    inventario.find(i => i.id === item.id).cantidad++;
  }
};

const totalAmount = computed(() => 
  ticket.value.reduce((total, item) => total + item.precio * item.ticketQuantity, 0)
);

const showToast = (icon, title) => {
  Swal.mixin({
    toast: true,
    position: "top-end",
    showConfirmButton: false,
    timer: 1500,
    timerProgressBar: true,
    didOpen: (toast) => {
      toast.onmouseenter = Swal.stopTimer;
      toast.onmouseleave = Swal.resumeTimer;
    }
  }).fire({ icon, title });
};

const completeSale = () => {
  // Evitar múltiples envíos
  if (loading.value || ticket.value.length === 0) return;
  
  loading.value = true;
  router.post('/ventas', {
    productos: ticket.value,
    total: totalAmount.value,
    metodo_pago: metodoPago.value,
    factura: facturado.value,
    vendedor_email: vendedorEmail.value || null,
  }, {
    preserveScroll: true,
    onSuccess: (a) => {
      console.log("props: ",a)

      
      setTimeout(() => {
        console.log("a.props.venta?.idVentaDia || a.props.ticket_id: ",a.props.venta?.idVentaDia || a.props.ticket_id)
        printTicket(a.props.venta?.idVentaDia || a.props.ticket_id);
        inventario.value = a.props.inventario;
        ticket.value = [];
        searchTerm.value = '';
        metodoPago.value = 'efectivo';
        facturado.value = false;
        vendedorEmail.value = '';
        showToast("success", "Registrado correctamente");
        loading.value = false;
      }, 500);
      
    },
    onError: (errors) => {
      console.error('Error al enviar el pedido:', errors);
      showToast("error", "Error al registrar");
      loading.value = false;
    }
  });
};

const completeAsignacion = () => {  
  // Evitar múltiples envíos
  if (loading.value || ticket.value.length === 0) return;
  if (sucursal_id.value === 0) {
    showToast("error", "Seleccione una sucursal");
    return;
  }

  loading.value = true;
  router.post('/asignar', {
    productos: ticket.value,
    sucursal_id: sucursal_id.value,
    trabajador_id: trabajador_id.value,
    total: '-', // Total no aplica para asignaciones
    metodo_pago: '-', // Método de pago no aplica
  }, {
    preserveScroll: true,
    onSuccess: (a) => {
      // Agregar el ítem especial al inicio del ticket
      ticket.value.unshift({
        id: a.props.ticket_id || 0, // ID único para este ítem
        nombre: `Asignación ticket no.`, // Nombre visible en el ticket
        detalle: `Sucursal: ${sucursal_id.value}`, // Detalle adicional
        ticketQuantity: a.props.ticket_id || 0,
        precio: 0,
        sucursal_id: 0,
        tipo: 'asignacion'
      });

      // Imprimir 3 veces el ticket
      printTicket(a.props.ticket_id);
      printTicket(a.props.ticket_id);
      printTicket(a.props.ticket_id);

      // Resetear valores después de registrar
      inventario.value = a.props.inventario;
      ticket.value = [];
      searchTerm.value = '';
      sucursal_id.value = 0;
      trabajador_id.value = 0;
      metodoPago.value = 'efectivo';
      showToast("success", "Registrado correctamente");
      loading.value = false;
    },
    onError: (errors) => {
      console.error('Error al enviar el pedido:', errors);
      showToast("error", "Error al registrar");
      loading.value = false;
    }
  });
};


const printTicket = (id) => {
  console.log(ticket.value)
  
  fetch('https://print.test/print-ticket', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      productos: ticket.value,
      total: totalAmount.value || '-',
      metodo_pago: metodoPago.value || '-',
      ticket_id: id,
    })
  })
  .then(data => {
    if (data.success) {
      showToast('success', 'Ticket impreso correctamente');
    }
  })
  .catch(error => {
    console.error('Error en la impresión del ticket:', error);
    showToast('error', 'Error al imprimir el ticket');
  });
};



</script>