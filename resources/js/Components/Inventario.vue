<template>
    <div class="container mx-auto p-8 md:p-10 ">
        <h1 class="text-2xl font-bold mb-4">Gestión de Inventario</h1>
        <!--Seccion completar asignnar ticekts-->
        <div class="space-y-4 mb-5 no-print">
            <div 
                v-for="ticket in tickets" 
                :key="ticket.id" 
                class="flex flex-col gap-4 p-4 border rounded-lg shadow-sm bg-white"
            >
                <!-- Encabezado del ticket -->
                <div class="flex justify-between items-center">
                <div class="flex items-center space-x-2">
                    <span class="text-lg font-semibold text-gray-800">Ticket #{{ ticket.id }}</span>
                    <span class="text-gray-600 text-sm">Fecha: {{ ticket.created_at.split('T')[0] }}</span>
                </div>
                <div class="text-gray-600 text-sm">
                    Estado: <span class="font-medium text-gray-800 capitalize">{{ ticket.estado }}</span>
                </div>
                </div>

                <!-- Detalles del ticket -->
                <div>
                <div class="text-sm text-gray-700">
                    <span class="font-medium">Hora de salida:</span> {{ ticket.hora_salida || 'N/A' }}
                </div>
                <div class="mt-2 space-y-1">
                    <h4 class="text-sm font-medium text-gray-800">Productos asignados:</h4>
                    <ul class="text-sm text-gray-700 space-y-1 pl-4 list-disc">
                    <li  class="flex gap-1 items-center"
                        v-for="p in ticket.ticket_productos_asignacion" 
                        :key="p.id"
                    >
                        {{ p.cantidad }}x {{ p.producto.nombre}}<p v-if="p.producto.detalle" class="text-xs">- {{ p.producto.detalle }}</p>
                    </li>
                    </ul>
                </div>
                </div>

                <!-- Botón de acción -->
                <div class="flex justify-end">
                <button 
                    @click="completeTicket(ticket.id)" 
                    class="bg-orange-500 text-white px-4 py-2 rounded-md hover:bg-orange-600 focus:outline-none focus:ring focus:ring-orange-200 focus:ring-opacity-50"
                >
                <!--COMPLETAR Y ASIGNAR CANTIDAD AL INVENTARIO-->
                <!--IMPRIMIR LOS TICKETS-->
                    Completar
                </button>
                </div>
            </div>
        </div>

       

        <div class="no-print flex flex-col md:flex-row gap-10 w-full" v-if="props.user.roles[0] ==='admin' || props.user.roles[0] === 'sucursal' || props.user.roles[0] === 'almacen'">
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
                                <option v-for="categoria in categorias" :key="categoria.id" class=" capitalize" :value="categoria.tipo">{{categoria.tipo}}</option>
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
                <div class="w-full" v-if="isAlmacen">
                     <button
                        @click="openModal"
                        class="bg-orange-500 w-full hover:bg-orange-700 text-white font-bold py-2 px-4 rounded mb-4">
                        Agregar nueva categoría
                    </button>
                    <button
                        @click="openModalDelete"
                        class="bg-red-500 w-full hover:bg-red-700 text-white font-bold py-2 px-4 rounded mb-4">
                        Eliminar categoría
                    </button>
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
                <div class="bg-white shadow rounded-lg overflow-auto h-screen">
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
                                    Existentes</th>
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

        <RegistroInventario  />

        <!-- Modal para agregar categoría -->
        <div
            v-if="showModal"
            class="no-print fixed inset-0 bg-gray-900 bg-opacity-50 flex justify-center items-center z-50">
            <div class="bg-white p-6 rounded-lg shadow-lg w-96">
                <h2 class="text-xl font-semibold mb-4">Agregar Categoría</h2>
                <input
                    v-model="newCategory"
                    type="text"
                    placeholder="Nombre de la categoría"
                    class="w-full mb-4 px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
                />
                <div class="flex justify-end space-x-2">
                    <button
                        @click="closeModal"
                        class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg">
                        Cancelar
                    </button>
                    <button
                        @click="addCategory"
                        class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-lg">
                        Agregar
                    </button>
                </div>
            </div>
        </div>

        <div
            v-if="showModalDelete"
            class="fixed inset-0 bg-gray-900 bg-opacity-50 flex justify-center items-center z-50">
            <div class="bg-white p-6 rounded-lg shadow-lg w-96">
                <h2 class="text-xl font-semibold mb-4">Eliminar Categoría</h2>
                
                <select v-model="categoriaToDelete" class="w-full mb-4 px-3 py-2 border rounded-lg capitalize focus:outline-none focus:ring-2 focus:ring-orange-500">
                    <option v-for="categoria in categorias"  :key="categoria.id" :value="categoria" class=" capitalize">{{ categoria.tipo }}</option>
                </select>
                <div class="flex justify-end space-x-2">
                    <button
                        @click="closeModal"
                        class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg">
                        Cancelar
                    </button>
                    <button
                        @click="subsCategory"
                        class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-lg">
                        Eliminar
                    </button>
                </div>
            </div>
        </div>

    </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import Swal from 'sweetalert2'
