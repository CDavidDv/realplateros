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
          {{ isFormValid ? 'Asignar Unidades' : 'Sin ingredientes suficientes' }}
        </button>
        <div v-if="nuevoPaste.relleno && !isFormValid" class="text-sm text-red-600 mt-2">
          <span v-if="maxCantidadDisponible === 0">No hay ingredientes disponibles</span>
          <span v-else>Máximo disponible: {{ maxCantidadDisponible }} unidades</span>
        </div>
      </form>
    </div>
  </div>

  <!-- Sección de Horneado -->
  <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-4">
    <div v-for="(horno, index) in hornosActivos" :key="horno.id" class="bg-white shadow rounded-lg p-6">
      <div class="flex justify-between">
        <h2 class="text-xl font-semibold mb-4">Horno {{ index + 1 }}</h2>
        <button @click="deleteHorno(horno.id)" v-if="!horno.horneando && hornos.length > 1" 
          class="flex items-center gap-2 bg-red-600 text-white hover:bg-red-800 rounded-lg px-2 py-1 focus:outline-none focus:underline">

          <Trash2 class="w-4 h-4" />
        </button>
      </div>

      <div v-if="horno.horneando">
        <p class="text-lg mb-2">Horneando grupo de pastes:</p>
        <ul>
          <li v-for="paste in horno.pastesHorneando" :key="paste.nombre">
            {{ paste.cantidad }} {{ paste.nombre }} - masa {{ paste.masa }}
          </li>
        </ul>
        <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700 mb-4">
          <div class="bg-orange-600 h-2.5 rounded-full transition-all duration-100"
            :style="{ width: `${(horno.tiempoTranscurrido / tiempoTotal) * 100}%` }"></div>
        </div>
        <p>Tiempo restante: {{ horno.labeltime }}</p>
      </div>
      <div v-else>
        <p class="text-lg mb-4">El horno está disponible</p>
        <button @click="iniciarHorneado" :disabled="!pastesPorHornear.length"
          class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed">
          Iniciar Horneado de Grupo
        </button>
      </div>
    </div>
    <!-- Botón para crear nuevo horno solo cuando todos estén ocupados -->
    <div class="mt-4 h-full flex justify-center items-center">
      <button @click="crearHorno" 
        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
        Crear Nuevo Horno
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
import { Trash, Trash2 } from 'lucide-vue-next';

const { props } = usePage();
const inventario = props.inventario || [];
const hornos = ref(props.hornos || []);

// Inicialización del estado de los hornos
const hornosActivos = ref(hornos.value.map(horno => ({
  ...horno,
  horneando: horno.estado || false,
  pastesHorneando: horno.pastesHorneando || [],
  tiempoInicio: horno.tiempo_inicio ? new Date(horno.tiempo_inicio).getTime() : 0,
  tiempoFin: horno.tiempo_fin ? new Date(horno.tiempo_fin).getTime() : 0,
  tiempoTranscurrido: 0,
  timer: null,
  labeltime: '00:00'
})));

//tiempo horneado 15 min 15 * 60 * 10000
//900000 == 15 min
const tiempoTotal = ref(900000); 
const pastesPorHornear = ref([]);

// Función para iniciar el horneado
const iniciarHorneado = () => {
  if (pastesPorHornear.value.length === 0) return;

  // Buscar un horno disponible
  const hornoDisponible = hornosActivos.value.find(h => !h.horneando);
  

  if (hornoDisponible) {
    // Usar el horno disponible
    hornoDisponible.horneando = true;
    hornoDisponible.pastesHorneando = [...pastesPorHornear.value];
    pastesPorHornear.value = [];

    hornoDisponible.tiempoInicio = Date.now();
    hornoDisponible.tiempoFin = hornoDisponible.tiempoInicio + tiempoTotal.value;

    try {
      axios.post('/iniciar-horneado', {
        horno_id: hornoDisponible.id,
        tiempo_inicio: hornoDisponible.tiempoInicio,
        tiempo_fin: hornoDisponible.tiempoFin,
        pastes_horneando: hornoDisponible.pastesHorneando,
        estado: true,
      }).then(response => {
        console.log(response)
      }).catch(error => {
        console.error('Error al iniciar el horneado:', error);
      });
      
      Swal.fire({ icon: 'success', title: 'Horneando', toast: true, position: 'top-end', showConfirmButton: false, timer: 1500 });
      iniciarTemporizador(hornoDisponible.id);
    } catch (error) {
      console.error('Error al iniciar el horneado:', error);
      Swal.fire({ icon: 'error', title: 'Error al iniciar', toast: true, position: 'top-end', showConfirmButton: false, timer: 1500 });
    }
  } else {
    // Si no hay hornos disponibles, crear uno nuevo
    crearHorno();
  }
};

