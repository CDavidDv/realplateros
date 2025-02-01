<template>
  <div class="p-10 min-h-screen bg-gray-50">
    <!-- Encabezado -->
    <div class="flex flex-col md:flex-row justify-between items-center mb-8">
      <h1 class="text-2xl font-bold text-gray-800">Tarjetas Recientes</h1>
      <form @submit.prevent="handleSearch" class="flex flex-col md:flex-row gap-4 mt-4 md:mt-0">
        <div class="flex flex-col">
          <label for="fecha" class="text-sm font-medium text-gray-700 mb-1">Buscar por fecha</label>
          <input
            type="date"
            id="fecha"
            v-model="fecha"
            required
            class="p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
          />
        </div>
        <div class="flex flex-col">
          <label for="sucursal" class="text-sm font-medium text-gray-700 mb-1">Buscar por sucursal</label>
          <select
            id="sucursal"
            v-model="sucursal"
            required
            class="py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
          >
            <option value="">Seleccione sucursal</option>
            <option value="todas">Todas</option>
            <option
              v-for="sucursal in sucursales"
              :key="sucursal.id"
              :value="sucursal.id"
            >
              {{ sucursal.nombre }}
            </option>
          </select>
        </div>
        <button
          type="submit"
          class="mt-6 md:mt-0 px-4 h-fit py-2 bg-orange-500 text-white font-semibold rounded-lg hover:bg-orange-600 transition"
        >
          Buscar
        </button>
      </form>
    </div>

    <!-- Tarjetas de tickets -->
    <div class="container mx-auto grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <TicketCard
        title="Entregas Asignados"
        :tickets="ticketsAsignados"
        tipo="asignar"
        @view-ticket-details="handleTicketDetails"
        @cancel-ticket="handleCancelTicket"
      />
      <TicketCard
        title="Entregas Completados"
        :tickets="ticketsCerrados"
        tipo="cerrar"
        @view-ticket-details="handleTicketDetails"
      />
      <TicketCard
        title="Entregas Cancelados"
        :tickets="ticketsCancelados"
        tipo="cancelar"
        @view-ticket-details="handleTicketDetails"
      />
    </div>

    <!-- Modal para ver detalles -->
    <div
      v-if="showModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
    >
      <div class="bg-white p-8 rounded-2xl shadow-2xl w-full max-w-screen-lg overflow-y-auto max-h-screen">
        <h3 class="text-2xl font-bold text-gray-800 mb-6 text-center border-b pb-4">
          Detalles del Ticket
        </h3>
        <div class="space-y-4">
          <p><strong class="font-semibold">ID:</strong> {{ selectedTicket.id }}</p>
          <p><strong class="font-semibold">Empleado:</strong> {{ selectedTicket?.usuario?.name || 'No asignado' }}</p>
          <p><strong class="font-semibold">Sucursal destino:</strong> {{ selectedTicket?.sucursal?.nombre || 'Sin destino' }}</p>
          <p><strong class="font-semibold">Hora salida:</strong> {{ selectedTicket.hora_salida || 'N/A' }}</p>
          <p v-if="selectedTicket.hora_llegada">
            <strong class="font-semibold">Hora llegada:</strong> {{ selectedTicket.hora_llegada }}
          </p>
        </div>
        <div class="mt-6">
          <span class="font-semibold text-lg text-gray-800">Productos:</span>
          <ul class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mt-2">
            <li
              v-for="producto in selectedTicket.ticket_productos_asignacion"
              :key="producto.id"
              class="p-4 border rounded-lg shadow-sm hover:shadow-md transition"
            >
              <p><strong>Nombre:</strong> {{ producto?.producto?.nombre }}</p>
              <p><strong>Tipo:</strong> {{ producto?.producto?.tipo }}</p>
              <p><strong>Cantidad:</strong> {{ producto?.cantidad }}</p>
            </li>
          </ul>
        </div>
        <div class="flex justify-center mt-6">
          <button
            class="px-6 py-2 bg-red-500 text-white font-semibold text-sm rounded-lg hover:bg-red-600 transition"
            @click="closeModal"
          >
            Cerrar
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { router, usePage } from "@inertiajs/vue3";
import { ref } from "vue";
import TicketCard from "./TicketCard.vue";
import Swal from "sweetalert2";

const { props } = usePage();
const ticketsCerrados = ref(props.ticketsCerrados);
const ticketsAsignados = ref(props.ticketsAsignados);
const ticketsCancelados = ref(props.ticketsCancelados);
const sucursales = ref(props.sucursales);

const showModal = ref(false);
const selectedTicket = ref(null);
const fecha = ref("");
const sucursal = ref("");

const handleTicketDetails = (ticket) => {
  selectedTicket.value = ticket;
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  selectedTicket.value = null;
};

const handleCancelTicket = (ticket) => {
  router.post('/tickets/cancelar', { ticket: ticket.id }, {
    preserveScroll: true,
    onSuccess: (response) => {
      ticketsAsignados.value = response.props.ticketsAsignados;
      ticketsCancelados.value = response.props.ticketsCancelados;
    },
    onError: (error) => {
      console.error('Error al cancelar el ticket:', error);
    }
  });
};

const Toast = Swal.mixin({
  toast: true,
  position: "top-end",
  showConfirmButton: false,
  timer: 3000,
  timerProgressBar: true,
  didOpen: (toast) => {
    toast.onmouseenter = Swal.stopTimer;
    toast.onmouseleave = Swal.resumeTimer;
  }
});

const handleSearch = () => {
  if (!fecha.value || !sucursal.value) {
    Toast.fire({
      icon: "error",
      title: "Por favor, seleccione una fecha y una sucursal"
    });
    return;
  }

  axios.post('/tickets/buscar', { fecha: fecha.value, sucursal: sucursal.value })
    .then(response => {
      ticketsAsignados.value = response.data.ticketsAsignados;
      ticketsCerrados.value = response.data.ticketsCerrados;
      ticketsCancelados.value = response.data.ticketsCancelados;
      console.log(response.data);
      Toast.fire({
        icon: "success",
        title: "Tickets encontrados"
      });
    })
    .catch(error => {
      console.error('Error al buscar tickets:', error);
      Toast.fire({
        icon: "error",
        title: "Error al buscar tickets"
      });
    });
};
</script>

<style scoped>
/* Estilos para el modal */
.fixed {
  display: flex;
  align-items: center;
  justify-content: center;
}

.bg-opacity-50 {
  background-color: rgba(0, 0, 0, 0.5);
}
</style>