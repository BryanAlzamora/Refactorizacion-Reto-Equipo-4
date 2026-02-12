<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { useAlumnosStore } from '@/stores/alumnos'
import Toast from '@/components/Notification/Toast.vue'

const alumnosStore = useAlumnosStore()

onMounted(() => {
  alumnosStore.fetchAlumno()
})

const misDatos = computed(() => alumnosStore.alumno ?? null)
</script>

<template>
  <div class="container mt-4">
    <h2 class="mb-4">Datos del Alumno</h2>

    <Toast
    v-if="alumnosStore.message"
    :message="alumnosStore.message"
    :messageType="alumnosStore.messageType"
    />

    <div v-else-if="misDatos" class="card">
      <div class="card-body">
        <p><strong>Nombre:</strong> {{ misDatos.nombre }}</p>
        <p><strong>Apellidos:</strong> {{ misDatos.apellidos }}</p>
        <p><strong>Correo electrónico:</strong> {{ misDatos.email }}</p>
        <p><strong>Teléfono:</strong> {{ misDatos.telefono}}</p>
        <p><strong>Ciudad:</strong> {{ misDatos.ciudad}}</p>
      </div>
    </div>

    <div v-else class="text-muted">Sin datos</div>
  </div>
</template>