import RegistroInventario from './RegistroInventario.vue';
import EstimacionPastes from './EstimacionPastes.vue';

const { props } = usePage()
const tickets = ref(props.tickets)

const inventory = ref(props?.inventario?.filter((item) => {
    return item.nombre != '-'
}))
const categorias = ref(props.categorias)
const isAlmacen = ref(props.user.roles[0] === 'almacen')

const showModal = ref(false)
const showModalDelete = ref(false)
const categoriaToDelete = ref()
const newCategory = ref("")

const openModal = () => {
    showModal.value = true;
}
const openModalDelete = () => {
    showModalDelete.value = true;
}


const  closeModal = () => {
    showModal.value = false;
    showModalDelete.value = false;
    newCategory.value = ""; // Limpia el input al cerrar
}
const addCategory = () => {
    if (newCategory.value.trim()) {
        // Aquí puedes agregar lógica para guardar la categoría
        router.post(`/categorias`, {
            tipo: newCategory.value
        }, {
            preserveScroll: true,
            preserveState: false,  
            replace: true,
            onSuccess: (response) => {
                Toast.fire({
                    icon: "success",
                    title: "Categoría agregada correctamente"
                });
                categorias.value = response.props.categorias
            },
            onError: (errors) => {
                Toast.fire({
                    icon: "error",
                    title: "Hubo un problema al agregar la categoría"
                });
            }
        });
        closeModal();
    } else {
        Toast.fire({
            icon: "error",
            title: "Por favor, ingresa un nombre para la categoría"
        });
    }
}

const completeTicket = (id) =>{
    router.put(`/tickets/${id}`, {
        estado: 'completado'
    }, {
        preserveScroll: true,
        preserveState: false,  
        replace: true,
        onSuccess: (response) => {
            Toast.fire({
                icon: "success",
                title: "Ticket completado correctamente"
            });
            tickets.value = response.props.tickets
        },
        onError: (errors) => {
            Toast.fire({
                icon: "error",
                title: "Hubo un problema al completar el ticket"
            });
            console.error(errors)
        }
    });
}
const subsCategory = () => {
    
    if (categoriaToDelete?.value?.tipo.trim()) {
        router.delete(`/categorias/${categoriaToDelete.value.tipo}`, {
            preserveScroll: true,
            preserveState: false,  
            replace: true,
            onSuccess: (response) => {
                if(response.props.flash.success){
                    Toast.fire({
                        icon: "success",
                        title: "Categoría eliminada correctamente"
                    });
                    categorias.value = response.props.categorias
                }else{
                    Toast.fire({
                        icon: "error",
                        title: "La categoría tiene mas de un elemento aún en alguna sucursal"
                    });
                }
            },
            onError: (errors) => {
                Toast.fire({
                    icon: "error",
                    title: "Hubo un problema al eliminar la categoría"
                });
            }
        });
        closeModal();
    } else {
        Toast.fire({
            icon: "error",
            title: "Por favor, selecciona una categoría"
        });
    }
}

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
    return inventory?.value?.filter(item =>
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
            preserveScroll: true,
            preserveState: false,  
            replace: true,
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
            preserveScroll: true,
            preserveState: false,  
            replace: true,
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
                preserveScroll: true,
                preserveState: false,  
                replace: true,
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

<style>
.print {
    display: block !important;
}

@media print {
    .no-print {
        display: none !important;
    }

    .print {
        display: block !important;
    }

    body {
        overflow: scroll !important;
    }

    ::-webkit-scrollbar {
        display: none;
    }

    astro-dev-toolbar {
        display: none !important;
    }

    article {
        break-inside: avoid;
    }

    @page {
        size: A4 landscape;
        margin: 0;
    }

    .tabla {
        width: 100%;
        table-layout: fixed;
    }

    .tabla th, .tabla td {
        word-wrap: break-word;
    }
}

@media print {
  .text-red-700 {
    color: #6b7280 !important; /* Cambiar a gris al imprimir */
  }
  .ventasR{
    height: fit-content !important;
    overflow: auto !important;
  }
}

</style>