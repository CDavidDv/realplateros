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
                    
                    <Corte class="print" />
                    <ChartCorte class="no-print" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
<style>
.print {
    display: block !important;
}

@media print {
    .no-print {
    display: none !important;
    }

    .print {
    display: block !important;
    }

    astro-dev-toolbar {
    display: none !important;
    }

    article {
    break-inside: avoid;
    }
}
</style>