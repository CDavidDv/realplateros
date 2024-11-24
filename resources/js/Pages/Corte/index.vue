<script setup>
import ChartCorte from '@/Components/ChartCorte.vue';
import Corte from '@/Components/Corte.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
</script>

<template>
    <AppLayout title="Tablero principal">
        <div class="py-6">
            <div class="max-w-7xl w-full mx-auto sm:px-6 lg:px-8 rounded-xl">
                <div class="overflow-scroll md:overflow-hidden rounded-xl">
                    
                    <Corte />
                    <ChartCorte />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
<script>
</script>