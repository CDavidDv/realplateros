<template>
  <div class="relative px-10 py-10 bg-white shadow-lg sm:rounded-3xl sm:p-20"
  v-if="props.user.roles[0] !== 'supervisor'"
  >
    <h1 class="text-3xl font-semibold mb-6 text-center">Gestión de Personal</h1>

    <!-- Botón Actividad Personal (solo admin) -->
    <div class="flex justify-center mb-6" v-if="esAdmin">
      <a
        href="/actividad-personal"
        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200 shadow-md"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
          <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
          <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd" />
        </svg>
        Ver Actividad del Personal
      </a>
    </div>

    <!--Notificaciones de contrato-->
    <div>
      <div class="container my-10 grid gap-2" v-if="contratosVencidos.length > 0 || contratosPorVencer.length > 0">
      <CardContratos 
        :contratos="contratosPorVencer" 
        v-if="contratosPorVencer.length > 0"
        title="Contratos por Vencer"  
        color="yellow"
      />
      <CardContratos 
        :contratos="contratosVencidos" 
        v-if="contratosVencidos.length > 0"
        title="Contratos Vencidos"  
        color="red"
      />
      </div>

    </div>


    <!-- Navegación de Tabs -->
    <div class="mb-6 flex justify-center">
      <!--Que si es almacen solo poder agregar trabajadores-->
      <button 
        v-for="tab in tabs" 
        :key="tab.value"
        @click="changeTab(tab.value)"
        :class="[
          'px-4 py-2 mx-1 rounded-full transition-colors duration-200 ease-in-out', 
          currentTab === tab.value ? 'bg-orange-500 text-white' : 'bg-gray-200 hover:bg-gray-300'
        ]"
      >
        {{ tab.label }}
      </button>
    </div>

    <!-- Botón para abrir el modal -->
    <div class="flex justify-end mb-4">
      <button 
        @click="openModal" 
        class="bg-orange-500 text-white px-4 py-2 rounded hover:bg-orange-600 transition-colors duration-200 ease-in-out"
      >
        Agregar {{ singularTabTitle }}
      </button>
    </div>

    <!-- Tabla de Usuarios o Sucursales -->
    <div class="mb-8 overflow-auto">
      <h2 class="text-xl font-semibold mb-4">{{ tabTitle }}</h2>
      <table class="min-w-full bg-white border border-gray-200">
        <thead>
          <tr>
            <th v-if="currentTab === 'users'" class="px-4 py-2 border-b">Nombre</th>
            <th v-if="currentTab === 'users'" class="px-4 py-2 border-b">Apellido P.</th>
            <th v-if="currentTab === 'users'" class="px-4 py-2 border-b">Apellido M.</th>
            <th v-if="currentTab === 'users'" class="px-4 py-2 border-b">Telefono</th>
            <th v-if="currentTab === 'users'" class="px-4 py-2 border-b">Usuario</th>
            <th v-if="currentTab === 'users'" class="px-4 py-2 border-b">Inicio Contrato</th>
            <th v-if="currentTab === 'users'" class="px-4 py-2 border-b">Fin Contrato</th>
            <th v-if="currentTab === 'users'" class="px-4 py-2 border-b">Sucursal</th>
            <th v-if="currentTab === 'users'" class="px-4 py-2 border-b">Activo</th>
            <th v-if="currentTab === 'users'" class="px-4 py-2 border-b">Rol</th>
            <th v-if="currentTab === 'sucursales'" class="px-4 py-2 border-b">Nombre de Sucursal</th>
            <th v-if="currentTab === 'sucursales'" class="px-4 py-2 border-b">Dirección</th>
            <th v-if="currentTab === 'sucursales'" class="px-4 py-2 border-b">Teléfono</th>
            <th class="px-4 py-2 border-b">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in currentItems" :key="item.id" class="hover:bg-gray-50">
            <td v-if="currentTab === 'users'" class="px-4 py-2 border-b">{{ item.name }}</td>
            <td v-if="currentTab === 'users'" class="px-4 py-2 border-b">{{ item.apellido_p }}</td>
            <td v-if="currentTab === 'users'" class="px-4 py-2 border-b">{{ item.apellido_m }}</td>
            <td v-if="currentTab === 'users'" class="px-4 py-2 border-b">{{ item.tel }}</td>
            <td v-if="currentTab === 'users'" class="px-4 py-2 border-b">{{ item.email }}</td>
            <td v-if="currentTab === 'users'" class="px-4 py-2 border-b">{{ item.inicio_contrato }}</td>
            <td v-if="currentTab === 'users'" class="px-4 py-2 border-b">{{ item.fin_contrato }}</td>
            <td v-if="currentTab === 'users'" class="px-4 py-2 border-b">{{ item.sucursal }}</td>
            <!--if active pill green-->
            <td v-if="currentTab === 'users'" class="px-4 py-2 border-b">
              <span v-if="item.active" class="bg-green-500 text-white px-2 py-1 rounded-full">Activo</span>
              <span v-else class="bg-red-500 text-white px-2 py-1 rounded-full">Inactivo</span>
            </td>
            
            <td v-if="currentTab === 'users'" class="px-4 py-2 border-b">{{ item.roles[0] || 'Sin rol' }}</td>
            <td v-if="currentTab === 'sucursales'" class="px-4 py-2 border-b">{{ item.nombre }}</td>
            <td v-if="currentTab === 'sucursales'" class="px-4 py-2 border-b">{{ item.direccion }}</td>
            <td v-if="currentTab === 'sucursales'" class="px-4 py-2 border-b">{{ item.telefono || 'Sin teléfono' }}</td>
            <td class="px-4 py-2 border-b">
              <button @click="editItem(item)" class="text-orange-500 hover:text-orange-700 mr-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                  <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                </svg>
              </button>
              <button @click="deleteItem(item.id)" class="text-red-500 hover:text-red-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal para agregar/editar -->
    <div v-if="isModalOpen" class="fixed inset-0 bg-black w-full bg-opacity-50 flex items-center justify-center">
      <div class="bg-white p-6 rounded-lg md:w-1/2 w-full m-5">
        <h2 class="text-xl font-semibold mb-4">
          {{ form.id ? 'Editar' : 'Agregar' }} {{ singularTabTitle }}
        </h2>
        <form @submit.prevent="handleSubmit" class="space-y-4">
          <div v-if="currentTab === 'users'">
            <input v-model="form.name" placeholder="Nombre" class="w-full px-3 py-2 border rounded">
            <span v-if="errors.name" class="text-red-500 text-sm">{{ errors.name }}</span>

            <input v-model="form.apellido_p" placeholder="Apellido Paterno" class="mt-2 w-full px-3 py-2 border rounded">
            <span v-if="errors.apellido_p" class="text-red-500 text-sm">{{ errors.apellido_p }}</span>

            <input v-model="form.apellido_m" placeholder="Apellido Materno" class="mt-2 w-full px-3 py-2 border rounded">
            <span v-if="errors.apellido_m" class="text-red-500 text-sm">{{ errors.apellido_m }}</span>

            <input v-model="form.tel" placeholder="Teléfono" class="w-full px-3 py-2 border mt-2 rounded">
            <span v-if="errors.tel" class="text-red-500 text-sm">{{ errors.tel }}</span>
            <!--Inicio y fin contrato -->
            <div class="flex w-full space-x-4 mt-2">
              <div class="w-full">
                <label for="inicio_contrato" class="block text-sm font-medium text-gray-700">Inicio de Contrato</label>
                <input v-model="form.inicio_contrato" type="date" id="inicio_contrato" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                <span v-if="errors.inicio_contrato" class="text-red-500 text-sm">{{ errors.inicio_contrato }}</span>
              </div>
              <div class="w-full">
                <label for="fin_contrato" class="block text-sm font-medium text-gray-700">Fin de Contrato</label>
                <input v-model="form.fin_contrato" type="date" id="fin_contrato" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                <span v-if="errors.fin_contrato" class="text-red-500 text-sm">{{ errors.fin_contrato }}</span>
              </div>
            </div>
            <!--activo-->
            
            <input v-model="form.email" type="text" placeholder="Matricula" class="mt-2 w-full px-3 py-2 border rounded">
            <span v-if="errors.email" class="text-red-500 text-sm">{{ errors.email }}</span>

            <input v-model="form.password" type="password" placeholder="Contraseña" class="mt-2 w-full px-3 py-2 border rounded">
            <span v-if="errors.password" class="text-red-500 text-sm">{{ errors.password }}</span>

            <select v-model="form.sucursal_id" class="mt-2 w-full px-3 py-2 border rounded">
              <option value="">Seleccionar Sucursal</option>
              <option v-for="sucursal in availableSucursales" :key="sucursal.id" :value="sucursal.id">
                {{ sucursal.nombre }}
              </option>
            </select>
            <span v-if="errors.sucursal_id" class="text-red-500 text-sm">{{ errors.sucursal_id }}</span>

            <select v-model="form.role" class="mt-2 w-full px-3 py-2 border rounded">
              <option value="">Seleccionar Rol</option>
              <option v-for="role in availableRoles" :key="role.id" :value="role.name">
                {{ role.name }}
              </option>
            </select>
            <div class="flex items-center mt-2">
              <input v-model="form.active" v-bind:true-value="1" v-bind:false-value="0" type="checkbox" id="active" class="form-checkbox h-5 w-5 text-orange-500">
              <label for="active" class="ml-2 text-sm font-medium text-gray-700">Activo</label>
            </div>
            <span v-if="errors.role" class="text-red-500 text-sm">{{ errors.role }}</span>
          </div>

          <div v-else-if="currentTab === 'sucursales'" class="flex flex-col">
            <input v-model="form.nombre" placeholder="Nombre de Sucursal" class="w-full px-3 py-2 border rounded">
            <span v-if="errors.nombre" class="text-red-500 text-sm">{{ errors.nombre }}</span>

            <input v-model="form.direccion" placeholder="Dirección" class="mt-2 w-full px-3 py-2 border rounded">
            <span v-if="errors.direccion" class="text-red-500 text-sm">{{ errors.direccion }}</span>

            <input v-model="form.telefono" placeholder="Teléfono" class="mt-2 w-full px-3 py-2 border rounded">
            <span v-if="errors.telefono" class="text-red-500 text-sm">{{ errors.telefono }}</span>

            <label for="credenciales" class="pt-4 font-semibold">Credenciales de inicio de sesión</label>
            <input v-model="form.email" placeholder="Matricula" class="mt-2 w-full px-3 py-2 border rounded">
            <span v-if="errors.email" class="text-red-500 text-sm">{{ errors.email }}</span>

            <input v-model="form.password" placeholder="Contraseña" class="mt-2 w-full px-3 py-2 border rounded">
            <span v-if="errors.password" class="text-red-500 text-sm">{{ errors.password }}</span>
          </div>

          <div class="flex justify-end">
            <button type="submit" class="bg-orange-500 text-white px-4 py-2 rounded hover:bg-orange-600 transition-colors duration-200 ease-in-out">
              {{ form.id ? 'Actualizar' : 'Agregar' }}
            </button>
            <button @click="closeModal" type="button" class="ml-2 bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400 transition-colors duration-200 ease-in-out">
              Cancelar
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import { AlertCircle } from 'lucide-vue-next';
import CardContratos from './CardContratos.vue';

