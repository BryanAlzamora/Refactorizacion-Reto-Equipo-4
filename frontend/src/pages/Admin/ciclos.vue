<script setup lang="ts">
  import { onMounted } from 'vue';
  import { useCiclosStore } from '@/stores/ciclos';
  import CiclosTodos from "@/components/CiclosTodos.vue";

  const ciclosStore = useCiclosStore();

  onMounted(() => {
    ciclosStore.fetchCiclos();
  });
</script>

<template>
  <div>
    <h2>Lista de Ciclos</h2>
    <hr />
    <CiclosTodos />
  </div>
</template>

<style scoped></style>
