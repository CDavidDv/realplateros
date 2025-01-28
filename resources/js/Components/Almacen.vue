<template>
  <div class="min-h-screen bg-gray-100 py-8 px-4">
    <div class="max-w-4xl mx-auto space-y-6">
      <!-- Header -->
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-4xl font-bold text-gray-900">Almacén</h1>
      </div>

      <!-- Buttons -->
      <div class="flex justify-between items-center mb-6">
        <button 
          @click="showModal('addProduct')" 
          class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg shadow transition">
          Ingresar productos
        </button>
        <button 
          @click="showModal('assignProduct')" 
          class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg shadow transition">
          Asignar productos
        </button>
      </div>

      <!-- Categories -->
      <div class="bg-white shadow-lg rounded-lg overflow-hidden divide-y capitalize">
        <div 
          v-for="(category, categoryName) in inventory" 
          :key="categoryName" 
          class="border-b last:border-b-0">

          <!-- Category Header -->
          <div class="p-4 bg-gray-200 flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">{{ categoryName }}</h2>
            <button 
              @click="deleteCategory(categoryName)" 
              class="text-red-600 hover:text-red-700 font-medium flex items-center gap-2 transition">
              <MinusIcon class="h-5 w-5" /> Eliminar categoría
            </button>
          </div>

          <!-- Items -->
          <div class="divide-y">
            <div 
              v-for="(item, itemName) in category" 
              :key="itemName" 
              class="flex items-center justify-between p-4 hover:bg-gray-50 transition">
              <span class="text-gray-700 font-medium">{{ itemName }}</span>
              <div class="flex items-center gap-3">
                <span class="px-3 py-1 bg-gray-200 rounded-full text-gray-800 font-medium">{{ item }}</span>
              </div>
            </div>
            <button 
              @click="showModal('addProduct', categoryName)" 
              class="flex w-full p-4 text-left text-blue-600 hover:bg-gray-50 transition items-center gap-2">
              <PlusIcon class="h-5 w-5" /> Agregar producto
            </button>
          </div>
        </div>
      </div>

      <!-- Add Category Button -->
      <div class="flex justify-end mt-4">
        <button 
          @click="showModal('addCategory')" 
          class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg shadow transition">
          Agregar categoría
        </button>
      </div>
    </div>

    <!-- Modals -->
    <div v-if="modalType" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
      <div class="bg-white rounded-lg shadow-lg p-6 max-w-sm w-full">
        <h2 v-if="modalType === 'addCategory'" class="text-xl font-bold mb-4">Agregar categoría</h2>
        <h2 v-if="modalType === 'addProduct'" class="text-xl font-bold mb-4">Agregar producto</h2>

        <!-- Add Category -->
        <div v-if="modalType === 'addCategory'">
          <input 
            v-model="newCategory" 
            type="text" 
            placeholder="Nombre de la categoría" 
            class="w-full border border-gray-300 rounded-lg px-4 py-2 mb-4 focus:ring-blue-500 focus:border-blue-500">
          <button 
            @click="addCategory" 
            class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg w-full transition">
            Agregar
          </button>
        </div>

        <!-- Add Product -->
        <div v-if="modalType === 'addProduct'">
          <input 
            v-model="newProduct.name" 
            type="text" 
            placeholder="Nombre del producto" 
            class="w-full border border-gray-300 rounded-lg px-4 py-2 mb-4 focus:ring-blue-500 focus:border-blue-500">
          <input 
            v-model.number="newProduct.quantity" 
            type="number" 
            placeholder="Cantidad" 
            class="w-full border border-gray-300 rounded-lg px-4 py-2 mb-4 focus:ring-blue-500 focus:border-blue-500">
          <button 
            @click="addProduct" 
            class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg w-full transition">
            Agregar
          </button>
        </div>

        <!-- Close Modal -->
        <button 
          @click="closeModal" 
          class="text-gray-500 hover:text-gray-600 mt-4 block mx-auto">
          Cancelar
        </button>
      </div>
    </div>
  </div>
</template>
<script setup>
import { ref, watch } from 'vue'
import { PlusIcon, MinusIcon } from 'lucide-vue-next'
import Swal from 'sweetalert2'
import { usePage } from '@inertiajs/vue3'

// Obtener categorías desde el servidor
const categorias = ref(usePage().props.categorias)
console.log(categorias.value)
// Convertir categorías en el formato requerido para `inventory`
const inventory = ref({})

// Cargar categorías del servidor en el inventario
const loadCategories = () => {
  categorias.value.forEach((categoria) => {
    inventory.value[categoria.tipo] = categoria.productos || {}
  })
}

// Ejecutar al cargar el componente
loadCategories()

// Modal Controls
const modalType = ref(null) // Tipo de modal a mostrar
const newCategory = ref('') // Nueva categoría
const newProduct = ref({ name: '', quantity: 0 }) // Nuevo producto
let currentCategory = null // Categoría actual para agregar producto

// Mostrar modal
const showModal = (type, category = null) => {
  modalType.value = type
  currentCategory = category
}

// Cerrar modal
const closeModal = () => {
  modalType.value = null
  newCategory.value = ''
  newProduct.value = { name: '', quantity: 0 }
}

// Agregar categoría
const addCategory = () => {
  if (newCategory.value.trim() !== '') {
    if (!inventory.value[newCategory.value]) {
      inventory.value[newCategory.value] = {}
      closeModal()
      // Aquí envías la nueva categoría al backend
      // Ejemplo: axios.post('/categorias', { nombre: newCategory.value })
    }
  }
}

// Agregar producto
const addProduct = () => {
  if (newProduct.value.name.trim() !== '' && newProduct.value.quantity > 0) {
    if (currentCategory) {
      if (!inventory.value[currentCategory]) {
        inventory.value[currentCategory] = {}
      }
      inventory.value[currentCategory][newProduct.value.name] = newProduct.value.quantity
      closeModal()
      // Aquí envías el nuevo producto al backend
      // Ejemplo: axios.post(`/categorias/${currentCategory}/productos`, newProduct.value)
    }
  }
}

// Eliminar categoría
const deleteCategory = (categoryName) => {
  Swal.fire({
    title: '¿Estás seguro?',
    text: '¡No podrás revertir esto!',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Sí, eliminar!',
  }).then((result) => {
    if (result.isConfirmed) {
      delete inventory.value[categoryName]
      Swal.fire('Eliminado!', 'La categoría ha sido eliminada.', 'success')
      // Aquí envías la eliminación al backend
      // Ejemplo: axios.delete(`/categorias/${categoryName}`)
    }
  })
}

// Sincronizar cambios en las categorías desde el servidor
watch(categorias, loadCategories, { deep: true })
</script>

<style scoped>
button {
  transition: all 0.2s ease-in-out;
}
</style>