const { props } = usePage();

const sucursales = ref(props.sucursales);
const isModalOpen = ref(false);

// Si es almacén solo ver tab usuarios
const tabs = computed(() =>
  props.auth?.user?.es_almacen
    ? [{ label: 'Usuarios', value: 'users' }]
    : [
        { label: 'Usuarios', value: 'users' },
        { label: 'Sucursales', value: 'sucursales' },
      ]
);


const currentTab = ref('users');

const form = ref({
  id: null,
  id_user: null,
  name: '',
  apellido_p: '',
  apellido_m: '',
  inicio_contrato: '',
  fin_contrato: '',
  email: '',
  password: '',
  sucursal_id: '',
  role: '',
  nombre: '',
  direccion: '',
  telefono: '',
  active: false
});

const tabTitle = computed(() => currentTab.value === 'users' ? 'Usuarios' : 'Sucursales');
const singularTabTitle = computed(() => currentTab.value === 'users' ? 'Usuario' : 'Sucursal');
const errors = ref({}); 
const currentItems = computed(() => currentTab.value === 'users' ? props.users : props.sucursales.filter(sucursal => sucursal.id !== 0));

// Si es almacén solo mostrar roles admin y trabajador
const availableRoles = computed(() => {
  const allowedRoles = props.auth?.user?.es_almacen
    ? ['admin', 'trabajador']
    : ['admin', 'trabajador', 'supervisor'];
  return props.roles.filter(role => allowedRoles.includes(role.name));
});

