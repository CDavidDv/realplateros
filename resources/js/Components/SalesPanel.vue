<template>
  <div class="container mx-auto">
    <h1 class="text-2xl font-bold mb-4">Tablero de Ventas</h1>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <!-- Inventario -->
      <div class="bg-white shadow rounded-lg p-6">
        <div class="flex gap-8 justify-between">
          <h2 class="text-xl font-semibold mb-4">Inventario</h2>
          <input
            v-model="searchTerm"
            placeholder="Buscar en inventario..."
            class="w-6/12 p-2 mb-4 border rounded"
          />
        </div>
        <div v-for="category in categories" :key="category" class="mb-6 overflow-scroll md:overflow-hidden">
          <h3 class="text-lg font-semibold mb-2 text-gray-700">{{ categoryNames[category] }}</h3>
          <table class="min-w-full">
            <thead>
              <tr>
                <th class="text-left">Nombre</th>
                <th class="text-left">Precio</th>
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
                <td>${{ item.precio }}</td>
                <td class="hidden sm:block">{{ item.cantidad }}</td>
                <td>
                  <button
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
      <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-xl font-semibold mb-4">Ticket de Venta</h2>
        <table class="min-w-full">
          <thead>
            <tr>
              <th class="text-left">Producto</th>
              <th class="text-left">Cantidad</th>
              <th class="text-left">Precio</th>
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
              <td>${{ (item.precio * item.ticketQuantity).toFixed(2) }}</td>
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
        <div class="flex justify-end mt-4">
          <select v-model="metodoPago" class="rounded">
            <option value="efectivo">Efectivo</option>
            <option value="tarjeta">Tarjeta</option>
          </select>
        </div>
        <div class="mt-4 flex justify-between items-center">
          <span class="text-lg font-bold">Total: ${{ totalAmount.toFixed(2) }}</span>
          <button
            @click="completeSale"
            :disabled="ticket.length === 0 || loading"
            class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 disabled:opacity-50"
          >
            {{ loading ? 'Procesando...' : 'Completar Venta' }}
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
const inventario = props.inventario;

const ticket = ref([]);
const searchTerm = ref('');
const metodoPago = ref('efectivo');
const loading = ref(false);
const categories = ['pastes', 'empanadas saladas', 'empanadas dulces', 'bebida', 'extras'];
const categoryNames = {
  pastes: 'Pastes',
  'empanadas saladas': 'Empanadas Saladas',
  'empanadas dulces': 'Empanadas Dulces',
  bebida: 'Bebidas',
  extras: 'Extras',
};

const filteredInventory = (category) => 
  inventario.filter(item => 
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
  printTicket();
  if (ticket.value.length === 0) return;
  loading.value = true;
  router.post('/ventas', {
    productos: ticket.value,
    total: totalAmount.value,
    metodo_pago: metodoPago.value
  }, {
    preserveScroll: true,
    onSuccess: (a) => {
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

const printTicket = () => {
  fetch('/print-ticket', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      productos: ticket.value,
      total: totalAmount.value,
      metodo_pago: metodoPago.value
    })
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      showToast('success', 'Ticket impreso correctamente');
    } else {
      showToast('error', 'Error al imprimir el ticket');
    }
  })
  .catch(error => {
    console.error('Error en la impresión del ticket:', error);
    showToast('error', 'Error al imprimir el ticket');
  });
};

</script>