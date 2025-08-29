<script setup>
import GestorVentas from '@/Components/GestorVentas.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
</script>

<template>
    <AppLayout title="Tablero principal">
        <div class="py-6">
            <div class="max-w-9xl w-full mx-auto sm:px-6 lg:px-8 rounded-xl">
                <div class="overflow-scroll md:overflow-hidden rounded-xl">
                    <GestorVentas />
                </div>
            </div>
        </div>
    </AppLayout>
</template>