// Si es almacén solo mostrar sucursal de almacén (id = 0)
const availableSucursales = computed(() => {
  if (props.auth?.user?.es_almacen) {
    return props.sucursales.filter(s => s.id === 0);
  }
  return props.sucursales;
});

// Verificar si el usuario es admin
const esAdmin = computed(() => {
  const roles = props.user?.roles || [];
  return roles.includes('admin') || roles[0] === 'admin';
});

const itemName = (item) => item.name || item.email || item.nombre;

const contratosVencidos = computed(() => {
  const today = new Date();
  return props.users.filter(user => {
    if (user.fin_contrato === null) return false;
    
    const endDate = new Date(user.fin_contrato);
    if (isNaN(endDate)) return false;

    return endDate < today; // Contrato ya vencido
  });
});




const contratosPorVencer = computed(() => {
  const today = new Date();
  const cincoDiasEnMilisegundos = 5 * 24 * 60 * 60 * 1000;

  return props.users.filter(user => {
    if (user.fin_contrato === null) return false;
    if (user.active === 0) return false;
    
    const endDate = new Date(user.fin_contrato);
    if (isNaN(endDate)) return false;

    const diferenciaEnMilisegundos = endDate - today;
    return diferenciaEnMilisegundos > 0 && diferenciaEnMilisegundos <= cincoDiasEnMilisegundos;
  });
});


