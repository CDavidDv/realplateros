<template>
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <!-- Sección de Masas y Rellenos -->
    <div class="bg-white shadow rounded-lg p-6">
      <h2 class="text-xl font-semibold mb-4">Ingredientes Disponibles</h2>
      <div class="mb-6">
        <h3 class="text-lg font-medium mb-2">Masas</h3>
        <ul class="list-disc pl-5">
          <li v-for="masa in masasActualizadas" :key="masa.id" class="flex justify-between">
            <span class="capitalize">{{ masa.nombre }}</span>
            <span class="font-semibold">{{ masa.cantidad }} unidades</span>
          </li>
        </ul>
      </div>
      <div>
        <h3 class="text-lg font-medium mb-2">Rellenos</h3>
        <ul class="list-disc pl-5">
          <li v-for="relleno in rellenosActualizados" :key="relleno.id" class="flex justify-between">
            <span>{{ relleno.nombre }}</span>
            <span class="font-semibold">{{ relleno.cantidad }} unidades</span>
          </li>
        </ul>
      </div>
    </div>

    <!-- Sección de Creación de Pastes -->
    <div class="bg-white shadow rounded-lg p-6">
      <h2 class="text-xl font-semibold mb-4">Crear Nuevo Paste/Empanada</h2>
      <form @submit.prevent="crearPaste" class="space-y-4">
        <div>
          <label for="relleno" class="block text-sm font-medium text-gray-700">Seleccionar Relleno</label>
          <select v-model="nuevoPaste.relleno" id="relleno" required
            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-orange-500 focus:border-orange-500 sm:text-sm rounded-md">
            <option value="">Seleccione un relleno</option>
            <option v-for="relleno in rellenos" :key="relleno.id" :value="relleno.nombre">{{ relleno.nombre }}</option>
          </select>
        </div>
        <div>
          <label for="cantidad" class="block text-sm font-medium text-gray-700">Cantidad</label>
          <input v-model.number="nuevoPaste.cantidad" type="number" id="cantidad" required min="1"
            :max="maxCantidadDisponible"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 sm:text-sm">
        </div>
        <button type="submit" :disabled="!isFormValid"
          class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 disabled:opacity-50 disabled:cursor-not-allowed">
          Asignar Unidades
        </button>
      </form>
    </div>
  </div>

  <!-- Sección de Horneado -->
  <div class="mt-8 bg-white shadow rounded-lg p-6">
    <h2 class="text-xl font-semibold mb-4">Horno</h2>
    <div v-if="horneando">
      <p class="text-lg mb-2">Horneando grupo de pastes:</p>
      <ul>
        <li v-for="paste in pastesHorneando" :key="paste.nombre">
          {{ paste.cantidad }} {{ paste.nombre }} - masa {{ paste.masa }}
        </li>
      </ul>
      <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700 mb-4">
        <div class="bg-orange-600 h-2.5 rounded-full transition-all duration-100"
          :style="{ width: `${(tiempoTranscurrido / tiempoTotal) * 100}%` }"></div>
          
      </div>
      <p>Tiempo restante: {{ labeltime }}</p>
    </div>
    <div v-else>
      
      <p class="text-lg mb-4">El horno está disponible</p>
      <button @click="iniciarHorneado" :disabled="!pastesPorHornear.length"
        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed">
        Iniciar Horneado de Grupo
      </button>
    </div>
  </div>

  <!-- Lista de Pastes por Hornear -->
  <div class="mt-8 bg-white shadow rounded-lg p-6">
    <h2 class="text-xl font-semibold mb-4">Grupos de Pastes por Hornear</h2>
    <div v-if="pastesPorHornear.length === 0" class="text-gray-500 text-center py-4">
      No hay grupos de pastes en la cola de horneado
    </div>
    <ul v-else class="divide-y divide-gray-200">
      <li v-for="paste in pastesPorHornear" :key="paste.id" class="py-4 flex justify-between items-center">
        <span>{{ paste.cantidad }} {{ paste.nombre }} - masa {{ paste.masa }}</span>
        <button @click="cancelarPaste(paste.id)"
          class="bg-red-600 text-white hover:bg-red-800 rounded-lg px-2 py-1 focus:outline-none focus:underline">
          Cancelar
        </button>
      </li>
    </ul>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import axios from 'axios';

const { props } = usePage();
const inventario = props.inventario;

