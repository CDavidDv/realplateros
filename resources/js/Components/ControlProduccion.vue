<template>
  <div class="mt-8 bg-white shadow rounded-lg p-6" v-if="isAdmin">
    <div class="flex justify-between items-center mb-4">
      <div>
      <h2 class="text-xl font-semibold">Control de Producción</h2>
        <p class="text-sm text-gray-600 mt-1">
          Mostrando datos del día: 
          <span class="font-medium">{{ fechaSeleccionada || 'Cargando...' }}</span>
          <span v-if="cargando" class="ml-2 inline-flex items-center text-blue-600">
            <div class="animate-spin rounded-full h-3 w-3 border-b-2 border-blue-500 mr-1"></div>
            Actualizando...
          </span>
        </p>
      </div>
      <div class="flex items-center space-x-4">
        <div class="flex items-center space-x-2">
          <label for="fecha" class="text-sm font-medium text-gray-700">Seleccionar día:</label>
          <input
            type="date"
            id="fecha"
            :max="getFechaActualMexico()"
            v-model="fechaSeleccionada"
            @change="cambiarFecha"
            class="mt-1 block w-40 pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
          >
        </div>
        <div class="flex items-center space-x-2">
          <label for="hora" class="text-sm font-medium text-gray-700">Filtrar por hora:</label>
          <select
            id="hora"
            v-model="horaSeleccionada"
            @change="cambiarHora"
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
        <div class="flex justify-between items-center mb-2">
          <h3 class="text-lg font-medium">Productos Faltantes - Control de Producción</h3>
          <div class="flex items-center space-x-2">
            <div class="text-sm text-gray-600">
              <span class="font-medium">{{ notificacionesFiltradas.length }}</span> de 
              <span class="font-medium">{{ totalNotificacionesLocales }}</span> 
              notificaciones para {{ fechaSeleccionada }}
            </div>
            <!-- Indicador de carga -->
            <div v-if="cargando" class="flex items-center space-x-1">
              <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-500"></div>
              <span class="text-xs text-blue-600">Cargando...</span>
            </div>
          </div>
        </div>
        
        <!-- Estado de carga -->
        <div v-if="cargando" class="text-center py-12">
          <div class="flex flex-col items-center space-y-4">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500"></div>
            <div class="text-center">
              <p class="text-lg font-medium text-gray-700">Cargando notificaciones...</p>
              <p class="text-sm text-gray-500">Obteniendo datos para {{ fechaSeleccionada }}</p>
            </div>
          </div>
        </div>
        
                <!-- Tabla de notificaciones -->
        <div v-else-if="notificacionesFiltradas.length > 0" class="overflow-x-auto">
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
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tiempo Horneado</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tiempo Venta</th>
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
                {{ (notif.hora_notificacion) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="[
                  'px-2 py-1 text-xs font-medium rounded-full',
                  notif.estado === 'pendiente' ? 'bg-red-100 text-red-800' : 
                  notif.estado === 'horneando' ? 'bg-yellow-100 text-yellow-800' :
                  notif.estado === 'en_espera' ? 'bg-blue-100 text-blue-800' :
                  notif.estado === 'vendido' ? 'bg-green-100 text-green-800' :
                  notif.estado === 'desperdicio' ? 'bg-red-100 text-red-800' :
                  'bg-gray-100 text-gray-800'
                ]">
                  {{ notif.estado === 'pendiente' ? 'Sin hornear' : 
                     notif.estado === 'horneando' ? 'Horneando' :
                     notif.estado === 'en_espera' ? 'En espera' :
                     notif.estado === 'vendido' ? 'Vendido' :
                     notif.estado === 'desperdicio' ? 'Desperdicio' : 
                     notif.estado }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ calcularTiempoHorneado(notif) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ calcularTiempoVenta(notif) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
        
        <!-- Estado sin datos -->
        <div v-else class="text-center py-12">
          <div class="flex flex-col items-center space-y-4">
            <!-- Icono de estado vacío -->
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center">
              <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
              </svg>
      </div>
            
            <div class="text-center">
              <h3 class="text-lg font-medium text-gray-700">
                {{ totalNotificacionesLocales === 0 
                  ? `Sin notificaciones para ${fechaSeleccionada}` 
                  : 'Sin notificaciones para la hora seleccionada' 
                }}
              </h3>
              <p class="text-sm text-gray-500 mt-1">
                {{ totalNotificacionesLocales === 0 
                  ? 'No se encontraron productos faltantes para esta fecha' 
                  : 'Cambia la hora del filtro para ver más notificaciones' 
                }}
              </p>
    </div>

        </div>
      </div>
    </div>

    <!-- Tabla de Control de Tiempo (Solo para Administrador) -->
    <div class="mt-8">
      <div class="flex justify-between items-center mb-2">
        <h3 class="text-lg font-medium">Control de Tiempo de Producción</h3>
        <div class="text-sm text-gray-600">
          <span class="font-medium">{{ notificacionesHorneandoFiltradas.length }}</span> 
          notificaciones procesadas
        </div>
      </div>
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
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatHora(notificacion.created_at) }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ esTiempoProduccionValido(notificacion) ? formatHora(notificacion.tiempo_inicio_horneado) : '-' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ calcularTiempoProduccion(notificacion) }}</td>
              <td class="px-6 py-4 whitespace-nowrap capitalize">
                <span :class="getEstadoClass(notificacion.estado)">
                  {{ notificacion.estado }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <!-- Estado sin datos para Control de Tiempo -->
      <div v-if="notificacionesHorneandoFiltradas.length === 0" class="text-center py-8">
        <div class="flex flex-col items-center space-y-3">
          <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center">
            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
          <div class="text-center">
            <p class="text-gray-500 text-lg">No hay notificaciones procesadas</p>
            <p class="text-gray-400 text-sm mt-1">Solo se muestran notificaciones que han sido horneadas o procesadas</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import axios from 'axios';

const { props } = usePage();

// Variables reactivas principales
const fechaSeleccionada = ref();
const horaSeleccionada = ref('actual');
const cargando = ref(false);
const notificacionesLocales = ref({
  faltantes: [],
  horneados: []
});

// Computed para verificar si es admin
const isAdmin = computed(() => {
  const user = props.auth?.user;
  const roles = user?.roles;
  return roles && roles.length > 0 && roles[0]?.name === 'admin';
});

// Opciones de horas disponibles
const horasDisponibles = ref([
  { value: 'todas', label: 'Todas las horas' },
  { value: 'actual', label: 'Hora Siguiente' },
  { value: '7-am', label: '7:00 AM' },
  { value: '8-am', label: '8:00 AM' },
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
  { value: '9-pm', label: '9:00 PM' },
  { value: '10-pm', label: '10:00 PM' },
  { value: '11-pm', label: '11:00 PM' }
]);

// Función para obtener la fecha actual en zona horaria de México
const getFechaActualMexico = () => {
  try {
    const hoy = new Date();
    const hoyMX = new Date(hoy.toLocaleString('en-US', { timeZone: 'America/Mexico_City' }));
    return hoyMX.toISOString().split('T')[0];
  } catch (error) {
    console.error('Error al obtener fecha México:', error);
    return new Date().toISOString().split('T')[0];
  }
};

// Función para obtener la hora siguiente redondeada
const getHoraSiguienteRedondeada = () => {
  const ahora = new Date();
  const horaSiguiente = new Date(ahora.getTime() + 60 * 60 * 1000);
  const horaRedondeada = new Date(horaSiguiente);
  horaRedondeada.setMinutes(0, 0, 0);
  
  const hora = horaRedondeada.getHours();
  const periodo = hora >= 12 ? 'pm' : 'am';
  const hora12 = hora % 12 || 12;
  return `${hora12}-${periodo}`;
};

// Función para convertir hora de notificación al formato del filtro
const convertirHoraNotificacion = (horaNotificacion) => {
  if (!horaNotificacion || typeof horaNotificacion !== 'string') return '';
  
  try {
    // Si ya está en el formato correcto (ej: "9-am"), retornarlo
    if (horaNotificacion.includes('-')) {
      return horaNotificacion;
    }
    
    // Si es formato "9:00 am" o "3:00 pm", extraer la hora y el periodo
    const match = horaNotificacion.match(/(\d+):(\d+)\s*(am|pm)/i);
    if (match) {
      const [, hora, minutos, periodo] = match;
      const horaNum = parseInt(hora);
      const hora12 = horaNum % 12 || 12;
      return `${hora12}-${periodo.toLowerCase()}`;
    }
    
    // Si es formato "9:00" sin periodo, asumir AM
    if (horaNotificacion.includes(':')) {
      const [hora, minutos] = horaNotificacion.split(':');
      const horaNum = parseInt(hora);
      const periodo = horaNum >= 12 ? 'pm' : 'am';
      const hora12 = horaNum % 12 || 12;
      return `${hora12}-${periodo}`;
    }
    
    return '';
  } catch (error) {
    console.error('Error al convertir hora notificación:', horaNotificacion, error);
    return '';
  }
};

// Computed principal para filtrar notificaciones
const notificacionesFiltradas = computed(() => {
  // Obtener notificaciones (priorizar locales sobre backend)
  let notificaciones = [];
  
  // Si hay notificaciones locales para la fecha seleccionada, usarlas
  if (notificacionesLocales.value?.faltantes?.length > 0) {
    notificaciones = notificacionesLocales.value.faltantes;
    
  } else {
    // Si no hay locales, usar las del backend solo si son de la fecha actual
    const fechaActual = getFechaActualMexico();
    if (fechaSeleccionada.value === fechaActual && props.notificaciones?.faltantes) {
      notificaciones = props.notificaciones.faltantes;
      
    } else {
      
      return [];
    }
  }
  
  // Aplicar filtro de hora
  if (horaSeleccionada.value === 'todas') {
    
    return notificaciones;
  }
  
  if (horaSeleccionada.value === 'actual') {
    const horaSiguiente = getHoraSiguienteRedondeada();
    
    
    const filtradas = notificaciones.filter(notif => {
      if (!notif.hora_notificacion) return false;
      
      const horaFormato = convertirHoraNotificacion(notif.hora_notificacion);
      const coincide = horaFormato === horaSiguiente;
      
      
      
      return coincide;
    });
    
    return filtradas;
  }
  
  
  const filtradas = notificaciones.filter(notif => {
    if (!notif.hora_notificacion) return false;
    
    const horaFormato = convertirHoraNotificacion(notif.hora_notificacion);
    const coincide = horaFormato === horaSeleccionada.value;
    
    
    return coincide;
  });
  
  
  return filtradas;
});

// Computed para notificaciones horneando filtradas
const notificacionesHorneandoFiltradas = computed(() => {
  // Obtener todas las notificaciones (faltantes + horneados)
  let todasLasNotificaciones = [];
  
  // Agregar notificaciones faltantes
  if (notificacionesLocales.value?.faltantes?.length > 0) {
    todasLasNotificaciones = [...todasLasNotificaciones, ...notificacionesLocales.value.faltantes];
  } else if (props.notificaciones?.faltantes) {
    todasLasNotificaciones = [...todasLasNotificaciones, ...props.notificaciones.faltantes];
  }
  
  // Agregar notificaciones horneados
  if (notificacionesLocales.value?.horneados?.length > 0) {
    todasLasNotificaciones = [...todasLasNotificaciones, ...notificacionesLocales.value.horneados];
  } else if (props.notificaciones?.horneados) {
    todasLasNotificaciones = [...todasLasNotificaciones, ...props.notificaciones.horneados];
  }
  
  // Filtrar para mostrar solo las que NO están en espera y SÍ se han horneado
  const notificacionesFiltradas = todasLasNotificaciones.filter(notif => {
    // Excluir notificaciones en espera
    if (notif.estado === 'en_espera') return false;
    
    // Excluir notificaciones que no se han horneado
    if (!notif.tiempo_inicio_horneado) return false;
    
    // Incluir solo las que tienen estado de horneado, vendido, desperdicio, etc.
    return ['horneando', 'vendido', 'desperdicio', 'retirado'].includes(notif.estado);
  });
  
  console.log('🔍 Control de Tiempo - Total notificaciones:', todasLasNotificaciones.length);
  console.log('🔍 Control de Tiempo - Filtradas por estado:', notificacionesFiltradas.length);
  console.log('🔍 Control de Tiempo - Estados encontrados:', [...new Set(todasLasNotificaciones.map(n => n.estado))]);
  
  return notificacionesFiltradas;
});

// Computed seguro para el total de notificaciones locales
const totalNotificacionesLocales = computed(() => {
  return notificacionesLocales.value?.faltantes?.length || 0;
});

// Función para cambiar fecha
const cambiarFecha = async () => {
  
  if (fechaSeleccionada.value) {
    await cargarNotificacionesPorFecha(fechaSeleccionada.value);
  }
};

// Función para cambiar hora
const cambiarHora = () => {
  
  // El computed se actualizará automáticamente
};

// Función para cargar notificaciones por fecha
const cargarNotificacionesPorFecha = async (fecha) => {
  try {
    
    
    // Activar estado de carga
    cargando.value = true;
    
    // Limpiar notificaciones locales antes de cargar nuevas
    notificacionesLocales.value = { faltantes: [], horneados: [] };
    
    // Simular un pequeño delay para mejor UX (opcional)
    await new Promise(resolve => setTimeout(resolve, 300));
    
    const response = await axios.post(route('control.produccion.notificaciones'), { 
      fecha: fecha 
    });
    
    if (response.data.success) {
      notificacionesLocales.value = response.data.notificaciones;
      
    } else {
      console.error('❌ Error en respuesta:', response.data.message);
      // Si hay error, mantener las notificaciones locales vacías
      notificacionesLocales.value = { faltantes: [], horneados: [] };
    }
  } catch (error) {
    console.error('❌ Error al cargar notificaciones:', error);
    // Si hay error, mantener las notificaciones locales vacías
    notificacionesLocales.value = { faltantes: [], horneados: [] };
  } finally {
    // Desactivar estado de carga
    cargando.value = false;
  }
};

// Inicialización del componente
onMounted(() => {
  // Inicializar fecha
  fechaSeleccionada.value = props.fechaFiltro || props.fechaActual || getFechaActualMexico();
  
  
  // Cargar notificaciones iniciales
  if (fechaSeleccionada.value) {
    cargarNotificacionesPorFecha(fechaSeleccionada.value);
  }
});

// Computed para tiempos de reposición calculados
const tiemposReposicionCalculados = computed(() => {
  // Usar las notificaciones filtradas
  return notificacionesFiltradas.value
    .filter(notif => notif.tiempo_inicio_horneado) // Solo los que tienen tiempo de inicio
    .map(notif => ({
      paste: notif.paste.nombre,
      ...calcularTiempoReposicion(notif)
    }));
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
    created_at: notif.created_at
  }));
});

