<template>
  <div class="p-10 min-h-screen bg-gray-50">
    <!-- Encabezado -->
    <div class="flex flex-col md:flex-row justify-between items-center mb-8">
      <h1 class="text-2xl font-bold text-gray-800">Tarjetas Recientes</h1>
      <form @submit.prevent="handleSearch" class="flex flex-col md:flex-row gap-4 mt-4 md:mt-0">
        <div class="flex flex-col">
          <label for="fecha" class="text-sm font-medium text-gray-700 mb-1 inline-flex items-center gap-1">
            Buscar por fecha
            <span title="Busca tickets creados en este día (usa horario México)" class="inline-flex items-center justify-center w-4 h-4 text-xs font-bold text-white bg-gray-400 rounded-full cursor-help">?</span>
          </label>
          <input
            type="date"
            id="fecha"
            v-model="fecha"
            required
            class="p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
          />
        </div>
        <div class="flex flex-col">
          <label for="sucursal" class="text-sm font-medium text-gray-700 mb-1 inline-flex items-center gap-1">
            Buscar por sucursal
            <span title="Filtra por la sucursal de destino del ticket" class="inline-flex items-center justify-center w-4 h-4 text-xs font-bold text-white bg-gray-400 rounded-full cursor-help">?</span>
          </label>
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

    <!-- Gráficas - Solo para Admin de Almacén -->
    <div v-if="isAdminAlmacen" class="mt-12">
      <h2 class="text-3xl font-bold text-gray-800 mb-8 text-center">Análisis y Estadísticas</h2>

      <!-- Gráfica 1: Productos enviados por sucursal -->
      <div class="bg-white p-6 rounded-2xl shadow-lg mb-8">
        <h3 class="text-xl font-semibold text-gray-800 mb-4">Productos Enviados por Sucursal</h3>
        <div class="flex flex-col md:flex-row gap-4 mb-6">
          <div class="flex flex-col">
            <label class="text-sm font-medium text-gray-700 mb-1">Fecha Inicio</label>
            <input
              type="date"
              v-model="graficaEnviados.fechaInicio"
              class="p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
            />
          </div>
          <div class="flex flex-col">
            <label class="text-sm font-medium text-gray-700 mb-1">Fecha Fin</label>
            <input
              type="date"
              v-model="graficaEnviados.fechaFin"
              class="p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
            />
          </div>
          <button
            @click="cargarGraficaEnviados"
            class="mt-6 md:mt-0 px-6 h-fit py-2 bg-orange-500 text-white font-semibold rounded-lg hover:bg-orange-600 transition"
          >
            Actualizar
          </button>
        </div>
        <div class="h-96">
          <canvas ref="chartEnviados"></canvas>
        </div>
      </div>

      <!-- Gráfica 2: Productos que ingresan y salen -->
      <div class="bg-white p-6 rounded-2xl shadow-lg mb-8">
        <h3 class="text-xl font-semibold text-gray-800 mb-4">Movimientos de Inventario (Ingresos vs Salidas)</h3>
        <div class="flex flex-col md:flex-row gap-4 mb-6">
          <div class="flex flex-col">
            <label class="text-sm font-medium text-gray-700 mb-1">Fecha Inicio</label>
            <input
              type="date"
              v-model="graficaMovimientos.fechaInicio"
              class="p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
            />
          </div>
          <div class="flex flex-col">
            <label class="text-sm font-medium text-gray-700 mb-1">Fecha Fin</label>
            <input
              type="date"
              v-model="graficaMovimientos.fechaFin"
              class="p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
            />
          </div>
          <button
            @click="cargarGraficaMovimientos"
            class="mt-6 md:mt-0 px-6 h-fit py-2 bg-orange-500 text-white font-semibold rounded-lg hover:bg-orange-600 transition"
          >
            Actualizar
          </button>
        </div>
        <div class="h-96">
          <canvas ref="chartMovimientos"></canvas>
        </div>
      </div>

      <!-- Gráfica 3: Productos individuales enviados por sucursal -->
      <div class="bg-white p-6 rounded-2xl shadow-lg mb-8">
        <h3 class="text-xl font-semibold text-gray-800 mb-4">Productos Más Enviados por Sucursal</h3>
        <div class="flex flex-col md:flex-row gap-4 mb-6">
          <div class="flex flex-col">
            <label class="text-sm font-medium text-gray-700 mb-1">Sucursal</label>
            <select
              v-model="graficaProductosPorSucursal.sucursalId"
              class="p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
            >
              <option value="">Seleccione sucursal</option>
              <option
                v-for="sucursal in sucursales"
                :key="sucursal.id"
                :value="sucursal.id"
              >
                {{ sucursal.nombre }}
              </option>
            </select>
          </div>
          <div class="flex flex-col">
            <label class="text-sm font-medium text-gray-700 mb-1">Fecha Inicio</label>
            <input
              type="date"
              v-model="graficaProductosPorSucursal.fechaInicio"
              class="p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
            />
          </div>
          <div class="flex flex-col">
            <label class="text-sm font-medium text-gray-700 mb-1">Fecha Fin</label>
            <input
              type="date"
              v-model="graficaProductosPorSucursal.fechaFin"
              class="p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
            />
          </div>
          <button
            @click="cargarGraficaProductosPorSucursal"
            class="mt-6 md:mt-0 px-6 h-fit py-2 bg-orange-500 text-white font-semibold rounded-lg hover:bg-orange-600 transition"
          >
            Actualizar
          </button>
        </div>
        <div class="h-96">
          <canvas ref="chartProductosPorSucursal"></canvas>
        </div>
      </div>

      <!-- Gráfica 4: Productos que más ingresan y salen por sucursal -->
      <div class="bg-white p-6 rounded-2xl shadow-lg">
        <h3 class="text-xl font-semibold text-gray-800 mb-4">Productos con Más Movimientos (Ingresos vs Salidas) por Sucursal</h3>
        <div class="flex flex-col md:flex-row gap-4 mb-6">
          <div class="flex flex-col">
            <label class="text-sm font-medium text-gray-700 mb-1">Sucursal</label>
            <select
              v-model="graficaMovimientosPorSucursal.sucursalId"
              class="p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
            >
              <option value="">Seleccione sucursal</option>
              <option
                v-for="sucursal in sucursales"
                :key="sucursal.id"
                :value="sucursal.id"
              >
                {{ sucursal.nombre }}
              </option>
            </select>
          </div>
          <div class="flex flex-col">
            <label class="text-sm font-medium text-gray-700 mb-1">Fecha Inicio</label>
            <input
              type="date"
              v-model="graficaMovimientosPorSucursal.fechaInicio"
              class="p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
            />
          </div>
          <div class="flex flex-col">
            <label class="text-sm font-medium text-gray-700 mb-1">Fecha Fin</label>
            <input
              type="date"
              v-model="graficaMovimientosPorSucursal.fechaFin"
              class="p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
            />
          </div>
          <button
            @click="cargarGraficaMovimientosPorSucursal"
            class="mt-6 md:mt-0 px-6 h-fit py-2 bg-orange-500 text-white font-semibold rounded-lg hover:bg-orange-600 transition"
          >
            Actualizar
          </button>
        </div>
        <div class="h-96">
          <canvas ref="chartMovimientosPorSucursal"></canvas>
        </div>
      </div>
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
              <p><strong>Detalle:</strong> {{ producto?.producto?.detalle }}</p>
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
import { ref, computed, onMounted } from "vue";
import TicketCard from "./TicketCard.vue";
import Swal from "sweetalert2";
import axios from "axios";
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

