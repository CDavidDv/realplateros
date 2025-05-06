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
      <!-- <ControlProduccion /> -->
      <PastesHorneados />
      <EstimacionPastes v-if="isAdmin" />
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref, onUnmounted } from "vue";
import { usePage } from "@inertiajs/vue3";
import PastesHorneados from "./PastesHorneados.vue";
import EstimacionPastes from "./EstimacionPastes.vue";
import SistemaHorneado from "./SistemaHorneado.vue";
import ControlProduccion from "./ControlProduccion.vue";
import NotificationPanel from "./NotificationPanel.vue";
import Swal from "sweetalert2";

// Estado
const mostrarFaltantes = ref(false);
const mostrarExcedentes = ref(false);
const checkNotificationsInterval = ref(null);

// Props y computed properties
const { props } = usePage();
const isAdmin = computed(() => props.auth.user.roles[0].name === 'admin');
const inventario = computed(() => props.inventario || []);
const estimaciones = computed(() => props.estimacionesHoy || []);

console.log('Props iniciales:', {
  inventario: inventario.value,
  estimaciones: estimaciones.value,
  isAdmin: isAdmin.value
});

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

console.log(relevantProducts.value)

const estimacionesVsExistentes = computed(() => {
  if (!estimaciones.value.length || !relevantProducts.value.length) {
    console.log('No hay estimaciones o productos relevantes');
    return [];
  }

  const productMap = new Map(relevantProducts.value.map(item => [item.id, item]));
  console.log('Productos relevantes:', relevantProducts.value);
  console.log('Mapa de productos:', Object.fromEntries(productMap));
  
  const resultado = estimaciones.value
    .map(estimacion => {
      const producto = productMap.get(estimacion.inventario_id);
      if (!producto) {
        console.log('Producto no encontrado para estimación:', estimacion);
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
      };
    })
    .filter(Boolean)
    .filter((item, index, self) => 
      index === self.findIndex(t => t.id === item.id)
    );

  console.log('Estimaciones vs Existentes:', resultado);
  return resultado;
});

const itemsDelDia = computed(() => {
  const items = estimacionesVsExistentes.value.filter(item => item.dia === currentTime.value.day);
  console.log('Items del día:', {
    diaActual: currentTime.value.day,
    items
  });
  return items;
});

const notificacionesActuales = computed(() => {
  const currentHour = currentTime.value.hour;
  console.log('Evaluando notificaciones actuales:', {
    horaActual: currentHour,
    itemsDelDia: itemsDelDia.value
  });

  // Obtenemos los productos relevantes para la hora actual
  const productosHoraActual = itemsDelDia.value.filter(item => item.horaEnNumero === currentHour);
  
  // Creamos un mapa de productos existentes para la hora actual
  const productosExistentes = new Map(
    productosHoraActual.map(item => [item.nombre, item])
  );

  // Para cada producto relevante, verificamos si existe en la hora actual
  const notificaciones = relevantProducts.value.map(producto => {
    const productoExistente = productosExistentes.get(producto.nombre);
    
    if (productoExistente) {
      console.log('Producto existente en hora actual:', productoExistente);
      return productoExistente;
    } else {
      // Si no existe, creamos un registro con valor 0
      const nuevoRegistro = {
        id: `${producto.id}-${currentHour}:00`,
        nombre: producto.nombre,
        estimado: 0,
        existente: producto.cantidad,
        diferencia: producto.cantidad, // La diferencia será igual a la cantidad existente
        hora: `${currentHour}:00`,
        dia: currentTime.value.day,
        horaEnNumero: currentHour
      };
      console.log('Creando registro para producto sin estimación:', nuevoRegistro);
      return nuevoRegistro;
    }
  });

  console.log('Notificaciones actuales filtradas:', notificaciones);
  return notificaciones;
});

