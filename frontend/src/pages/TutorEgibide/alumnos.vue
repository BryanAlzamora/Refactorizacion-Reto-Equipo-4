<script setup>
import AlumnosAsignados from "@/components/AlumnosAsignados.vue";
import { useAuthStore } from "@/stores/auth";

const authStore = useAuthStore();

const tutorId = authStore.currentUser.id;
</script>

<template>
  <h2>Mis Alumnos</h2>
  <hr />
  <AlumnosAsignados tipoTutor="egibide" :tutorId="tutorId.toString()" />
</template>

<style scoped></style>
