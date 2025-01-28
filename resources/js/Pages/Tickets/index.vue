<script setup>
import TicketsAsignados from '@/Components/TicketsAsignados.vue';
import AppLayout from '@/Layouts/AppLayout.vue';

</script>

<template>
    <AppLayout title="Tablero principal">
        <div class="py-6 px-2">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl rounded-lg py-2">
                   <TicketsAsignados />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
