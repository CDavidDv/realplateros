<template>
  <div 
    class="p-4 border rounded shadow mb-4 size-fit cursor-pointer" 
    :class="{
      'bg-yellow-100': type === 'warning',
      'bg-blue-100': type === 'info'
    }"
    @click="$emit('toggle')"
  >
    <h2 
      class="text-lg font-bold text-center"
      :class="{
        'text-yellow-800': type === 'warning',
        'text-blue-800': type === 'info'
      }"
    >
      {{ type === 'warning' ? '⚠️' : 'ℹ️' }} 
      {{ title }} 
      <span class="animate-pulse">({{ notifications.length }})</span>
      {{ type === 'warning' ? '⚠️' : 'ℹ️' }}
    </h2>
    
    <ul v-if="isVisible" class="mt-2">
      <li v-for="notif in notifications" :key="notif.id" class="mb-2">
        <span>
          <span class="font-bold">{{ notif.nombre }}</span>: 
          {{ type === 'warning' ? 'Faltan' : 'Hay' }}
          {{ type === 'warning' ? Math.abs(notif.diferencia) : notif.diferencia }}
          unidades para las {{ notif.hora }}
          <span class="text-sm" :class="{
            'text-red-600': type === 'warning',
            'text-green-600': type === 'info'
          }">
            ({{ notif.porcentaje }}% del estimado)
          </span>
        </span>
      </li>
    </ul>
  </div>
</template>

<script setup>
defineProps({
  notifications: {
    type: Array,
    required: true
  },
  isVisible: {
    type: Boolean,
    default: false
  },
  type: {
    type: String,
    required: true,
    validator: (value) => ['warning', 'info'].includes(value)
  },
  title: {
    type: String,
    required: true
  }
});

defineEmits(['toggle']);
</script> 