const Toast = Swal.mixin({
  toast: true,
  position: 'top-end',
  showConfirmButton: false,
  timer: 1500,
  timerProgressBar: true,
  didOpen: (toast) => {
    toast.addEventListener('mouseenter', Swal.stopTimer);
    toast.addEventListener('mouseleave', Swal.resumeTimer);
  },
});

// Función para finalizar el horneado
const finalizarHorneado = async (hornoId) => {
  
  const horno = hornosActivos.value.find(h => h.id === hornoId);
  if (!horno || !horno.horneando) {
    return;
  }

  try {
    // Limpiar el timer inmediatamente para evitar que siga ejecutándose
    clearInterval(horno.timer);
    
    const pastesFinalizados = [...horno.pastesHorneando];
    
    // Actualizar el estado del horno inmediatamente
    horno.horneando = false;
    horno.pastesHorneando = [];
    horno.tiempoTranscurrido = 0;
    horno.labeltime = '00:00';

    reproducirSonido();

    // Registrar en control de producción
    for (const paste of pastesFinalizados) {
      // Buscar el ID del paste en el inventario
      const pasteEnInventario = inventario.find(item => 
        item.nombre.toLowerCase() === paste.nombre.toLowerCase() && 
        (item.tipo === 'pastes' || item.tipo === 'empanadas saladas' || item.tipo === 'empanadas dulces')
      );

      if (pasteEnInventario) {
        try {
          await axios.post('/api/control-produccion/horneado', {
            horno_id: hornoId,
            paste_id: pasteEnInventario.id,
            cantidad: paste.cantidad
          });
        } catch (error) {
          console.error(`Horno ${hornoId} - Error al registrar producción para ${paste.nombre}:`, error);
        }
      } else {
        console.error(`Horno ${hornoId} - No se encontró el paste ${paste.nombre} en el inventario`);
      }
    }

    // Actualizar el estado del horno y registrar horneado
    router.post('/hornear', { 
      horno_id: hornoId,
      pastes: pastesFinalizados 
    }, {
      preserveScroll: true,
      preserveState: false,
      replace: true,
      onSuccess: () => {
        Toast.fire({ icon: 'success', title: 'Horneado finalizado' });
      },
      onError: (errors) => {
        console.error(`Horno ${hornoId} - Error al registrar el horneado:`, errors);
        Toast.fire({ icon: 'error', title: 'Error al registrar el horneado' });
      },
    });
    
  } catch (error) {
    console.error(`Horno ${hornoId} - Error en finalizarHorneado:`, error);
  }
};




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
    const cantidadHorneando = hornosActivos.value
      .filter(horno => horno.horneando && horno.pastesHorneando.some(paste => paste.masa === masa.nombre))
      .reduce((acc, horno) => acc + horno.pastesHorneando.find(paste => paste.masa === masa.nombre).cantidad, 0);

    return {
      ...masa,
      cantidad: Math.max(0, masa.cantidad - cantidadHorneando), // Evitar cantidades negativas
    };
  });
});

const rellenosActualizados = computed(() => {
  return rellenos.value.map(relleno => {
    const cantidadHorneando = hornosActivos.value
      .filter(horno => horno.horneando && horno.pastesHorneando.some(paste => paste.nombre === relleno.nombre))
      .reduce((acc, horno) => acc + horno.pastesHorneando.find(paste => paste.nombre === relleno.nombre).cantidad, 0);

    return {
      ...relleno,
      cantidad: Math.max(0, relleno.cantidad - cantidadHorneando), // Evitar cantidades negativas
    };
  });
});

// Lógica para crear un nuevo paste
const nuevoPaste = ref({
  masa: '',
  relleno: '',
  cantidad: 1,
  sucursal_id: props.auth?.user?.sucursal_id,
});

