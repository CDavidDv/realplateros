// stores/useTimerStore.js
import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import Swal from 'sweetalert2'

export const useTimerStore = defineStore('timerStore', () => {
  const horneando = ref(false);
  const pastesHorneando = ref([]); // Almacena el grupo de pastes que están horneándose
  const tiempoTotal = ref(5000); // 30 segundos
  const tiempoTranscurrido = ref(0);
  const pastesPorHornear = ref([]); // Grupos de pastes por hornear
  const tiempoInicio = ref(null);
  const tiempoRestante = computed(() => {
    return tiempoTotal.value - tiempoTranscurrido.value;
  });

  const agregarPaste = (paste) => {
    pastesPorHornear.value.push(paste); // Agrega el paste a la lista de pastes por hornear
  };

  const iniciarHorneado = () => {
    if (pastesPorHornear.value.length === 0 || horneando.value) return;
    horneando.value = true;
    

    // Hornear todo el grupo de pastes juntos
    pastesHorneando.value = [...pastesPorHornear.value];
    pastesPorHornear.value = []; // Limpiamos la lista de pendientes para reflejar que están en el horno
    tiempoInicio.value = Date.now();
    
    const timer = setInterval(() => {
      tiempoTranscurrido.value = Date.now() - tiempoInicio.value;
      if (tiempoTranscurrido.value >= tiempoTotal.value) {
        finalizarHorneado(timer);
      }
    }, 100);
  };

  const finalizarHorneado = (timer) => {
    // Terminar el temporizador
    clearInterval(timer);
  
    // Obtener los pastes horneados
    const pastesFinalizados = [...pastesHorneando.value];
  
    // Restablecer estado del horno
    horneando.value = false;
    pastesHorneando.value = [];
    tiempoTranscurrido.value = 0;
  
    // Reproducir sonido de finalización
    reproducirSonido();
  
    // Enviar los pastes horneados al backend
    router.post('/hornear', { pastes: pastesFinalizados } , {
      onSuccess: (a) => {
        
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
        console.error('Error al registrar el horneadp:', errors);
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
      }})
  };

  // Función para reproducir sonido
  const reproducirSonido = () => {
    const audio = new Audio('/sound/videoplayback.mp3'); // Asegúrate de que el archivo de sonido esté en esta ruta
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
