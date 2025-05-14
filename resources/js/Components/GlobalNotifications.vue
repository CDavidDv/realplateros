<template>
  <div class="fixed top-4 right-4 z-50 flex flex-col gap-2">
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
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import NotificationPanel from './NotificationPanel.vue';
import Swal from 'sweetalert2';

// Estado
const mostrarFaltantes = ref(false);
const mostrarExcedentes = ref(false);
const checkNotificationsInterval = ref(null);

// Props y computed properties
const { props } = usePage();
const isAdmin = computed(() => props.auth.user.roles[0].name === 'admin');
const inventario = computed(() => props.inventario || []);
const estimaciones = computed(() => props.estimacionesHoy || []);

// Funciones de utilidad
const getCurrentDayAndTime = () => {
  const now = new Date();
  const days = ['Domingo', 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sábado'];
  return {
    day: days[now.getDay()],
    hour: now.getHours(),
    minute: now.getMinutes()
  };
};

const currentTime = ref(getCurrentDayAndTime());

// Computed properties para notificaciones
const relevantProducts = computed(() => 
  inventario.value.filter(item => 
    ['pastes', 'empanadas dulces', 'empanadas saladas'].includes(item.tipo)
  )
);

const estimacionesVsExistentes = computed(() => {
  const productMap = new Map(relevantProducts.value.map(item => [item.id, item]));
  
  const resultado = estimaciones.value
    .map(estimacion => {
      const producto = productMap.get(estimacion.inventario_id);
      if (!producto) return null;

      return {
        id: `${producto.id}-${estimacion.hora}`,
        nombre: producto.nombre,
        estimado: estimacion.cantidad,
        existente: producto.cantidad,
        diferencia: producto.cantidad - estimacion.cantidad,
        hora: estimacion.hora,
        dia: estimacion.dia,
        horaEnNumero: parseInt(estimacion.hora.split(':')[0]),
        tipo: producto.tipo
      };
    })
    .filter(Boolean);

  return resultado;
});

const itemsDelDia = computed(() => {
  const items = estimacionesVsExistentes.value.filter(item => item.dia === currentTime.value.day);
  return items;
});

const notificacionesActuales = computed(() => {
  const currentHour = currentTime.value.hour;
  const productosHoraActual = itemsDelDia.value.filter(item => item.horaEnNumero === currentHour);
  const productosExistentes = new Map(
    productosHoraActual.map(item => [item.nombre, item])
  );

  const notificaciones = relevantProducts.value.map(producto => {
    const productoExistente = productosExistentes.get(producto.nombre);
    
    if (productoExistente) {
      return productoExistente;
    } else {
      return {
        id: `${producto.id}-${currentHour}:00`,
        nombre: producto.nombre,
        estimado: 0,
        existente: producto.cantidad,
        diferencia: producto.cantidad,
        hora: `${currentHour}:00`,
        dia: currentTime.value.day,
        horaEnNumero: currentHour,
        tipo: producto.tipo
      };
    }
  });
  return notificaciones;
});

const notificacionesFaltantes = computed(() => {
  return notificacionesActuales.value.filter(item => {
    const esFaltante = item.estimado > 0 && item.diferencia < 0;
    return esFaltante;
  });
});

const notificacionesExcedentes = computed(() => {
  return notificacionesActuales.value.filter(item => {
    const esExcedente = (item.estimado > 0 && item.diferencia > 0) || 
                       (item.estimado === 0 && item.existente > 0);
    return esExcedente;
  });
});

// Funciones de notificación
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
  // Actualizar tiempo cada 5 segundos
  const timeInterval = setInterval(() => {
    currentTime.value = getCurrentDayAndTime();
  }, 5000);

  // Verificar notificaciones cada minuto
  checkNotificationsInterval.value = setInterval(() => {
    const notificarFaltantes = notificacionesFaltantes.value;
    const notificarExcedentes = notificacionesExcedentes.value;

    if (notificarFaltantes.length > 0) {
      notificarFaltantes.forEach(notif => 
        showToast('warning', `Faltan ${Math.abs(notif.diferencia)} unidades para las ${notif.hora}`)
      );
    }

    if (notificarExcedentes.length > 0) {
      notificarExcedentes.forEach(notif => {
        showToast('info', `Hay ${notif.diferencia} unidades extra para las ${notif.hora}`);
      });
    }
  }, 1000);

  onUnmounted(() => {
    clearInterval(timeInterval);
    clearInterval(checkNotificationsInterval.value);
  });
});
</script> 