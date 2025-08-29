<template>
    <div class="w-full max-w-4xl mx-auto p-6">
      <h2 class="text-2xl font-bold mb-4 text-center text-gray-800">Ventas por Hora (6:00 AM - 11:00 PM)</h2>
      <canvas ref="chartRef"></canvas>
    </div>
  </template>
  
  <script setup>
  import { ref, onMounted, computed } from 'vue';
  import { usePage } from '@inertiajs/vue3';
  import Chart from 'chart.js/auto';
  
  const { props } = usePage();
  const chartRef = ref(null);
  
  const salesByHour = computed(() => {
    const hourlyData = Array(18).fill(0); // 18 horas: 6:00 AM a 11:00 PM
    
    props.productosVendidos.forEach(item => {
      const saleDate = new Date(item.producto.created_at);
      const hour = saleDate.getHours();
      // Solo incluir horas entre 6:00 AM (6) y 11:00 PM (23)
      if (hour >= 6 && hour <= 23) {
        const adjustedHour = hour - 6; // Ajustar índice: 6:00 AM = índice 0, 11:00 PM = índice 17
        hourlyData[adjustedHour] += parseInt(item.total_vendido);
      }
    });
    
    return hourlyData;
  });
  
  const maxSales = Math.max(...salesByHour.value);
  const minSales = Math.min(...salesByHour.value);
  const maxHour = salesByHour.value.indexOf(maxSales);
  const minHour = salesByHour.value.indexOf(minSales);
  
  onMounted(() => {
    const ctx = chartRef.value.getContext('2d');
    
    new Chart(ctx, {
      type: 'line',
      data: {
        labels: Array.from({ length: 18 }, (_, i) => {
          const hour = i + 6; // Convertir índice a hora real (6:00 AM a 11:00 PM)
          const ampm = hour < 12 ? 'AM' : 'PM';
          const displayHour = hour === 12 ? 12 : hour % 12;
          return `${displayHour}:00 ${ampm}`;
        }),
        datasets: [{
          label: 'Sales',
          data: salesByHour.value,
          borderColor: 'rgb(75, 192, 192)',
          tension: 0.1
        }]
      },
      options: {
        responsive: true,
        scales: {
          y: {
            beginAtZero: true,
            title: {
              display: true,
              text: 'Number of Sales'
            }
          },
          x: {
            title: {
              display: true,
              text: 'Hora del Día (6:00 AM - 11:00 PM)'
            }
          }
        },
        plugins: {
          tooltip: {
            callbacks: {
              title: (context) => `Hour: ${context[0].label}`,
              label: (context) => `Sales: ${context.formattedValue}`
            }
          }
        }
      }
    });
  });
  </script>