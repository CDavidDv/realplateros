<template>
  <div class="p-6 text-left border shadow-lg rounded-xl bg-white">
    <h2 class="text-xl font-semibold mb-4 "
        :class="{
            'text-yellow-500': tipo === 'asignar',
            'text-green-500': tipo === 'cerrar',
            'text-red-500': tipo === 'cancelar',
          }">{{ title }}</h2>

    <div v-if="tickets.length">
      <ul
        class="overflow-y-auto scrollbar-thin divide-y divide-gray-200"
        style="max-height: 25rem"
      >
        <li
          v-for="ticket in tickets"
          :key="ticket.id"
          class="p-4 flex justify-between items-start bg-gradient-to-br rounded-lg mb-2"
          :class="{
            'from-yellow-50 to-yellow-100': tipo === 'asignar',
            'from-green-50 to-green-100': tipo === 'cerrar',
            'from-red-50 to-red-100': tipo === 'cancelar',
          }"
        >
          <div class="w-full h-full">
            <p :class="`text-sm text-${textColor}`">
              <strong>ID:</strong> {{ ticket.id }}
            </p>
            <p :class="`text-sm text-${textColor}`">
              <strong>Empleado:</strong> {{ ticket?.usuario?.name || 'No asignado' }}
            </p>
            <p :class="`text-sm text-${textColor}`">
              <strong>Sucursal destino:</strong> {{ ticket?.sucursal?.nombre || 'Sin destino' }}
            </p>
            <p :class="`text-sm text-${textColor}`">
              <strong>Hora salida:</strong> {{ ticket.hora_salida || 'N/A' }}
            </p>
            <p v-if="ticket.hora_llegada" :class="`text-sm text-${textColor}`">
              <strong>Hora llegada:</strong> {{ ticket.hora_llegada }}
            </p>
          </div>
          <div class="w-1/4 my-auto h-full flex flex-col gap-2   items-center justify-center">
            <button
              class="text-sm bg-blue-400 hover:bg-blue-500 text-gray-50 py-1 px-2 rounded-xl "
              @click="viewDetails(ticket)"
            >
              Ver detalles
            </button>
            <button 
              v-if="tipo === 'asignar'"
              class="text-sm bg-red-400 hover:bg-red-500 text-gray-50 py-1 px-2 rounded-xl ml-2"
              @click="CancelTicket(ticket)"
            >
              Cancelar
            </button>
          </div>

        </li>
      </ul>
    </div>

    <p v-else :class="`text-${textColor}`">
      {{ emptyMessage || 'No hay tickets en esta categoría' }}
    </p>
  </div>
</template>

<script setup>
import { defineEmits } from "vue";

defineProps({
  title: {
    type: String,
    required: true,
  },
  tickets: {
    type: Array,
    required: true,
  },
  textColor: {
    type: String,
    default: 'gray-800',
  },
  tipo: {
    type: String,
    default: 'asignar',
  },
  emptyMessage: {
    type: String,
    default: '',
  },
});

const emit = defineEmits(['view-ticket-details', 'cancel-ticket']);

const viewDetails = (ticket) => {
  emit('view-ticket-details', ticket);
};

const CancelTicket = (ticket) => {
  emit('cancel-ticket', ticket);
};
</script>

<style scoped>
/* Custom scrollbar styling */
ul::-webkit-scrollbar {
  width: 8px;
}

ul::-webkit-scrollbar-thumb {
  background-color: rgba(0, 0, 0, 0.2);
  border-radius: 4px;
}

ul::-webkit-scrollbar-thumb:hover {
  background-color: rgba(0, 0, 0, 0.4);
}
</style>
