<template>
    <div class=" min-h-max h-[80vh] gap-5 m-auto md:flex-row flex flex-col items-center p-4 md:p-8 justify-center">
        <div class="md:w-4/12 h-fit w-full border p-10 rounded-xl shadow-xl">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    Registro de Entrada/Salida
                </h2>
            </div>

            <!-- Formulario de Check-in/Check-out -->
            <form class="mt-8 space-y-6" @submit.prevent="handleSubmit" >
                <div class="rounded-md shadow-sm -space-y-px">
                    <div>
                        <label for="email" class="sr-only">Matricula de Usuario</label>
                        <input id="email" name="email" type="text" required
                            class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-orange-500 focus:border-orange-500 focus:z-10 sm:text-sm"
                            placeholder="Matricula de Usuario" v-model="form.email" />
                    </div>
                    <div>
                        <label for="password" class="sr-only">Contraseña</label>
                        <input id="password" name="password" type="password" required
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

        <div class="md:w-8/12 w-full h-fit md:overflow-scroll">
            <!-- Mostrar registros previos -->
            <div class="mt-8">
                <h3 class="text-lg font-semibold text-gray-700 mb-4">Registros recientes</h3>
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
                (trabajador.horas_trabajadas/60).toFixed(2) : "-" }}
        </td>
    </tr>
</tbody>


                </table>
            </div>
        </div>
    </div>
</template>

<script setup>
import { router, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
const { props } = usePage();

const form = useForm({
    email: '',
    password: '',
});


const trabajadores = ref(props.checkIns);
const successMessage = ref('');
const errorMessage = ref('');
const isCheckingIn = ref(true);
console.log("Trabajadores: ", trabajadores.value);
console.log("Registros: ", trabajadores.value);
console.log("Registros: ", form);
// Formato de la hora
const formatTime = (time) => {
    if (!time) return '-';
    return new Date(time).toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });
};

// Manejar el check-in/out
const handleSubmit = () => {

    router.post('/checkInOut',{email: form.email, password: form.password},{
        onSuccess: (page) => {
            successMessage.value = page.props.flash.success;
            errorMessage.value = '';
            isCheckingIn.value = !isCheckingIn.value;
            form.reset('password');
            console.log(page)
            trabajadores.value = page.props.checkIns
        },
        onError: (errors) => {
            errorMessage.value = Object.values(errors)[0];
            successMessage.value = '';
            console.log(errors)
        }
    });
};
</script>