<template>
  <div class="container mx-auto">
    <h1 class="text-2xl font-bold mb-4" v-if="isAlmacen">Tablero de Asignación</h1>
    <h1 class="text-2xl font-bold mb-4" v-else>Tablero de Ventas</h1>
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
                <th class="text-left">Acción</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in filteredInventory(category)" :key="item.id">
                <td class="flex flex-col w-full">
                  <p>{{ item.nombre }}</p>
                  <p class="pl-6 text-sm text-gray-500">{{ item.detalle }}</p>
                </td>
                <td v-if="!isAlmacen">${{ item.precio }}</td>
                <td class="hidden sm:block">{{ item.cantidad }}</td>
                <td>
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
      <div class="bg-white w-full shadow rounded-lg p-6">
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
        <div class="flex justify-end mt-4" v-if="!isAlmacen">
          <select v-model="metodoPago" class="rounded">
            <option value="efectivo">Efectivo</option>
            <option value="tarjeta">Tarjeta</option>
          </select>
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


const { props } = usePage();
const isAlmacen = ref(props.user.roles[0] === 'almacen')
const inventario = props.inventario;


const ticket = ref([]);
const sucursal_id = ref(0);
const trabajador_id = ref(0);
const searchTerm = ref('');
const metodoPago = ref('efectivo');
const loading = ref(false);
const categories = ref()
const sucursales = ref(
  props.sucursales.filter(item => item.nombre !== 'almacen')
);

const trabajadores = ref(
  props.trabajadores
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
  //variable
  if (ticket.value.length === 0) return;
  
  loading.value = true;
  router.post('/ventas', {
    productos: ticket.value,
    total: totalAmount.value,
    metodo_pago: metodoPago.value
  }, {
    preserveScroll: true,
    onSuccess: (a) => {
      
      printTicket(a.props.ticket_id)
      
      inventario.value = a.props.inventario;
      ticket.value = [];
      searchTerm.value = '';
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

const completeAsignacion = () => {  
  if (ticket.value.length === 0) return;
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