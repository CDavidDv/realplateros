<template>
    <div class="bg-white p-4 sm:p-10 rounded-xl shadow-xl mb-6">
        <div class="mb-4 border-b pb-2">
            <h1 class="text-xl font-bold text-gray-800">Sobrantes</h1>
        </div>

        <!-- Sobrantes agrupados por corte y tipo -->
        <div v-for="(corteId, index) in Object.keys(sobrantesPorCorte).sort()" :key="corteId" class="mb-6">
            <div class="mb-4 w-full bg-slate-300 p-4 rounded-lg text-center">
                <h2 class="text-2xl font-semibold text-gray-700">Corte #{{ index + 1 }}</h2>
            </div>

            <!-- Tabla para cada tipo de producto -->
            <div v-for="(sobrantes, tipo) in sobrantesPorCorte[corteId]" :key="tipo" class="overflow-x-auto mb-4">
                <table class="min-w-full bg-white border border-gray-200 rounded-lg shadow-md">
                    <caption class="text-base font-semibold text-gray-700 mb-2 capitalize">{{ tipo }}</caption>
                    <thead>
                        <tr class="bg-gray-50 border-b">
                            <th class="py-2 px-4 text-left text-sm font-medium text-gray-600">Producto</th>
                            <th class="py-2 px-4 text-left text-sm font-medium text-gray-600">Cantidad</th>
                            <th class="py-2 px-4 text-left text-sm font-medium text-gray-600">Detalle</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="sobrante in sobrantes" :key="sobrante.id" class="border-b hover:bg-gray-50">
                            <td class="py-2 px-4">{{ sobrante.inventario.nombre }}</td>
                            <td class="py-2 px-4">{{ sobrante.cantidad }}</td>
                            <td class="py-2 px-4">{{ sobrante.inventario.detalle }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div v-if="!props.sobrantes.length" class="text-center py-4">
            <p class="text-gray-500">No hay sobrantes registrados</p>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    sobrantes: Array,
    categoriasInventario: Array,
    sobrantesInventario: Array,
    cantidadDeCortes: Number,
});

const sobrantesPorCorte = computed(() => {
    return props.sobrantes.reduce((acc, sobrante) => {
        const corteId = sobrante.corte_caja_id || 'sin_corte';
        const tipo = sobrante.inventario?.tipo || 'sin_tipo';

        if (!acc[corteId]) {
            acc[corteId] = {};
        }
        if (!acc[corteId][tipo]) {
            acc[corteId][tipo] = [];
        }
        acc[corteId][tipo].push(sobrante);
        return acc;
    }, {});
});

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleString('es-MX', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        timeZone: 'America/Mexico_City'
    });
};

console.log(props.sobrantes);
</script>
