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
      <h3 class="text-lg font-medium mb-2">Productos Faltantes (Hora Siguiente) - Control de Producción</h3>
      <div v-if="notificacionesFiltradas.length > 0" class="overflow-x-auto">
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
                {{ notif.paste.id }} - {{ notif.paste.nombre }}
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
      <div v-else class="text-center py-8">
        <p class="text-gray-500 text-lg">No hay productos faltantes para la hora siguiente</p>
        <p class="text-gray-400 text-sm mt-2">Los faltantes se calculan en tiempo real basándose en el inventario y estimaciones</p>
      </div>
    </div>

    <!-- Tiempo de Reposición 
    <div class="mb-6" v-if="tiemposReposicionCalculados.length > 0">
      <h3 class="text-lg font-medium mb-2">Tiempo de Reposición</h3>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div v-for="(tiempo, index) in tiemposReposicionCalculados" :key="index" class="bg-gray-50 p-4 rounded-lg">
          <p class="font-medium">{{ tiempo.paste }}</p>
          <p>Tiempo promedio: {{ tiempo.promedio }} minutos</p>
          <p>Última reposición: {{ tiempo.ultima }}</p>
          <p>Tiempo restante: {{ tiempo.restante }}</p>
        </div>
      </div>
    </div>
-->

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
    </div>

   
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { usePage, router } from '@inertiajs/vue3';

const { props } = usePage();

console.log(props);

const produccionActual = ref([]);
const recomendaciones = ref([]);
const tiemposProduccion = ref([]);

const isAdmin = computed(() => {
  const user = props.auth?.user;
  const roles = user?.roles;
  return roles && roles.length > 0 && roles[0]?.name === 'admin';
});

const fechaSeleccionada = ref(new Date().toISOString().split('T')[0]);
const horaSeleccionada = ref('actual');

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

// Función para convertir hora_notificacion (ej: "9:00") al formato que buscamos (ej: "9-am")
const convertirHoraNotificacion = (horaNotificacion) => {
  if (!horaNotificacion || typeof horaNotificacion !== 'string') return '';
  
  try {
    // Si ya está en el formato correcto (ej: "9-am"), retornarlo
    if (horaNotificacion.includes('-')) {
      return horaNotificacion;
    }
    
    // Si es formato "9:00" o "9:00 am", convertirlo
    if (horaNotificacion.includes(':')) {
      const [hora, minutos] = horaNotificacion.split(':');
      const horaNum = parseInt(hora);
      const periodo = horaNum >= 12 ? 'pm' : 'am';
      const hora12 = horaNum % 12 || 12;
      
      return `${hora12}-${periodo}`;
    }
    
    // Si es formato "9:00 am", extraer la hora y convertir
    const match = horaNotificacion.match(/(\d+):(\d+)\s*(am|pm)/i);
    if (match) {
      const [, hora, minutos, periodo] = match;
      const horaNum = parseInt(hora);
      const hora12 = horaNum % 12 || 12;
      
      return `${hora12}-${periodo.toLowerCase()}`;
    }
    
    return '';
  } catch (error) {
    console.error('Error al convertir hora notificación:', horaNotificacion, error);
    return '';
  }
};

// Función para obtener la hora siguiente redondeada (ej: 8:32 -> 9:00, 9:15 -> 10:00)
const getHoraSiguienteRedondeada = () => {
  const ahora = new Date();
  const horaSiguiente = new Date(ahora.getTime() + 60 * 60 * 1000); // Agregar 1 hora
  
  // Redondear a la hora en punto (00 minutos)
  const horaRedondeada = new Date(horaSiguiente);
  horaRedondeada.setMinutes(0, 0, 0);
  
  const horaFormato = getHoraFormato(horaRedondeada);
  return horaFormato;
};

