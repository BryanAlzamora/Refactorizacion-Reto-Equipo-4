<script setup lang="ts">
import type { Alumno } from "@/interfaces/Alumno";
import { useAlumnosStore } from "@/stores/alumnos";
import { ref, onMounted, computed } from "vue";
import { useRouter } from "vue-router";

const router = useRouter();
const alumnosStore = useAlumnosStore();

const alumnos = ref<Alumno[]>([]);
const isLoading = ref(true);
const searchQuery = ref("");

// Filtrado de alumnos por búsqueda
const alumnosFiltrados = computed(() => {
  if (!searchQuery.value.trim()) {
    return alumnos.value;
  }

  const query = searchQuery.value.toLowerCase();
  return alumnos.value.filter(
    (alumno) =>
      alumno.nombre.toLowerCase().includes(query) ||
      (alumno.apellidos && alumno.apellidos.toLowerCase().includes(query)),
  );
});

onMounted(async () => {
  try {
    await alumnosStore.fetchAlumnos(); // SELECT ALL
    alumnos.value = alumnosStore.alumnos;
  } catch (error) {
    console.error("Error al cargar alumnos:", error);
  } finally {
    isLoading.value = false;
  }
});

const verDetalleAlumno = (alumnoId: number) => {
  router.push({
    name: "admin-detalle_alumno", // ajusta si el nombre es distinto | index.ts
    params: { alumnoId: alumnoId.toString() },
  });
};
</script>

<template>
  <div class="alumnos-asignados-container">
    <!-- Header con búsqueda -->
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

    <!-- Estado de carga -->
    <div v-if="isLoading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Cargando...</span>
      </div>
      <p class="mt-3 text-muted">Cargando alumnos...</p>
    </div>

    <!-- Sin alumnos -->
    <div
      v-else-if="alumnos.length === 0"
      class="alert alert-info d-flex align-items-center"
      role="alert"
    >
      <i class="bi bi-info-circle-fill me-2"></i>
      <div>No hay alumnos registrados.</div>
    </div>

    <!-- Sin resultados -->
    <div
      v-else-if="alumnosFiltrados.length === 0"
      class="alert alert-warning d-flex align-items-center"
      role="alert"
    >
      <i class="bi bi-search me-2"></i>
      <div>No se encontraron alumnos con "{{ searchQuery }}"</div>
    </div>

    <!-- Lista -->
    <div v-else class="list-group list-group-flush">
      <div
        v-for="alumno in alumnosFiltrados"
        :key="alumno.id"
        class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3 hover-card mb-2"
        @click="verDetalleAlumno(alumno.id)"
        role="button"
        tabindex="0"
        @keypress.enter="verDetalleAlumno(alumno.id)"
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

    <!-- Contador -->
    <div v-if="!isLoading && alumnos.length > 0" class="mt-3">
      <small class="text-muted">
        Mostrando {{ alumnosFiltrados.length }} de {{ alumnos.length }} alumno(s)
      </small>
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
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
  border-left-color: #667eea;
  transform: translateX(5px);
}

.input-group-text {
  border-right: none;
}

.form-control:focus {
  box-shadow: none;
}
</style>