// Inicialización del estado del horno
const horneando = ref(props?.horno?.estado || false);
const pastesHorneando = ref(
  horneando.value && props?.horno?.pastesHorneando
    ? props.horno.pastesHorneando
    : []
);
//tiempo horneado 15 min 15 * 60 * 10000
//90000
const tiempoTotal = ref(15 *  1000); 
const tiempoTranscurrido = ref(0);
const pastesPorHornear = ref([]);
const tiempoInicio = ref(horneando.value ? new Date(props?.horno?.tiempo_inicio).getTime() : 0);
const tiempoFin = ref(horneando.value ? new Date(props?.horno?.tiempo_fin).getTime() : 0);
let timer = null;

// Función para iniciar el horneado
const iniciarHorneado = () => {
  if (pastesPorHornear.value.length === 0 || horneando.value) return;

  horneando.value = true;
  pastesHorneando.value = [...pastesPorHornear.value];
  pastesPorHornear.value = [];

  tiempoInicio.value = Date.now();
  tiempoFin.value = tiempoInicio.value + tiempoTotal.value;

  try {

    axios.post('/iniciar-horneado', {
      tiempo_inicio: tiempoInicio.value,
      tiempo_fin: tiempoFin.value,
      pastes_horneando: pastesHorneando.value,
      estado: true,
    }).then(response => {
    }).catch(error => {
      console.error('Error al iniciar el horneado:', error);
    });
    
    Swal.fire({ icon: 'success', title: 'Horneando', toast: true, position: 'top-end', showConfirmButton: false, timer: 1500 });
    iniciarTemporizador();
  } catch (error) {
    console.error('Error al iniciar el horneado:', error);
    Swal.fire({ icon: 'error', title: 'Error al iniciar', toast: true, position: 'top-end', showConfirmButton: false, timer: 1500 });
  }
};

const Toast = Swal.mixin({
  toast: true,
  position: 'top-end',
  showConfirmButton: false,
  timer: 3000,
  timerProgressBar: true,
  didOpen: (toast) => {
    toast.addEventListener('mouseenter', Swal.stopTimer);
    toast.addEventListener('mouseleave', Swal.resumeTimer);
  },
});

// Función para finalizar el horneado
const finalizarHorneado = () => {

  if(checkEstado()) return;

  clearInterval(timer);

  const pastesFinalizados = [...pastesHorneando.value];
  horneando.value = false;
  pastesHorneando.value = [];
  tiempoTranscurrido.value = 0;

  reproducirSonido();

  router.post('/hornear', { pastes: pastesFinalizados },  {
    preserveScroll: true,
    preserveState: false,
    replace: true,
    onSuccess: () => {
      Toast.fire({ icon: 'success', title: 'Horneado finalizado' });
    },
    onError: (errors) => {
      console.error('Error al registrar el horneado:', errors);
      Toast.fire({ icon: 'error', title: 'Error al registrar el horneado' });
    },
  });
};

const checkEstado = () => {
  axios.post('/check-estado', { pastes: pastesHorneando.value })
  .then(response => {
    return response.data.estado;
  })
  .catch(error => {
    console.error('Error al obtener el estado del horno:', error);
    return false;
  });
}
// Función para reproducir sonido
const reproducirSonido = () => {
  const audio = new Audio('/sound/videoplayback.mp3');
  audio.play().catch(error => console.error("Error al reproducir el sonido:", error));
};

// Computed para masas y rellenos
const masas = computed(() => inventario.filter(item => item.tipo === 'masa'));
const rellenos = computed(() => inventario.filter(item => item.tipo === 'relleno'));

const masasActualizadas = computed(() => {
  return masas.value.map(masa => {
    const cantidadHorneando = pastesHorneando.value
      .filter(paste => paste.masa === masa.nombre)
      .reduce((acc, paste) => acc + paste.cantidad, 0);

    return {
      ...masa,
      cantidad: masa.cantidad - cantidadHorneando,
    };
  });
});

const rellenosActualizados = computed(() => {
  return rellenos.value.map(relleno => {
    const cantidadHorneando = pastesHorneando.value
      .filter(paste => paste.nombre === relleno.nombre)
      .reduce((acc, paste) => acc + paste.cantidad, 0);

    return {
      ...relleno,
      cantidad: relleno.cantidad - cantidadHorneando,
    };
  });
});

// Lógica para crear un nuevo paste
const nuevoPaste = ref({
  masa: '',
  relleno: '',
  cantidad: 1,
  sucursal_id: props.auth.user.sucursal_id,
});

