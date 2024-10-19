<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Personal from '@/Components/Personal.vue';
</script>

<template>
    <AppLayout title="Tablero principal" v-if="$page.props.user.roles[0] !== 'trabajador'">
        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 p-2 md:p-8">
                <div class="bg-white overflow-hidden shadow-xl rounded-xl">
                    <Personal />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