const notificacionesHorneando = computed(() => {
  if (!props.notificaciones?.horneados) return [];

  return props.notificaciones.horneados;
});



// Función para calcular tiempo de reposición
const calcularTiempoReposicion = (notificacion) => {
  // Para notificaciones calculadas en tiempo real, no hay tiempo de inicio
  if (!notificacion.tiempo_inicio_horneado) {
    return { promedio: 0, ultima: 'N/A', restante: 'N/A' };
  }

  try {
    const tiempoInicio = new Date(notificacion.tiempo_inicio_horneado);
    const tiempoNotificacion = new Date(notificacion.created_at);
    
    if (isNaN(tiempoInicio.getTime()) || isNaN(tiempoNotificacion.getTime())) {
      return { promedio: 0, ultima: 'N/A', restante: 'N/A' };
    }
    
    const diferencia = Math.max(0, tiempoInicio - tiempoNotificacion);
    const minutos = Math.floor(diferencia / (1000 * 60));

    return {
      promedio: minutos,
      ultima: formatDateTime(notificacion.tiempo_inicio_horneado),
      restante: minutos > 0 ? `${minutos} min` : 'Completado'
    };
  } catch (error) {
    console.error('Error al calcular tiempo de reposición:', error);
    return { promedio: 0, ultima: 'Error', restante: 'Error' };
  }
};

