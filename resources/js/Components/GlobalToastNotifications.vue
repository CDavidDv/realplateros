<template>
  <!-- Este componente no necesita template ya que solo maneja las notificaciones toast -->
   <div>
   </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

// Estado
const checkNotificationsInterval = ref(null);
const lastNotificationTime = ref(Date.now());
// 5 * 60 * 1000 = 5 minutos
const NOTIFICATION_INTERVAL = 1 * 30 * 1000; // 5 minutos en milisegundos
const UPDATE_INTERVAL = 30 * 1000; // 30 segundos para actualizar datos

// Props y computed properties
const { props } = usePage();
const inventario = computed(() => props.inventario || []);

const estimaciones = computed(() => props.estimacionesHoy || []);

//estimaciones
watch(estimaciones.value, (newEstimaciones) => {
  console.log(newEstimaciones);
});

const notificaciones_guardadas = ref(props.notificaciones?.faltantes || []);

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
  
  // Obtenemos los productos relevantes para la hora actual
  const productosHoraActual = itemsDelDia.value.filter(item => {
    // Convertimos la hora del item a formato 24 horas si es necesario
    const horaItem = item.horaEnNumero || convertTo24Hour(item.hora);
    return horaItem === currentHour;
  });
  
  
  const productosExistentes = new Map(
    productosHoraActual.map(item => [item.nombre, item])
  );

  // Si no hay productos para la hora actual, usamos todos los productos relevantes
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

// Watch para detectar cambios de hora
watch(() => currentTime.value.hour, (newHour) => {
  if (newHour !== lastHour.value) {
    lastHour.value = newHour;
    // Forzar actualización de notificaciones al cambiar de hora
    const notificarFaltantes = notificacionesFaltantes.value;
    if (notificarFaltantes.length > 0) {
      reproducirSonido();
      showNotificationsWithDelay(notificarFaltantes.map(notif => ({
        tipo: 'warning',
        mensaje: `${notif.nombre} para las ${notif.hora}: ${Math.round((notif.existente / notif.estimado) * 100)}% del inventario estimado (mínimo 70%)`
      })));
      lastNotificationTime.value = Date.now();
    }
  }
});

// Lifecycle hooks
onMounted(() => {
  // Actualizar tiempo cada segundo
  const timeInterval = setInterval(() => {
    currentTime.value = getCurrentDayAndTime();
  }, 1000);

  // Verificar notificaciones cada 30 segundos
  checkNotificationsInterval.value = setInterval(() => {
    const notificarFaltantes = notificacionesFaltantes.value;
    const notificarExcedentes = notificacionesExcedentes.value;
    const currentTime = Date.now();

    // Solo mostrar notificaciones si han pasado 30 segundos desde la última vez
    if (currentTime - lastNotificationTime.value >= NOTIFICATION_INTERVAL) {
      reproducirSonido();

      if (notificarFaltantes.length > 0) {
        // Procesar cada notificación individualmente
        notificarFaltantes.forEach(async (notif) => {
          const notificacionId = `${notif.id}`;
          const cantidad = notif.estimado - notif.existente;

          // Extraemos el ID del paste y la hora de la notificación actual
          const pasteId = parseInt(notificacionId.split('-')[0]);
          const horaNotificacion = notificacionId.split('-')[1];

          // Verificamos que tengamos notificaciones antes de buscar
          if (!notificaciones_guardadas.value || notificaciones_guardadas.value.length === 0) {
            router.post(route('notificaciones.registrar'), {
              sucursal_id: usePage().props.auth.user.sucursal_id,
              notificacion_id: notificacionId,
              cantidad: cantidad
            }, {
              preserveScroll: true,
              onSuccess: (response) => {
                if (response.props.notificaciones?.faltantes) {
                  actualizarNotificaciones(response.props.notificaciones.faltantes);
                }
              }
            });
            return;
          }

          // Buscamos en todas las notificaciones guardadas
          const notificacionExistente = notificaciones_guardadas.value.find(
            notif => notif.paste_id === pasteId && notif.hora_notificacion === horaNotificacion
          );

          if (notificacionExistente) {
            // Si existe y la cantidad es diferente, actualizamos
            if (notificacionExistente.cantidad !== cantidad) {
              router.post(route('notificaciones.actualizar'), {
                sucursal_id: usePage().props.auth.user.sucursal_id,
                notificacion_id: notificacionId,
                cantidad: cantidad
              }, {
                preserveScroll: true,
                onSuccess: (response) => {
                  if (response.props.notificaciones?.faltantes) {
                    actualizarNotificaciones(response.props.notificaciones.faltantes);
                  }
                }
              });
            }
          } else {
            // Si no existe, creamos una nueva notificación
            router.post(route('notificaciones.registrar'), {
              sucursal_id: usePage().props.auth.user.sucursal_id,
              notificacion_id: notificacionId,
              cantidad: cantidad
            }, {
              preserveScroll: true,
              onSuccess: (response) => {
                if (response.props.notificaciones?.faltantes) {
                  actualizarNotificaciones(response.props.notificaciones.faltantes);
                }
              }
            });
          }
        });

        showNotificationsWithDelay(notificarFaltantes.map(notif => ({
          tipo: 'warning',
          mensaje: `${notif.nombre} para las ${notif.hora}: ${Math.round((notif.existente / notif.estimado) * 100)}% del inventario estimado (mínimo 70%)`
        })));

        // Actualizar el tiempo de la última notificación
        lastNotificationTime.value = currentTime;
      }
    }
  }, UPDATE_INTERVAL); // Verificar cada 30 segundos

  onUnmounted(() => {
    clearInterval(timeInterval);
    clearInterval(checkNotificationsInterval.value);
  });
});

// Watch para actualizar notificaciones cuando cambia el inventario
watch(() => props.inventario, (newInventario) => {
  if (newInventario) {
    // Actualizar las notificaciones sin mostrar toast
    const notificarFaltantes = notificacionesFaltantes.value;
    if (notificarFaltantes.length > 0) {
      notificarFaltantes.forEach(async (notif) => {
        const notificacionId = `${notif.id}`;
        const cantidad = notif.estimado - notif.existente;
        const pasteId = parseInt(notificacionId.split('-')[0]);
        const horaNotificacion = notificacionId.split('-')[1];

        // Actualizar o crear notificación sin mostrar toast
        if (!notificaciones_guardadas.value || notificaciones_guardadas.value.length === 0) {
          router.post(route('notificaciones.registrar'), {
            sucursal_id: usePage().props.auth.user.sucursal_id,
            notificacion_id: notificacionId,
            cantidad: cantidad
          }, {
            preserveScroll: true,
            onSuccess: (response) => {
              if (response.props.notificaciones?.faltantes) {
                actualizarNotificaciones(response.props.notificaciones.faltantes);
              }
            }
          });
        } else {
          const notificacionExistente = notificaciones_guardadas.value.find(
            notif => notif.paste_id === pasteId && notif.hora_notificacion === horaNotificacion
          );

          if (notificacionExistente && notificacionExistente.cantidad !== cantidad) {
            router.post(route('notificaciones.actualizar'), {
              sucursal_id: usePage().props.auth.user.sucursal_id,
              notificacion_id: notificacionId,
              cantidad: cantidad
            }, {
              preserveScroll: true,
              onSuccess: (response) => {
                if (response.props.notificaciones?.faltantes) {
                  actualizarNotificaciones(response.props.notificaciones.faltantes);
                }
              }
            });
          }
        }
      });
    }
  }
}, { deep: true });
</script> 