const notificacionesFaltantes = computed(() => {
  const faltantes = notificacionesActuales.value.filter(item => {
    // Un producto es faltante si:
    // 1. Tiene estimación y la diferencia es negativa (existen menos de lo estimado)
    const esFaltante = item.estimado > 0 && item.diferencia < 0;
    console.log('Evaluando faltante:', {
      item,
      diferencia: item.diferencia,
      esFaltante,
      estimado: item.estimado,
      existente: item.existente
    });
    return esFaltante;
  });
  console.log('Notificaciones faltantes finales:', faltantes);
  return faltantes;
});

const notificacionesExcedentes = computed(() => {
  const excedentes = notificacionesActuales.value.filter(item => {
    // Un producto es excedente si:
    // 1. Tiene estimación y la diferencia es positiva (existen más de lo estimado)
    // 2. O si no tiene estimación pero tiene existencias (producto sin estimación)
    const esExcedente = (item.estimado > 0 && item.diferencia > 0) || 
                       (item.estimado === 0 && item.existente > 0);
    console.log('Evaluando excedente:', {
      item,
      diferencia: item.diferencia,
      esExcedente,
      estimado: item.estimado,
      existente: item.existente
    });
    return esExcedente;
  });
  console.log('Notificaciones excedentes finales:', excedentes);
  return excedentes;
});

console.log('Notificaciones excedentes:', notificacionesExcedentes.value);

// Funciones de notificación
const reproducirSonido = () => {
  const audio = new Audio('/sound/videoplayback.mp3');
  audio.play().catch(console.error);
};

const showToast = (icon, title) => {
  Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    customClass: "no-print",
    didOpen: (toast) => {
      toast.addEventListener('mouseenter', Swal.stopTimer);
      toast.addEventListener('mouseleave', Swal.resumeTimer);
    }
  }).fire({ icon, title });
};

// Event handlers
const toggleFaltantes = () => mostrarFaltantes.value = !mostrarFaltantes.value;
const toggleExcedentes = () => mostrarExcedentes.value = !mostrarExcedentes.value;

// Lifecycle hooks
onMounted(() => {
  console.log('Componente montado');
  console.log('Estado inicial:', {
    currentTime: currentTime.value,
    mostrarFaltantes: mostrarFaltantes.value,
    mostrarExcedentes: mostrarExcedentes.value,
    productosRelevantes: relevantProducts.value
  });

  // Actualizar tiempo cada segundo
  const timeInterval = setInterval(() => {
    const nuevoTiempo = getCurrentDayAndTime();
    console.log('Tiempo actualizado:', {
      anterior: currentTime.value,
      nuevo: nuevoTiempo
    });
    currentTime.value = nuevoTiempo;
  }, 1000);

  // Verificar notificaciones cada minuto
  checkNotificationsInterval.value = setInterval(() => {
    console.log('Verificando notificaciones...');
    console.log('Estado actual de notificaciones:', {
      horaActual: currentTime.value.hour,
      actuales: notificacionesActuales.value,
      faltantes: notificacionesFaltantes.value,
      excedentes: notificacionesExcedentes.value
    });

    // Mostramos todas las notificaciones de la hora actual
    const notificarFaltantes = notificacionesFaltantes.value;
    const notificarExcedentes = notificacionesExcedentes.value;

    console.log('Notificaciones a mostrar:', {
      horaActual: currentTime.value.hour,
      faltantes: notificarFaltantes,
      excedentes: notificarExcedentes
    });

    if (notificarFaltantes.length > 0) {
      //TODO:reproducirSonido();
      /*reproducirSonido();
      notificarFaltantes.forEach(notif => 
        showToast('warning', `Faltan ${Math.abs(notif.diferencia)} unidades para las ${notif.hora}`)
      );*/
    }

    if (notificarExcedentes.length > 0) {
      notificarExcedentes.forEach(notif => {
        console.log('Mostrando toast para excedente:', notif);
        showToast('info', `Hay ${notif.diferencia} unidades extra para las ${notif.hora}`);
      });
    }
  }, 60000); // Verificar cada minuto

  // Limpiar intervalos al desmontar
  onUnmounted(() => {
    clearInterval(timeInterval);
    clearInterval(checkNotificationsInterval.value);
  });
});
</script>