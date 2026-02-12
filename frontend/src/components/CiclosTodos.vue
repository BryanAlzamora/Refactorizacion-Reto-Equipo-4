<script setup lang="ts">
import type { Ciclo } from "@/interfaces/Ciclo";
import { useCiclosStore } from "@/stores/ciclos";
import { ref, onMounted, computed, watch } from "vue";
import { useRouter } from "vue-router";

const router = useRouter();
const ciclosStore = useCiclosStore();

const ciclos = ref<Ciclo[]>([]);
const isLoading = ref(true);
const searchQuery = ref("");

// =======================
// PAGINACIÓN
// =======================
const currentPage = ref(1);
const itemsPerPage = ref(8);
const goToPageInput = ref<number | null>(null);

// =======================
// FILTRO
// =======================
const ciclosFiltrados = computed(() => {
  if (!searchQuery.value.trim()) return ciclos.value;

  const query = searchQuery.value.toLowerCase();
  return ciclos.value.filter((ciclo) =>
    ciclo.nombre.toLowerCase().includes(query)
  );
});

// =======================
// PAGINADOS
// =======================
const ciclosPaginados = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value;
  return ciclosFiltrados.value.slice(start, start + itemsPerPage.value);
});

const totalPages = computed(() =>
  Math.ceil(ciclosFiltrados.value.length / itemsPerPage.value)
);

// Reset página al buscar
watch(searchQuery, () => {
  currentPage.value = 1;
});

// Ir a página específica
function goToPage() {
  if (!goToPageInput.value) return;

  if (goToPageInput.value < 1) {
    currentPage.value = 1;
  } else if (goToPageInput.value > totalPages.value) {
    currentPage.value = totalPages.value;
  } else {
    currentPage.value = goToPageInput.value;
  }

  goToPageInput.value = null;
}

onMounted(async () => {
  try {
    await ciclosStore.fetchCiclos();
    ciclos.value = ciclosStore.ciclos;
  } catch (error) {
    console.error("Error al cargar ciclos:", error);
  } finally {
    isLoading.value = false;
  }
});

const verDetalleCiclo = (cicloId: number) => {
  router.push({
    name: "admin-detalle_ciclo",
    params: { cicloId: cicloId.toString() },
  });
};
</script>

<template>
  <div class="alumnos-asignados-container">
    <!-- BUSCADOR -->
    <div class="mb-3">
      <div class="input-group">
        <span class="input-group-text bg-white border-end-0">
          <i class="bi bi-search"></i>
        </span>
        <input
          v-model="searchQuery"
          type="text"
          class="form-control border-start-0"
          placeholder="Buscar ciclo..."
          :disabled="isLoading || ciclos.length === 0"
        />
      </div>
    </div>

    <!-- LOADING -->
    <div v-if="isLoading" class="text-center py-5">
      <div class="spinner-border" style="color: #81045f;" role="status">
        <span class="visually-hidden">Cargando...</span>
      </div>
      <p class="mt-3 text-muted fw-semibold">Cargando ciclos...</p>
    </div>

    <!-- SIN CICLOS -->
    <div
      v-else-if="ciclos.length === 0"
      class="alert alert-info d-flex align-items-center"
    >
      <i class="bi bi-info-circle-fill me-2"></i>
      No hay ciclos registrados.
    </div>

    <!-- SIN RESULTADOS -->
    <div
      v-else-if="ciclosFiltrados.length === 0"
      class="alert alert-warning d-flex align-items-center"
    >
      <i class="bi bi-search me-2"></i>
      No se encontraron ciclos con "{{ searchQuery }}"
    </div>

    <!-- LISTA -->
    <div v-else class="list-group list-group-flush">
      <div
        v-for="ciclo in ciclosPaginados"
        :key="ciclo.id"
        class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3 hover-card mb-2"
        @click="verDetalleCiclo(ciclo.id)"
        role="button"
        tabindex="0"
        @keypress.enter="verDetalleCiclo(ciclo.id)"
      >
        <div class="d-flex align-items-center">
          <div class="avatar-circle me-3">
            <i class="bi bi-mortarboard-fill"></i>
          </div>
          <div>
            <h6 class="mb-0">{{ ciclo.nombre }}</h6>
          </div>
        </div>

        <i class="bi bi-chevron-right text-muted"></i>
      </div>
    </div>

    <!-- PAGINACIÓN -->
    <div
      v-if="totalPages > 1"
      class="d-flex justify-content-between align-items-center mt-4 px-2 gap-2"
    >
      <button
        class="btn btn-outline-secondary btn-sm"
        :disabled="currentPage === 1"
        @click="currentPage--"
      >
        <i class="bi bi-chevron-left me-1"></i>
        Anterior
      </button>

      <div class="d-flex align-items-center gap-2">
        <span class="text-muted small">
          Página <strong>{{ currentPage }}</strong> de {{ totalPages }}
        </span>

        <input
          type="number"
          class="form-control form-control-sm page-input"
          v-model.number="goToPageInput"
          :min="1"
          :max="totalPages"
          placeholder="Ir"
          @keyup.enter="goToPage"
        />
      </div>

      <button
        class="btn btn-outline-secondary btn-sm"
        :disabled="currentPage === totalPages"
        @click="currentPage++"
      >
        Siguiente
        <i class="bi bi-chevron-right ms-1"></i>
      </button>
    </div>
  </div>
</template>

<style scoped>
.alumnos-asignados-container {
  padding: 0.5rem 0;
}

.avatar-circle {
  width: 45px;
  height: 45px;
  border-radius: 50%;
  background: linear-gradient(135deg, #81045f 0%, #27ae60 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1.2rem;
  flex-shrink: 0;
}

.hover-card {
  cursor: pointer;
  transition: all 0.2s ease;
  border-left: 3px solid transparent;
  border-radius: 0.5rem;
}

.hover-card:hover {
  background-color: var(--bs-primary);
  color: white;
  border-left-color: #27ae60;
  transform: translateX(5px);
}

.page-input {
  width: 70px;
}

.input-group-text {
  border-right: none;
}

.form-control:focus {
  box-shadow: none;
}
</style>
