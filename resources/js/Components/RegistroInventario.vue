<template>
  <div class="min-h-screen pt-10">
    <div class="max-w-5xl mx-auto bg-white rounded-lg p-1">
      <!-- Header -->
      <div class="mb-6 border-b pb-4">
        <div class="flex justify-between items-center">
          <h1 class="text-2xl font-bold text-gray-900">Registro de Inventario</h1>
          <div class="flex gap-4 items-center">
            
          </div>
        </div>
      </div>

      <!-- Tabs -->
      <div class="border-b border-gray-200 overflow-scroll">
        <nav class="-mb-px flex space-x-8">
          <button 
            v-for="tab in tabs" 
            :key="tab.id" 
            @click="activeTab = tab.id" 
            :class="[
              'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm',
              activeTab === tab.id 
                ? 'border-blue-500 text-blue-600' 
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
          >
            {{ tab.name }}
          </button>
        </nav>
      </div>

      <!-- Content -->
      <div class="mt-6 overflow-scroll">
        <table class="min-w-full divide-y divide-gray-200">
          <thead>
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" v-if="activeTab === 'gastos'">
                Costo
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" v-if="activeTab !== 'gastos' && activeTab !== 'sobrantes'">
                Existe
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" v-if="activeTab !== 'gastos' && activeTab !== 'sobrantes'">
                Entra
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" v-if="activeTab !== 'gastos' && activeTab !== 'sobrantes'">
                Total
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" v-if="showVendidos">
                Vendidos
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" v-if="activeTab !== 'gastos'">
                Sobra
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-if="activeTab === 'gastos' ">
              <td colspan="2" class="px-6 py-4">
                <button 
                  @click="addNewGasto"
                  class="text-blue-600 hover:text-blue-800"
                >
                  + Agregar nuevo gasto
                </button>
              </td>
            </tr>
            <tr v-for="(item, index) in filteredProducts" :key="index">
              <td class="px-6 py-4 whitespace-nowrap">
                <div v-if="activeTab === 'gastos'  " class="flex gap-2">
                  <input 
                    type="text" 
                    v-model="item.nombre"
                    class="w-48 rounded-md border-gray-300 shadow-sm"
                    placeholder="Nombre del gasto"
                  >
                  <button 
                    @click="removeGasto(index)" 
                    class="text-red-600 hover:text-red-800"
                  >
                    ×
                  </button>
                </div>
                <template v-else>
                  <h1>{{ item.nombre }}</h1>
                  <span class="ml-5 text-sm text-gray-500">{{ item.detalle }}</span>
                </template>
              </td>
              <td class="px-6 py-4" v-if="activeTab !== 'gastos' ">
                
                {{calculateExisted(item)}}
                </td>

              <td class="px-6 py-4" v-if="activeTab !== 'gastos'  && activeTab !== 'sobrantes'">
                <input 
                  v-model.number="item.entra"
                  type="number"
                  min="0"
                  placeholder="0"
                  class="w-20 rounded-md border-gray-300 shadow-sm text-center"
                  @input="calculateTotals(item)"
                />
              </td>
              <td class="px-6 py-4 font-medium" v-if="activeTab !== 'gastos'  && activeTab !== 'sobrantes'">
                {{ calculateTotal(item) }}
              </td>
              <td class="px-6 py-4" v-if="showVendidos">
                {{ getVendidos(item.id) }}
              </td>
              <td class="px-6 py-4" v-if="activeTab !== 'gastos'  && activeTab !== 'sobrantes'">
                {{ calculateSobra(item) }}
              </td>
              <td class="px-6 py-4" v-if="activeTab === 'gastos'">
                <input 
                  v-model.number="item.costo"
                  type="number"
                  min="0"
                  placeholder="0"
                  class="w-20 rounded-md border-gray-300 shadow-sm text-center"
                />
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-if="activeTab === 'sobrantes'" class="mt-6 flex justify-end">
      <button 
        @click="saveSobrantes"
        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
        :disabled="isSaving"
      >
        {{ isSaving ? 'Guardando...' : 'Guardar Sobrantes' }}
      </button>
    </div>
      <!-- Save Button -->
      <div v-else class="mt-6 flex justify-end">
        
        <button 
          v-if="activeTab === 'gastos'"
          @click="saveGastos"
          class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
          :disabled="isSaving"
        >
          {{ isSaving ? 'Guardando...' : 'Guardar Gastos' }}
        </button>
        <button 
          v-else
          @click="saveForm"
          class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
          :disabled="isSaving"
        >
          {{ isSaving ? 'Guardando...' : 'Guardar Registro' }}
        </button>
        
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import Swal from 'sweetalert2'

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


