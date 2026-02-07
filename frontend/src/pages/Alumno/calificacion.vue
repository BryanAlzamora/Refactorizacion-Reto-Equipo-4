<script setup lang="ts">
import { onMounted, ref } from "vue";
import { useAlumnosStore } from "@/stores/alumnos";
import { useRoute } from "vue-router";
import router from "@/router";
import type { NotaCuaderno } from "@/interfaces/Notas";
import calificaciones from "../TutorEgibide/calificaciones.vue";
const alumnosStore = useAlumnosStore();
const route = useRoute();

const alumnoId = Number(route.params.alumnoId);
const error = ref<string | null>(null);
const notaCuaderno = ref<number | null>(null);

function volver() {
  router.back();
}

onMounted(async () => {
  await alumnosStore.getNotaCuadernoByAlumno(alumnoId);
  notaCuaderno.value = alumnosStore.notaCuaderno
});
</script>

<template>
  <div class="container mt-4">
    <h2 class="mb-4">CALIFICACION</h2>

    <div v-if="alumnosStore.notaCuaderno !== null" class="nota card">
      <calificaciones modo="alumno"></calificaciones>
    </div>

    <div
      v-else-if="error"
      class="alert alert-danger d-flex align-items-center"
      role="alert"
    >
      <i class="bi bi-exclamation-triangle-fill me-2"></i>
      <div>
        {{ error }}
        <button class="btn btn-sm btn-outline-danger ms-3" @click="volver">
          Volver a alumno
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.nota,
.msg {
  margin-top: 12px;
  padding: 12px;
  border: 1px solid #ddd;
  border-radius: 8px;
}
</style>
