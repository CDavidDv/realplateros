<template>
  <div class="bg-white shadow rounded-lg p-4 mb-4">
    <!-- Encabezado compacto -->
    <div class="flex items-center justify-between cursor-pointer" @click="expandido = !expandido">
      <div class="flex items-center space-x-3">
        <!-- Icono de alerta si hay pendientes -->
        <div v-if="contadores.pendiente.cantidad > 0" class="flex-shrink-0">
          <svg class="h-6 w-6 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
        </div>
        <div v-else class="flex-shrink-0">
          <svg class="h-6 w-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>

        <!-- Texto del contador -->
        <div>
          <h3 class="text-lg font-semibold text-gray-900">
            Estado de Produccion
          </h3>
          <p class="text-sm text-gray-600">
            <span v-if="contadores.pendiente.cantidad > 0" class="font-medium text-yellow-700">
              {{ contadores.pendiente.cantidad }} notificacion(es) pendiente(s) de hornear
            </span>
            <span v-else class="font-medium text-green-700">
              Sin notificaciones pendientes
            </span>
          </p>
        </div>
      </div>

      <!-- Boton expandir/colapsar -->
      <button class="text-gray-400 hover:text-gray-600 transition-transform duration-200"
              :class="{ 'rotate-180': expandido }">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
      </button>
    </div>

    <!-- Contenido expandible -->
    <transition
      enter-active-class="transition ease-out duration-200"
      enter-from-class="opacity-0 -translate-y-2"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition ease-in duration-150"
      leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 -translate-y-2"
    >
      <div v-show="expandido" class="mt-4 pt-4 border-t border-gray-200">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <!-- Card Pendientes -->
          <div class="bg-red-50 rounded-lg p-3 border border-red-200">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-xs text-red-600 font-medium uppercase">Sin hornear</p>
                <p class="text-2xl font-bold text-red-700">
                  {{ contadores.pendiente.cantidad }}
                </p>
                <p class="text-xs text-red-600 mt-1">
                  {{ contadores.pendiente.total_unidades }} unidades
                </p>
              </div>
              <div class="bg-red-100 rounded-full p-2">
                <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
            </div>
          </div>

          <!-- Card Horneando -->
          <div class="bg-yellow-50 rounded-lg p-3 border border-yellow-200">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-xs text-yellow-600 font-medium uppercase">Horneando</p>
                <p class="text-2xl font-bold text-yellow-700">
                  {{ contadores.horneando.cantidad }}
                </p>
                <p class="text-xs text-yellow-600 mt-1">
                  {{ contadores.horneando.total_unidades }} unidades
                </p>
              </div>
              <div class="bg-yellow-100 rounded-full p-2">
                <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z" />
                </svg>
              </div>
            </div>
          </div>

          <!-- Card En Espera -->
          <div class="bg-blue-50 rounded-lg p-3 border border-blue-200">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-xs text-blue-600 font-medium uppercase">En espera</p>
                <p class="text-2xl font-bold text-blue-700">
                  {{ contadores.en_espera.cantidad }}
                </p>
                <p class="text-xs text-blue-600 mt-1">
                  {{ contadores.en_espera.total_unidades }} unidades
                </p>
              </div>
              <div class="bg-blue-100 rounded-full p-2">
                <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
            </div>
          </div>
        </div>

        <!-- Mensaje de ayuda -->
        <div class="mt-3 text-xs text-gray-500 text-center">
          Para ver detalles completos, visita la seccion de Hornear
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
  contadores: {
    type: Object,
    required: true,
    default: () => ({
      pendiente: { cantidad: 0, total_unidades: 0 },
      horneando: { cantidad: 0, total_unidades: 0 },
      en_espera: { cantidad: 0, total_unidades: 0 }
    })
  }
});

const expandido = ref(false);
</script>