async function saveSobrantes() {
  try {
    isSaving.value = true;

    const formattedData = {
      sobrantes: products.value
        .filter(item => item.existe > 0) // Filtra solo los productos con sobrantes
        .map(item => ({
          inventario_id: item.id,
          sucursal_id: props.sucursal_id, // Ajusta según tu lógica
          nombre: item.nombre,
          cantidad: item.existe,
        })),
    };

    router.post('/sobrantes', formattedData, {
      preserveScroll: true,
      preserveState: true,
      onSuccess: () => {
        Toast.fire({
          icon: "success",
          title: "Sobrantes guardados exitosamente",
        });
      },
      onError: (errors) => {
        console.error("Error al guardar sobrantes:", errors);
        Toast.fire({
          icon: "error",
          title: "Error al guardar los sobrantes",
        });
      },
    });
  } catch (error) {
    console.error("Error al guardar sobrantes:", error);
    Toast.fire({
      icon: "error",
      title: "Error al guardar los sobrantes",
    });
  } finally {
    isSaving.value = false;
  }
}


const { props } = usePage();
const activeTab = ref('bebida');
const isSaving = ref(false);
const gastos = ref(props.gastos || []);



function addNewGasto() {
  gastos.value.push({
    nombre: '',
    costo: 0
  });
}

function removeGasto(index) {
  gastos.value.splice(index, 1);
}
const tabs = [
  { id: 'bebida', name: 'Bebidas' },
  { id: 'masa', name: 'Tortillas y Bolas' },
  { id: 'extras', name: 'Extras' },
  { id: 'relleno', name: 'Rellenos' },
  { id: 'pastes', name: 'Pastes Empanadas' },
  { id: 'gastos', name: 'Gastos generales' },
  { id: 'sobrantes', name: 'Sobrantes' },
];


const products = ref(props.inventario.map(item => ({
  ...item,
  existe: props?.registros[item.id-1]?.existe || item?.existe || 0,
  entra: props?.registros[item.id-1]?.entra ||  item?.entra || 0,
  total: null,
  sobra: null,
  costo: item?.costo || 0
})));



const showVendidos = computed(() => 
  activeTab.value === 'bebida' || activeTab.value === 'extras' || activeTab.value === 'pastes'
);



const filteredProducts = computed(() => {
  if (activeTab.value === 'gastos') {
    return gastos.value;
  } 
  if (activeTab.value === 'pastes' ||  activeTab.value === 'sobrantes') {
    return products.value.filter(item =>
      item.tipo === 'pastes' ||
      item.tipo === 'empanadas saladas' ||
      item.tipo === 'empanadas dulces'
    );
  }
  return products.value.filter(item => item.tipo === activeTab.value);
});

function getVendidos(productoId) {
  const venta = props.ventas.find(v => v.producto_id === productoId);
  return venta ? venta.total_vendido : 0;
}

function calculateTotal(item) {
  return (calculateExisted(item)) + (item.entra || 0);
}

function calculateSobra(item) {
  const total = calculateTotal(item);
  const vendidos = getVendidos(item.id);
  return total - vendidos;
}

function calculateTotals(item) {

  item.total = (item.existe || 0) + (item.entra || 0);
  const vendidos = getVendidos(item.id);
  item.sobra = item.total - vendidos;
}

function calculateExisted(item) {
  if(item.existe) return item.existe

  const vendidos = getVendidos(item.id);
  
  return (item.cantidad) + Number(vendidos) ; 
}

async function saveGastos() {
  try {
    isSaving.value = true;
    
    const formattedData = {
      gastos: gastos.value.map(item => ({
        id: item.id || 0,
        sucursal_id: item.sucursal_id || 0,
        nombre: item.nombre,
        costo: item.costo || 0,
      }))
    };
    console.log(formattedData)
    await router.post('/gastos', formattedData, {
      
      preserveScroll: true,
      preserveState: true,
      onSuccess: (e) => {
        
        Toast.fire({
            icon: "success",
            title: "Gastos guardados exitosamente"
        });
        activeTab.value = 'gastos' ;
        gastos.value = e.props.gastos;
        
      },
      onError: (errors) => {
        console.error('Error al guardar:', errors);
        Toast.fire({
            icon: "error",
            title: "Error al guardar los gastos"
        });
      }
    });
  } catch (error) {
    console.error('Error al guardar:', error);
    Toast.fire({
        icon: "error",
        title: "Error al guardar los gastos"
    });
  } finally {
    isSaving.value = false;
  }
}

async function saveForm() {
  try {
    isSaving.value = true;
    
    const formattedData = {
      productos: products.value.map(item => ({
        id: item.id,
        existe: item.existe || item.cantidad || 0,
        entra: item.entra || 0, 
        total: calculateTotal(item),
        vende: Number(getVendidos(item.id)),
        costo: item.costo || 0,
        sobra: calculateSobra(item)
      }))
    };

    router.post('/registro', {registros: formattedData},{
      preserveScroll: true,
      preserveState: false,  
      replace: true,
      onSuccess: () => {
        console.log('Registro guardado exitosamente');
      },
      onError: (errors) => {
        console.error('Error al guardar:', errors);
      }}
    );
    
    
    Toast.fire({
        icon: "success",
        title: "Registro guardado exitosamente"
    });
  } catch (error) {
    console.error('Error al guardar:', error);
    Toast.fire({
        icon: "error",
        title: "Error al guardar el registro"
    });
    
  } finally {
    isSaving.value = false;
  }
}
</script>

<style scoped>
input {
  text-align: center;
}

input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

input[type=number] {
  -moz-appearance: textfield;
}
</style>