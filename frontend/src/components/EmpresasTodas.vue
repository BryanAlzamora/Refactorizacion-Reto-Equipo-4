<script setup lang="ts">
import type { Empresa } from "@/interfaces/Empresa";
import { useEmpresasStore } from "@/stores/empresas";
import { ref, onMounted, computed, watch } from "vue";
import { useRouter } from "vue-router";

const router = useRouter();
const empresasStore = useEmpresasStore();

const empresas = ref<Empresa[]>([]);
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
const empresasFiltradas = computed(() => {
  if (!searchQuery.value.trim()) return empresas.value;

  const query = searchQuery.value.toLowerCase();
  return empresas.value.filter((empresa) =>
    empresa.nombre.toLowerCase().includes(query)
  );
});

// =======================
// PAGINADOS
// =======================
const empresasPaginadas = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value;
  return empresasFiltradas.value.slice(start, start + itemsPerPage.value);
});

const totalPages = computed(() =>
  Math.ceil(empresasFiltradas.value.length / itemsPerPage.value)
);

// Reset página al buscar
watch(searchQuery, () => {
  currentPage.value = 1;
});

// Función ir a página
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
    await empresasStore.fetchEmpresas();
    empresas.value = empresasStore.empresas;
  } catch (error) {
    console.error("Error al cargar empresas:", error);
  } finally {
    isLoading.value = false;
  }
});

const verDetalleEmpresa = (empresaId: number) => {
  router.push({
    name: "admin-detalle_empresa",
    params: { empresaId: empresaId.toString() },
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
          placeholder="Buscar empresa..."
          :disabled="isLoading || empresas.length === 0"
        />
      </div>
    </div>

    <!-- LOADING -->
    <div v-if="isLoading" class="text-center py-5">
      <div class="spinner-border" style="color: #81045f;" role="status">
        <span class="visually-hidden">Cargando...</span>
      </div>
      <p class="mt-3 text-muted fw-semibold">Cargando empresas...</p>
    </div>

    <!-- SIN EMPRESAS -->
    <div
      v-else-if="empresas.length === 0"
      class="alert alert-info d-flex align-items-center"
    >
      <i class="bi bi-info-circle-fill me-2"></i>
      No hay empresas registradas.
    </div>

    <!-- SIN RESULTADOS -->
    <div
      v-else-if="empresasFiltradas.length === 0"
      class="alert alert-warning d-flex align-items-center"
    >
      <i class="bi bi-search me-2"></i>
      No se encontraron empresas con "{{ searchQuery }}"
    </div>

    <!-- LISTA -->
    <div v-else class="list-group list-group-flush">
      <div
        v-for="empresa in empresasPaginadas"
        :key="empresa.id"
        class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3 hover-card mb-2"
        @click="verDetalleEmpresa(empresa.id)"
        role="button"
        tabindex="0"
        @keypress.enter="verDetalleEmpresa(empresa.id)"
      >
        <div class="d-flex align-items-center">
          <div class="avatar-circle me-3">
            <i class="bi bi-building"></i>
          </div>
          <div>
            <h6 class="mb-0">{{ empresa.nombre }}</h6>
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
  background: linear-gradient(135deg, #81045f 0%, #2c3e50 100%);
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
  border-left-color: #2c3e50;
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
