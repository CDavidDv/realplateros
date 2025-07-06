|<template>
  <div class="container mx-auto p-4">
    <div class="sm:flex justify-between flex-row text-sm">
      <h1 class="text-2xl font-bold mb-4">Sistema de Horneado</h1>

      <div class="flex gap-3 sm:flex-row flex-col">
        <!-- Notificaciones Faltantes -->
        <NotificationPanel
          :notifications="notificacionesFaltantes"
          :is-visible="mostrarFaltantes"
          type="warning"
          title="Faltantes"
          @toggle="toggleFaltantes"
        />

        <!-- Notificaciones Excedentes -->
        <NotificationPanel
          :notifications="notificacionesExcedentes"
          :is-visible="mostrarExcedentes"
          type="info"
          title="Excedentes"
          @toggle="toggleExcedentes"
        />
      </div>
    </div>

    <!-- Sección de Productos -->
    <div class="mt-6">
      <SistemaHorneado />
      <!-- TODO: poner el control de produccion en el sistema de horneado -->
      <ControlProduccion />
      <PastesHorneados v-if="props.user.roles[0] !== 'supervisor'" />
      <EstimacionPastes v-if="isAdmin" />
    </div>
  </div>
</template>

<script setup>
import { computed, ref, watch, onMounted, onUnmounted } from "vue";
import { usePage } from "@inertiajs/vue3";
import PastesHorneados from "./PastesHorneados.vue";
import EstimacionPastes from "./EstimacionPastes.vue";
import SistemaHorneado from "./SistemaHorneado.vue";
import ControlProduccion from "./ControlProduccion.vue";
import NotificationPanel from "./NotificationPanel.vue";

// Estado
const mostrarFaltantes = ref(false);
const mostrarExcedentes = ref(false);
const lastHour = ref(null);
const timeInterval = ref(null);

// Props y computed properties
const { props } = usePage();
const isAdmin = computed(() => props.auth.user.roles[0].name === 'admin');
const inventario = computed(() => props.inventario || []);
const estimaciones = computed(() => props.estimacionesHoy || []);

// Funciones de utilidad
const getCurrentDayAndTime = () => {
  const now = new Date();
  const daysOfWeek = ["Domingo", "Lunes", "Martes", "Miercoles", "Jueves", "Viernes", "Sábado"];
  return {
    day: daysOfWeek[now.getDay()].toLowerCase(),
    hour: now.getHours(),
    minutes: now.getMinutes(),
  };
};

const currentTime = ref(getCurrentDayAndTime());

// Watch para detectar cambios de hora
watch(() => currentTime.value.hour, (newHour) => {
  if (newHour !== lastHour.value) {
    lastHour.value = newHour;
    // Forzar actualización de notificaciones al cambiar de hora
    if (notificacionesFaltantes.value.length > 0) {
      mostrarFaltantes.value = true;
    }
    if (notificacionesExcedentes.value.length > 0) {
      mostrarExcedentes.value = true;
    }
  }
});

// Lifecycle hooks
onMounted(() => {
  // Inicializar lastHour
  lastHour.value = currentTime.value.hour;
  
  // Actualizar tiempo cada segundo
  timeInterval.value = setInterval(() => {
    currentTime.value = getCurrentDayAndTime();
  }, 1000);
});

onUnmounted(() => {
  if (timeInterval.value) {
    clearInterval(timeInterval.value);
  }
});

const convertTo24Hour = (timeStr) => {
  const [time, period] = timeStr.toLowerCase().split(" ");
  let [hours, minutes] = time.split(":").map(Number);
  return {
    hours: period === "pm" && hours !== 12 ? hours + 12 : period === "am" && hours === 12 ? 0 : hours,
    minutes
  };
};

// Computed properties optimizadas
const relevantProducts = computed(() => 
  inventario.value.filter(item => 
    ["pastes", "empanadas dulces", "empanadas saladas"].includes(item.tipo?.toLowerCase())
  )
);

