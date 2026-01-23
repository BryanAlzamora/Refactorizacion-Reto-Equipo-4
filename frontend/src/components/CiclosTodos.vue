<script setup lang="ts">
import type { Ciclo } from "@/interfaces/Ciclo";
import { useCiclosStore } from "@/stores/ciclos";
import { ref, onMounted, computed } from "vue";
import { useRouter } from "vue-router";

const router = useRouter();
const ciclosStore = useCiclosStore();

const ciclos = ref<Ciclo[]>([]);
const isLoading = ref(true);
const searchQuery = ref("");

// Filtrado por búsqueda
const ciclosFiltrados = computed(() => {
  if (!searchQuery.value.trim()) {
    return ciclos.value;
  }

  const query = searchQuery.value.toLowerCase();
  return ciclos.value.filter((ciclo) =>
    ciclo.nombre.toLowerCase().includes(query),
  );
});

onMounted(async () => {
  try {
    await ciclosStore.fetchCiclos(); // SELECT ALL
    ciclos.value = ciclosStore.ciclos;
  } catch (error) {
    console.error("Error al cargar ciclos:", error);
  } finally {
    isLoading.value = false;
  }
});

const verDetalleCiclo = (cicloId: number) => {
  router.push({
    name: "admin-detalle_ciclo", // ajusta si el nombre es distinto
    params: { cicloId: cicloId.toString() },
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
          placeholder="Buscar ciclo..."
          :disabled="isLoading || ciclos.length === 0"
        />
      </div>
    </div>

    <!-- Estado de carga -->
    <div v-if="isLoading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Cargando...</span>
      </div>
      <p class="mt-3 text-muted">Cargando ciclos...</p>
    </div>

    <!-- Sin ciclos -->
    <div
      v-else-if="ciclos.length === 0"
      class="alert alert-info d-flex align-items-center"
      role="alert"
    >
      <i class="bi bi-info-circle-fill me-2"></i>
      <div>No hay ciclos registrados.</div>
    </div>

    <!-- Sin resultados -->
    <div
      v-else-if="ciclosFiltrados.length === 0"
      class="alert alert-warning d-flex align-items-center"
      role="alert"
    >
      <i class="bi bi-search me-2"></i>
      <div>No se encontraron ciclos con "{{ searchQuery }}"</div>
    </div>

    <!-- Lista -->
    <div v-else class="list-group list-group-flush">
      <div
        v-for="ciclo in ciclosFiltrados"
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

    <!-- Contador -->
    <div v-if="!isLoading && ciclos.length > 0" class="mt-3">
      <small class="text-muted">
        Mostrando {{ ciclosFiltrados.length }} de
        {{ ciclos.length }} ciclo(s)
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
  background: linear-gradient(135deg, #f7971e 0%, #ffd200 100%);
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
  border-left-color: #f7971e;
  transform: translateX(5px);
}

.input-group-text {
  border-right: none;
}

.form-control:focus {
  box-shadow: none;
}
</style>
