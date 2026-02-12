<template>
  <!-- Este componente no necesita template ya que solo maneja las notificaciones toast -->
   <div>
   </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import Swal from 'sweetalert2';

// Estado
const checkNotificationsInterval = ref(null);
const lastNotificationTime = ref(Date.now());

const NOTIFICATION_INTERVAL = 300000; // 5 minutos (aumentado de 3 a 5)
const UPDATE_INTERVAL = 30000; // 30 segundos (aumentado de 5 a 30)

// Props y computed properties
const { props } = usePage();
const inventario = computed(() => props.inventario || []);

const estimaciones = computed(() => props.estimacionesHoy || []);

//estimaciones
watch(estimaciones.value, (newEstimaciones) => {
  // console.log(newEstimaciones); // Comentado para reducir ruido, descomentar si es necesario
});

const notificaciones_guardadas = ref(props.notificaciones?.faltantes || []);

// Maps optimizados para búsquedas rápidas
const notificacionesMap = computed(() => {
  const map = new Map();
  notificaciones_guardadas.value.forEach(notif => {
    const key = `${notif.paste_id}-${notif.hora_notificacion}`;
    map.set(key, notif);
  });
  return map;
});

const horneadosMap = computed(() => {
  const map = new Map();
  const horneados = props.notificaciones?.horneados || [];
  horneados.forEach(h => {
    const key = `${h.paste_id}-${h.hora_notificacion}`;
    map.set(key, h);
  });
  return map;
});

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

// Función para convertir hora 12h a 24h
const convertTo24Hour = (time12h) => {
  if (!time12h) return 0;
  
  const [time, modifier] = time12h.toLowerCase().split(' ');
  let [hours, minutes] = time.split(':');
  
  hours = parseInt(hours, 10);
  
  if (hours === 12) {
    hours = modifier === 'am' ? 0 : 12;
  } else if (modifier === 'pm') {
    hours = hours + 12;
  }
  
  return hours;
};

const currentTime = ref(getCurrentDayAndTime());
const lastHour = ref(currentTime.value.hour);

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

const itemsDelDia = computed(() => {
  const items = estimacionesVsExistentes.value.filter(item => item.dia === currentTime.value.day);
  return items;
});

const notificacionesActuales = computed(() => {
  const currentHour = currentTime.value.hour;
  const nextHour = (currentHour + 1) % 24; // Obtener la siguiente hora
  
  // Obtenemos los productos relevantes para la siguiente hora
  const productosHoraSiguiente = itemsDelDia.value.filter(item => {
    // Convertimos la hora del item a formato 24 horas si es necesario
    const horaItem = item.horaEnNumero || convertTo24Hour(item.hora);
    return horaItem === nextHour;
  });
  
  const productosExistentes = new Map(
    productosHoraSiguiente.map(item => [item.nombre, item])
  );

  // Si no hay productos para la siguiente hora, usamos todos los productos relevantes
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
        dia: currentTime.value.day,
        horaEnNumero: nextHour,
        tipo: producto.tipo
      };
    }
  });

  return notificaciones;
});

const notificacionesFaltantes = computed(() => {
  const faltantes = notificacionesActuales.value.filter(item => {
    // Un producto es faltante si:
    // 1. Tiene estimación y la cantidad existente es menor al 70% de lo estimado
    const porcentajeMinimo = 0.7; // 70%
    const cantidadMinima = item.estimado * porcentajeMinimo;
    const esFaltante = item.estimado > 0 && item.existente < cantidadMinima;
    
    if (esFaltante) {
      
    }
    return esFaltante;
  });
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
  });
  return excedentes;
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

// Función para mostrar notificaciones con delay
const showNotificationsWithDelay = (notificaciones, delay = 1000) => {
  notificaciones.forEach((notif, index) => {
    setTimeout(() => {
      showToast(notif.tipo, notif.mensaje);
    }, index * delay);
  });
};

// Watch para notificaciones de venta
watch(() => props.notificaciones, (newNotificaciones) => {
  if (newNotificaciones && newNotificaciones.length > 0) {
    showNotificationsWithDelay(newNotificaciones);
  }
}, { immediate: true });

const reproducirSonido = () => {
  const audio = new Audio('/sound/videoplayback.mp3');
  audio.play().catch(error => console.error("Error al reproducir el sonido:", error));
};

// Función para actualizar las notificaciones
const actualizarNotificaciones = (nuevasNotificaciones) => {
  notificaciones_guardadas.value = nuevasNotificaciones;
};

