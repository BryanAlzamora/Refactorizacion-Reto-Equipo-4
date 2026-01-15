<script setup lang="ts">
import { onMounted, computed } from 'vue'
import { useCompetenciasStore } from '@/stores/competencias'

const competenciasStore = useCompetenciasStore()

onMounted(() => {
  competenciasStore.fetchCompetencias()
})

// Computed para filtrar por tipo
const competenciasTecnicas = computed(() =>
  competenciasStore.competencias.filter(c => c.tipo === 'TECNICA')
)

const competenciasTransversales = computed(() =>
  competenciasStore.competencias.filter(c => c.tipo === 'TRANSVERSAL')
)
</script>

<template>
  <div class="competencias-container" style="display: flex; gap: 2rem;">
    <!-- Técnicas -->
    <div class="competencias-tecnicas" style="flex: 1;">
      <h2>Competencias Técnicas</h2>
      <ul class="list-group">
        <li
          class="list-group-item"
          v-for="competencia in competenciasTecnicas"
          :key="competencia.id"
        >
          {{ competencia.descripcion }}
        </li>
      </ul>
    </div>

    <!-- Transversales -->
    <div class="competencias-transversales" style="flex: 1;">
      <h2>Competencias Transversales</h2>
      <ul class="list-group">
        <li
          class="list-group-item"
          v-for="competencia in competenciasTransversales"
          :key="competencia.id"
        >
          {{ competencia.descripcion }}
        </li>
      </ul>
    </div>
  </div>
</template>

<style scoped></style>
