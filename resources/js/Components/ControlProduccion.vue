<template>
  <div class="mt-8 bg-white shadow rounded-lg p-6" v-if="isAdmin">
    <div class="flex justify-between items-center mb-4">
      <h2 class="text-xl font-semibold">Control de Producción</h2>
      <div class="flex items-center space-x-4">
        <div class="flex items-center space-x-2">
          <label for="fecha" class="text-sm font-medium text-gray-700">Seleccionar día:</label>
          <input
            type="date"
            id="fecha"
            v-model="fechaSeleccionada"
            class="mt-1 block w-40 pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
          >
        </div>
        <div class="flex items-center space-x-2">
          <label for="hora" class="text-sm font-medium text-gray-700">Filtrar por hora:</label>
          <select
            id="hora"
            v-model="horaSeleccionada"
            class="mt-1 block w-40 pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
          >
            <option v-for="hora in horasDisponibles" :key="hora.value" :value="hora.value">
              {{ hora.label }}
            </option>
          </select>
        </div>
      </div>
    </div>
    
    <!-- Tabla de Notificaciones Faltantes -->
    <div class="mb-6">
      <h3 class="text-lg font-medium mb-2">Productos Faltantes</h3>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Producto</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cantidad Faltante</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cantidad Horneada</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cantidad Vendida</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Día</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Hora Requerida</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Última Venta</th>
              
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="notif in notificacionesFiltradas" :key="notif.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 capitalize">
                {{ notif.paste.nombre }}
                <span class="text-xs text-gray-500 block capitalize">{{ notif.paste.tipo }}</span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ notif.cantidad }} unidades
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ notif.cantidad_horneada ? `${notif.cantidad_horneada} unidades` : '-' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ notif.cantidad_vendida ? `${notif.cantidad_vendida} unidades` : '-' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 capitalize">
                {{ notif.dia_notificacion }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ formatDateTime(notif.created_at) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="[
                  'px-2 py-1 text-xs font-medium rounded-full',
                  notif.estado === 'pendiente' ? 'bg-red-100 text-red-800' : 
                  notif.estado === 'horneando' ? 'bg-yellow-100 text-yellow-800' :
                  notif.estado === 'desperdicio' ? 'bg-red-100 text-red-800' :
                  'bg-green-100 text-green-800'
                ]">
                  {{ notif.estado === 'pendiente' ? 'Sin hornear' : 
                     notif.estado === 'desperdicio' ? 'Desperdicio' : 
                     notif.estado }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ notif.estado === 'pendiente' ? '-' :notif.hora_ultima_venta ? formatDateTime(notif.hora_ultima_venta) : 'Sin ventas' }}
              </td>
              
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Tiempo de Reposición -->
    <div class="mb-6">
      <h3 class="text-lg font-medium mb-2">Tiempo de Reposición</h3>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div v-for="(tiempo, index) in tiemposReposicion" :key="index" class="bg-gray-50 p-4 rounded-lg">
          <p class="font-medium">{{ tiempo.paste }}</p>
          <p>Tiempo promedio: {{ tiempo.promedio }} minutos</p>
          <p>Última reposición: {{ tiempo.ultima }}</p>
        </div>
      </div>
    </div>


    <!-- Tabla de Control de Tiempo (Solo para Administrador) -->
    <div  class="mt-8">
      <h3 class="text-lg font-medium mb-2">Control de Tiempo de Producción</h3>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Paste</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Hora de notificación</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Hora de Producción</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tiempo en meter producción</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="notificacion in notificacionesHorneandoFiltradas" :key="notificacion.id">
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ notificacion.paste.nombre }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatDateTime(notificacion.created_at) }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatDateTime(notificacion.tiempo_inicio_horneado) }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ Math.round((new Date(notificacion.diferencia_notificacion_inicio) - new Date(notificacion.created_at)) / 60000) }} min</td>
              <td class="px-6 py-4 whitespace-nowrap capitalize">
                <span :class="getEstadoClass(notificacion.estado)">
                  {{ notificacion.estado }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

   
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { usePage, router } from '@inertiajs/vue3';

const { props } = usePage();

const produccionActual = ref([]);
const tiemposReposicion = ref([]);
const recomendaciones = ref([]);
const tiemposProduccion = ref([]);

const isAdmin = computed(() => props.auth.user.roles[0].name === 'admin');

console.log(isAdmin.value);

const fechaSeleccionada = ref(new Date().toISOString().split('T')[0]);
const horaSeleccionada = ref('actual');

const horasDisponibles = ref([
  { value: 'todas', label: 'Todas las horas' },
  { value: 'actual', label: 'Hora Actual' },
  { value: '9-am', label: '9:00 AM' },
  { value: '10-am', label: '10:00 AM' },
  { value: '11-am', label: '11:00 AM' },
  { value: '12-pm', label: '12:00 PM' },
  { value: '1-pm', label: '1:00 PM' },
  { value: '2-pm', label: '2:00 PM' },
  { value: '3-pm', label: '3:00 PM' },
  { value: '4-pm', label: '4:00 PM' },
  { value: '5-pm', label: '5:00 PM' },
  { value: '6-pm', label: '6:00 PM' },
  { value: '7-pm', label: '7:00 PM' },
  { value: '8-pm', label: '8:00 PM' },
  { value: '9-pm', label: '9:00 PM' }
]);

// Función para obtener la hora en formato AM/PM
const getHoraFormato = (fecha) => {
  const date = new Date(fecha);
  const hora = date.getHours();
  const minutos = date.getMinutes();
  const periodo = hora >= 12 ? 'pm' : 'am';
  const hora12 = hora % 12 || 12;
  return `${hora12}-${periodo}`;
};

// Función para obtener el rango de fechas
const obtenerRangoFechas = () => {
  const fecha = new Date(fechaSeleccionada.value + 'T00:00:00-06:00'); // Ajustando a zona horaria de México
  const fechaFin = new Date(fechaSeleccionada.value + 'T23:59:59-06:00');
  return {
    inicio: fecha,
    fin: fechaFin
  };
};

// Función para verificar si una fecha está dentro del rango seleccionado
const estaEnRangoSeleccionado = (fecha) => {
  const rango = obtenerRangoFechas();
  if (!rango) return true;
  
  // Convertir la fecha de la notificación a la zona horaria de México
  const fechaNotificacion = new Date(fecha);
  const fechaNotificacionMX = new Date(fechaNotificacion.toLocaleString('en-US', { timeZone: 'America/Mexico_City' }));
  
  // Asegurarnos de que las fechas de inicio y fin estén en la zona horaria correcta
  const inicioMX = new Date(rango.inicio.toLocaleString('en-US', { timeZone: 'America/Mexico_City' }));
  const finMX = new Date(rango.fin.toLocaleString('en-US', { timeZone: 'America/Mexico_City' }));
  
  return fechaNotificacionMX >= inicioMX && fechaNotificacionMX <= finMX;
};

// Función para actualizar la fecha al montar el componente
const actualizarFechaInicial = () => {
  const hoy = new Date();
  const hoyMX = new Date(hoy.toLocaleString('en-US', { timeZone: 'America/Mexico_City' }));
  fechaSeleccionada.value = hoyMX.toISOString().split('T')[0];
};

// Función para determinar el estado real de la notificación
const determinarEstadoReal = (notif) => {
  if (notif.estado === 'pendiente') return 'pendiente';
  
  const ahora = new Date();
  const horaActual = getHoraFormato(ahora);
  const horaNotificacion = getHoraFormato(notif.created_at);
  
  // Si no es la hora actual y el estado no es pendiente, es desperdicio
  if (horaNotificacion !== horaActual) {
    return 'desperdicio';
  }
  
  return notif.estado;
};

// Modificar el computed notificacionesFiltradas
const notificacionesFiltradas = computed(() => {
  if (!props.notificaciones?.faltantes) return [];
  
  const notificaciones = props.notificaciones.faltantes.map(notif => ({
    id: notif.id,
    paste: notif.paste,
    cantidad: notif.cantidad,
    hora_notificacion: notif.hora_notificacion,
    dia_notificacion: notif.dia_notificacion,
    estado: determinarEstadoReal(notif),
    tiempo_inicio_horneado: notif.tiempo_inicio_horneado,
    hora_ultima_venta: notif.hora_ultima_venta,
    cantidad_vendida: notif.cantidad_vendida,
    diferencia_notificacion_inicio: notif.diferencia_notificacion_inicio,
    created_at: notif.created_at,
    cantidad_horneada: notif.cantidad_horneada
  }));

  // Filtrar por día
  let notificacionesFiltradasPorDia = notificaciones.filter(notif => 
    estaEnRangoSeleccionado(notif.created_at)
  );

  // Filtrar por hora
  if (horaSeleccionada.value === 'todas') {
    return notificacionesFiltradasPorDia;
  } else if (horaSeleccionada.value === 'actual') {
    const ahora = new Date();
    const horaActual = getHoraFormato(ahora);
    return notificacionesFiltradasPorDia.filter(notif => {
      const horaNotificacion = getHoraFormato(notif.created_at);
      return horaNotificacion === horaActual;
    });
  }

  return notificacionesFiltradasPorDia.filter(notif => {
    const horaNotificacion = getHoraFormato(notif.created_at);
    return horaNotificacion === horaSeleccionada.value;
  });
});

// Computed para procesar las notificaciones faltantes
const notificacionesFaltantes = computed(() => {
  if (!props.notificaciones?.faltantes) return [];
  
  return props.notificaciones.faltantes.map(notif => ({
    id: notif.id,
    paste: notif.paste,
    cantidad: notif.cantidad,
    hora_notificacion: notif.hora_notificacion,
    dia_notificacion: notif.dia_notificacion,
    estado: notif.estado,
    tiempo_inicio_horneado: notif.tiempo_inicio_horneado,
    hora_ultima_venta: notif.hora_ultima_venta,
    cantidad_vendida: notif.cantidad_vendida,
    diferencia_notificacion_inicio: notif.diferencia_notificacion_inicio,
    created_at: notif.created_at
  }));
});

const notificacionesHorneando = computed(() => {
  if (!props.notificaciones?.horneados) return [];

  return props.notificaciones.horneados;
});

// Computed para las recomendaciones basadas en notificaciones
const recomendacionesActualizadas = computed(() => {
  const recomendacionesBase = notificacionesFaltantes.value.map(notif => ({
    paste: notif.paste.nombre,
    recomendacion: 'Producir',
    razon: `Faltan ${notif.cantidad} unidades para las ${notif.hora_notificacion}`
  }));

  return recomendacionesBase;
});

// Watch para actualizar las recomendaciones cuando cambian las notificaciones
watch(() => props.notificaciones?.faltantes, (newNotificaciones) => {
  if (newNotificaciones) {
    recomendaciones.value = recomendacionesActualizadas.value;
  }
}, { immediate: true });

const calcularPorcentajeVenta = (produccion) => {
  if (!produccion.cantidad) return 0;
  return Math.round((produccion.cantidad_vendida / produccion.cantidad) * 100);
};

const calcularTiempoSinVenta = (produccion) => {
  if (!produccion.tiempo_ultima_venta) return '-';
  const ultimaVenta = new Date(produccion.tiempo_ultima_venta);
  const ahora = new Date();
  const minutos = Math.floor((ahora - ultimaVenta) / (1000 * 60));
  
  if (minutos < 60) {
    return `${minutos} min`;
  } else {
    const horas = Math.floor(minutos / 60);
    const minutosRestantes = minutos % 60;
    return `${horas}h ${minutosRestantes}min`;
  }
};

const formatDateTime = (date) => {
  if (!date) return '-';
  return new Date(date).toLocaleString('es-MX', {
    hour: '2-digit',
    minute: '2-digit',
    hour12: true,
    timeZone: 'America/Mexico_City'
  });
};

const getEstadoClass = (estado) => {
  const classes = {
    horneando: 'bg-yellow-100 text-yellow-800 capitalize',
    retirado: 'bg-blue-100 text-blue-800 capitalize',
    vendido: 'bg-green-100 text-green-800 capitalize',
    desperdicio: 'bg-red-100 text-red-800 capitalize',
    pendiente: 'bg-gray-100 text-gray-800 capitalize',
    en_espera: 'bg-green-100 text-green-800'
  };
  return `px-2 py-1 rounded-full text-xs font-medium ${classes[estado]}`;
};

// Modificar el computed notificacionesHorneandoFiltradas
const notificacionesHorneandoFiltradas = computed(() => {
  if (!props.notificaciones?.horneados) return [];
  
  const notificaciones = props.notificaciones.horneados;

  // Filtrar por día
  let notificacionesFiltradasPorDia = notificaciones.filter(notif => 
    estaEnRangoSeleccionado(notif.created_at)
  );

  // Filtrar por hora
  if (horaSeleccionada.value === 'todas') {
    return notificacionesFiltradasPorDia;
  } else if (horaSeleccionada.value === 'actual') {
    const ahora = new Date();
    const horaActual = getHoraFormato(ahora);
    return notificacionesFiltradasPorDia.filter(notif => {
      const horaNotificacion = getHoraFormato(notif.created_at);
      return horaNotificacion === horaActual;
    });
  }

  return notificacionesFiltradasPorDia.filter(notif => {
    const horaNotificacion = getHoraFormato(notif.created_at);
    return horaNotificacion === horaSeleccionada.value;
  });
});

// Inicializar la fecha al montar el componente
onMounted(() => {
  actualizarFechaInicial();
});

</script> 