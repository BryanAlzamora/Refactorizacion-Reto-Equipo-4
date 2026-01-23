<script setup lang="ts">
import type { Competencia } from "@/interfaces/Competencia";
import { useCompetenciasStore } from "@/stores/competencias";
import { ref, onMounted, computed } from "vue";

const competenciasStore = useCompetenciasStore();

const competencias = ref<Competencia[]>([]);
const isLoading = ref(true);
const searchQuery = ref("");

// Computed para filtrar por búsqueda
const competenciasFiltradas = computed(() => {
  if (!searchQuery.value.trim()) {
    return competencias.value;
  }

  const query = searchQuery.value.toLowerCase();
  return competencias.value.filter(
    (c) =>
      c.descripcion.toLowerCase().includes(query) ||
      (c.tipo && c.tipo.toLowerCase().includes(query))
  );
});

// Computed por tipo
const competenciasTecnicas = computed(() =>
  competenciasFiltradas.value.filter((c) => c.tipo === "TECNICA")
);

const competenciasTransversales = computed(() =>
  competenciasFiltradas.value.filter((c) => c.tipo === "TRANSVERSAL")
);

onMounted(async () => {
  try {
    await competenciasStore.fetchCompetencias(); // SELECT ALL
    competencias.value = competenciasStore.competencias;
  } catch (error) {
    console.error("Error al cargar competencias:", error);
  } finally {
    isLoading.value = false;
  }
});
</script>

<template>
  <div class="competencias-container">
    <!-- Buscador -->
    <div class="mb-3">
      <input
        v-model="searchQuery"
        type="text"
        class="form-control"
        placeholder="Buscar competencias..."
        :disabled="isLoading || competencias.length === 0"
      />
    </div>

    <!-- Loading -->
    <div v-if="isLoading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status"></div>
      <p class="mt-3 text-muted">Cargando competencias...</p>
    </div>

    <!-- Sin competencias -->
    <div
      v-else-if="competencias.length === 0"
      class="alert alert-info d-flex align-items-center"
    >
      <i class="bi bi-info-circle-fill me-2"></i>
      <div>No hay competencias registradas.</div>
    </div>

    <!-- Listado -->
    <div v-else class="d-flex gap-4 flex-wrap">
      <!-- Técnicas -->
      <div style="flex: 1">
        <h2>Competencias Técnicas</h2>
        <ul class="list-group">
          <li
            v-for="c in competenciasTecnicas"
            :key="c.id"
            class="list-group-item"
          >
            {{ c.descripcion }}
          </li>
        </ul>
      </div>

      <!-- Transversales -->
      <div style="flex: 1">
        <h2>Competencias Transversales</h2>
        <ul class="list-group">
          <li
            v-for="c in competenciasTransversales"
            :key="c.id"
            class="list-group-item"
          >
            {{ c.descripcion }}
          </li>
        </ul>
      </div>
    </div>

    <!-- Contador -->
    <div v-if="!isLoading && competencias.length > 0" class="mt-3">
      <small class="text-muted">
        Mostrando {{ competenciasFiltradas.length }} de
        {{ competencias.length }} competencia(s)
      </small>
    </div>
  </div>
</template>

<style scoped>
.competencias-container {
  padding: 0.5rem 0;
}

.list-group-item {
  cursor: default;
}

h2 {
  margin-bottom: 0.5rem;
}
</style>
