<template>
  <div class="relative px-10  py-10 bg-white shadow-lg sm:rounded-3xl sm:p-20 ">
    <h1 class="text-3xl font-semibold mb-6 text-center">Gestión de Personal</h1>

    <!-- Navegación de Tabs -->
    <div class="mb-6 flex justify-center">
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

    <div class="flex gap-5 flex-col sm:flex-row">
      <!-- Formulario de Usuarios o Sucursales -->
      <form @submit.prevent="handleSubmit" class="space-y-4 w-full sm:w-1/2">
        <h2 class="text-xl font-semibold mb-4">
          {{ form.id ? 'Editar' : 'Agregar' }} {{ singularTabTitle }}
        </h2>

        <!-- Campos del formulario -->
        <div v-if="currentTab === 'users'">
          <input v-model="form.name" placeholder="Nombre" class="w-full px-3 py-2 border rounded">
          <span v-if="errors.name" class="text-red-500 text-sm">{{ errors.name }}</span>

          <input v-model="form.email" type="text" placeholder="Matricula" class="mt-2 w-full px-3 py-2 border rounded">
          <span v-if="errors.email" class="text-red-500 text-sm">{{ errors.email }}</span>

          <input v-model="form.password" type="password" placeholder="Contraseña" class="mt-2 w-full px-3 py-2 border rounded">
          <span v-if="errors.password" class="text-red-500 text-sm">{{ errors.password }}</span>

          <select v-model="form.sucursal_id" class="mt-2 w-full px-3 py-2 border rounded">
            <option value="">Seleccionar Sucursal</option>
            <option v-for="sucursal in sucursales" :key="sucursal.id" :value="sucursal.id">
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
          <span v-if="errors.role" class="text-red-500 text-sm">{{ errors.role }}</span>

          <!-- error -->
        </div>

        <div v-else-if="currentTab === 'sucursales'" class="flex flex-col">
          <input v-model="form.nombre" placeholder="Nombre de Sucursal" class="w-full px-3 py-2 border rounded">
          <span v-if="errors.nombre" class="text-red-500 text-sm">{{ errors.nombre }}</span>

          <input v-model="form.direccion" placeholder="Dirección" class="mt-2 w-full px-3 py-2 border rounded">
          <span v-if="errors.direccion" class="text-red-500 text-sm">{{ errors.direccion }}</span>

          <input v-model="form.telefono" placeholder="Teléfono" class="mt-2 w-full px-3 py-2 border rounded">
          

          <label for="credenciales" class="pt-4 font-semibold">Credenciales de inicio de sesión</label>
          <input v-model="form.email" placeholder="Matricula" class="mt-2 w-full px-3 py-2 border rounded">
          <span v-if="errors.email" class="text-red-500 text-sm">{{ errors.email }}</span>

          <input v-model="form.password" placeholder="Contraseña" class="mt-2 w-full px-3 py-2 border rounded">
          <span v-if="errors.password" class="text-red-500 text-sm">{{ errors.password }}</span>

        </div>

        <!-- Botones de acción -->
        <div class="flex justify-end">
          <button type="submit" class="bg-orange-500 text-white px-4 py-2 rounded hover:bg-orange-600 transition-colors duration-200 ease-in-out">
            {{ form.id ? 'Actualizar' : 'Agregar' }}
          </button>
          <button v-if="form.id" @click="resetForm" type="button" class="ml-2 bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400 transition-colors duration-200 ease-in-out">
            Cancelar
          </button>
        </div>
      </form>

      <!-- Listado de Usuarios o Sucursales -->
      <div class="mb-8 w-full sm:w-1/2">
        <h2 class="text-xl font-semibold mb-4">{{ tabTitle }}</h2>
        <div class="space-y-4">
          <div 
            v-for="item in currentItems" 
            :key="item.id" 
            class="bg-gray-50 p-4 rounded-lg shadow hover:shadow-md transition-shadow duration-200 ease-in-out"
          >
            <div class="flex justify-between items-center">
              <div>
                <h3 class="font-semibold">Nombre: {{ itemName(item) }}</h3>
                <p v-if="currentTab === 'users'" class="text-sm text-gray-600">
                  Matricula: {{ item.email }} <br> Rol: {{ item.roles[0] || 'Sin rol' }}
                </p>
                <div v-if="currentTab === 'sucursales'" class="text-sm text-gray-600 flex justify-between ">
                  <p>
                    {{ item.direccion }} - {{ item.telefono || 'Sin teléfono' }}
                    <br>
                    Credenciales
                    <br>
                    Matricula: {{ getSucursalSession(item.id)?.email }}
                  </p>
                </div>
              </div>
              <div>
                <button @click="editItem(item, item.id)" class="text-orange-500 hover:text-orange-700 mr-2">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                  </svg>
                </button>
                
                <button @click="deleteItem(item.id)" class="text-red-500 hover:text-red-700">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import Swal from 'sweetalert2'

const { props } = usePage();

console.log(props)

const sucursales = ref(props.sucursales);

const tabs = [
  { label: 'Usuarios', value: 'users' },
  { label: 'Sucursales', value: 'sucursales' },
];

const currentTab = ref('users');

const form = ref({
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
});

const tabTitle = computed(() => currentTab.value === 'users' ? 'Usuarios' : 'Sucursales');
const singularTabTitle = computed(() => currentTab.value === 'users' ? 'Usuario' : 'Sucursal');
const errors = ref({}); 
const currentItems = computed(() => currentTab.value === 'users' ? props.users : props.sucursales);

const availableRoles = computed(() => props.roles.filter(role => ['admin', 'trabajador'].includes(role.name)));

const itemName = (item) => item.name || item.email || item.nombre;

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
      // Actualizar la lista de items si es necesario
      if (currentTab.value === 'users') {
        props.users = response.props.users;
      } else {
        props.sucursales = response.props.sucursales;
        props.sucursalSession = response.props.sucursalSession;
      }
      resetForm();
    },
    onError: (error) => {
      Toast.fire({
        icon: "error",
        title: "Hubo un problema al procesar la solicitud"
      });
      console.log(error)
      errors.value = error
      console.log(errors.value)
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

const editItem = (item, id) => {
  form.value = {
    id: item.id,
    id_user: getSucursalSession(id)?.id || null,
    name: item.name || '',
    email: item.email || getSucursalSession(id)?.email ||  '',
    sucursal_id: item.sucursal_id || '',
    role: item.roles?.[0] || '',
    nombre: item.nombre || '',
    direccion: item.direccion || '',
    telefono: item.telefono || '',
    password: '' // Normalmente no se rellena la contraseña al editar
  };
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
          console.log("Eliminado el id "+ currentTab.value +" - "+id)
          Toast.fire({
            icon: "success",
            title: "Eliminado correctamente"
          });
          // Actualizar la lista de items
          if (currentTab.value === 'users') {
            props.users = response.props.users;
          } else {
            props.sucursales = response.props.sucursales;
            props.sucursalSession = response.props.sucursalSession;
          }
          errors.value = {}
          console.log(response)
        },
        onError: (error) => {
          console.log("Error eliminado el id "+ currentTab.value +" - "+id)
          Toast.fire({
            icon: "error",
            title: errors.value = error.error || "Hubo un problema al eliminar el elemento"
          });
          errors.value = error;

          console.log(error)
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