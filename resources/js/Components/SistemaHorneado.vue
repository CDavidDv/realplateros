<template>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Sección de Masas y Rellenos -->
        <div class="bg-white shadow rounded-lg p-6">
          <h2 class="text-xl font-semibold mb-4">Ingredientes Disponibles</h2>

          <div class="mb-6">
            <h3 class="text-lg font-medium mb-2">Masas</h3>
            <ul class="list-disc pl-5">
              <li v-for="masa in masasActualizadas" :key="masa.id" class="flex justify-between">
                <span class=" capitalize">{{ masa.nombre }}</span>
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
                <option v-for="relleno in rellenos" :key="relleno.id" :value="relleno.nombre">{{ relleno.nombre }}
                </option>
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
        <div v-if="timerStore.horneando">
          <p class="text-lg mb-2">
            Horneando grupo de pastes:
          </p>
          <ul>
            <li v-for="paste in timerStore.pastesHorneando" :key="paste.nombre">
              {{ paste.cantidad }} {{ paste.nombre }} - masa {{ paste.masa }}
            </li>
          </ul>
          <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700 mb-4">
            <div class="bg-orange-600 h-2.5 rounded-full transition-all duration-100"
              :style="{ width: `${(timerStore.tiempoTranscurrido / timerStore.tiempoTotal) * 100}%` }"></div>
          </div>
          <p>Tiempo restante: {{ formatearTiempo(timerStore.tiempoRestante) }}</p>

        </div>
        <div v-else>
          <p class="text-lg mb-4">El horno está disponible</p>
          <button @click="timerStore.iniciarHorneado" :disabled="!timerStore.pastesPorHornear.length"
            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed">
            Iniciar Horneado de Grupo
          </button>
        </div>
      </div>

      <!-- Lista de Pastes por Hornear -->
      <div class="mt-8 bg-white shadow rounded-lg p-6">
        <h2 class="text-xl font-semibold mb-4">Grupos de Pastes por Hornear</h2>
        <div v-if="timerStore.pastesPorHornear.length === 0" class="text-gray-500 text-center py-4">
          No hay grupos de pastes en la cola de horneado
        </div>
        <ul v-else class="divide-y divide-gray-200">
          <li v-for="paste in timerStore.pastesPorHornear" :key="paste.id" class="py-4 flex justify-between items-center">
            <span>{{ paste.cantidad }} {{ paste.nombre }} - masa {{ paste.masa }}</span>
            <button @click="timerStore.cancelarPaste(paste.id)"
              class="bg-red-600 text-white hover:bg-red-800 rounded-lg px-2 py-1 focus:outline-none focus:underline">
              Cancelar
            </button>
          </li>
        </ul>
      </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useTimerStore } from '@/stores/useTimerStore';
import { usePage } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

const timerStore = useTimerStore();
const { props } = usePage();
const inventario = props.inventario;

const masas = computed(() => inventario.filter(item => item.tipo === 'masa'));
const rellenos = computed(() => inventario.filter(item => item.tipo === 'relleno'));

const masasActualizadas = computed(() => {
  return masas.value.map(masa => {
    const cantidadHorneando = timerStore.pastesHorneando
      .filter(paste => paste.masa === masa.nombre)
      .reduce((acc, paste) => acc + paste.cantidad, 0);

    return {
      ...masa,
      cantidad: masa.cantidad - cantidadHorneando
    };
  });
});

const rellenosActualizados = computed(() => {
  return rellenos.value.map(relleno => {
    const cantidadHorneando = timerStore.pastesHorneando
      .filter(paste => paste.nombre === relleno.nombre)
      .reduce((acc, paste) => acc + paste.cantidad, 0);

    return {
      ...relleno,
      cantidad: relleno.cantidad - cantidadHorneando
    };
  });
});


const nuevoPaste = ref({
  masa: '',
  relleno: '',
  cantidad: 1
});