const getSucursalSession = (id) => {
  if (!props.sucursales || !props.sucursalSession) {
    return null;
  }
  const sucursalSeleccionada = props.sucursales.find(sucursal => sucursal.id === id);

  if (sucursalSeleccionada) {
    const sesionSucursal = props.sucursalSession.find(session => session.sucursal_id === sucursalSeleccionada.id);
    
    if (sesionSucursal) {
      return sesionSucursal;
    }
  }
  
  return null;
};

const openModal = () => {
  // Si es usuario de almacén, pre-seleccionar sucursal almacén (id=0)
  if (props.auth?.user?.es_almacen && currentTab.value === 'users') {
    form.value.sucursal_id = 0;
  }
  isModalOpen.value = true;
};

const closeModal = () => {
  isModalOpen.value = false;
  resetForm();
};

const handleSubmit = () => {
  const isUpdate = !!form.value.id;
  
  const url = isUpdate 
    ? `/${currentTab.value}/${form.value.id}`
    : `/${currentTab.value}`;
  
  const method = isUpdate ? 'put' : 'post';

  router[method](url, form.value, {
    preserveScroll: true,
    onSuccess: (response) => {
      Toast.fire({
        icon: "success",
        title: isUpdate ? "Actualizado correctamente" : "Agregado correctamente"
      });
      if (currentTab.value === 'users') {
        props.users = response.props.users;
      } else {
        props.sucursales = response.props.sucursales;
        props.sucursalSession = response.props.sucursalSession;
      }
      closeModal();
    },
    onError: (error) => {
      Toast.fire({
        icon: "error",
        title: "Hubo un problema al procesar la solicitud"
      });
      errors.value = error;
    }
  });
};

const changeTab = (tab) => {
  currentTab.value = tab;
  resetForm();
};

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

const editItem = (item) => {
  form.value = {
    id: item.id,
    id_user: getSucursalSession(item.id)?.id || null,
    name: item.name || '',
    apellido_p: item.apellido_p || '',
    apellido_m: item.apellido_m || '',
    tel: item.tel || '',
    email: item.email || getSucursalSession(item.id)?.email ||  '',
    sucursal_id: item.sucursal_id || '',
    role: item.roles?.[0] || '',
    nombre: item.nombre || '',
    direccion: item.direccion || '',
    telefono: item.telefono || '',
    inicio_contrato: item.inicio_contrato || '',
    fin_contrato: item.fin_contrato || '',
    active: item.active || false,
    password: '' // Normalmente no se rellena la contraseña al editar
  };
  openModal();
};

const deleteItem = (id) => {
  Swal.fire({
    title: '¿Estás seguro?',
    text: "¡No podrás revertir esta acción!",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Sí, eliminarlo',
    cancelButtonText: 'Cancelar'
  }).then((result) => {
    if (result.isConfirmed) {
      router.delete(`/${currentTab.value}/${id}`, {
        preserveScroll: true,
        onSuccess: (response) => {
          Toast.fire({
            icon: "success",
            title: "Eliminado correctamente"
          });
          if (currentTab.value === 'users') {
            props.users = response.props.users;
          } else {
            props.sucursales = response.props.sucursales;
            props.sucursalSession = response.props.sucursalSession;
          }
          errors.value = {};
        },
        onError: (error) => {
          Toast.fire({
            icon: "error",
            title: errors.value = error.error || "Hubo un problema al eliminar el elemento"
          });
          errors.value = error;
        }
      });
    }
  });
};

const resetForm = () => {
  form.value = {
    id: null,
    id_user: null,
    name: '',
    email: '',
    password: '',
    sucursal_id: '',
    role: '',
    nombre: '',
    direccion: '',
    telefono: '',
  };
  errors.value = {};
};
</script>

