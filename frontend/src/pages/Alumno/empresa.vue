<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { useEmpresasStore } from '@/stores/empresas'
import Toast from '@/components/Notification/Toast.vue'

const empresasStore = useEmpresasStore()

onMounted(() => {
  empresasStore.fetchMiEmpresa()
})

const miEmpresa = computed(() => empresasStore.empresas?.[0] ?? null)
</script>

<template>
  <div class="container mt-4">
    <h2 class="mb-4">Datos de la Empresa</h2>
    
    <Toast
    v-if="empresasStore.message"
    :message="empresasStore.message"
    :messageType="empresasStore.messageType"
    />

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