const determinarMasaPorRelleno = (nombreRelleno) => {
  
  const masaPorRelleno = {
    'Papa con carne': 'bola', //paste
    'Papa con Carne': 'bola',
    'Crema con pollo': 'bola',  //paste
    'Minero Papa con Pollo': 'bola',  //paste
    'Minero papa con pollo': 'bola',  //paste
    'Crema con Pollo': 'bola',  //paste
    'Frijol con Chorizo': 'bola',
    'Frijol con chorizo': 'bola', //paste
    'Mole rojo': 'salada', //empanada salada
    'Mole Rojo con Pollo': 'salada', //empanada salada
    'Mole verde': 'salada', //empanada salada
    'Mole verde con Pollo': 'salada', //empanada salada
    'Papa con Atún': 'salada', //empanada salada
    'Papa con atún': 'salada', //empanada salada
    'Salchicha': 'salada', //empanada salada
    'Tinga': 'salada', //empanada salada
    'Minero': 'salada', //empanada salada
    'Atún': 'salada', //empanada salada
    'Atun': 'salada', //empanada salada
    'Choriqueso': 'salada', //empanada salada
    'Chorizo con Queso': 'salada', //empanada salada
    'Chorizo con queso': 'salada', //empanada salada
    'Rajas con champiñones': 'salada', //empanada salada
    'Rajas': 'salada', //empanada salada
    'Rajas con Queso': 'salada', //empanada salada
    'Salchicha': 'salada', //empanada salada
    'Salchicha con Queso': 'salada', //empanada salada
    'Jamón con Queso': 'dulce', //empanada salada
    'Hawaiano': 'dulce', //empanada dulce
    'Arroz con leche': 'dulce', //empanada dulce
    'Arroz': 'dulce', //empanada dulce
    'Piña': 'dulce', //empanada dulce
    'Manzana': 'dulce', //empanada dulce
    'Zarzamora': 'dulce', //empanada dulce
    'Cajeta': 'dulce', //empanada dulce
    'Fresa': 'dulce', //empanada dulce
    'Fresa con queso': 'dulce', //empanada dulce
    'Fresa con Queso': 'dulce', //empanada dulce
    'Budin': 'dulce', //empanada dulce
    'Budín': 'dulce', //empanada dulce    
  };
  return masaPorRelleno[nombreRelleno] || '';
};

const tipoDeMasa = (nuevoPaste) => {
  const validTypes = { pastes: 'bola', 'empanadas saladas': 'salada', 'empanadas dulces': 'dulce' };

  const masa = props.inventario.find(
    item => 
      item?.nombre?.toLowerCase() === nuevoPaste?.relleno?.toLowerCase() && 
      validTypes[item?.tipo]
  );
  const tipoFinal = masa ? validTypes[masa.tipo] : 'Tipo no válido';
};


// Agrupar pastes por tipo de relleno
const crearPaste = () => {
  const masaCorrespondiente = tipoDeMasa(nuevoPaste);
  const masa = masasActualizadas.value.find(m => m.nombre === masaCorrespondiente);
  const relleno = rellenosActualizados.value.find(r => r.nombre === nuevoPaste.value.relleno);
  tipoDeMasa(nuevoPaste.value)
  // Validación para evitar crear si no hay masa disponible
  if (!masa || masa.cantidad <= 0) {
    const Toast = Swal.mixin({
      toast: true,
      position: "top-end",
      showConfirmButton: false,
      timer: 1500,
      timerProgressBar: true,
      didOpen: (toast) => {
        toast.onmouseenter = Swal.stopTimer;
        toast.onmouseleave = Swal.resumeTimer;
      }
    });
    Toast.fire({
      icon: "error",
      title: "No hay masa suficiente"
    });
    return;
  }

  if (masa && relleno && nuevoPaste.value.cantidad <= maxCantidadDisponible.value) {
    masa.cantidad -= nuevoPaste.value.cantidad;
    relleno.cantidad -= nuevoPaste.value.cantidad;

    const grupoExistente = timerStore.pastesPorHornear.find(p => p.nombre === relleno.nombre);
    if (grupoExistente) {
      grupoExistente.cantidad += nuevoPaste.value.cantidad;
    } else {
      timerStore.agregarPaste({
        id: Date.now(),
        masa: masa.nombre,
        nombre: relleno.nombre,
        cantidad: nuevoPaste.value.cantidad
      });
    }

    nuevoPaste.value = { masa: '', relleno: '', cantidad: 1 };
  }
};

const maxCantidadDisponible = computed(() => {
  const rellenoSeleccionado = rellenosActualizados.value.find(r => r.nombre === nuevoPaste.value.relleno);
  return rellenoSeleccionado ? rellenoSeleccionado.cantidad : 1;
});

const isFormValid = computed(() => {
  return nuevoPaste.value.relleno && nuevoPaste.value.cantidad > 0 && nuevoPaste.value.cantidad <= maxCantidadDisponible.value;
});


const formatearTiempo = (milisegundos) => {
  const totalSegundos = Math.ceil(milisegundos / 1000); // Convertir a segundos
  const minutos = Math.floor(totalSegundos / 60); // Obtener los minutos
  const segundos = totalSegundos % 60; // Obtener los segundos restantes
  return `${minutos}:${segundos.toString().padStart(2, '0')}`; // Asegurarse de que los segundos siempre tengan dos dígitos
};

</script>