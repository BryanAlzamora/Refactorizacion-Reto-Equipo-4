<script setup lang="ts">
import type { Empresa } from "@/interfaces/Empresa";
import { useEmpresasStore } from "@/stores/empresas";
import { ref, onMounted, computed } from "vue";
import { useRouter } from "vue-router";

const router = useRouter();
const empresasStore = useEmpresasStore();

const empresas = ref<Empresa[]>([]);
const isLoading = ref(true);
const searchQuery = ref("");

// Filtrado por búsqueda
const empresasFiltradas = computed(() => {
  if (!searchQuery.value.trim()) {
    return empresas.value;
  }

  const query = searchQuery.value.toLowerCase();
  return empresas.value.filter((empresa) =>
    empresa.nombre.toLowerCase().includes(query),
  );
});

onMounted(async () => {
  try {
    await empresasStore.fetchEmpresas(); // SELECT ALL
    empresas.value = empresasStore.empresas;
  } catch (error) {
    console.error("Error al cargar empresas:", error);
  } finally {
    isLoading.value = false;
  }
});

const verDetalleEmpresa = (empresaId: number) => {
  router.push({
    name: "admin-detalle_empresa", // ajusta si el nombre es distinto | index.ts
    params: { empresaId: empresaId.toString() },
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
          placeholder="Buscar empresa..."
          :disabled="isLoading || empresas.length === 0"
        />
      </div>
    </div>

    <!-- Estado de carga -->
    <div v-if="isLoading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Cargando...</span>
      </div>
      <p class="mt-3 text-muted">Cargando empresas...</p>
    </div>

    <!-- Sin empresas -->
    <div
      v-else-if="empresas.length === 0"
      class="alert alert-info d-flex align-items-center"
      role="alert"
    >
      <i class="bi bi-info-circle-fill me-2"></i>
      <div>No hay empresas registradas.</div>
    </div>

    <!-- Sin resultados -->
    <div
      v-else-if="empresasFiltradas.length === 0"
      class="alert alert-warning d-flex align-items-center"
      role="alert"
    >
      <i class="bi bi-search me-2"></i>
      <div>No se encontraron empresas con "{{ searchQuery }}"</div>
    </div>

    <!-- Lista -->
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

    <!-- Contador -->
    <div v-if="!isLoading && empresas.length > 0" class="mt-3">
      <small class="text-muted">
        Mostrando {{ empresasFiltradas.length }} de
        {{ empresas.length }} empresa(s)
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
  background: linear-gradient(135deg, #43cea2 0%, #185a9d 100%);
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
  border-left-color: #43cea2;
  transform: translateX(5px);
}

.input-group-text {
  border-right: none;
}

.form-control:focus {
  box-shadow: none;
}
</style>
