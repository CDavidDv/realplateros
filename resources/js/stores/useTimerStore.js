// stores/useTimerStore.js
import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

export const useTimerStore = defineStore('timerStore', () => {
  const horneando = ref(false);
  const pastesHorneando = ref([]);
  const tiempoTotal = ref(0); // Se ajustará según el tipo de producto
  const tiempoTranscurrido = ref(0);
  const pastesPorHornear = ref([]);
  const tiempoInicio = ref(null);

  const tiempoRestante = computed(() => {
    return tiempoTotal.value - tiempoTranscurrido.value;
  });

  const agregarPaste = (producto) => {
    // Agregar el producto a la lista de pendientes
    pastesPorHornear.value.push(producto);
  };

  const definirTiempoHorneado = (producto) => {
    // Asigna el tiempo en milisegundos dependiendo del tipo
    if (producto.masa === 'bola') {
      return 900000; // 30 minutos en milisegundos
    } else if (producto.masa === 'salada' || producto.masa === 'dulce') {
      return 900000; // 40 minutos en milisegundos
    }
    return 900000; // Tiempo por defecto, 30 minutos
  };

  const iniciarHorneado = () => {
    if (pastesPorHornear.value.length === 0 || horneando.value) return;

    horneando.value = true;
    pastesHorneando.value = [...pastesPorHornear.value];
    pastesPorHornear.value = [];

    // Aquí definimos el tiempo de horneado para el primer producto.
    // Si quieres manejar varios productos con diferentes tiempos, deberías ajustar la lógica.
    tiempoTotal.value = definirTiempoHorneado(pastesHorneando.value[0]);

    tiempoInicio.value = Date.now();

    const timer = setInterval(() => {
      tiempoTranscurrido.value = Date.now() - tiempoInicio.value;
      if (tiempoTranscurrido.value >= tiempoTotal.value) {
        finalizarHorneado(timer);
      }
    }, 100);
  };

  const finalizarHorneado = (timer) => {
    clearInterval(timer);

    const pastesFinalizados = [...pastesHorneando.value];

    horneando.value = false;
    pastesHorneando.value = [];
    tiempoTranscurrido.value = 0;

    reproducirSonido();

    router.post('/hornear', { pastes: pastesFinalizados }, {
      onSuccess: () => {
        const Toast = Swal.mixin({
          toast: true,
          position: "top-end",
          showConfirmButton: false,
          timer: 1500,
          timerProgressBar: true,
          didOpen: (toast) => {
            toast.onmouseenter = Swal.stopTimer;
            toast.onmouseleave = Swal.resumeTimer;
          }
        });
        Toast.fire({
          icon: "success",
          title: "Registrado correctamente"
        });
      },
      onError: (errors) => {
        console.error('Error al registrar el horneado:', errors);
        const Toast = Swal.mixin({
          toast: true,
          position: "top-end",
          showConfirmButton: false,
          timer: 1500,
          timerProgressBar: true,
          didOpen: (toast) => {
            toast.onmouseenter = Swal.stopTimer;
            toast.onmouseleave = Swal.resumeTimer;
          }
        });
        Toast.fire({
          icon: "error",
          title: "Error al registrar"
        });
      }
    });
  };

  const reproducirSonido = () => {
    const audio = new Audio('/sound/videoplayback.mp3');
    audio.play().catch(error => console.log("Error al reproducir el sonido:", error));
  };

  const cancelarPaste = (id) => {
    const index = pastesPorHornear.value.findIndex((p) => p.id === id);
    if (index !== -1) {
      pastesPorHornear.value.splice(index, 1);
    }
  };

  return {
    horneando,
    pastesHorneando,
    tiempoTotal,
    tiempoTranscurrido,
    tiempoRestante,
    pastesPorHornear,
    agregarPaste,
    iniciarHorneado,
    cancelarPaste,
  };
});
