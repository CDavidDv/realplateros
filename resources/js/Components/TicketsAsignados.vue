<template>
  <div class="container mx-auto p-8 md:p-10 grid grid-cols-1 sm:grid-cols-3 gap-2">
    <!-- Tarjeta de Tickets Asignados -->
    <TicketCard
      title="Tickets Asignados"
      :tickets="ticketsAsignados"
      tipo="asignar"
      @view-ticket-details="handleTicketDetails"
      @cancel-ticket="handleCancelTicket"
    />

    <!-- Tarjeta de Tickets Cerrados -->
    <TicketCard
      title="Tickets Completados"
      :tickets="ticketsCerrados"
      tipo="cerrar"
      @view-ticket-details="handleTicketDetails"
    />

    <!-- Tarjeta de Tickets Cancelados -->
    <TicketCard
      title="Tickets Cancelados"
      :tickets="ticketsCancelados"
      tipo="cancelar"
      @view-ticket-details="handleTicketDetails"
    />
  </div>

  <!-- Modal para ver detalles -->
<div 
  v-if="showModal" 
  class="fixed overflow-scroll  pt-20 inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
>
  <div class="bg-white p-8 rounded-2xl shadow-2xl w-full max-w-screen-xl relative">
    <!-- Título del modal -->
    <h3 class="text-2xl font-bold text-gray-800 mb-6 text-center border-b pb-4">
      Detalles del Ticket
    </h3>
    
    <!-- Información principal -->
    <div class="space-y-4">
      <p><strong class="font-semibold">ID:</strong> {{ selectedTicket.id }}</p>
      <p><strong class="font-semibold">Empleado:</strong> {{ selectedTicket?.usuario?.name || 'No asignado' }}</p>
      <p><strong class="font-semibold">Sucursal destino:</strong> {{ selectedTicket?.sucursal?.nombre || 'Sin destino' }}</p>
      <p><strong class="font-semibold">Hora salida:</strong> {{ selectedTicket.hora_salida || 'N/A' }}</p>
      <p v-if="selectedTicket.hora_llegada">
        <strong class="font-semibold">Hora llegada:</strong> {{ selectedTicket.hora_llegada }}
      </p>
    </div>
    
    <!-- Productos -->
    <div class="mt-6">
      <span class="font-semibold text-lg w-40 text-gray-800">Productos:</span>
      <ul class="grid grid-cols-1 sm:grid-cols-3  gap-4 mt-2">
        <li 
          v-for="producto in selectedTicket.ticket_productos_asignacion" 
          :key="producto.id" 
          class="p-4 border rounded-lg shadow-sm"
        >
          <p><strong>Nombre:</strong> {{ producto?.producto?.nombre }}</p>
          <p><strong>Tipo:</strong> {{ producto?.producto?.tipo }}</p>
          <p><strong>Cantidad:</strong> {{ producto?.cantidad }}</p>
        </li>
      </ul>
    </div>
    
    <!-- Botón de cierre -->
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

</template>

<script setup>
import { router, usePage } from "@inertiajs/vue3";
import { ref } from "vue";
import TicketCard from "./TicketCard.vue";

// Obtener los datos de la página
const { props } = usePage();
const ticketsCerrados = ref(props.ticketsCerrados);
const ticketsAsignados = ref(props.ticketsAsignados);
const ticketsCancelados = ref(props.ticketsCancelados);

// Estado del modal
const showModal = ref(false);
const selectedTicket = ref(null);

// Abrir el modal con los detalles del ticket
const handleTicketDetails = (ticket) => {
  selectedTicket.value = ticket;
  showModal.value = true;
};

// Cerrar el modal
const closeModal = () => {
  showModal.value = false;
  selectedTicket.value = null;
};

// Cancelar un ticket
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
