<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { useEmpresasStore } from '@/stores/empresas'

const empresasStore = useEmpresasStore()

onMounted(() => {
  empresasStore.fetchMiEmpresa()
})

const miEmpresa = computed(() => empresasStore.empresas?.[0] ?? null)
</script>

<template>
  <div class="container mt-4">
    <h2 class="mb-4">Datos de la Empresa</h2>

    <div v-if="empresasStore.loading" class="text-muted">Cargando...</div>

    <div v-else-if="empresasStore.error" class="alert alert-danger">
      {{ empresasStore.error }}
    </div>

    <div v-else-if="miEmpresa" class="card">
      <div class="card-body">
        <p><strong>Nombre:</strong> {{ miEmpresa.nombre }}</p>
        <p><strong>CIF:</strong> {{ miEmpresa.cif }}</p>
        <p><strong>Correo electrónico:</strong> {{ miEmpresa.email }}</p>
        <p><strong>Teléfono:</strong> {{ miEmpresa.telefono ?? 'No indicado' }}</p>
        <p><strong>Direccion:</strong> {{ miEmpresa.direccion ?? 'No indicada' }}</p>
      </div>
    </div>

    <div v-else class="text-muted">Sin datos</div>
  </div>
</template>

<style scoped></style>