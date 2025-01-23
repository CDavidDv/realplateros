<template>
    <div class="min-h-screen bg-gray-50 py-8 px-4">
      <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-6">
          <h1 class="text-3xl font-bold text-gray-900">Almacén</h1>
          <button class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded shadow transition">
            Agregar categoría
          </button>
        </div>
  
        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
          <!-- Categories -->
          <div 
            v-for="(category, categoryName) in inventory" 
            :key="categoryName" 
            class="border-b last:border-b-0"
          >
            <!-- Category Header -->
            <div class="p-4 bg-gray-100 flex justify-between items-center">
              <h2 class="text-xl font-semibold text-gray-800">{{ categoryName }}</h2>
              <button 
                class="text-red-600 hover:text-red-700 font-medium flex items-center gap-1 transition"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <line x1="18" y1="6" x2="6" y2="18" />
                  <line x1="6" y1="6" x2="18" y2="18" />
                </svg>
                Eliminar categoría
              </button>
            </div>
  
            <!-- Items -->
            <div class="divide-y">
              <div 
                v-for="(item, itemName) in category" 
                :key="itemName"
                class="flex items-center justify-between p-4 hover:bg-gray-50 transition"
              >
                <span class="text-gray-700 font-medium">{{ itemName }}</span>
                <div class="flex items-center gap-3">
                  <span class="px-3 py-1 bg-gray-200 rounded-full text-gray-800 font-medium">
                    {{ item }}
                  </span>
                  <button 
                    @click="updateQuantity(categoryName, itemName, item + 1)"
                    class="p-1 text-gray-600 hover:text-gray-800 rounded transition"
                  >
                    <PlusIcon class="h-5 w-5" />
                  </button>
                  <button 
                    @click="updateQuantity(categoryName, itemName, Math.max(0, item - 1))"
                    class="p-1 text-gray-600 hover:text-gray-800 rounded transition"
                  >
                    <MinusIcon class="h-5 w-5" />
                  </button>
                </div>
              </div>
              <button 
                class="flex w-full p-4 text-left text-blue-500 hover:bg-gray-50 transition items-center gap-2"
              >
                <PlusIcon class="h-5 w-5" /> Agregar producto
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref } from 'vue'
  import { PlusIcon, MinusIcon } from 'lucide-vue-next'
  
  const inventory = ref({
    'Rellenos': { 'Mole rojo': 10, 'Frijol': 30 },
    'Bebidas': { 'Cocacola (lata)': 10, 'Fresca': 15 },
    'Masas': { 'M. dulce': 10, 'Bola': 20, 'M. salada': 30 },
    'Extras': { 'Caja chica': 100 },
    'Objetos': { 'Escoba': 15, 'Trapeador': 12 }
  })
  
  const updateQuantity = (category, item, newValue) => {
    inventory.value[category][item] = newValue
  }
  </script>
  
  <style scoped>
  /* Transitions for buttons and hover effects */
  button {
    transition: all 0.2s ease-in-out;
  }
  
  /* Divider styles for child elements */
  .divide-y > :not([hidden]) ~ :not([hidden]) {
    --tw-divide-y-reverse: 0;
    border-top-width: calc(1px * calc(1 - var(--tw-divide-y-reverse)));
    border-color: rgba(229, 231, 235, 1);
  }
  </style>
  