const { props } = usePage();
const ticketsCerrados = ref(props.ticketsCerrados);
const ticketsAsignados = ref(props.ticketsAsignados);
const ticketsCancelados = ref(props.ticketsCancelados);
const sucursales = ref(props.sucursales);

const showModal = ref(false);
const selectedTicket = ref(null);
const fecha = ref("");
const sucursal = ref("");

// Referencias para los canvas de las gráficas
const chartEnviados = ref(null);
const chartMovimientos = ref(null);
const chartProductosPorSucursal = ref(null);
const chartMovimientosPorSucursal = ref(null);
let chartEnviadosInstance = null;
let chartMovimientosInstance = null;
let chartProductosPorSucursalInstance = null;
let chartMovimientosPorSucursalInstance = null;

// Verificar si es admin de almacén
const isAdminAlmacen = computed(() => {
  const user = props.auth?.user;
  if (!user) return false;
  const isAlmacen = user.es_almacen === true || user.es_almacen === 1;
  const isAdmin = user.roles?.some(role => role.name === 'admin' || role.name === 'almacen');
  return isAlmacen && isAdmin;
});

// Configuración de fechas para gráficas (últimos 30 días por defecto)
const graficaEnviados = ref({
  fechaInicio: new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
  fechaFin: new Date().toISOString().split('T')[0]
});

