<script setup lang="ts">
  import { onMounted } from 'vue';
  import { useEmpresasStore } from '@/stores/empresas';
  import EmpresasTodas from "@/components/EmpresasTodas.vue";

  const empresasStore = useEmpresasStore();

  onMounted(() => {
    empresasStore.fetchEmpresas();
  });
</script>

<template>
  <div>
    <h2>Lista de Empresas</h2>
    <hr />
    <EmpresasTodas />
  </div>
</template>

<style scoped></style>
