<template>
  <div class="container mx-auto p-4">
    <div class="sm:flex justify-between flex-row text-sm">
      <h1 class="text-2xl font-bold mb-4">Sistema de Horneado</h1>

      <div class="flex gap-3 sm:flex-row flex-col ">
        <!-- Notificaciones Faltantes -->
        <div class="p-4 border rounded shadow bg-yellow-100 mb-4 size-fit cursor-pointer" @click="toggleFaltantes">
          <h2 class="text-lg font-bold text-center text-yellow-800">
            ⚠️ Faltantes <span class="animate-pulse">({{ notificacionesFaltantes.length }})</span> ⚠️
          </h2>
          <ul v-if="mostrarFaltantes" class="mt-2">
            <li v-for="notif in notificacionesFaltantes" :key="notif.id" class="mb-2">
              <span>
                <span class="font-bold">{{ notif.nombre }}</span>: Faltan
                {{ Math.abs(notif.diferencia) }} unidades para las
                {{ notif.hora }}
              </span>
            </li>
          </ul>
        </div>

        <!-- Notificaciones Excedentes -->
        <div class="p-4 border rounded shadow bg-blue-100 mb-4 size-fit cursor-pointer" @click="toggleExcedentes">
          <h2 class="text-lg font-bold text-center text-blue-800">
            ℹ️ Excedentes <span class="animate-pulse">({{ notificacionesExcedentes.length }})</span> ℹ️
          </h2>
          <ul v-if="mostrarExcedentes" class="mt-2">
            <li v-for="notif in notificacionesExcedentes" :key="notif.id" class="mb-2">
              <span>
                <span class="font-bold">{{ notif.nombre }}</span>: Hay
                {{ notif.diferencia }} unidades extra para las {{ notif.hora }}
              </span>
            </li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Sección de Productos -->
    <div class="mt-6">
      <SistemaHorneado />
      <PastesHorneados />
      <EstimacionPastes />
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from "vue";
import { usePage } from "@inertiajs/vue3";
import PastesHorneados from "./PastesHorneados.vue";
import EstimacionPastes from "./EstimacionPastes.vue";
import SistemaHorneado from "./SistemaHorneado.vue";
import Swal from "sweetalert2";

// Lógica de estado para mostrar/ocultar
const mostrarFaltantes = ref(false);
const mostrarExcedentes = ref(false);

function toggleFaltantes() {
  mostrarFaltantes.value = !mostrarFaltantes.value;
}

function toggleExcedentes() {
  mostrarExcedentes.value = !mostrarExcedentes.value;
}

// Restante código de lógica
const page = usePage();
const inventario = computed(() => page.props.inventario || []);
const estimaciones = computed(() => page.props.estimacionesHoy || []);
const currentTime = ref(getCurrentDayAndTime());

function getCurrentDayAndTime() {
  const now = new Date();
  const daysOfWeek = [
    "Domingo",
    "Lunes",
    "Martes",
    "Miercoles",
    "Jueves",
    "Viernes",
    "Sábado",
  ];
  return {
    day: daysOfWeek[now.getDay()].toLowerCase(),
    hour: now.getHours(),
    minutes: now.getMinutes(),
  };
}

onMounted(() => {
  setInterval(() => {
    currentTime.value = getCurrentDayAndTime();
  }, 60000);
});

function convertTo24Hour(timeStr) {
  const [time, period] = timeStr.toLowerCase().split(" ");
  let [hours, minutes] = time.split(":").map(Number);

  if (period === "pm" && hours !== 12) {
    hours += 12;
  } else if (period === "am" && hours === 12) {
    hours = 0;
  }

  return { hours, minutes };
}

const relevantProducts = computed(() => {
  return inventario.value.filter((item) =>
    ["pastes", "empanadas dulces", "empanadas saladas"].includes(
      item.tipo?.toLowerCase()
    )
  );
});

const estimacionesVsExistentes = computed(() => {
  if (!estimaciones.value.length || !relevantProducts.value.length) return [];

  return estimaciones.value
    .map((estimacion) => {
      const producto = relevantProducts.value.find(
        (item) => item.id === estimacion.inventario_id
      );

      if (!producto) return null;

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
      index === self.findIndex((t) => (
        t.id === item.id
      ))
    ); // Elimina duplicados basados en el id
});

const itemsDelDia = computed(() => {
  return estimacionesVsExistentes.value.filter(
    (item) => item.dia === currentTime.value.day
  );
});

const itemsFaltantes = computed(() => {
  return itemsDelDia.value.filter((item) => item.diferencia < 0);
});

const itemsExcedentes = computed(() => {
  return itemsDelDia.value.filter((item) => item.diferencia > 0);
});

const notificacionesActuales = computed(() => {
  return itemsDelDia.value.filter((item) => {
    const currentHour = currentTime.value.hour;
    const isRelevantHour = Math.abs(item.horaEnNumero - currentHour) <= 1;
    return isRelevantHour;
  });
});

const notificacionesFaltantes = computed(() => {
  return notificacionesActuales.value.filter((item) => item.diferencia < 0);
});

const checkNotificationsInterval = ref(null);
onMounted(() => {
  let sonidoReproducido = false; // Variable de control

  notificacionesFaltantes.value.forEach(notif => {
    if (Math.abs(notif.diferencia) > 3 && !sonidoReproducido) {
      reproducirSonido();
      sonidoReproducido = true; // Marcamos que el sonido ya se reprodujo
    }
    if (Math.abs(notif.diferencia) > 3) {
      showToast('warning', `Faltan ${Math.abs(notif.diferencia)} unidades para las ${notif.hora}`);
    }
  });

  clearInterval(checkNotificationsInterval.value);

  // Verificar notificaciones cada 10 minutos
  checkNotificationsInterval.value = setInterval(() => {
    let sonidoReproducidoEnCiclo = false; // Reiniciar la variable de control por ciclo

    notificacionesFaltantes.value.forEach(notif => {
      if (Math.abs(notif.diferencia) > 3 && !sonidoReproducidoEnCiclo) {
        reproducirSonido();
        sonidoReproducidoEnCiclo = true; // Evita que suene más de una vez en este ciclo
      }
      if (Math.abs(notif.diferencia) > 3) {
        showToast('warning', `Faltan ${Math.abs(notif.diferencia)} unidades para las ${notif.hora}`);
      }
    });
  }, 10 * 60 * 1000);
});

const reproducirSonido = () => {
  const audio = new Audio('/sound/videoplayback.mp3');
  audio.play().catch(error => console.error("Error al reproducir el sonido:", error));
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
      toast.addEventListener('mouseenter', Swal.stopTimer)
      toast.addEventListener('mouseleave', Swal.resumeTimer)
    }
  }).fire({
    icon,
    title
  })
}

const notificacionesExcedentes = computed(() => {
  return notificacionesActuales.value.filter((item) => item.diferencia > 0);
});
</script>