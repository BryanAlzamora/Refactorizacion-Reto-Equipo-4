<script setup lang="ts">
import type { Alumno } from "@/interfaces/Alumno";
import { useAlumnosStore } from "@/stores/alumnos";
import { ref, onMounted, computed, watch } from "vue";
import { useRouter } from "vue-router";

const router = useRouter();
const alumnosStore = useAlumnosStore();

const alumnos = ref<Alumno[]>([]);
const isLoading = ref(true);
const searchQuery = ref("");

// PAGINACIÓN
const currentPage = ref(1);
const itemsPerPage = ref(8);
const goToPageInput = ref<number | null>(null);

// FILTRO
const alumnosFiltrados = computed(() => {
  if (!searchQuery.value.trim()) return alumnos.value;

  const query = searchQuery.value.toLowerCase();
  return alumnos.value.filter(
    (a) =>
      a.nombre.toLowerCase().includes(query) ||
      (a.apellidos && a.apellidos.toLowerCase().includes(query))
  );
});

// PAGINADOS
const alumnosPaginados = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value;
  return alumnosFiltrados.value.slice(start, start + itemsPerPage.value);
});

const totalPages = computed(() =>
  Math.ceil(alumnosFiltrados.value.length / itemsPerPage.value)
);

// RESET PAGINA AL BUSCAR
watch(searchQuery, () => {
  currentPage.value = 1;
});

// FUNCIÓN IR A PÁGINA
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
    await alumnosStore.fetchAlumnos();
    alumnos.value = alumnosStore.alumnos;
  } finally {
    isLoading.value = false;
  }
});

const verDetalleAlumno = (id: number) => {
  router.push({
    name: "admin-detalle_alumno",
    params: { alumnoId: id.toString() },
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
          placeholder="Buscar alumno..."
          :disabled="isLoading || alumnos.length === 0"
        />
      </div>
    </div>

    <!-- LOADING -->
    <div v-if="isLoading" class="text-center py-5">
      <div class="spinner-border text-primary"></div>
      <p class="mt-3 text-muted">Cargando alumnos…</p>
    </div>

    <!-- VACÍO -->
    <div
      v-else-if="alumnos.length === 0"
      class="alert alert-info d-flex align-items-center"
    >
      <i class="bi bi-info-circle-fill me-2"></i>
      No hay alumnos registrados
    </div>

    <!-- SIN RESULTADOS -->
    <div
      v-else-if="alumnosFiltrados.length === 0"
      class="alert alert-warning d-flex align-items-center"
    >
      <i class="bi bi-search me-2"></i>
      No hay resultados para "{{ searchQuery }}"
    </div>

    <!-- LISTA -->
    <div v-else class="list-group list-group-flush">
      <div
        v-for="alumno in alumnosPaginados"
        :key="alumno.id"
        class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3 hover-card mb-2"
        @click="verDetalleAlumno(alumno.id)"
        role="button"
      >
        <div class="d-flex align-items-center">
          <div class="avatar-circle me-3">
            <i class="bi bi-person-fill"></i>
          </div>
          <div>
            <h6 class="mb-0">
              {{ alumno.nombre }} {{ alumno.apellidos }}
            </h6>
          </div>
        </div>

        <i class="bi bi-chevron-right text-muted"></i>
      </div>
    </div>

    <!-- PAGINACIÓN PRO -->
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
  width: 42px;
  height: 42px;
  border-radius: 50%;
  background: linear-gradient(135deg, #81045f, #4a90e2);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
}

.hover-card {
  cursor: pointer;
  transition: all 0.15s ease;
  border-left: 3px solid transparent;
  border-radius: 0.5rem;
}

.hover-card:hover {
  background-color: var(--bs-primary);
  color: white;
  border-left-color: #4a90e2;
  transform: translateX(4px);
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
