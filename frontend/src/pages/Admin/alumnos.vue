<script setup lang="ts">
  import { onMounted } from 'vue';
  import { useAlumnosStore } from '@/stores/alumnos';
  import AlumnosTodos from "@/components/AlumnosTodos.vue";

  const alumnosStore = useAlumnosStore();

  onMounted(() => {
    alumnosStore.fetchAlumnos();
  });
</script>

<template>
  <div>
    <h2>Lista de Alumnos</h2>
    <hr />
    <AlumnosTodos />
  </div>
</template>

<style scoped></style>
