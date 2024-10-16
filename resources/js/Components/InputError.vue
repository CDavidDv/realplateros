<script setup>
defineProps({
    message: String,
});
</script>

<template>
    <div v-show="message" class=" disabled">
        <p class="text-xs text-red-600 ">
            {{ message }}
        </p>
    </div>
</template>
