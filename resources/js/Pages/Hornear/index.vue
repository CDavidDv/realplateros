<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Hornear from '@/Components/Hornear.vue';

</script>

<template>
    <AppLayout title="Tablero principal">
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 rounded-xl overflow-hidden">
                <div class="flex justify-between p-6 lg:p-8 bg-white border-b border-gray-200 rounded-2xl">
                    <Hornear />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
