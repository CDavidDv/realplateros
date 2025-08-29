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
                    <tbody v-if="sobrantes && sobrantes.length > 0">
                        <tr v-for="sobrante in sobrantes" :key="sobrante.id" class="border-b hover:bg-gray-50">
                            <td class="py-2 px-4">
                                <span v-if="sobrante.inventario?.nombre">
                                    {{ sobrante.inventario.nombre }}
                                </span>
                                <span v-else class="text-red-500 font-medium">
                                    ID: {{ sobrante.inventario_id }} (Inventario no cargado)
                                </span>
                            </td>
                            <td class="py-2 px-4">{{ sobrante.cantidad }}</td>
                            <td class="py-2 px-4">
                                <span v-if="sobrante.inventario?.detalle">
                                    {{ sobrante.inventario.detalle }}
                                </span>
                                <span v-else class="text-gray-400 italic">
                                    Detalle no disponible
                                </span>
                            </td>
                        </tr>
                    </tbody>
                    <tbody v-else>
                        <tr>
                            <td colspan="3" class="py-2 px-4 text-center text-gray-500">No hay sobrantes registrados</td>
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
    sobrantes: {
        type: Array,
        default: () => []
    },
    categoriasInventario: {
        type: Array,
        default: () => []
    },
    sobrantesInventario: {
        type: Array,
        default: () => []
    },
    cantidadDeCortes: {
        type: Number,
        default: 0
    },
});

const sobrantesPorCorte = computed(() => {
    if (!props.sobrantes || !Array.isArray(props.sobrantes)) {
        return {};
    }
    
    return props.sobrantes.reduce((acc, sobrante) => {
        if (!sobrante) return acc;
        
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

// Verificar si faltan datos del inventario
const faltanDatosInventario = computed(() => {
    if (!props.sobrantes || !Array.isArray(props.sobrantes)) {
        return false;
    }
    return props.sobrantes.some(sobrante => sobrante && !sobrante.inventario);
});

const formatDate = (dateString) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    if (isNaN(date.getTime())) return '-';
    
    return date.toLocaleString('es-MX', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        timeZone: 'America/Mexico_City'
    });
};

console.log('Sobrantes recibidos:', props.sobrantes);
console.log('Sobrantes por corte:', sobrantesPorCorte.value);

// Validar estructura de datos
if (props.sobrantes && Array.isArray(props.sobrantes)) {
  console.log('=== VALIDACIÓN DE ESTRUCTURA DE SOBRANTES ===');
  props.sobrantes.forEach((sobrante, index) => {
    console.log(`Sobrante [${index}]:`, {
      id: sobrante?.id,
      inventario_id: sobrante?.inventario_id,
      tiene_inventario: !!sobrante?.inventario,
      inventario_completo: sobrante?.inventario,
      estructura_completa: sobrante
    });
    
    if (!sobrante) {
      console.warn(`⚠️  Sobrante [${index}] es null o undefined`);
    } else if (!sobrante.inventario) {
      console.warn(`⚠️  Sobrante [${index}] (ID: ${sobrante.id}) no tiene inventario cargado. Solo tiene inventario_id: ${sobrante.inventario_id}`);
    } else {
      console.log(`✅ Sobrante [${index}] (ID: ${sobrante.id}) tiene inventario válido:`, sobrante.inventario);
    }
  });
  console.log('=== FIN VALIDACIÓN ===');
}

// Validar estructura de datos
if (props.sobrantes && Array.isArray(props.sobrantes)) {
  props.sobrantes.forEach((sobrante, index) => {
    if (!sobrante) {
      console.warn(`Sobrante en índice ${index} es null o undefined`);
    } else if (!sobrante.inventario) {
      console.warn(`Sobrante en índice ${index} no tiene inventario:`, sobrante);
    }
  });
}
</script>
