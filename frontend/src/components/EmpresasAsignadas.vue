<script setup lang="ts">
import type { Empresa } from "@/interfaces/Empresa";
import { useTutorEgibideStore } from "@/stores/tutorEgibide";
import { ref, onMounted, computed } from "vue";
import { useRouter } from "vue-router";

const props = defineProps<{
  tutorId: string;
}>();

const router = useRouter();
const tutorEgibideStore = useTutorEgibideStore();

const empresasAsignadas = ref<Empresa[]>([]);
const isLoading = ref(true);
const searchQuery = ref("");

// Filtrado de empresas por búsqueda
const empresasFiltradas = computed(() => {
  if (!searchQuery.value.trim()) {
    return empresasAsignadas.value;
  }

  const query = searchQuery.value.toLowerCase();
  return empresasAsignadas.value.filter(
    (empresa) =>
      empresa.nombre.toLowerCase().includes(query) ||
      (empresa.cif && empresa.cif.toLowerCase().includes(query)) ||
      (empresa.email && empresa.email.toLowerCase().includes(query))
  );
});

onMounted(async () => {
  try {
    await tutorEgibideStore.fetchEmpresasAsignadas(props.tutorId);
    empresasAsignadas.value = tutorEgibideStore.empresasAsignadas;
  } catch (error) {
    console.error("Error al cargar empresas:", error);
  } finally {
    isLoading.value = false;
  }
});

const verDetalleEmpresa = (empresaId: number) => {
  router.push({
    name: "tutor_egibide-detalle_empresa",
    params: { empresaId: empresaId.toString() }
  });
};
</script>

<template>
  <div class="empresas-asignadas-container">
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
          :disabled="isLoading || empresasAsignadas.length === 0"
        />
      </div>
    </div>

    <!-- Estado de carga -->
    <div v-if="isLoading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Cargando...</span>
      </div>
      <p class="mt-3 text-muted">Cargando empresas asignadas...</p>
    </div>

    <!-- Sin empresas asignadas -->
    <div
      v-else-if="empresasAsignadas.length === 0"
      class="alert alert-info d-flex align-items-center"
      role="alert"
    >
      <i class="bi bi-info-circle-fill me-2"></i>
      <div>No tienes empresas asignadas actualmente.</div>
    </div>

    <!-- Sin resultados de búsqueda -->
    <div
      v-else-if="empresasFiltradas.length === 0"
      class="alert alert-warning d-flex align-items-center"
      role="alert"
    >
      <i class="bi bi-search me-2"></i>
      <div>No se encontraron empresas con "{{ searchQuery }}"</div>
    </div>

    <!-- Lista de empresas -->
    <div v-else class="list-group list-group-flush">
      <div
        v-for="empresa in empresasFiltradas"
        :key="empresa.id"
        class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3 hover-card mb-2"
        @click="verDetalleEmpresa(empresa.id)"
        role="button"
        tabindex="0"
        @keypress.enter="verDetalleEmpresa(empresa.id)"
      >
        <div class="d-flex align-items-center flex-grow-1">
          <div class="avatar-circle me-3">
            <i class="bi bi-building"></i>
          </div>
          <div class="flex-grow-1">
            <h6 class="mb-0">{{ empresa.nombre }}</h6>
          </div>
        </div>

        <div class="d-flex gap-2 align-items-center">
          <i class="bi bi-chevron-right text-muted"></i>
        </div>
      </div>
    </div>

    <!-- Contador de resultados -->
    <div v-if="!isLoading && empresasAsignadas.length > 0" class="mt-3">
      <small class="text-muted">
        Mostrando {{ empresasFiltradas.length }} de
        {{ empresasAsignadas.length }} empresa(s)
      </small>
    </div>
  </div>
</template>

<style scoped>
.empresas-asignadas-container {
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

.hover-card:hover .text-muted {
  color: rgba(255, 255, 255, 0.8) !important;
}

.hover-card:focus {
  outline: 2px solid #667eea;
  outline-offset: -2px;
}

.input-group-text {
  border-right: none;
}

.form-control:focus {
  border-color: #ced4da;
  box-shadow: none;
}

.list-group-item {
  border-left: none;
  border-right: none;
}

.list-group-item:first-child {
  border-top: 1px solid #dee2e6;
}
</style>