// Función para obtener la hora en formato AM/PM
const getHoraFormato = (fecha) => {
  if (!fecha) return '';
  
  try {
    const date = new Date(fecha);
    if (isNaN(date.getTime())) return '';
    
    const hora = date.getHours();
    const minutos = date.getMinutes();
    const periodo = hora >= 12 ? 'pm' : 'am';
    const hora12 = hora % 12 || 12;
    return `${hora12}-${periodo}`;
  } catch (error) {
    console.error('Error al obtener formato de hora:', fecha, error);
    return '';
  }
};

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
    const diferencia = tiempoInicioHorneado - tiempoNotificacion;
    const minutos = Math.floor(diferencia / (1000 * 60));
    
    // Si la producción empezó antes que la notificación, es un error lógico
    if (minutos < 0) {
      return 'Error: Producción antes de notificación';
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
  if (!fecha) return false;
  
  try {
    const fechaNotificacion = new Date(fecha);
    const hoy = new Date();
    
    // Comparar solo la fecha (sin hora)
    const fechaNotificacionStr = fechaNotificacion.toISOString().split('T')[0];
    const hoyStr = hoy.toISOString().split('T')[0];
    
    return fechaNotificacionStr === hoyStr;
  } catch (error) {
    console.error('Error al verificar rango de fecha:', fecha, error);
    return false;
  }
};

// Función para actualizar la fecha al montar el componente
const actualizarFechaInicial = () => {
  try {
    const hoy = new Date();
    const hoyMX = new Date(hoy.toLocaleString('en-US', { timeZone: 'America/Mexico_City' }));
    fechaSeleccionada.value = hoyMX.toISOString().split('T')[0];
  } catch (error) {
    console.error('Error al actualizar fecha inicial:', error);
    // Fallback: usar fecha actual
    fechaSeleccionada.value = new Date().toISOString().split('T')[0];
  }
};

// Función para determinar el estado real de la notificación
const determinarEstadoReal = (notif) => {
  // Para notificaciones calculadas en tiempo real, siempre son 'pendiente'
  if (notif.estado) {
    return notif.estado;
  }
  
  return 'pendiente';
};