// Función para calcular tiempo restante sin números negativos
const calcularTiempoRestante = (fechaInicio, fechaFin) => {
  if (!fechaInicio || !fechaFin) return 'N/A';
  
  try {
    const inicio = new Date(fechaInicio);
    const fin = new Date(fechaFin);
    const ahora = new Date();
    
    if (isNaN(inicio.getTime()) || isNaN(fin.getTime())) {
      return 'N/A';
    }
    
    // Si ya pasó la fecha, mostrar 0
    if (ahora > fin) return 'Completado';
    
    const diferencia = Math.max(0, fin - ahora);
    const minutos = Math.floor(diferencia / (1000 * 60));
    
    if (minutos < 60) {
      return `${minutos} min`;
    } else {
      const horas = Math.floor(minutos / 60);
      const minutosRestantes = minutos % 60;
      return `${horas}h ${minutosRestantes}min`;
    }
  } catch (error) {
    console.error('Error al calcular tiempo restante:', error);
    return 'Error';
  }
};

// Función para calcular tiempo de producción (desde notificación hasta inicio de horneado)
const calcularTiempoProduccion = (notificacion) => {
  if (!notificacion.tiempo_inicio_horneado || !notificacion.created_at) {
    return 'N/A';
  }
  
  try {
    const tiempoNotificacion = new Date(notificacion.created_at);
    const tiempoInicioHorneado = new Date(notificacion.tiempo_inicio_horneado);
    
    if (isNaN(tiempoNotificacion.getTime()) || isNaN(tiempoInicioHorneado.getTime())) {
      return 'N/A';
    }
    
    // Verificar que ambas fechas sean del mismo día
    const fechaNotificacion = tiempoNotificacion.toDateString();
    const fechaInicio = tiempoInicioHorneado.toDateString();
    
    if (fechaNotificacion !== fechaInicio) {
      return 'Diferente día';
    }
    
    // Calcular diferencia: tiempo de inicio - tiempo de notificación
    const diferencia = Math.max(0, tiempoInicioHorneado - tiempoNotificacion); // Evitar números negativos
    const minutos = Math.floor(diferencia / (1000 * 60));
    
    // Si la producción empezó antes que la notificación, mostrar 0
    if (minutos === 0) {
      return '0 min';
    }
    
    // Limitar el tiempo máximo a 24 horas para evitar errores
    if (minutos > 1440) {
      return 'Error de cálculo';
    }
    
    // Formato horas:minutos
    if (minutos < 60) {
    return `${minutos} min`;
    } else {
      const horas = Math.floor(minutos / 60);
      const minutosRestantes = minutos % 60;
      return `${horas}:${minutosRestantes.toString().padStart(2, '0')} hrs`;
    }
  } catch (error) {
    console.error('Error al calcular tiempo de producción:', error);
    return 'Error';
  }
};