const tipoDeMasa = (nuevoPaste) => {
  const validTypes = {
    pastes: 'bola',
    'empanadas saladas': 'salada',
    'empanadas dulces': 'dulce',
  };

  const masa = inventario.find((item) => {
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

  // Validar que exista masa y tenga cantidad suficiente
  if (!masa || masa.cantidad <= 0) {
    Toast.fire({ icon: 'error', title: 'No hay suficiente masa disponible' });
    return;
  }

  // Validar que exista relleno y tenga cantidad suficiente
  if (!relleno || relleno.cantidad <= 0) {
    Toast.fire({ icon: 'error', title: 'No hay suficiente relleno disponible' });
    return;
  }

  // Validar que la cantidad solicitada no exceda lo disponible
  const cantidadDisponible = Math.min(masa.cantidad, relleno.cantidad);
  if (nuevoPaste.value.cantidad > cantidadDisponible) {
    Toast.fire({ 
      icon: 'error', 
      title: `Solo hay ${cantidadDisponible} unidades disponibles (${masa.cantidad} masa, ${relleno.cantidad} relleno)` 
    });
    return;
  }

  // Si todas las validaciones pasan, crear el paste
  if (masa && relleno && nuevoPaste.value.cantidad <= cantidadDisponible) {
    // Actualizar cantidades disponibles
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
        sucursal_id: props.auth?.user?.sucursal_id
      });
    }

    nuevoPaste.value = { masa: '', relleno: '', cantidad: 1, sucursal_id: props.auth?.user?.sucursal_id };
  }
};

const maxCantidadDisponible = computed(() => {
  const masaCorrespondiente = tipoDeMasa(nuevoPaste).toLowerCase();
  const masa = masasActualizadas.value.find(m => m.nombre.toLowerCase() === masaCorrespondiente);
  const rellenoSeleccionado = rellenosActualizados.value.find(r => r.nombre === nuevoPaste.value.relleno);
  
  if (!masa || !rellenoSeleccionado) return 0;
  
  // Retornar el mínimo entre masa y relleno disponible
  return Math.min(masa.cantidad, rellenoSeleccionado.cantidad);
});

