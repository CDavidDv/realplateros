<template>
  <div class="min-h-screen pt-10 no-print">
    <div class="max-w-5xl mx-auto bg-white rounded-lg p-1">
      <!-- Header -->
      <div class="mb-6 border-b pb-4">
        <div class="flex gap-5 justify-between items-center flex-col md:flex-row">
          <h1 class="text-2xl font-bold text-gray-900">Registro de Inventario</h1>
          <div class="flex gap-4 items-center no-print">
            <!--Seleccionar fecha -->
            <div class="flex flex-col  gap-2 items-center" v-if="$page.props.user.roles[0] != 'trabajador'">
              <div class="flex flex-col md:flex-row gap-3 items-center">

                <label for="fecha">Buscar por:</label>
                <div class="flex gap-5">
                  <select class="rounded" id="filter" v-model="selectedFilter">
                    <option value="day">Día</option>
                    <option value="week">Semana</option>
                    <option value="month">Mes</option>
                  </select>
    
                  <input class="w-fit h-fit rounded" id="valueSelect" type="date" v-if="selectedFilter === 'day'" v-model="selectedDate" />
                  <input class="w-fit h-fit rounded" id="valueSelect" type="week" v-if="selectedFilter === 'week'" v-model="selectedWeek" />
                  <input class="w-fit h-fit rounded" id="valueSelect" type="month" v-if="selectedFilter === 'month'" v-model="selectedMonth" />
                </div>
              </div>

              <div class="grid grid-cols-2 gap-4 ">

                <button @click="searchFilter" class="no-print bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded" type="button">Buscar</button>
                <button @click="clearFilter" class="no-print bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded" type="button">Limpiar filtro</button>
                <button @click="printAllTabs" class="bg-green-500 text-white px-4 py-2 rounded no-print">
                    Imprimir inventario - <span class="capitalize">{{ activeTab  }}</span>
                </button>
              </div>

            </div>
          </div>
        </div>
      </div>

      <!-- Tabs -->
      <div class="border-b border-gray-200 overflow-scroll  ">
        <nav class="-mb-px flex space-x-8">
          <button
            v-for="tab in tabs"
            :key="tab.id"
            @click="activeTab = tab.id"
            :class="[
              'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm capitalize',
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
      <div class="mt-6 overflow-scroll ">
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
            <tr v-if="activeTab === 'gastos'">
              <td colspan="2" class="px-6 py-4">
                <div class="">
                  <button
                    @click="addNewGasto"
                    class="text-blue-600 hover:text-blue-800"
                  >
                    + Agregar nuevo gasto
                  </button>
                </div>
              </td>
            </tr>
            <tr v-for="(item, index) in filteredProducts" :key="index">
              <td class="px-6 py-4 whitespace-nowrap">
                <div v-if="activeTab === 'gastos'" class="flex gap-2">
                  <input
                    type="text"
                    v-model="item.nombre"
                    class="w-48 rounded-md border-gray-300 shadow-sm"
                    placeholder="Nombre del gasto"
                  >
                  <button
                    @click="removeGasto(index)"
                    class="text-red-600 no-print hover:text-red-800"
                  >
                    <XIcon class="no-print" />
                  </button> 
                </div>
                <template v-else>
                  <h1>{{ item.nombre }}</h1>
                  <span class="ml-5 text-sm text-gray-500">{{ item.detalle }}</span>
                </template>
              </td>
              <td class="px-6 py-4" v-if="activeTab !== 'gastos' && activeTab !== 'sobrantes'">
                {{ filtro ? item.total - item.entra : calculateExisted(item)}}
              </td>
              <td class="px-6 py-4" v-if="activeTab !== 'gastos' && activeTab !== 'sobrantes'">
                <input
                  v-model.number="item.entra"
                  v-if="!filtro"
                  type="number"
                  min="0"
                  placeholder="0"
                  class="w-20 rounded-md  border-gray-300 shadow-sm text-center"
                  @input="calculateTotals(item)"
                />
                <div v-else>
                  {{ filtro ? item.entra : 0 }}
                </div>
              </td>
              <td class="px-6 py-4 font-medium" v-if="activeTab !== 'gastos' && activeTab !== 'sobrantes'">
                {{ filtro ? item.total : calculateTotal(item) }}
              </td>
              <td class="px-6 py-4" v-if="showVendidos && activeTab !== 'sobrantes'">
                {{ filtro ? item.vende : getVendidos(item.id) }}
              </td>
              <td class="px-6 py-4" v-if="activeTab !== 'gastos'">
                {{ filtro ? item.sobra : calculateSobra(item) }}
              </td>
              <td class="px-6 py-4" v-if="activeTab === 'gastos' && activeTab !== 'sobrantes'">
                <input
                  v-model.number="item.costo"
                  type="number"
                  min="0"
                  placeholder="0"
                  class="w-fit rounded-md  border-gray-300 shadow-sm text-center"
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
      <div v-else class="mt-6 flex justify-end no-print">
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
  <div id="print-all" class="print-all-tabs">
  <div class="mb-8">
    <h2 class="text-xl font-bold mb-4 capitalize">{{ activeTab }}</h2>
    <table class="min-w-full divide-y divide-gray-200">
      <thead>
        <tr>
          <!-- Define las columnas que necesites -->
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
      <tbody>
        <!-- Aquí deberás crear una función o computed que retorne los items correspondientes a cada pestaña -->
        <tr v-for="item in filteredProducts" :key="item.index">
          <td class="px-6 py-4 whitespace-nowrap">
                <div v-if="activeTab === 'gastos'" class="flex gap-2">
                  <span
                    class="w-48"
                    
                  > {{ 
                    item.nombre }} </span>
                  <button
                    @click="removeGasto(index)"
                    class="text-red-600 hover:text-red-800"
                  >
                    <XIcon class="no-print" />
                  </button>
                </div>
                <template v-else>
                  <h1>{{ item.nombre }}</h1>
                  <span class="ml-5 text-sm text-gray-500">{{ item.detalle }}</span>
                </template>
              </td>
              <td class="px-6 py-4" v-if="activeTab !== 'gastos' && activeTab !== 'sobrantes'">
                {{ filtro ? item.total - item.entra : calculateExisted(item)}}
              </td>
              <td class="px-6 py-4" v-if="activeTab !== 'gastos' && activeTab !== 'sobrantes'">
                <input
                  v-model.number="item.entra"
                  v-if="!filtro"
                  type="number"
                  min="0"
                  placeholder="0"
                  class="w-20 rounded-md border-gray-300 shadow-sm text-center"
                  @input="calculateTotals(item)"
                />
                <div v-else>
                  {{ filtro ? item.entra : 0 }}
                </div>
              </td>
              <td class="px-6 py-4 font-medium" v-if="activeTab !== 'gastos' && activeTab !== 'sobrantes'">
                {{ filtro ? item.total : calculateTotal(item) }}
              </td>
              <td class="px-6 py-4" v-if="showVendidos && activeTab !== 'sobrantes'">
                {{ filtro ? item.vende : getVendidos(item.id) }}
              </td>
              <td class="px-6 py-4" v-if="activeTab !== 'gastos'">
                {{ filtro ? item.sobra : calculateSobra(item) }}
              </td>
              <td class="px-6 py-4" v-if="activeTab === 'gastos' && activeTab !== 'sobrantes'">
                <span
                  class="w-fit rounded-md border-gray-300 shadow-sm text-center"
                >{{ item.costo }} </span>
              </td>
        </tr>
      </tbody>
    </table>
  </div>