// Las funciones de fecha ya no son necesarias, la fecha viene del backend



// Función para determinar el estado real de la notificación
const determinarEstadoReal = (notif) => {
  // Para notificaciones calculadas en tiempo real, siempre son 'pendiente'
  if (notif.estado) {
    return notif.estado;
  }
  
  return 'pendiente';
};





// Función para convertir hora 12h a 24h
const convertTo24Hour = (time12h) => {
  if (!time12h || typeof time12h !== 'string') return 0;
  
  try {
    const [time, modifier] = time12h.toLowerCase().split(' ');
    let [hours, minutes] = time.split(':');
    
    hours = parseInt(hours, 10);
    
    if (hours === 12) {
      hours = modifier === 'am' ? 0 : 12;
    } else if (modifier === 'pm') {
      hours = hours + 12;
    }
    
    return hours;
  } catch (error) {
    console.error('Error al convertir hora:', time12h, error);
    return 0;
  }
};

// Función para obtener el día actual
const getCurrentDay = () => {
  const now = new Date();
  const days = ['Domingo', 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sábado'];
  return days[now.getDay()];
};

// Computed para productos relevantes
const relevantProducts = computed(() => 
  props.inventario?.filter(item => 
    ['pastes', 'empanadas dulces', 'empanadas saladas'].includes(item.tipo)
  ) || []
);

// Computed para estimaciones vs existentes
const estimacionesVsExistentes = computed(() => {
  const productMap = new Map(relevantProducts.value.map(item => [item.id, item]));
  
  const resultado = (props.estimacionesHoy || [])
    .map(estimacion => {
      const producto = productMap.get(estimacion.inventario_id);
      if (!producto) return null;

      const hora24 = convertTo24Hour(estimacion.hora);
      return {
        id: `${producto.id}-${estimacion.hora}`,
        nombre: producto.nombre,
        estimado: parseInt(estimacion.cantidad),
        existente: producto.cantidad,
        diferencia: producto.cantidad - parseInt(estimacion.cantidad),
        hora: estimacion.hora,
        dia: estimacion.dia,
        horaEnNumero: hora24,
        tipo: producto.tipo
      };
    })
    .filter(Boolean);

  return resultado;
});

// Computed para items del día
const itemsDelDia = computed(() => {
  const items = estimacionesVsExistentes.value.filter(item => item.dia === getCurrentDay());
  return items;
});

// Computed para notificaciones actuales (hora siguiente)
const notificacionesActuales = computed(() => {
  const currentHour = new Date().getHours();
  const nextHour = (currentHour + 1) % 24; // Obtener la siguiente hora
  
  // Obtenemos los productos relevantes para la siguiente hora
  const productosHoraSiguiente = itemsDelDia.value.filter(item => {
    const horaItem = item.horaEnNumero || convertTo24Hour(item.hora);
    return horaItem === nextHour;
  });
  
  const productosExistentes = new Map(
    productosHoraSiguiente.map(item => [item.nombre, item])
  );

  // Para cada producto relevante, verificamos si existe en la siguiente hora
  const notificaciones = relevantProducts.value.map(producto => {
    const productoExistente = productosExistentes.get(producto.nombre);
    
    if (productoExistente) {
      return productoExistente;
    } else {
      return {
        id: `${producto.id}-${nextHour}:00`,
        nombre: producto.nombre,
        estimado: 0,
        existente: producto.cantidad,
        diferencia: producto.cantidad,
        hora: `${nextHour}:00`,
        dia: getCurrentDay(),
        horaEnNumero: nextHour,
        tipo: producto.tipo
      };
    }
  });

  return notificaciones;
});

// Computed para notificaciones faltantes calculadas en tiempo real
const notificacionesFaltantesCalculadas = computed(() => {
  const faltantes = notificacionesActuales.value.filter(item => {
    // Un producto es faltante si:
    // 1. Tiene estimación y la cantidad existente es menor al 70% de lo estimado
    const porcentajeMinimo = 0.7; // 70%
    const cantidadMinima = item.estimado * porcentajeMinimo;
    const esFaltante = item.estimado > 0 && item.existente < cantidadMinima;
    
    return esFaltante;
  });
  
  return faltantes.map(item => ({
    id: item.id,
    paste: { nombre: item.nombre, tipo: item.tipo },
    cantidad: Math.abs(item.diferencia),
    hora_notificacion: item.hora,
    dia_notificacion: item.dia,
    estado: 'pendiente',
    created_at: new Date().toISOString(),
    cantidad_horneada: 0,
    cantidad_vendida: 0,
    hora_ultima_venta: null,
    tiempo_inicio_horneado: null
  }));
});

// Watch para registrar notificaciones cuando hay menos del 70%
const isRegistering = ref(false);
let registrationTimeout = null;

const registrarNotificacionesBatch = async (notificaciones) => {
    if (isRegistering.value || !notificaciones?.length) return;
    isRegistering.value = true;

    try {
        await axios.post(route('notificaciones.registrar-batch'), {
            sucursal_id: props.auth?.user?.sucursal_id,
            notificaciones: notificaciones.map(notif => ({
                notificacion_id: String(notif.id),
                cantidad: notif.cantidad
            }))
        });
    } catch (error) {
        console.error('Error al registrar notificaciones en batch:', error);
    } finally {
        isRegistering.value = false;
    }
};

watch(() => notificacionesFaltantesCalculadas.value, (nuevasNotificaciones) => {
    if (registrationTimeout) clearTimeout(registrationTimeout);

    registrationTimeout = setTimeout(() => {
        if (nuevasNotificaciones?.length > 0) {
            registrarNotificacionesBatch(nuevasNotificaciones);
        }
    }, 2000);
}, { deep: true });

// Función para iniciar horneado
const iniciarHorneado = (notificacion) => {
  const tiempoInicio = new Date();
  
  router.post(route('control-produccion.iniciar-horneado'), {
    control_produccion_id: notificacion.id,
    tiempo_inicio_horneado: tiempoInicio.toISOString(),
    cantidad_horneada: notificacion.cantidad
  }, {
    preserveScroll: true,
    onSuccess: (response) => {
      
      // Recargar la página para actualizar los datos
      window.location.reload();
    },
    onError: (error) => {
      console.error('Error al iniciar horneado:', error);
    }
  });
};

// Función para finalizar horneado
const finalizarHorneado = (notificacion) => {
  const tiempoFin = new Date();
  
  router.post(route('control-produccion.finalizar-horneado'), {
    control_produccion_id: notificacion.id,
    tiempo_fin_horneado: tiempoFin.toISOString()
  }, {
    preserveScroll: true,
    onSuccess: (response) => {
      
      window.location.reload();
    },
    onError: (error) => {
      console.error('Error al finalizar horneado:', error);
    }
  });
};

// Función para registrar venta
const registrarVenta = (notificacion, cantidad) => {
  router.post(route('control-produccion.registrar-venta'), {
    control_produccion_id: notificacion.id,
    cantidad_vendida: cantidad
  }, {
    preserveScroll: true,
    onSuccess: (response) => {
      
      window.location.reload();
    },
    onError: (error) => {
      console.error('Error al registrar venta:', error);
    }
  });
};

// Función para calcular tiempo de horneado (desde notificación hasta inicio)
const calcularTiempoHorneado = (notificacion) => {
  if (!notificacion.tiempo_inicio_horneado || !notificacion.created_at) {
    return 'No iniciado';
  }
  
  try {
    const tiempoNotificacion = new Date(notificacion.created_at);
    const tiempoInicioHorneado = new Date(notificacion.tiempo_inicio_horneado);
    
    if (isNaN(tiempoNotificacion.getTime()) || isNaN(tiempoInicioHorneado.getTime())) {
      return 'Fecha inválida';
    }
    
    // Verificar que ambas fechas sean del mismo día
    const fechaNotificacion = tiempoNotificacion.toDateString();
    const fechaInicio = tiempoInicioHorneado.toDateString();
    
    if (fechaNotificacion !== fechaInicio) {
      return 'Diferente día';
    }
    
    // Calcular diferencia: tiempo de inicio - tiempo de notificación
    const diferencia = Math.max(0, tiempoInicioHorneado - tiempoNotificacion); // Evitar números negativos
    const minutos = Math.floor(diferencia / (1000 * 60));
    
    // Si el horneado empezó antes que la notificación, mostrar 0
    if (minutos === 0) {
      return '0 min';
    }
    
    // Formato horas:minutos
    if (minutos < 60) {
    return `${minutos} min`;
    } else {
      const horas = Math.floor(minutos / 60);
      const minutosRestantes = minutos % 60;
      return `${horas}:${minutosRestantes.toString().padStart(2, '0')} hrs`;
    }
  } catch (error) {
    console.error('Error al calcular tiempo de horneado:', error);
    return 'Error';
  }
};

// Función para calcular tiempo de venta desde que se creo la notificacion hasta la ultima venta
const calcularTiempoVenta = (notificacion) => {
  if (!notificacion.created_at) {
    return 'N/A';
  }
  
  try {
    const tiempoNotificacion = new Date(notificacion.created_at);
    
    if (isNaN(tiempoNotificacion.getTime())) {
      return 'Fecha inválida';
    }
    
    // Si no hay ventas, calcular desde la notificación hasta ahora
    if (!notificacion.hora_ultima_venta) {
    const ahora = new Date();
      const diferencia = Math.max(0, ahora - tiempoNotificacion); // Evitar números negativos
      const minutos = Math.floor(diferencia / (1000 * 60));
      
      // Verificar que sea del mismo día
      const fechaNotificacion = tiempoNotificacion.toDateString();
      const fechaActual = ahora.toDateString();
      
      if (fechaNotificacion !== fechaActual) {
        return 'Diferente día';
      }
      
      // Limitar el tiempo máximo a 24 horas
      if (minutos > 1440) {
      return 'Sin ventas del día';
    }
    
      // Formato horas:minutos
      if (minutos < 60) {
        return `${minutos} min`;
      } else {
        const horas = Math.floor(minutos / 60);
        const minutosRestantes = minutos % 60;
        return `${horas}:${minutosRestantes.toString().padStart(2, '0')} hrs`;
      }
    }
    
    // Si hay ventas, calcular desde la notificación hasta la última venta
    const tiempoUltimaVenta = new Date(notificacion.hora_ultima_venta);
    
    if (isNaN(tiempoUltimaVenta.getTime())) {
      return 'Fecha de venta inválida';
    }
    
    // Verificar que ambas fechas sean del mismo día
    const fechaNotificacion = tiempoNotificacion.toDateString();
    const fechaVenta = tiempoUltimaVenta.toDateString();
    
    if (fechaNotificacion !== fechaVenta) {
      return 'Diferente día';
    }
    
    // Calcular diferencia: última venta - notificación
    const diferencia = Math.max(0, tiempoUltimaVenta - tiempoNotificacion); // Evitar números negativos
    const minutos = Math.floor(diferencia / (1000 * 60));
    
    // Si la venta fue antes que la notificación, mostrar 0
    if (minutos === 0) {
      return '0 min';
    }
    
    // Limitar el tiempo máximo a 24 horas
    if (minutos > 1440) {
      return 'Sin ventas del día';
    }
    
    // Formato horas:minutos
    if (minutos < 60) {
      return `${minutos} min`;
    } else {
      const horas = Math.floor(minutos / 60);
      const minutosRestantes = minutos % 60;
      return `${horas}:${minutosRestantes.toString().padStart(2, '0')} hrs`;
    }
    
  } catch (error) {
    console.error('Error al calcular tiempo de venta:', error);
    return 'Error';
  }
};

// Función para verificar si el tiempo de producción es válido
const esTiempoProduccionValido = (notificacion) => {
  if (!notificacion.tiempo_inicio_horneado || !notificacion.created_at) {
    return false;
  }
  
  try {
    const tiempoNotificacion = new Date(notificacion.created_at);
    const tiempoInicioHorneado = new Date(notificacion.tiempo_inicio_horneado);
    
    if (isNaN(tiempoNotificacion.getTime()) || isNaN(tiempoInicioHorneado.getTime())) {
      return false;
    }
    
    // Verificar que ambas fechas sean del mismo día
    const fechaNotificacion = tiempoNotificacion.toDateString();
    const fechaInicio = tiempoInicioHorneado.toDateString();
    
    if (fechaNotificacion !== fechaInicio) {
      return false;
    }
    
    // Verificar que el tiempo de inicio sea después de la notificación
    const diferencia = tiempoInicioHorneado - tiempoNotificacion;
    const minutos = Math.floor(diferencia / (1000 * 60));
    
    // Solo es válido si el tiempo es positivo y menor a 24 horas
    return minutos >= 0 && minutos <= 1440;
    
  } catch (error) {
    console.error('Error al validar tiempo de producción:', error);
    return false;
  }
};

// Función para formatear hora en formato HH:MM:SS AM/PM
const formatHora = (hora) => {
  if (!hora) return '-';
  
  try {
    //hora mexico 
    const fecha = new Date(hora);
    
    // Verificar que la fecha sea válida
    if (isNaN(fecha.getTime())) {
      return 'Fecha inválida';
    }
    
    return fecha.toLocaleString('es-MX', {
      hour: '2-digit',
      minute: '2-digit',
      second: '2-digit',
      hour12: true,
      timeZone: 'America/Mexico_City'
    });
  } catch (error) {
    console.error('Error al formatear hora:', hora, error);
    return 'Error';
  }
};

// Función para formatear fecha y hora
const formatDateTime = (date) => {
  if (!date) return '-';
  
  try {
    const dateObj = new Date(date);
    if (isNaN(dateObj.getTime())) return 'Fecha inválida';
    
    return dateObj.toLocaleString('es-MX', {
      hour: '2-digit',
      minute: '2-digit',
      second: '2-digit',
      hour12: true,
      timeZone: 'America/Mexico_City'
    });
  } catch (error) {
    console.error('Error al formatear fecha:', date, error);
    return 'Error';
  }
};

// Función para obtener clases CSS del estado
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

</script> 