<template>
    <div class="container mx-auto p-8 md:p-10">
        <h1 class="text-2xl font-bold mb-4">Gestión de Inventario</h1>

        <div class="flex flex-col md:flex-row gap-10 w-full">
            <div class="md:w-4/12 w-full">
                <!-- Formulario para agregar/editar item -->
                <div class="bg-white shadow rounded-lg p-6 mb-6">
                    <h2 class="text-xl font-semibold mb-4">{{ editingItem ? 'Editar Item' : 'Agregar Nuevo Item' }}</h2>
                    <form @submit.prevent="saveItem" class="space-y-4">
                        <div>
                            <label for="itemName" class="block text-sm font-medium text-gray-700">Nombre del
                                Item</label>
                            <input v-model="currentItem.nombre" id="itemName" type="text" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50">
                        </div>
                        <div>
                            <label for="itemCategory" class="block text-sm font-medium text-gray-700">Categoría</label>
                            <select v-model="currentItem.tipo" id="itemCategory" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50">
                                <option value="relleno">Rellenos</option>
                                <option value="pastes">Pastes</option>
                                <option value="empanadas saladas">Empanadas saladas</option>
                                <option value="empanadas dulces">Empanadas dulces</option>
                                <option value="bebida">Bebidas</option>
                                <option value="masa">Masa</option>
                                <option value="extras">Extras</option>
                            </select>
                        </div>
                        <div>
                            <label for="itemDetalle" class="block text-sm font-medium text-gray-700">Detalle del item</label>
                            <input v-model="currentItem.detalle" id="itemDetalle" type="text" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50">
                        </div>
                        <div>
                            <label for="itemQuantity" class="block text-sm font-medium text-gray-700">Cantidad</label>
                            <input v-model.number="currentItem.cantidad" id="itemQuantity" type="number" required
                                min="0"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50">
                        </div>
                        <div>
                            <label for="itemPrice" class="block text-sm font-medium text-gray-700">Precio
                                Unitario</label>
                            <input v-model.number="currentItem.precio" id="itemPrice" type="number" required min="0"
                                step="0.01"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50">
                        </div>
                        <div class="flex justify-end space-x-2">
                            <button type="button" @click="resetForm"
                                class="px-4 py-2 border border-transparent text-sm font-medium rounded-md text-gray-700 bg-gray-200 hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                                Cancelar
                            </button>
                            <button type="submit"
                                class="px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                                {{ editingItem ? 'Actualizar' : 'Agregar' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="md:w-8/12 w-full">
                <!-- Buscador -->
                <div class="mb-4">
                    <input v-model="searchTerm" type="text" placeholder="Buscar en el inventario..."
                        class="w-full px-4 py-2 rounded-md border-gray-300 shadow-sm focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50"
                        @input="searchInventory">
                </div>

                <!-- Tabla de inventario -->
                <div class="bg-white shadow rounded-lg overflow-scroll">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Nombre</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Categoría</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Detalle</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Cantidad</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Precio</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="item in filteredInventory" :key="item.id" class="text-center">
                                <td class="py-1 px-2 whitespace bg-gray-50">{{ item.nombre }}</td>
                                <td class="py-1 px-2 whitespace-nowrap ">{{ item.tipo }}</td>
                                <td class="py-1 px-2 whitespace bg-gray-50">{{ item.detalle }}</td>
                                <td class="py-1 px-2 whitespace-nowrap">{{ item.cantidad }}</td>
                                <td class="py-1 px-2 whitespace-nowrap bg-gray-50">${{ item.precio }}</td>
                                <td class="py-1 px-2 whitespace-nowrap text-sm font-medium flex flex-col gap-1 place-items-center">
                                    <button @click="editItem(item)" class="bg-orange-600 py-1 px-2 text-white rounded-lg    hover:bg-orange-900 mr-2">Editar</button>
                                    <button @click="deleteItem(item.id)" class="bg-red-600 py-1 px-2 text-white rounded-lg  hover:bg-red-900">Eliminar</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>




    </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import Swal from 'sweetalert2'

const { props } = usePage();
const inventory = ref(props.inventario)



const currentItem = reactive({
    id: null,
    nombre: '',
    detalle: '',
    tipo: '',
    cantidad: 0,
    precio: 0
})

const editingItem = ref(false)
const searchTerm = ref('')

const filteredInventory = computed(() => {
    return inventory.value.filter(item =>
        item.nombre.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
        item.tipo.toLowerCase().includes(searchTerm.value.toLowerCase())
    )
})

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

// Guardar o actualizar ítem
const saveItem = () => {
    
    if (editingItem.value) {
        // Actualizar ítem (PUT)
        router.put(`/inventario/${currentItem.id}`, {
            nombre: currentItem.nombre,
            detalle: currentItem.detalle,
            tipo: currentItem.tipo,
            cantidad: currentItem.cantidad,
            precio: currentItem.precio
        }, {
            onSuccess: (response) => {
                Toast.fire({
                    icon: "success",
                    title: "Actualizado correctamente"
                });
                inventory.value = response.props.inventario
                resetForm();
            },
            onError: (errors) => {
                Toast.fire({
                    icon: "error",
                    title: "Hubo un problema al actualizar el ítem"
                });
            }
        });
    }else{
        // Actualizar ítem (PUT)
        router.post(`/inventario`, {
            nombre: currentItem.nombre,
            detalle: currentItem.detalle,
            tipo: currentItem.tipo,
            cantidad: currentItem.cantidad,
            precio: currentItem.precio
        }, {
            onSuccess: (response) => {
                Toast.fire({
                    icon: "success",
                    title: "Agregado correctamente"
                });
                inventory.value = response.props.inventario
                resetForm();
            },
            onError: (errors) => {
                Toast.fire({
                    icon: "error",
                    title: "Hubo un problema al agregar el ítem"
                });
            }
        });
    }
};



// Editar ítem
const editItem = (item) => {
    Object.assign(currentItem, item);
    editingItem.value = true;
}

// Eliminar ítem
const deleteItem = (id) => {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "¡No podrás revertir esto!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e66f23',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminarlo',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(`/inventario/${id}`, {
                onSuccess: (response) => {
                    Toast.fire({
                        icon: "success",
                        title: "Eliminado correctamente"
                    });
                    inventory.value = response.props.inventario
                },
                onError: (errors) => {
                    Toast.fire({
                        icon: "error",
                        title: "Hubo un problema al eliminar el ítem"
                    });
                }
            });
        }
    });
}

// Resetear formulario
const resetForm = () => {
    Object.assign(currentItem, {
        id: null,
        nombre: '',
        detalle: '',
        tipo: '',
        cantidad: 0,
        precio: 0
    });
    editingItem.value = false;
}
</script>