const estimacionesVsExistentes = computed(() => {
  if (!estimaciones.value.length || !relevantProducts.value.length) {
    console.log('No hay estimaciones o productos relevantes');
    return [];
  }

  const productMap = new Map(relevantProducts.value.map(item => [item.id, item]));
  
  const resultado = estimaciones.value
    .map(estimacion => {
      const producto = productMap.get(estimacion.inventario_id);
      if (!producto) {
        return null;
      }

      const { hours } = convertTo24Hour(estimacion.hora);
      return {
        id: `${producto.id}-${estimacion.hora}`,
        nombre: producto.nombre,
        estimado: estimacion.cantidad,
        existente: producto.cantidad,
        diferencia: producto.cantidad - estimacion.cantidad,
        hora: estimacion.hora,
        dia: estimacion.dia.toLowerCase(),
        horaEnNumero: hours,
        tipo: producto.tipo
      };
    })
    .filter(Boolean)
    .filter((item, index, self) => 
      index === self.findIndex(t => t.id === item.id)
    );

  return resultado;
});

const itemsDelDia = computed(() => {
  const items = estimacionesVsExistentes.value.filter(item => item.dia === currentTime.value.day);
  return items;
});

const notificacionesActuales = computed(() => {
  const currentHour = currentTime.value.hour;
  const nextHour = (currentHour + 1) % 24; // Obtener la siguiente hora

  // Obtenemos los productos relevantes para la siguiente hora
  const productosHoraSiguiente = itemsDelDia.value.filter(item => item.horaEnNumero === nextHour);
  
  // Creamos un mapa de productos existentes para la siguiente hora
  const productosExistentes = new Map(
    productosHoraSiguiente.map(item => [item.nombre, item])
  );

  // Para cada producto relevante, verificamos si existe en la siguiente hora
  const notificaciones = relevantProducts.value.map(producto => {
    const productoExistente = productosExistentes.get(producto.nombre);
    
    if (productoExistente) {
      return productoExistente;
    } else {
      // Si no existe estimación, creamos un registro con la cantidad actual
      const nuevoRegistro = {
        id: `${producto.id}-${nextHour}:00`,
        nombre: producto.nombre,
        estimado: 0,
        existente: producto.cantidad,
        diferencia: producto.cantidad, // La diferencia será positiva si hay existencias
        hora: `${nextHour}:00`,
        dia: currentTime.value.day,
        horaEnNumero: nextHour,
        tipo: producto.tipo
      };
      return nuevoRegistro;
    }
  });
  return notificaciones;
});

const notificacionesFaltantes = computed(() => {
  const faltantes = notificacionesActuales.value.filter(item => {
    // Un producto es faltante si:
    // 1. Tiene estimación y la diferencia es negativa (existen menos de lo estimado)
    const esFaltante = item.estimado > 0 && item.diferencia < 0;
   
    return esFaltante;
  }).map(item => ({
    ...item,
    porcentaje: item.estimado > 0 ? Math.round((item.existente / item.estimado) * 100) : 0
  }));
  return faltantes;
});

const notificacionesExcedentes = computed(() => {
  const excedentes = notificacionesActuales.value.filter(item => {
    // Un producto es excedente si:
    // 1. Tiene estimación y la diferencia es positiva (existen más de lo estimado)
    // 2. O si no tiene estimación pero tiene existencias (producto sin estimación)
    const esExcedente = (item.estimado > 0 && item.diferencia > 0) || 
                       (item.estimado === 0 && item.existente > 0);
    
    return esExcedente;
  }).map(item => ({
    ...item,
    porcentaje: item.estimado > 0 ? Math.round((item.existente / item.estimado) * 100) : 100
  }));
  return excedentes;
});

// Event handlers
const toggleFaltantes = () => mostrarFaltantes.value = !mostrarFaltantes.value;
const toggleExcedentes = () => mostrarExcedentes.value = !mostrarExcedentes.value;
</script>