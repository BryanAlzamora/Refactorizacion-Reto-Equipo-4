<script setup>
import EmpresasAsignadas from "@/components/EmpresasAsignadas.vue";
import { useAuthStore } from "@/stores/auth";

const authStore = useAuthStore();

const tutorId = authStore.currentUser.id;
</script>

<template>
  <h2>Mis Empresas</h2>
  <hr />
  <EmpresasAsignadas :tutorId="tutorId.toString()" />
</template>

<style scoped></style>