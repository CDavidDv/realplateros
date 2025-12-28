<template>
  <div class="bg-white shadow rounded-lg p-4 mb-4">
    <!-- Encabezado compacto -->
    <div class="flex items-center justify-between cursor-pointer" @click="expandido = !expandido">
      <div class="flex items-center space-x-3">
        <!-- Icono según estado -->
        <div v-if="contadoresActualizados.total_en_hornos > 0" class="flex-shrink-0">
          <svg class="h-6 w-6 text-orange-500 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z" />
          </svg>
        </div>
        <div v-else class="flex-shrink-0">
          <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>

        <!-- Texto del contador -->
        <div>
          <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
            Hornos en Produccion
            <!-- Indicador de actualización -->
            <span v-if="actualizando" class="inline-block">
              <svg class="animate-spin h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
            </span>
          </h3>
          <p class="text-sm text-gray-600">
            <span v-if="contadoresActualizados.total_en_hornos > 0" class="font-medium text-orange-700">
              {{ contadoresActualizados.total_en_hornos }} paste(s) en {{ contadoresActualizados.cantidad_hornos }} horno(s)
            </span>
            <span v-else class="font-medium text-gray-600">
              No hay hornos activos
            </span>
          </p>
        </div>
      </div>

      <!-- Boton expandir/colapsar -->
      <button
        v-if="contadoresActualizados.cantidad_hornos > 0"
        class="text-gray-400 hover:text-gray-600 transition-transform duration-200"
        :class="{ 'rotate-180': expandido }"
      >
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
      </button>
    </div>

    <!-- Contenido expandible: Desglose de hornos -->
    <transition
      enter-active-class="transition ease-out duration-200"
      enter-from-class="opacity-0 -translate-y-2"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition ease-in duration-150"
      leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 -translate-y-2"
    >
      <div v-show="expandido && contadoresActualizados.hornos_activos && contadoresActualizados.hornos_activos.length > 0" class="mt-4 pt-4 border-t border-gray-200">
        <div class="space-y-3">
          <div
            v-for="horno in contadoresActualizados.hornos_activos"
            :key="horno.horno_id"
            class="bg-orange-50 rounded-lg p-4 border border-orange-200"
          >
            <!-- Header del horno -->
            <div class="flex items-center justify-between mb-3">
              <div class="flex items-center gap-2">
                <svg class="h-5 w-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z" />
                </svg>
                <span class="text-sm font-bold text-orange-700">Horno #{{ horno.horno_id }}</span>
                <span class="text-xs bg-orange-100 text-orange-700 px-2 py-1 rounded-full font-semibold">
                  {{ horno.total_pastes }} pastes
                </span>
              </div>
              <div v-if="horno.tiempo_fin" class="text-xs text-orange-600 font-medium">
                Termina: {{ formatearTiempo(horno.tiempo_fin) }}
              </div>
            </div>

            <!-- Lista de pastes en este horno -->
            <div class="space-y-2">
              <div
                v-for="paste in horno.pastes"
                :key="paste.id"
                class="flex items-center justify-between text-sm bg-white px-3 py-2 rounded border border-orange-100"
              >
                <div class="flex items-center gap-2">
                  <span class="w-2 h-2 bg-orange-500 rounded-full"></span>
                  <span class="font-medium text-gray-800">{{ paste.nombre }}</span>
                  <span class="text-xs text-gray-500">({{ paste.masa }})</span>
                </div>
                <span class="font-bold text-orange-700">{{ paste.cantidad }} unid.</span>
              </div>
            </div>
          </div>
        </div>

      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';

const props = defineProps({
  contadores: {
    type: Object,
    required: true,
    default: () => ({
      hornos_activos: [],
      total_en_hornos: 0,
      cantidad_hornos: 0
    })
  }
});

const expandido = ref(false);
const contadoresActualizados = ref({ ...props.contadores });
const actualizando = ref(false);
let intervalo = null;

// Función para obtener los contadores actualizados
const actualizarContadores = async () => {
  try {
    actualizando.value = true;
    const response = await axios.get('/api/contador-estados');
    contadoresActualizados.value = response.data;
  } catch (error) {
    console.error('Error al actualizar contadores:', error);
  } finally {
    actualizando.value = false;
  }
};

// Función para formatear tiempo restante
const formatearTiempo = (tiempo) => {
  if (!tiempo) return 'N/A';

  try {
    const fecha = new Date(tiempo);
    const ahora = new Date();
    const diff = fecha - ahora;

    if (diff < 0) {
      return 'Terminado ✓';
    }

    const minutos = Math.floor(diff / 60000);
    const horas = Math.floor(minutos / 60);
    const mins = minutos % 60;

    if (horas > 0) {
      return `${horas}h ${mins}m`;
    }
    return `${mins}m`;
  } catch (e) {
    return 'N/A';
  }
};

// Iniciar polling cuando el componente se monta
onMounted(() => {
  // Actualizar cada 30 segundos (30000 ms)
  intervalo = setInterval(actualizarContadores, 15000);
});

// Limpiar el intervalo cuando el componente se desmonta
onUnmounted(() => {
  if (intervalo) {
    clearInterval(intervalo);
  }
});
</script>