// Modificar el computed notificacionesFiltradas
const notificacionesFiltradas = computed(() => {
  // Combinar notificaciones calculadas en tiempo real con las registradas en BD
  const notificacionesCalculadas = notificacionesFaltantesCalculadas.value;
  const notificacionesRegistradas = props.notificaciones?.faltantes || [];
  
  // Debug temporal: verificar campos disponibles
  if (notificacionesRegistradas.length > 0) {
    const primerRegistro = notificacionesRegistradas[0];
    console.log('Debug campos disponibles:', {
      campos: Object.keys(primerRegistro),
      tiene_updated_at: 'updated_at' in primerRegistro,
      updated_at_valor: primerRegistro.updated_at,
      tiene_hora_ultima_venta: 'hora_ultima_venta' in primerRegistro,
      hora_ultima_venta_valor: primerRegistro.hora_ultima_venta
    });
  }
  
  // Crear un mapa de notificaciones registradas para evitar duplicados
  const notificacionesRegistradasMap = new Map();
  notificacionesRegistradas.forEach(notif => {
    const key = `${notif.paste_id}-${notif.hora_notificacion}`;
    notificacionesRegistradasMap.set(key, notif);
  });
  
  // Combinar notificaciones, dando prioridad a las registradas
  const notificacionesCombinadas = notificacionesCalculadas.map(notifCalculada => {
    // Verificar que notifCalculada.id sea una cadena antes de usar split
    const notifId = typeof notifCalculada.id === 'string' ? notifCalculada.id : String(notifCalculada.id);
    const key = `${notifId.split('-')[0]}-${notifCalculada.hora_notificacion}`;
    const notifRegistrada = notificacionesRegistradasMap.get(key);
    
    if (notifRegistrada) {
      // Usar la notificación registrada (tiene más información como estado, tiempos, etc.)
      return {
        id: notifRegistrada.id,
        paste: notifRegistrada.paste || notifCalculada.paste,
        cantidad: notifRegistrada.cantidad || notifCalculada.cantidad,
        hora_notificacion: notifRegistrada.hora_notificacion || notifCalculada.hora_notificacion,
        dia_notificacion: notifRegistrada.dia_notificacion || notifCalculada.dia_notificacion,
        estado: notifRegistrada.estado || 'pendiente',
        tiempo_inicio_horneado: notifRegistrada.tiempo_inicio_horneado,
        hora_ultima_venta: notifRegistrada.hora_ultima_venta,
        cantidad_vendida: notifRegistrada.cantidad_vendida || 0,
        created_at: notifRegistrada.created_at || notifCalculada.created_at,
        cantidad_horneada: notifRegistrada.cantidad_horneada || 0,
        updated_at: notifRegistrada.updated_at 
      };
    } else {
      // Usar la notificación calculada
      return notifCalculada;
    }
  });
  
  // Agregar notificaciones registradas que no están en las calculadas
  notificacionesRegistradas.forEach(notifRegistrada => {
    const key = `${notifRegistrada.paste_id}-${notifRegistrada.hora_notificacion}`;
    const existe = notificacionesCombinadas.some(notif => {
      // Verificar que notif.id sea una cadena antes de usar split
      const notifId = typeof notif.id === 'string' ? notif.id : String(notif.id);
      const notifKey = `${notifId.split('-')[0]}-${notif.hora_notificacion}`;
      return notifKey === key;
    });
    
    if (!existe) {
      notificacionesCombinadas.push({
        id: notifRegistrada.id,
        paste: notifRegistrada.paste,
        cantidad: notifRegistrada.cantidad,
        hora_notificacion: notifRegistrada.hora_notificacion,
        dia_notificacion: notifRegistrada.dia_notificacion,
        estado: notifRegistrada.estado || 'pendiente',
        tiempo_inicio_horneado: notifRegistrada.tiempo_inicio_horneado,
        hora_ultima_venta: notifRegistrada.hora_ultima_venta,
        cantidad_vendida: notifRegistrada.cantidad_vendida || 0,
        created_at: notifRegistrada.created_at,
        cantidad_horneada: notifRegistrada.cantidad_horneada || 0
      });
    }
  });

  // Filtrar por hora - por defecto solo mostrar la hora siguiente redondeada
  let notificacionesFiltradasPorHora;
  if (horaSeleccionada.value === 'todas') {
    notificacionesFiltradasPorHora = notificacionesCombinadas;
  } else if (horaSeleccionada.value === 'actual') {
    const horaSiguienteRedondeada = getHoraSiguienteRedondeada();
    
    // Si no hay hora siguiente válida, retornar array vacío
    if (!horaSiguienteRedondeada) return [];
    
    notificacionesFiltradasPorHora = notificacionesCombinadas.filter(notif => {
      // Buscar por hora_notificacion
      const horaNotificacion = notif.hora_notificacion;
      if (!horaNotificacion) return false;
      
      // Convertir la hora_notificacion (ej: "9:00") al formato que buscamos (ej: "9-am")
      const horaFormato = convertirHoraNotificacion(horaNotificacion);
      return horaFormato === horaSiguienteRedondeada;
    });
  } else {
    notificacionesFiltradasPorHora = notificacionesCombinadas.filter(notif => {
      const horaNotificacion = notif.hora_notificacion;
      if (!horaNotificacion) return false;
      
      const horaFormato = convertirHoraNotificacion(horaNotificacion);
      return horaFormato === horaSeleccionada.value;
    });
  }

  // Aplicar filtro adicional para excluir notificaciones con tiempos inválidos
  return notificacionesFiltradasPorHora.filter(notif => {
    // Si no tiene tiempo de inicio, es válida (pendiente de horneado)
    if (!notif.tiempo_inicio_horneado) {
      return true;
    }
    
    // Si tiene tiempo de inicio, validar que sea correcto
    return esTiempoProduccionValido(notif);
  });
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
  if (!produccion.hora_ultima_venta) return '-';
  const ultimaVenta = new Date(produccion.hora_ultima_venta);
  const ahora = new Date();
  const diferencia = Math.max(0, ahora - ultimaVenta);
  const minutos = Math.floor(diferencia / (1000 * 60));
  
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
  // Filtrar solo notificaciones con tiempos válidos
  return notificacionesFiltradas.value.filter(notif => {
    if (!notif.tiempo_inicio_horneado || !notif.created_at) {
      return false; // No mostrar si no tiene tiempo de inicio
    }
    
    try {
      const tiempoNotificacion = new Date(notif.created_at);
      const tiempoInicioHorneado = new Date(notif.tiempo_inicio_horneado);
      
      if (isNaN(tiempoNotificacion.getTime()) || isNaN(tiempoInicioHorneado.getTime())) {
        return false; // No mostrar si las fechas son inválidas
      }
      
      // Verificar que ambas fechas sean del mismo día
      const fechaNotificacion = tiempoNotificacion.toDateString();
      const fechaInicio = tiempoInicioHorneado.toDateString();
      
      if (fechaNotificacion !== fechaInicio) {
        return false; // No mostrar si son de días diferentes
      }
      
      // Verificar que el tiempo de inicio sea después de la notificación
      const diferencia = tiempoInicioHorneado - tiempoNotificacion;
      const minutos = Math.floor(diferencia / (1000 * 60));
      
      // Solo mostrar si el tiempo es válido (positivo y menor a 24 horas)
      return minutos >= 0 && minutos <= 1440;
      
    } catch (error) {
      console.error('Error al validar notificación:', error);
      return false; // No mostrar si hay error
    }
  });
});

