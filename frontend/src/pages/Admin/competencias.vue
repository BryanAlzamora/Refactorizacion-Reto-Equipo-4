<script setup lang="ts">
import { computed, ref, onMounted } from "vue";
import { useCompetenciasStore } from "@/stores/competencias";
import type { Competencia } from "@/interfaces/Competencia";

const competenciasStore = useCompetenciasStore();

const isLoading = ref(true);
const searchQuery = ref("");

// Fetch competencias al montar
onMounted(async () => {
  isLoading.value = true;
  try {
    await competenciasStore.fetchCompetencias();
  } catch (err) {
    console.error("Error al cargar competencias:", err);
  } finally {
    isLoading.value = false;
  }
});

// Filtrado por búsqueda
const competenciasFiltradas = computed<Competencia[]>(() => {
  const all = competenciasStore.competencias;
  if (!searchQuery.value.trim()) return all;

  const query = searchQuery.value.toLowerCase();
  return all.filter(
    c =>
      c.descripcion.toLowerCase().includes(query) ||
      (c.tipo && c.tipo.toLowerCase().includes(query))
  );
});

// Separar por tipo
const competenciasTecnicas = computed(() =>
  competenciasFiltradas.value.filter(c => c.tipo === "TECNICA")
);

const competenciasTransversales = computed(() =>
  competenciasFiltradas.value.filter(c => c.tipo === "TRANSVERSAL")
);
</script>

<template>
  <div class="alumnos-asignados-container">
    <h2>Listado de Competencias</h2>
    <hr />

    <!-- Buscador -->
    <div class="mb-3">
      <div class="input-group">
        <span class="input-group-text bg-white border-end-0">
          <i class="bi bi-search"></i>
        </span>
        <input
          v-model="searchQuery"
          type="text"
          class="form-control border-start-0"
          placeholder="Buscar competencias..."
          :disabled="isLoading || competenciasStore.competencias.length === 0"
        />
      </div>
    </div>

    <!-- Loading -->
    <div v-if="isLoading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status"></div>
      <p class="mt-3 text-muted">Cargando competencias...</p>
    </div>

    <!-- Sin competencias -->
    <div v-else-if="competenciasStore.competencias.length === 0" class="alert alert-info d-flex align-items-center">
      <i class="bi bi-info-circle-fill me-2"></i>
      <div>No hay competencias registradas.</div>
    </div>

    <!-- Sin resultados -->
    <div v-else-if="competenciasFiltradas.length === 0" class="alert alert-warning d-flex align-items-center">
      <i class="bi bi-search me-2"></i>
      <div>No se encontraron competencias con "{{ searchQuery }}"</div>
    </div>

    <!-- Listado estático -->
    <div v-else class="list-group list-group-flush">
      <!-- Técnicas -->
      <h3 class="mt-3">Competencias Técnicas</h3>
      <div
        v-for="c in competenciasTecnicas"
        :key="c.id"
        class="list-group-item d-flex align-items-center py-3 mb-2"
      >
        <div class="avatar-circle bg-tecnica me-3">
          <i class="bi bi-tools"></i>
        </div>
        <div>
          <span>{{ c.descripcion }}</span>
        </div>
      </div>

      <!-- Transversales -->
      <h3 class="mt-4">Competencias Transversales</h3>
      <div
        v-for="c in competenciasTransversales"
        :key="c.id"
        class="list-group-item d-flex align-items-center py-3 mb-2"
      >
        <div class="avatar-circle bg-transversal me-3">
          <i class="bi bi-people-fill"></i>
        </div>
        <div>
          <span>{{ c.descripcion }}</span>
        </div>
      </div>
    </div>

    <!-- Contador -->
    <div v-if="!isLoading && competenciasStore.competencias.length > 0" class="mt-3">
      <small class="text-muted">
        Mostrando {{ competenciasFiltradas.length }} de {{ competenciasStore.competencias.length }} competencia(s)
      </small>
    </div>
  </div>
</template>

<style scoped>
.list-group-item {
  border-radius: 0.5rem;
}

.alumnos-asignados-container {
  padding: 0.5rem 0;
}

.avatar-circle {
  width: 45px;
  height: 45px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1.2rem;
  flex-shrink: 0;
}

/* Colores según tipo */
.bg-tecnica {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
.bg-transversal {
  background: linear-gradient(135deg, #f7971e 0%, #ffd200 100%);
}

h2 {
  margin-bottom: 1rem;
}

h3 {
  margin-bottom: 0.5rem;
}
</style>
