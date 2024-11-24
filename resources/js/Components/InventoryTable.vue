<template>
    
  </template>
  
  <script setup>
  defineProps({
    products: {
      type: Array,
      required: true
    },
    activeTab: {
      type: String,
      required: true
    }
  });
  
  defineEmits(['calculate-totals']);
  </script>