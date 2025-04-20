<template>
    <div class="bg-white p-4 sm:p-10 rounded-xl shadow-xl mb-6">
        <div class="mb-4 border-b pb-2">
            <h1 class="text-xl font-bold text-gray-800">Entradas al Inventario</h1>
        </div>
        
        <div class="overflow-x-auto">
            <table v-if="registrosInventario.length > 0" class="min-w-full bg-white border border-gray-200 rounded-lg shadow-md">
                <thead>
                    <tr class="bg-gray-100 border-b">
                        <th class="py-3 px-6 text-left text-sm font-semibold text-gray-700">Producto</th>
                        <th class="py-3 px-6 text-left text-sm font-semibold text-gray-700">Cantidad</th>
                        <th class="py-3 px-6 text-left text-sm font-semibold text-gray-700">Empleado</th>
                        <th class="py-3 px-6 text-left text-sm font-semibold text-gray-700">Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="registro in registrosInventario" :key="registro.id" class="border-b hover:bg-gray-100">
                        <td class="py-2 px-6"> 
                            <span class="font-medium text-gray-800">{{ registro.inventario.nombre }}</span> 
                            <span class="text-sm text-gray-500"> ({{ registro.inventario.detalle }})</span>
                        </td>
                        <td class="py-2 px-6 text-gray-700">{{ registro.cantidad }}</td>
                        <td class="py-2 px-6 text-gray-700">
                            {{ registro.trabajador.name }} {{ registro.trabajador.apellido_p }} {{ registro.trabajador.apellido_m }}
                        </td>
                        <td class="py-2 px-6 text-gray-700">{{ formatDate(registro.updated_at) }}</td>
                    </tr>
                </tbody>
            </table>
            <div v-else class="text-center py-4">
                <p class="text-gray-500">No hay registros de entradas.</p>
            </div>
        </div>
    </div>
</template>

<script setup>

defineProps({
    registrosInventario: Array,
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