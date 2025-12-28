<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import SalesPanel from '@/Components/SalesPanel.vue';
import EstadoPastes from '@/Components/EstadoPastes.vue';



</script>


<template>
    <AppLayout title="Tablero principal">
        <div class="py-6 px-2">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl rounded-lg">
                    <div class="p-6 lg:p-8 bg-white">
                        
                        <SalesPanel />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
