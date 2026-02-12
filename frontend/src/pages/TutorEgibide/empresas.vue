<script setup lang="ts">
import type { Empresa } from "@/interfaces/Empresa";
import { useTutorEgibideStore } from "@/stores/tutorEgibide";
import { ref, onMounted, computed } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "@/stores/auth";

const router = useRouter();
const tutorEgibideStore = useTutorEgibideStore();
const authStore = useAuthStore();

const empresasAsignadas = ref<Empresa[]>([]);
const todasLasEmpresas = ref<Empresa[]>([]);
const isLoading = ref(true);
const searchQuery = ref("");

const tutorId = computed(() => authStore.currentUser?.id?.toString() || "");

// Empresas que NO están asignadas (para mostrar después)
const empresasNoAsignadas = computed(() => {
  const idsAsignados = new Set(empresasAsignadas.value.map(e => e.id));
  return todasLasEmpresas.value.filter(e => !idsAsignados.has(e.id));
});

// Filtrado por búsqueda en empresas asignadas
const empresasAsignadasFiltradas = computed(() => {
  if (!searchQuery.value.trim()) {
    return empresasAsignadas.value;
  }

  const query = searchQuery.value.toLowerCase();
  return empresasAsignadas.value.filter((empresa) =>
    empresa.nombre.toLowerCase().includes(query)
  );
});

// Filtrado por búsqueda en empresas no asignadas
const empresasNoAsignadasFiltradas = computed(() => {
  if (!searchQuery.value.trim()) {
    return empresasNoAsignadas.value;
  }

  const query = searchQuery.value.toLowerCase();
  return empresasNoAsignadas.value.filter((empresa) =>
    empresa.nombre.toLowerCase().includes(query)
  );
});

onMounted(async () => {
  try {
    // Cargar empresas asignadas y todas las empresas en paralelo
    await Promise.all([
      tutorEgibideStore.fetchEmpresasAsignadas(tutorId.value),
      tutorEgibideStore.fetchTodasLasEmpresas(),
    ]);
    
    empresasAsignadas.value = tutorEgibideStore.empresasAsignadas;
    todasLasEmpresas.value = tutorEgibideStore.todasLasEmpresas;
  } catch (error) {
    console.error("Error al cargar empresas:", error);
  } finally {
    isLoading.value = false;
  }
});

const verDetalleEmpresa = (empresaId: number) => {
  router.push({
    name: "tutor_egibide-detalle_empresa",
    params: { empresaId: empresaId.toString() },
  });
};
</script>

<template>
  <div class="empresas-container">
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
          placeholder="Buscar empresa..."
          :disabled="isLoading"
        />
      </div>
    </div>

    <!-- Estado de carga -->
    <div v-if="isLoading" class="text-center py-5">
      <div class="spinner-border" style="color: #81045f;" role="status">
        <span class="visually-hidden">Cargando...</span>
      </div>
      <p class="mt-3 text-muted fw-semibold">Cargando empresas...</p>
    </div>

    <div v-else>
      <!-- Sección: Empresas con alumnos asignados -->
      <div v-if="empresasAsignadas.length > 0" class="mb-4">
        <h5 class="mb-3 text-primary">
          <i class="bi bi-briefcase-fill me-2"></i>
          Empresas con mis alumnos
          <span class="badge bg-primary ms-2">{{ empresasAsignadasFiltradas.length }}</span>
        </h5>

        <div
          v-if="empresasAsignadasFiltradas.length === 0 && searchQuery"
          class="alert alert-warning"
        >
          <i class="bi bi-search me-2"></i>
          No se encontraron empresas asignadas con "{{ searchQuery }}"
        </div>

        <div v-else class="list-group list-group-flush mb-4">
          <div
            v-for="empresa in empresasAsignadasFiltradas"
            :key="'asignada-' + empresa.id"
            class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3 hover-card mb-2"
            @click="verDetalleEmpresa(empresa.id)"
            role="button"
            tabindex="0"
            @keypress.enter="verDetalleEmpresa(empresa.id)"
          >
            <div class="d-flex align-items-center flex-grow-1">
              <div class="avatar-circle avatar-asignada me-3">
                <i class="bi bi-building-fill"></i>
              </div>
              <div class="flex-grow-1">
                <h6 class="mb-0">{{ empresa.nombre }}</h6>
                <small class="text-muted">
                  <i class="bi bi-people-fill me-1"></i>
                  Con alumnos asignados
                </small>
              </div>
            </div>

            <i class="bi bi-chevron-right text-muted"></i>
          </div>
        </div>
      </div>

      <!-- Sección: Todas las empresas -->
      <div v-if="empresasNoAsignadas.length > 0">
        <h5 class="mb-3">
          <i class="bi bi-building me-2"></i>
          Todas las empresas
          <span class="badge bg-secondary ms-2">{{ empresasNoAsignadasFiltradas.length }}</span>
        </h5>

        <div
          v-if="empresasNoAsignadasFiltradas.length === 0 && searchQuery"
          class="alert alert-warning"
        >
          <i class="bi bi-search me-2"></i>
          No se encontraron empresas con "{{ searchQuery }}"
        </div>

        <div v-else class="list-group list-group-flush">
          <div
            v-for="empresa in empresasNoAsignadasFiltradas"
            :key="'todas-' + empresa.id"
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
      </div>

      <!-- Sin empresas -->
      <div
        v-if="empresasAsignadas.length === 0 && todasLasEmpresas.length === 0"
        class="alert alert-info"
      >
        <i class="bi bi-info-circle-fill me-2"></i>
        No hay empresas registradas en el sistema.
      </div>

      <!-- Contador total -->
      <div v-if="todasLasEmpresas.length > 0" class="mt-3">
        <small class="text-muted">
          Total: {{ empresasAsignadas.length }} con alumnos asignados, 
          {{ empresasNoAsignadas.length }} adicionales
        </small>
      </div>
    </div>
  </div>
</template>

<style scoped>
.empresas-container {
  padding: 0.5rem 0;
}

.avatar-circle {
  width: 45px;
  height: 45px;
  border-radius: 50%;
  background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1.2rem;
  flex-shrink: 0;
}

.avatar-asignada {
  background: linear-gradient(135deg, #81045f 0%, #2c3e50 100%);
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

.hover-card:hover .text-muted {
  color: rgba(255, 255, 255, 0.8) !important;
}

.input-group-text {
  border-right: none;
}

.form-control:focus {
  box-shadow: none;
}
</style>