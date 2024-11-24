<template>
    <div class="w-full max-w-4xl mx-auto p-6">
      <h2 class="text-2xl font-bold mb-4 text-center text-gray-800">Sales by Hour</h2>
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
    const hourlyData = Array(24).fill(0);
    
    props.productosVendidos.forEach(item => {
      const saleDate = new Date(item.producto.created_at);
      const hour = saleDate.getHours();
      hourlyData[hour] += parseInt(item.total_vendido);
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
        labels: Array.from({ length: 24 }, (_, i) => `${i}:00`),
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
              text: 'Hour of Day'
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