</div>
</template>


<script setup>
import { ref, reactive, computed } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import Swal from 'sweetalert2'
import { XIcon } from 'lucide-vue-next';

const { props } = usePage();
const showToast = (icon, title) => {
  Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    customClass: "no-print",
    didOpen: (toast) => {
      toast.addEventListener('mouseenter', Swal.stopTimer)
      toast.addEventListener('mouseleave', Swal.resumeTimer)
    }
  }).fire({
    icon,
    title
  })
}
const selectedDate = ref(null)
const selectedWeek = ref(null)
const selectedMonth = ref(null)

const selectedFilter = ref('day');
const inventario = ref(props.inventario);
const registros = ref(props.registros)
const gastos = ref(props.gastos || []);
const categorias = ref(props.categorias);

const activeTab = ref('bebida');
const isSaving = ref(false);
const isAlmacen = ref(props.user.roles[0] === 'almacen');
const filtro = ref(false)

const clearFilter = () => {
  selectedDate.value = null,
  selectedWeek.value = null,
  selectedMonth.value = null,
  filtro.value = false,
  router.get('/inventario', {}, {
    preserveScroll: true,

  })
}

const searchFilter = () =>{
  let filter = selectedFilter.value
  let value = null

  if (filter === 'day') {
    value = selectedDate.value
  } else if (filter === 'week') {
    value = selectedWeek.value
  } else if (filter === 'month') {
    value = selectedMonth.value
  }
  if(!value){
    showToast("error", "Debes seleccionar una fecha");
    return
  }

  router.post('/inventario/filtro', {
    filter: filter,
    value: value
  }, {
    preserveScroll: true,
    onSuccess(response) {
      console.log("Respuesta completa del servidor:", response.props);
      registros.value = response.props.registros
      inventario.value = response.props.inventario
      gastos.value = response.props.gastos

      const registrosMap = new Map(
        response.props.registros.map(reg => [reg.inventario_id, reg])
      );

      products.value = inventario.value
        .filter(item => item.nombre !== '-')
        .map(item => {
          const registro = registrosMap.get(item.id) || {}; // Buscar por ID del inventario
          return {
            ...item,
            existe: registro.existe || 0,
            entra: registro.entra || 0,
            total: registro.total || 0,
            vende: registro.vende || 0,
            sobra: registro.sobra || 0,
            costo: registro.precio || item?.costo || 0, // Ajustado para tomar el precio correcto
          };
        });

      filtro.value = true
      
      showToast("success", "Filtro actualizado correctamente");
    },
    onError(e) {
      console.error("Error al obtener datos:", e); // Agrega este log
      showToast("error", e.props.flash.error || "Error al obtener datos con este filtro");
      error.value = 'Ocurrió un error al obtener los datos. Inténtalo de nuevo.'
      isLoading.value = false
    }
  })
}