const graficaMovimientos = ref({
  fechaInicio: new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
  fechaFin: new Date().toISOString().split('T')[0]
});

const graficaProductosPorSucursal = ref({
  sucursalId: '',
  fechaInicio: new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
  fechaFin: new Date().toISOString().split('T')[0]
});

const graficaMovimientosPorSucursal = ref({
  sucursalId: '',
  fechaInicio: new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
  fechaFin: new Date().toISOString().split('T')[0]
});

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

// Función para cargar gráfica de productos enviados por sucursal
const cargarGraficaEnviados = async () => {
  try {
    const response = await axios.get('/almacen/graficas/productos-por-sucursal', {
      params: {
        fecha_inicio: graficaEnviados.value.fechaInicio,
        fecha_fin: graficaEnviados.value.fechaFin
      }
    });

    const datos = response.data.data || [];

    // Destruir gráfica anterior si existe
    if (chartEnviadosInstance) {
      chartEnviadosInstance.destroy();
    }

    const ctx = chartEnviados.value.getContext('2d');
    chartEnviadosInstance = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: datos.map(d => d.sucursal_nombre),
        datasets: [{
          label: 'Productos Enviados',
          data: datos.map(d => d.total_productos),
          backgroundColor: 'rgba(249, 115, 22, 0.6)',
          borderColor: 'rgba(249, 115, 22, 1)',
          borderWidth: 2
        }, {
          label: 'Total Tickets',
          data: datos.map(d => d.total_tickets),
          backgroundColor: 'rgba(59, 130, 246, 0.6)',
          borderColor: 'rgba(59, 130, 246, 1)',
          borderWidth: 2
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'top',
          },
          title: {
            display: true,
            text: `Productos Enviados (${graficaEnviados.value.fechaInicio} - ${graficaEnviados.value.fechaFin})`
          }
        },
        scales: {
          y: {
            beginAtZero: true
          }
        }
      }
    });

    Toast.fire({
      icon: "success",
      title: "Gráfica actualizada"
    });
  } catch (error) {
    console.error('Error al cargar gráfica:', error);
    Toast.fire({
      icon: "error",
      title: "Error al cargar la gráfica"
    });
  }
};

// Función para cargar gráfica de movimientos (ingresos/salidas)
const cargarGraficaMovimientos = async () => {
  try {
    const response = await axios.get('/almacen/graficas/movimientos-inventario', {
      params: {
        fecha_inicio: graficaMovimientos.value.fechaInicio,
        fecha_fin: graficaMovimientos.value.fechaFin
      }
    });

    const datos = response?.data?.data || [];

    // Extraer fechas
    const fechas = datos.map(d => d.fecha);

    // Destruir gráfica anterior si existe
    if (chartMovimientosInstance) {
      chartMovimientosInstance?.destroy();
    }

    const ctx = chartMovimientos?.value.getContext('2d');
    chartMovimientosInstance = new Chart(ctx, {
      type: 'line',
      data: {
        labels: fechas,
        datasets: [{
          label: 'Ingresos',
          data: datos.map(d => d.ingresos),
          borderColor: 'rgba(34, 197, 94, 1)',
          backgroundColor: 'rgba(34, 197, 94, 0.2)',
          tension: 0.4,
          fill: true
        }, {
          label: 'Salidas',
          data: datos.map(d => d.salidas),
          borderColor: 'rgba(239, 68, 68, 1)',
          backgroundColor: 'rgba(239, 68, 68, 0.2)',
          tension: 0.4,
          fill: true
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'top',
          },
          title: {
            display: true,
            text: `Movimientos de Inventario (${graficaMovimientos.value.fechaInicio} - ${graficaMovimientos.value.fechaFin})`
          }
        },
        scales: {
          y: {
            beginAtZero: true
          }
        }
      }
    });

    Toast.fire({
      icon: "success",
      title: "Gráfica actualizada"
    });
  } catch (error) {
    console.error('Error al cargar gráfica:', error);
    Toast.fire({
      icon: "error",
      title: "Error al cargar la gráfica"
    });
  }
};

