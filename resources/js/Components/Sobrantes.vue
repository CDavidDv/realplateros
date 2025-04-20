<template>
    <div class="bg-white p-4 sm:p-10 rounded-xl shadow-xl mb-6">
        <div class="mb-4 border-b pb-2">
            <h1 class="text-xl font-bold text-gray-800">Sobrantes</h1>
        </div>

        <!-- Sobrantes agrupados por categoría -->
        <div v-for="categoria in categoriasInventario" :key="categoria.tipo" class="mb-6">
            <h2 class="text-lg font-semibold text-gray-700 mb-3 capitalize">{{ categoria.tipo }}</h2>
            
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border border-gray-200 rounded-lg shadow-md">
                    <thead>
                        <tr class="bg-gray-50 border-b">
                            <th class="py-2 px-4 text-left text-sm font-medium text-gray-600">Producto</th>
                            <th class="py-2 px-4 text-left text-sm font-medium text-gray-600">Cantidad</th>
                            <th class="py-2 px-4 text-left text-sm font-medium text-gray-600">Detalle</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="sobrante in sobrantes.filter(s => s.tipo === categoria.tipo)" 
                            :key="sobrante.id" 
                            class="border-b hover:bg-gray-50">
                            <td class="py-2 px-4">{{ sobrante.nombre }}</td>
                            <td class="py-2 px-4">{{ sobrante.cantidad }}</td>
                            <td class="py-2 px-4">{{ sobrante.detalle }}</td>
                        </tr>
                        <tr v-if="!sobrantes.some(s => s.tipo === categoria.tipo)" class="border-b">
                            <td colspan="3" class="py-2 px-4 text-center text-gray-500">
                                No hay sobrantes en esta categoría
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div v-if="categoriasInventario.length === 0" class="text-center py-4">
            <p class="text-gray-500">No hay categorías definidas</p>
        </div>
    </div>
</template>

<script setup>

defineProps({
    sobrantes: Array,
    categoriasInventario: Array,
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
</script>