const tipoDeMasa = (nuevoPaste) => {
  const validTypes = {
    pastes: 'bola',
    'empanadas saladas': 'salada',
    'empanadas dulces': 'dulce',
  };

  const masa = props.inventario.find((item) => {
    return (
      item.nombre.toLowerCase() === nuevoPaste.value.relleno.toLowerCase() ||
      item.nombre.toLowerCase().startsWith(nuevoPaste.value.relleno.toLowerCase())
    ) && validTypes[item.tipo.toLowerCase()];
  });

  return masa ? validTypes[masa.tipo.toLowerCase()] : 'bola';
};

const crearPaste = () => {
  const masaCorrespondiente = tipoDeMasa(nuevoPaste).toLowerCase();
  const masa = masasActualizadas.value.find(m => m.nombre.toLowerCase() === masaCorrespondiente);
  const relleno = rellenosActualizados.value.find(r => r.nombre.toLowerCase() === nuevoPaste.value.relleno.toLowerCase());

  if (!masa || masa.cantidad <= 0) {
    Toast.fire({ icon: 'error', title: 'No hay suficiente masa disponible' });
    return;
  }

  if (masa && relleno && nuevoPaste.value.cantidad <= maxCantidadDisponible.value) {
    masa.cantidad -= nuevoPaste.value.cantidad;
    relleno.cantidad -= nuevoPaste.value.cantidad;

    const grupoExistente = pastesPorHornear.value.find(p => p.nombre === relleno.nombre);
    if (grupoExistente) {
      grupoExistente.cantidad += nuevoPaste.value.cantidad;
    } else {
      agregarPaste({
        id: Date.now(),
        masa: masa.nombre,
        nombre: relleno.nombre,
        cantidad: nuevoPaste.value.cantidad,
        sucursal_id: props.auth.user.sucursal_id
      });
    }

    nuevoPaste.value = { masa: '', relleno: '', cantidad: 1, sucursal_id: props.auth.user.sucursal_id };
  }
};

const maxCantidadDisponible = computed(() => {
  const rellenoSeleccionado = rellenosActualizados.value.find(r => r.nombre === nuevoPaste.value.relleno);
  return rellenoSeleccionado ? rellenoSeleccionado.cantidad : 1;
});

const isFormValid = computed(() => {
  return nuevoPaste.value.relleno && nuevoPaste.value.cantidad > 0 && nuevoPaste.value.cantidad <= maxCantidadDisponible.value;
});

const labeltime = ref('00:00');

const formatearTiempo = (milisegundos) => {
  if (milisegundos <= 0) return "00:00";
  
  const totalSegundos = Math.floor(milisegundos / 1000);
  const minutos = Math.floor(totalSegundos / 60);
  const segundos = totalSegundos % 60;

  return `${String(minutos).padStart(2, '0')}:${String(segundos).padStart(2, '0')}`;
};

const agregarPaste = (producto) => {
  pastesPorHornear.value.push(producto);
};

const cancelarPaste = (id) => {
  pastesPorHornear.value = pastesPorHornear.value.filter(paste => paste.id !== id);
};

const iniciarTemporizador = () => {
  const tiempoFinMs = tiempoFin.value;

  // Calcular el tiempo restante
  const tiempoRestante = tiempoFinMs - Date.now();
  
  // Si el tiempo restante es menor o igual a 0, finalizar el horneado
  if (tiempoRestante <= 0) {
    finalizarHorneado();
    return;
  }

  // Calcular el tiempo transcurrido correctamente
  tiempoTranscurrido.value = tiempoTotal.value - tiempoRestante;

  // Iniciar el temporizador
  timer = setInterval(() => {
    const tiempoRestante = tiempoFinMs - Date.now();
    
    // Actualizar el tiempo transcurrido
    tiempoTranscurrido.value = tiempoTotal.value - tiempoRestante;
    
    labeltime.value = formatearTiempo(tiempoRestante);
    console.log("tiempo transcurrido: ", labeltime.value)
    // Si el tiempo restante es menor o igual a 0, finalizar el horneado
    if (tiempoRestante <= 0) {
      finalizarHorneado();
    }
  }, 1000);
};

onMounted(() => {
  clearInterval(timer)
  if (horneando.value) {
    // Recalcula el tiempoFin en función del tiempo actual
    const tiempoTranscurridoDesdeInicio = Date.now() - tiempoInicio.value;
    tiempoFin.value = Date.now() + (tiempoTotal.value - tiempoTranscurridoDesdeInicio);
    
    // Inicia el temporizador
    iniciarTemporizador();
  }

  
});

</script>