// Función de utilidad para filtrar notificaciones considerando horneados (OPTIMIZADA)
const filterNotificationsForUI = (notificationsToFilter, horneadosList) => {
  if (!notificationsToFilter || notificationsToFilter.length === 0) return [];
  
  // Usar el Map optimizado en lugar de la lista
  const horneadosMap = new Map();
  if (horneadosList && horneadosList.length > 0) {
    horneadosList.forEach(h => {
      const key = `${h.paste_id}-${h.hora_notificacion}`;
      horneadosMap.set(key, h);
    });
  }

  return notificationsToFilter
    .map(notif => {
      const pasteId = parseInt(notif.id.toString().split('-')[0]);
      const horaOriginalNotif = notif.hora; // ej: "10:00 am"
      const key = `${pasteId}-${horaOriginalNotif}`;

      // ✅ Búsqueda O(1) en lugar de O(n)
      const itemHorneado = horneadosMap.get(key);
      const cantidadHorneada = itemHorneado ? (itemHorneado.cantidad_horneada || 0) : 0;
      const totalConsiderado = notif.existente + cantidadHorneada;
      const estimadoNumerico = Number(notif.estimado);
      const porcentajeCubierto = estimadoNumerico > 0 ? (totalConsiderado / estimadoNumerico) : 1;

      return { ...notif, cantidadHorneada, totalConsiderado, porcentajeCubierto, estimadoNumerico };
    })
    .filter(notif => notif.estimadoNumerico > 0 && notif.porcentajeCubierto < 0.7);
};

// Watch para detectar cambios de hora
watch(() => currentTime.value.hour, (newHour) => {
  if (newHour !== lastHour.value) {
    lastHour.value = newHour;
    // Forzar actualización de notificaciones al cambiar de hora
    const notificarFaltantesValor = notificacionesFaltantes.value;

    if (notificarFaltantesValor && notificarFaltantesValor.length > 0) {
      const horneadosData = usePage().props.notificaciones?.horneados || [];
      const notificacionesHoraUI = filterNotificationsForUI(notificarFaltantesValor, horneadosData);

      console.log('Watcher Cambio Hora - Faltantes Originales (solo inventario vs estimado):', JSON.parse(JSON.stringify(notificarFaltantesValor)));
      console.log('Watcher Cambio Hora - Faltantes Para UI (considerando horneados):', JSON.parse(JSON.stringify(notificacionesHoraUI)));

      if (notificacionesHoraUI.length > 0) {
        reproducirSonido();
        showNotificationsWithDelay(notificacionesHoraUI.map(notif => ({
          tipo: 'warning',
          mensaje: `${notif.nombre} para las ${notif.hora}: ${Math.round(notif.porcentajeCubierto * 100)}% cubierto (inv. + horneando). Mínimo 70%.`
        })));
        lastNotificationTime.value = Date.now();
      }
    }
  }
});

// Lifecycle hooks
onMounted(() => {
  // Actualizar tiempo cada segundo
  const timeInterval = setInterval(() => {
    currentTime.value = getCurrentDayAndTime();
  }, 1000);

  // Función para mostrar notificaciones
  const mostrarNotificaciones = () => {
    const faltantesActuales = notificacionesFaltantes.value;
    if (!faltantesActuales?.length) return;

    // Un solo request batch en vez de N requests individuales
    const notificacionesPayload = faltantesActuales.map(notif => ({
        notificacion_id: String(notif.id),
        cantidad: notif.estimado - notif.existente
    }));

    axios.post(route('notificaciones.registrar-batch'), {
        sucursal_id: usePage().props.auth.user.sucursal_id,
        notificaciones: notificacionesPayload
    }).catch(error => {
        console.error('Error al registrar notificaciones batch:', error);
    });

    // Lógica de UI toast
    const horneadosData = usePage().props.notificaciones?.horneados || [];
    const notificacionesIntervaloUI = filterNotificationsForUI(faltantesActuales, horneadosData);

    if (notificacionesIntervaloUI.length > 0) {
        reproducirSonido();
        showNotificationsWithDelay(notificacionesIntervaloUI.map(notif => ({
          tipo: 'warning',
          mensaje: `${notif.nombre} para las ${notif.hora}: ${Math.round(notif.porcentajeCubierto * 100)}% cubierto (inv. + horneando). Mínimo 70%.`
        })));
        lastNotificationTime.value = Date.now();
    }
  };

  // Mostrar notificaciones inmediatamente al cargar el componente
  mostrarNotificaciones();

  // Verificar notificaciones cada 3 minutos
  checkNotificationsInterval.value = setInterval(() => {
    const currentTimestamp = Date.now();
    if (currentTimestamp - lastNotificationTime.value >= NOTIFICATION_INTERVAL) {
      mostrarNotificaciones();
    }
  }, UPDATE_INTERVAL);

  onUnmounted(() => {
    clearInterval(timeInterval);
    clearInterval(checkNotificationsInterval.value);
  });
});

// Watch para actualizar notificaciones cuando cambia el inventario (OPTIMIZADO)
watch(() => props.inventario, (newInventario) => {
  if (!newInventario?.length) return;

  setTimeout(() => {
    const faltantes = notificacionesFaltantes.value;
    if (!faltantes?.length) return;

    axios.post(route('notificaciones.registrar-batch'), {
        sucursal_id: usePage().props.auth.user.sucursal_id,
        notificaciones: faltantes.map(notif => ({
            notificacion_id: String(notif.id),
            cantidad: notif.estimado - notif.existente
        }))
    }).catch(error => {
        console.error('Error al registrar notificaciones batch:', error);
    });
  }, 1000);
}, { deep: true });
</script> 