const filteredProducts = computed(() => {
  console.log("Filtrando productos con activeTab:", activeTab.value); // Agrega este log
  if (activeTab.value === 'gastos') {
    return gastos.value;
  }
  if (activeTab.value === 'pastes' ||  activeTab.value === 'sobrantes') {
    return products?.value?.filter(item =>
      item.tipo === 'pastes' ||
      item.tipo === 'empanadas saladas' ||
      item.tipo === 'empanadas dulces'
    );
  }
  return products?.value?.filter(item => item.tipo === activeTab.value);
});

const filtrarPorPestaña = (tab) => {
  if (tab === 'gastos') {
    return gastos.value;
  }
  if (tab === 'pastes' ||  tab === 'sobrantes') {
    return products?.value?.filter(item =>
      item.tipo === 'pastes' ||
      item.tipo === 'empanadas saladas' ||
      item.tipo === 'empanadas dulces'
    );
  }
  return products?.value?.filter(item => item.tipo === activeTab.value);
};



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
  ...categorias.value
    .filter(categoria => categoria.tipo !== 'empanadas saladas' && categoria.tipo !== 'empanadas dulces')
    .map(categoria => (
      categoria.tipo === 'masa' 
        ? { name: 'Tortillas y Bolas', id: categoria.tipo } 
        : categoria.tipo === 'pastes' 
        ? { name: 'Pastes / Empanadas', id: categoria.tipo } 
        : { id: categoria.tipo, name: categoria.tipo }
    )),
  ...(!isAlmacen.value 
    ? [
        { id: 'gastos', name: 'Gastos generales' },
        { id: 'sobrantes', name: 'Sobrantes' }
      ]
    : []
  )
];



const products = ref(
  inventario.value
    .filter(item => item.nombre !== '-') // Filtrar los elementos que cumplen con la condición
    .map(item => ({
      ...item,
      existe: registros.value[item.id - 1]?.existe || item?.existe || 0,
      entra: 0,
      total: null,
      sobra: registros.value[item.id - 1]?.existe,
      costo: item?.costo || 0,
    }))
);




const showVendidos = computed(() => 
  activeTab.value === 'bebida' || activeTab.value === 'extras' || activeTab.value === 'pastes'
);





const getVendidos = (productoId) => {
  
  const venta = props.ventas.find(v => v.producto_id === productoId);
  
  return venta ? venta.total_vendido : 0;
}

const calculateTotal = (item) => {
  if (filtro.value) return item.total;
  return (calculateExisted(item)) + (getVendidos(item) || 0) + (item.entra);
}

const calculateSobra = (item) => {
  if (filtro.value) return item.sobra;
  return calculateTotal(item) - getVendidos(item.id) ;
}


const calculateTotals = (item) => {

  item.total = (item.existe || 0) + (item.entra || 0);
  const vendidos = getVendidos(item.id);
  item.sobra = item.total - vendidos;
}

function calculateExisted(item) {

  const vendidos = getVendidos(item.id);
  if(filtro.value) return (item.existe + Number(vendidos))
  else return ((item.cantidad) + Number(vendidos)); 
}

async function saveGastos() {
  try {
    isSaving.value = true;
    
    const formattedData = {
      gastos: gastos.value.map(item => ({
        id: item.id || null,
        sucursal_id: item.sucursal_id || 0,
        nombre: item.nombre,
        costo: item.costo || 0,
      }))
    };
    router.post('/gastos', formattedData, {
      
      preserveScroll: true,
      preserveState: false,
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
        existe: item.existe || 0,
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

const printAllTabs = () => {
  window.print();
};


</script>
<style scoped>
  /* Ocultar el contenedor de impresión en pantalla */
  .print-all-tabs {
    display: none;
  }

  /* Mostrar el contenedor de impresión y ocultar elementos innecesarios al imprimir */
  @media print {
    .print-all-tabs {
      display: block;
    }
    .normal-view,
    .no-print {
      display: none !important;
    }
  }
</style>