const isFormValid = computed(() => {
  const masaCorrespondiente = tipoDeMasa(nuevoPaste).toLowerCase();
  const masa = masasActualizadas.value.find(m => m.nombre.toLowerCase() === masaCorrespondiente);
  const relleno = rellenosActualizados.value.find(r => r.nombre === nuevoPaste.value.relleno);
  
  if (!masa || !relleno) return false;
  
  const cantidadDisponible = Math.min(masa.cantidad, relleno.cantidad);
  
  return nuevoPaste.value.relleno && 
         nuevoPaste.value.cantidad > 0 && 
         nuevoPaste.value.cantidad <= cantidadDisponible &&
         cantidadDisponible > 0;
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
  // Buscar el paste en el inventario para obtener su ID
  const pasteEnInventario = inventario.find(item => 
    item.nombre.toLowerCase() === producto.nombre.toLowerCase() && 
    (item.tipo === 'pastes' || item.tipo === 'empanadas saladas' || item.tipo === 'empanadas dulces')
  );

  if (pasteEnInventario) {
    pastesPorHornear.value.push({
      ...producto,
      paste_id: pasteEnInventario.id // Agregar el ID del inventario
    });
  } else {
    console.error(`No se encontró el paste ${producto.nombre} en el inventario`);
  }
};

const cancelarPaste = (id) => {
  pastesPorHornear.value = pastesPorHornear.value.filter(paste => paste.id !== id);
};

// Función auxiliar para finalizar horneado desde temporizadores
const finalizarHorneadoDesdeTimer = (hornoId) => {
  finalizarHorneado(hornoId).catch(error => {
    console.error(`Error al finalizar horneado desde timer: ${error}`);
  });
};

const finalizarContinueTimer = async (hornoId) => {
  const horno = hornosActivos.value.find(h => h.id === hornoId);
  if (!horno) return;

  clearInterval(horno.timer);

  horno.horneando = false;
  horno.pastesHorneando = [];
  horno.tiempoTranscurrido = 0;

  reproducirSonido();
  Toast.fire({ icon: 'success', title: 'Horneado finalizado' });
};

const iniciarTemporizador = (hornoId) => {
  const horno = hornosActivos.value.find(h => h.id === hornoId);
  if (!horno) return;

  const tiempoFinMs = horno.tiempoFin;
  const tiempoRestante = tiempoFinMs - Date.now();
  
  
  if (tiempoRestante <= 0) {
    finalizarHorneadoDesdeTimer(hornoId);
    return;
  }

  horno.tiempoTranscurrido = tiempoTotal.value - tiempoRestante;
  horno.labeltime = formatearTiempo(tiempoRestante);

  if (horno.timer) {
    clearInterval(horno.timer);
  }

  horno.timer = setInterval(() => {
    const tiempoRestante = horno.tiempoFin - Date.now();
    
    
    if (tiempoRestante <= 0) {
      
      clearInterval(horno.timer);
      finalizarHorneadoDesdeTimer(hornoId);
      return;
    }

    horno.tiempoTranscurrido = tiempoTotal.value - tiempoRestante;
    horno.labeltime = formatearTiempo(tiempoRestante);
  }, 1000);
};

const continuarTemporizador = (hornoId) => {
  const horno = hornosActivos.value.find(h => h.id === hornoId);
  if (!horno) return;

  const tiempoFinMs = horno.tiempoFin;
  const tiempoRestante = tiempoFinMs - Date.now();
  
  // Si el tiempo ya pasó, finalizar inmediatamente
  if (tiempoRestante <= 0) {
    finalizarHorneadoDesdeTimer(hornoId);
    return;
  }
    
  horno.tiempoTranscurrido = tiempoTotal.value - tiempoRestante;
  horno.labeltime = formatearTiempo(tiempoRestante);

  // Limpiar el temporizador existente si hay uno
  if (horno.timer) {
    clearInterval(horno.timer);
  }

  horno.timer = setInterval(() => {
    const tiempoRestante = horno.tiempoFin - Date.now();
    
    // Si el tiempo ya pasó, finalizar inmediatamente
    if (tiempoRestante <= 0) {
      clearInterval(horno.timer);
      finalizarHorneadoDesdeTimer(hornoId);
      return;
    }

    horno.tiempoTranscurrido = tiempoTotal.value - tiempoRestante;
    horno.labeltime = formatearTiempo(tiempoRestante);
  }, 1000);
};

onMounted(() => {
  //limpiar todos los timers
  hornosActivos.value.forEach(horno => {
    if (horno.timer) {
      clearInterval(horno.timer);
    }
  });

  hornosActivos.value.forEach(horno => {
    if (horno.estado) {
    

      const tiempoTranscurridoDesdeInicio = Date.now() - new Date(horno.tiempo_inicio).getTime();
      const tiempoRestante = tiempoTotal.value - tiempoTranscurridoDesdeInicio;
      
      if (tiempoRestante <= 0) {
        finalizarHorneadoDesdeTimer(horno.id);
        return;
      }

      horno.tiempoFin = Date.now() + tiempoRestante;
      continuarTemporizador(horno.id);
    }
  });
});

/*
onUnmounted(() => {
  console.log('Componente desmontado - Limpiando timers');
  hornosActivos.value.forEach(horno => {
    if (horno.timer) {
      clearInterval(horno.timer);
      console.log(`Horno ${horno.id} - Timer limpiado en onUnmounted`);
    }
  });
});
*/
const crearHorno = () => {
  router.post('/crear-horno', {
    estado: false,
    pastesHorneando: [],
  }, {
    preserverScroll: true,
    preserveState: false,
    onSuccess: (response) => {

      hornos.value = response?.props?.hornos;
      pastesPorHornear.value = [];

      hornosActivos.value = hornos.value.map(horno => ({
        ...horno,
        horneando: horno.estado || false,
        pastesHorneando: horno.pastesHorneando || [],
        tiempoInicio: horno.tiempo_inicio ? new Date(horno.tiempo_inicio).getTime() : 0,
        tiempoFin: horno.tiempo_fin ? new Date(horno.tiempo_fin).getTime() : 0,
        timer: null,
        labeltime: '00:00'
      }));

      Swal.fire({ icon: 'success', title: 'Horno creado', toast: true, position: 'top-end', showConfirmButton: false, timer: 1500 });
    },
    onError: (errors) => {
      console.error('Error al crear el horno:', errors);
      Swal.fire({ icon: 'error', title: 'Error al crear horno', toast: true, position: 'top-end', showConfirmButton: false, timer: 1500 });
    }
  });
};

const deleteHorno = (id) => {
  router.post('/eliminar-horno', {
    horno_id: id
  }, {
    preserveScroll: true,
    preserveState: false,
    onSuccess: (response) => {
      hornos.value = response?.props?.hornos;
      hornosActivos.value = hornos.value.map(horno => ({
        ...horno,
        horneando: horno.estado || false,
        pastesHorneando: horno.pastesHorneando || [],
        tiempoInicio: horno.tiempo_inicio ? new Date(horno.tiempo_inicio).getTime() : 0,
        tiempoFin: horno.tiempo_fin ? new Date(horno.tiempo_fin).getTime() : 0,
        timer: null,
        labeltime: '00:00'
      }));
      
      Swal.fire({ icon: 'success', title: 'Horno eliminado', toast: true, position: 'top-end', showConfirmButton: false, timer: 1500 });
    },
    onError: (errors) => {
      console.error('Error al eliminar el horno:', errors);
      Swal.fire({ icon: 'error', title: 'Error al eliminar horno', toast: true, position: 'top-end', showConfirmButton: false, timer: 1500 });
    }
  });
};

</script>