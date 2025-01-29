<template>
    <div class="card  grid gap-2" v-if="contratos.length > 0">
        <span class="flex items-center gap-2">
        <AlertCircle :size="32" 
            :class="[ 'animate-pulse', color === 'red' ? 'text-red-500' :  'text-yellow-500']" />
        <h2 class="font-bold text-xl ">{{ title }}</h2>
        </span>

        <table class="table-auto w-full border ">
        <thead class="bg-gray-200">
            <tr class="text-left">
            <th>Nombre</th>
            <th>Apellido Paterno</th>
            <th>Apellido Materno</th>
            <th>Inicio del Contrato</th>
            <th>Fin del Contrato</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="contrato in contratos" :key="contrato.id">
            <td>{{ contrato.name }}</td>
            <td>{{ contrato.apellido_p }}</td>
            <td>{{ contrato.apellido_m }}</td>
            <td>{{ formatearFecha(contrato.inicio_contrato) }}</td>
            <td>{{ formatearFecha(contrato.fin_contrato) }}</td>
            </tr>
        </tbody>
        </table>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

import { AlertCircle } from 'lucide-vue-next';

defineProps({
    contratos: Array,
    title: String,
    color: String,
});

const formatearFecha = (fecha) => {
    if (!fecha) return "N/A";
    return new Date(fecha).toLocaleDateString();
  }

</script>