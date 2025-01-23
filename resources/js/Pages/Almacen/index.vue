<script setup>
import Almacen from '@/Components/Almacen.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
</script>

<template>
    <AppLayout title="Tablero principal">
        <div class="py-6 px-2">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-10">
                <div class="bg-white  overflow-hidden shadow-xl rounded-lg">
                    <div class="overflow-hidden">
                        <Almacen />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