// Función para cargar gráfica de productos individuales por sucursal
const cargarGraficaProductosPorSucursal = async () => {
  if (!graficaProductosPorSucursal.value.sucursalId) {
    Toast.fire({
      icon: "warning",
      title: "Por favor seleccione una sucursal"
    });
    return;
  }

  try {
    const response = await axios.get('/almacen/graficas/productos-individuales-por-sucursal', {
      params: {
        sucursal_id: graficaProductosPorSucursal.value.sucursalId,
        fecha_inicio: graficaProductosPorSucursal.value.fechaInicio,
        fecha_fin: graficaProductosPorSucursal.value.fechaFin
      }
    });

    const datos = response.data.data || [];

    // Destruir gráfica anterior si existe
    if (chartProductosPorSucursalInstance) {
      chartProductosPorSucursalInstance.destroy();
    }

    const ctx = chartProductosPorSucursal.value.getContext('2d');
    chartProductosPorSucursalInstance = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: datos.map(d => d.producto_nombre),
        datasets: [{
          label: 'Cantidad Total Enviada',
          data: datos.map(d => d.total_cantidad),
          backgroundColor: 'rgba(139, 92, 246, 0.6)',
          borderColor: 'rgba(139, 92, 246, 1)',
          borderWidth: 2
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'top',
          },
          title: {
            display: true,
            text: `Productos Más Enviados (${graficaProductosPorSucursal.value.fechaInicio} - ${graficaProductosPorSucursal.value.fechaFin})`
          }
        },
        scales: {
          y: {
            beginAtZero: true
          }
        }
      }
    });

    Toast.fire({
      icon: "success",
      title: "Gráfica actualizada"
    });
  } catch (error) {
    console.error('Error al cargar gráfica:', error);
    Toast.fire({
      icon: "error",
      title: "Error al cargar la gráfica"
    });
  }
};

// Función para cargar gráfica de movimientos de productos por sucursal
const cargarGraficaMovimientosPorSucursal = async () => {
  if (!graficaMovimientosPorSucursal.value.sucursalId) {
    Toast.fire({
      icon: "warning",
      title: "Por favor seleccione una sucursal"
    });
    return;
  }

  try {
    const response = await axios.get('/almacen/graficas/movimientos-productos-por-sucursal', {
      params: {
        sucursal_id: graficaMovimientosPorSucursal.value.sucursalId,
        fecha_inicio: graficaMovimientosPorSucursal.value.fechaInicio,
        fecha_fin: graficaMovimientosPorSucursal.value.fechaFin
      }
    });

    const datos = response.data.data || [];

    // Destruir gráfica anterior si existe
    if (chartMovimientosPorSucursalInstance) {
      chartMovimientosPorSucursalInstance.destroy();
    }

    const ctx = chartMovimientosPorSucursal.value.getContext('2d');
    chartMovimientosPorSucursalInstance = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: datos.map(d => d.producto_nombre),
        datasets: [{
          label: 'Ingresos',
          data: datos.map(d => d.total_ingresos),
          backgroundColor: 'rgba(34, 197, 94, 0.6)',
          borderColor: 'rgba(34, 197, 94, 1)',
          borderWidth: 2
        }, {
          label: 'Salidas',
          data: datos.map(d => d.total_salidas),
          backgroundColor: 'rgba(239, 68, 68, 0.6)',
          borderColor: 'rgba(239, 68, 68, 1)',
          borderWidth: 2
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'top',
          },
          title: {
            display: true,
            text: `Movimientos de Productos (${graficaMovimientosPorSucursal.value.fechaInicio} - ${graficaMovimientosPorSucursal.value.fechaFin})`
          }
        },
        scales: {
          y: {
            beginAtZero: true
          }
        }
      }
    });

    Toast.fire({
      icon: "success",
      title: "Gráfica actualizada"
    });
  } catch (error) {
    console.error('Error al cargar gráfica:', error);
    Toast.fire({
      icon: "error",
      title: "Error al cargar la gráfica"
    });
  }
};

// Cargar gráficas al montar el componente (solo si es admin de almacén)
onMounted(() => {
  if (isAdminAlmacen.value) {
    setTimeout(() => {
      cargarGraficaEnviados();
      cargarGraficaMovimientos();
    }, 500);
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