// Inicializar la fecha al montar el componente
onMounted(() => {
  actualizarFechaInicial();
});

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
watch(() => notificacionesFaltantesCalculadas.value, (nuevasNotificaciones) => {
  if (nuevasNotificaciones && nuevasNotificaciones.length > 0) {
    // Registrar cada notificación faltante en la base de datos
    nuevasNotificaciones.forEach(async (notif) => {
      const notificacionId = `${notif.id}`;
      const cantidad = notif.cantidad;
      
      // Verificar si ya existe la notificación
      const notificacionExistente = props.notificaciones?.faltantes?.find(
        n => {
          // Verificar que notif.id sea una cadena antes de usar split
          const notifId = typeof notif.id === 'string' ? notif.id : String(notif.id);
          const pasteId = parseInt(notifId.split('-')[0]);
          return n.paste_id === pasteId && n.hora_notificacion === notif.hora_notificacion;
        }
      );
      
      if (!notificacionExistente) {
        // Crear nueva notificación
        router.post(route('notificaciones.registrar'), {
          sucursal_id: props.auth?.user?.sucursal_id,
          notificacion_id: notificacionId,
          cantidad: cantidad
        }, {
          preserveScroll: true,
          onSuccess: (response) => {
            console.log('Notificación registrada:', response);
          },
          onError: (error) => {
            console.error('Error al registrar notificación:', error);
          }
        });
      } else if (notificacionExistente.cantidad !== cantidad) {
        // Actualizar notificación existente si la cantidad cambió
        router.post(route('notificaciones.actualizar'), {
          sucursal_id: props.auth?.user?.sucursal_id,
          notificacion_id: notificacionId,
          cantidad: cantidad
        }, {
          preserveScroll: true,
          onSuccess: (response) => {
            console.log('Notificación actualizada:', response);
          },
          onError: (error) => {
            console.error('Error al actualizar notificación:', error);
          }
        });
      }
    });
  }
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
      console.log('Horneado iniciado:', response);
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
      console.log('Horneado finalizado:', response);
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
      console.log('Venta registrada:', response);
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
    const diferencia = tiempoInicioHorneado - tiempoNotificacion;
    const minutos = Math.floor(diferencia / (1000 * 60));
    
    // Si el horneado empezó antes que la notificación, es un error lógico
    if (minutos < 0) {
      return 'Error: Horneado antes de notificación';
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
      const diferencia = ahora - tiempoNotificacion;
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
    const diferencia = tiempoUltimaVenta - tiempoNotificacion;
    const minutos = Math.floor(diferencia / (1000 * 60));
    
    // Si la venta fue antes que la notificación, es un error lógico
    if (minutos < 0) {
      return 'Error: Venta antes de notificación';
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

</script> 