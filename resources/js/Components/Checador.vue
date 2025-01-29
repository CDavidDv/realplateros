<template>
    <div class="min-h-max h-[80vh] gap-5 m-auto md:flex-row flex flex-col items-center p-4 md:p-8 justify-center">
        <div class="flex flex-col w-full">
            <div class="w-full flex gap-5 flex-col md:flex-row items-start">
                <div class="md:w-4/12 h-fit w-full border p-10 rounded-xl shadow-xl">
                    <div>
                        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                            Registro de Entrada/Salida
                        </h2>
                    </div>

                    <!-- Formulario de Check-in/Check-out -->
                    <form class="mt-8 space-y-6" @submit.prevent="handleSubmit">
                        <div class="rounded-md shadow-sm -space-y-px">
                            <div>
                                <label for="email" class="sr-only">Matricula de Usuario</label>
                                <input id="email" name="email" type="text" required
                                autocomplete="username"
                                    class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-orange-500 focus:border-orange-500 focus:z-10 sm:text-sm"
                                    placeholder="Matricula de Usuario" v-model="form.email" />
                            </div>
                            <div>
                                <label for="password" class="sr-only">Contraseña</label>
                                <input id="password" name="password" type="password" required
                                    autocomplete="current-password"
                                    class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-orange-500 focus:border-orange-500 focus:z-10 sm:text-sm"
                                    placeholder="Contraseña" v-model="form.password" />
                            </div>
                        </div>

                        <div>
                            <button type="submit"
                                class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                                Registrar
                            </button>
                        </div>
                    </form>

                    <!-- Mensajes de éxito o error -->
                    <div v-if="successMessage" class="mt-4 text-sm text-green-600">
                        {{ successMessage }}
                    </div>
                    <div v-if="errorMessage" class="mt-4 text-sm text-red-600">
                        {{ errorMessage }}
                    </div>
                </div>

                <div class="md:w-8/12 w-full h-fit" v-if="$page.props.user.roles[0].name !== 'trabajador'">
                    <!-- Formulario de búsqueda -->
                    <form class="mb-4 p-4 border rounded-xl shadow-md" @submit.prevent="handleSearch">
                        <div class="flex md:space-x-4 flex-col md:flex-row">
                            <div class="flex-1 p-2 md:p-0">
                                <label for="startDate" class="block text-sm font-medium text-gray-700">Fecha de Inicio</label>
                                <input id="startDate" type="date" v-model="searchForm.startDate"
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm" />
                            </div>
                            <div class="flex-1 p-2 md:p-0">
                                <label for="endDate" class="block text-sm font-medium text-gray-700">Fecha de Fin</label>
                                <input id="endDate" type="date" v-model="searchForm.endDate"
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm" />
                            </div>
                            <div class="flex-1 flex items-end p-2 md:p-0">
                                <button type="submit"
                                    class="w-full py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                                    Buscar
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Tabla de registros -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        ID
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Nombre
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Estado
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Entrada
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Salida
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Horas Trabajadas
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="trabajador in trabajadores" :key="trabajador.id">
                                    <td class="px-6 py-4 whitespace-nowrap">{{ trabajador.user.email }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ trabajador.user.name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            :class="(trabajador.estado || 'Ausente') === 'Presente' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                            {{ (trabajador.estado) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ trabajador.check_in?.length > 0 ? formatTime(trabajador.check_in) : "-" }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ trabajador.check_out?.length > 0 && trabajador.check_out ?
                                            formatTime(trabajador.check_out) : "-" }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ trabajador.check_out?.length > 0 ?
                                            (trabajador.horas_trabajadas / 60).toFixed(2) : "-" }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div v-if="$page.props.auth.user.roles[0].name != 'trabajador'" class="min-h-max w-full gap-5 m-auto md:flex-row flex flex-col items-center p-4 md:p-8 justify-center">
        <div class="w-full">
            <h2 class="text-center text-2xl font-bold text-gray-900 mb-6">
                Buscar Registro de Usuario
            </h2>
            
            <!-- Formulario de búsqueda por usuario y día -->
             <div class="flex flex-col items-center  justify-center ">
                 <form class="space-y-6 w-1/2 " @submit.prevent="handleUserSearch">
                     <div>
                         <label for="userEmail" class="block text-sm font-medium text-gray-700">Matricula del Usuario</label>
                         <input id="userEmail" type="text" v-model="userSearchForm.email" required
                             class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500"
                             placeholder="Matricula del usuario" />
                     </div>
                     <div>
                         <label for="searchDate" class="block text-sm font-medium text-gray-700">Fecha</label>
                         <input id="searchDate" type="date" v-model="userSearchForm.searchDate" required
                             class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500" />
                     </div>
                     <div>
                         <button type="submit"
                             class="w-full py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                             Buscar
                         </button>
                     </div>
                 </form>
                 <!-- Mensajes de éxito o error -->
                 <div v-if="userSearchSuccessMessage" class="mt-4 text-sm text-green-600">
                     {{ userSearchSuccessMessage }}
                 </div>
                 <div v-if="userSearchErrorMessage" class="mt-4 text-sm text-red-600">
                     {{ userSearchErrorMessage }}
                 </div>
             </div>


            <!-- Resultados de la búsqueda -->
            <div v-if="userSearchResults" class="mt-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Resultados de la Búsqueda</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    ID
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Sucursal
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Nombre
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Apellido P.
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Apellido M.
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Estado
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Entrada
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Salida
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Horas Trabajadas
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="result in userSearchResults" :key="result.id">
                                <td class="px-6 py-4 whitespace-nowrap">{{ result?.user?.email }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ result?.sucursal?.nombre }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ result?.user?.name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ result?.sucursal?.apellido_p }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ result?.sucursal?.apellido_m }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="(result?.estado || 'Ausente') === 'Presente' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                        {{ (result?.estado) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {{ result.check_in?.length > 0 ? formatTime(result.check_in) : "-" }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {{ result.check_out?.length > 0 && result.check_out ? formatTime(result.check_out) : "-" }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {{ result.check_out?.length > 0 ? (result.horas_trabajadas / 60).toFixed(2) : "-" }}
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
import { router, useForm, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { ref, onMounted } from 'vue';

const { props } = usePage();

const form = useForm({
    email: '',
    password: '',
    sucursal_id: props.auth.user.sucursal_id
});

const userSearchForm = ref({
    email: '',
    searchDate: ''
});

const userSearchErrorMessage = ref('');
const userSearchSuccessMessage = ref('')

const userSearchResults = ref([]);

const handleUserSearch = () => {
    axios.post('/search-user-check-ins', userSearchForm.value)
        .then(response => {
            userSearchResults.value = response.data.checkIns;
            userSearchSuccessMessage.value = 'Resultados de la búsqueda obtenidos con éxito.';
            userSearchErrorMessage.value = '';
        })
        .catch(error => {
            userSearchErrorMessage.value = `Error al obtener los resultados de la búsqueda. ${error}` ;
            userSearchSuccessMessage.value = '';
        });
};

const searchForm = useForm({
    startDate: '',
    endDate: '',
});

const trabajadores = ref(props.checkIns);
const successMessage = ref('');
const errorMessage = ref('');

onMounted(() => {
    if (props.filters) {
        searchForm.startDate = props.filters.startDate || '';
        searchForm.endDate = props.filters.endDate || '';
    }
});

const formatTime = (time) => {
    if (!time) return '-';
    return new Date(time).toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });
};

const handleSubmit = () => {
    router.post('/checkInOut', { email: form.email, password: form.password, sucursal_id: form.sucursal_id }, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: (page) => {
            successMessage.value = page.props.flash.success;
            errorMessage.value = '';
            form.reset('password');
            form.reset('email');
            trabajadores.value = page.props.checkIns;
        },
        onError: (errors) => {
            errorMessage.value = Object.values(errors)[0];
            successMessage.value = '';
        }
    });
};

const handleSearch = () => {
    router.post('/search-check-ins', searchForm.data(), {
        preserveState: true,
        preserveScroll: true,
        only: ['checkIns'],
        onSuccess: (page) => {
            trabajadores.value = page.props.checkIns;
        }
    });
};
</script>