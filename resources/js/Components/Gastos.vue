<template>
    <div class="bg-white p-4 sm:p-10 rounded-xl shadow-xl mb-6">
        <div class="mb-4 border-b pb-2">
            <h1 class="text-xl font-bold text-gray-800">Gastos</h1>
        </div>
        
        <div class="overflow-x-auto">
            <table v-if="gastos.length > 0" class="min-w-full bg-white border border-gray-200 rounded-lg shadow-md">
                <thead>
                    <tr class="bg-gray-100 border-b">
                        <th class="py-3 px-6 text-left text-sm font-semibold text-gray-700">Concepto</th>
                        <th class="py-3 px-6 text-left text-sm font-semibold text-gray-700">Costo</th>
                        <th class="py-3 px-6 text-left text-sm font-semibold text-gray-700">Empleado</th>
                        <th class="py-3 px-6 text-left text-sm font-semibold text-gray-700">Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="gasto in gastos" :key="gasto.id" class="border-b hover:bg-gray-100">
                        <td class="py-2 px-6 capitalize"> 
                            {{ gasto.nombre }}
                        </td>
                        <td class="py-2 px-6 justify-end flex"> 
                            {{ gasto.costo }}
                        </td>
                        <td class="py-2 px-6"> 
                            <span v-if="gasto.trabajador">{{ gasto.trabajador.name }} {{ gasto.trabajador.apellido_m }} {{ gasto.trabajador.apellido_p }}</span>
                            <span v-else class="text-gray-500">N/A</span>
                        </td>
                        <td class="py-2 px-6"> 
                            {{ formatDate(gasto.updated_at) }}
                        </td>
                    </tr>
                </tbody>
            </table>
            <div v-else class="text-center py-4">
                <p class="text-gray-500">No hay gastos de gastos.</p>
            </div>
        </div>
    </div>
</template>

<script setup>

defineProps({
    gastos: Array,
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