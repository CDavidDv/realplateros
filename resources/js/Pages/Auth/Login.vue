<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

defineProps({
    canResetPassword: Boolean,
    status: String,
    sucursales: Array
});

const { props } = usePage()
console.log(props)

const form = useForm({
    email: '',
    password: '',
    sucursal_id: '',
    remember: false,
});

const submit = () => {
    form.transform(data => ({
        ...data,
        remember: form.remember ? 'on' : '',
    })).post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Inicio de sesión" />

    <AuthenticationCard >
        <template #logo>
            <AuthenticationCardLogo />
        </template>

        <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            
            <div>
                <InputLabel for="sucursal" value="Sucursal" />
                <select
                    id="sucursal"
                    v-model="form.sucursal_id"
                    class="mt-1 block w-full border-gray-300 focus:border-orange-500 focus:ring-orange-500 rounded-md shadow-sm"
                >   
                <option v-for="sucursal in sucursales" :value="sucursal.id">{{ sucursal.nombre }}</option>

                </select>
                <InputError class="mt-2" :message="form.errors.sucursal_id" />
            </div>
            
            <div class="mt-2">
                <InputLabel for="email" value="Matricula" />
                <TextInput
                    id="text"
                    v-model="form.email"
                    type="text"
                    class="mt-1 block w-full"
                    required
                    autofocus
                    autocomplete="username"
                />
            </div>
            
            <div class="mt-2">
                <InputLabel for="password" value="Contraseña" />
                <TextInput
                id="password"
                    v-model="form.password"
                    type="password"
                    class="mt-1 block w-full"
                    required
                    autocomplete="current-password"
                />
            </div>
            
            <InputError class="mt-2" :message="form.errors.password" />
            <InputError class="mt-2" :message="form.errors.email" />
            <div class="block mt-4">
                <label class="flex items-center">
                    <Checkbox v-model:checked="form.remember" name="remember" />
                    <span class="ms-2 text-sm text-gray-600">Recuérdame</span>
                </label>
            </div>

            <div class="flex items-center justify-end mt-4">
               
                <PrimaryButton class="ms-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Inicio de sesión
                </PrimaryButton>
            </div>
        </form>
    </AuthenticationCard>
</template>
