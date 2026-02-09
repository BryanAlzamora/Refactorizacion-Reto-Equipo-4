<template>
  <div class="container py-4">
    <h2 class="mb-4 text-indigo fw-bold">Competencias vs RAs</h2>

    <div class="mb-4 col-12 col-md-4">
      <BuscadorSelect
        v-model="cicloSeleccionado"
        :options="store.ciclos"
        label-key="nombre"
        value-key="id"
        placeholder="Buscar ciclo..."
        @change="cargarMatriz"
      />
    </div>

    <p v-if="!cicloSeleccionado" class="text-muted">
      Selecciona un ciclo para ver la matriz.
    </p>

    <div v-if="asignaturas.length" class="table-responsive shadow-sm">
      <table class="table table-bordered text-center align-middle mb-0">
        <thead class="table-indigo">
          <tr>
            <th rowspan="2" class="align-middle">Asignatura</th>
            <th rowspan="2" class="align-middle">
              Resultado de Aprendizaje (RA)
            </th>
            <th :colspan="competencias.length" class="text-center py-2">
              Competencias
            </th>
          </tr>
          <tr>
            <th
              v-for="comp in competencias"
              :key="comp.id"
              class="th-competencia p-1 align-bottom"
            >
              <div
                class="competencia-scroll-container"
                :title="comp.descripcion"
              >
                {{ comp.descripcion }}
              </div>
            </th>
          </tr>
        </thead>

        <tbody>
          <template v-for="asignatura in asignaturas" :key="asignatura.id">
            <tr
              v-for="(ra, i) in asignatura.resultados_aprendizaje"
              :key="ra.id"
            >
              <td
                v-if="i === 0"
                :rowspan="asignatura.resultados_aprendizaje.length"
                class="fw-semibold text-start bg-light align-middle"
                style="min-width: 180px"
              >
                {{ asignatura.nombre_asignatura }}
              </td>

              <td class="text-start" style="min-width: 250px">
                {{ ra.descripcion }}
              </td>

              <td
                v-for="comp in competencias"
                :key="comp.id"
                class="cell-icon position-relative"
                @click="!isLoading(ra.id, comp.id) && toggleCompRa(ra, comp)"
                @mouseenter="setHover(ra.id, comp.id)"
                @mouseleave="clearHover"
              >
                <span
                  v-if="isLoading(ra.id, comp.id)"
                  class="spinner-border spinner-border-sm text-purple"
                ></span>

                <span
                  v-else-if="isHover(ra.id, comp.id)"
                  class="fw-bold"
                  :class="
                    tieneCompetencia(ra, comp.id)
                      ? 'text-danger'
                      : 'text-success'
                  "
                >
                  {{ tieneCompetencia(ra, comp.id) ? "✕" : "✓" }}
                </span>

                <span
                  v-else-if="tieneCompetencia(ra, comp.id)"
                  class="text-purple fw-bold"
                >
                  ✓
                </span>
              </td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useCompetenciaRaStore } from "@/stores/competenciaRa";
import BuscadorSelect from "@/components/BuscadorSelect.vue";

const store = useCompetenciaRaStore();
const cicloSeleccionado = ref(null);

// Datos desde el store
const asignaturas = computed(() => store.asignaturas);
const competencias = computed(() => store.competencias);

// Estado Local para UI (Hover y Loading)
// Usamos un estado local para no ensuciar los objetos del store
const hoverState = ref({ raId: null, compId: null });
const loadingState = ref({ raId: null, compId: null });

// --- Helpers de UI ---
const tieneCompetencia = (ra, compId) => {
  return ra.competencias_tec?.some((c) => c.id === compId);
};

const setHover = (raId, compId) => {
  hoverState.value = { raId, compId };
};

const clearHover = () => {
  hoverState.value = { raId: null, compId: null };
};

const isHover = (raId, compId) => {
  return hoverState.value.raId === raId && hoverState.value.compId === compId;
};

const isLoading = (raId, compId) => {
  return (
    loadingState.value.raId === raId && loadingState.value.compId === compId
  );
};

// --- Lógica de Negocio ---

const cargarMatriz = async () => {
  if (cicloSeleccionado.value) {
    await store.fetchMatriz(cicloSeleccionado.value);
  }
};

const toggleCompRa = async (ra, comp) => {
  // Inicializar array si no existe
  if (!ra.competencias_tec) ra.competencias_tec = [];

  const existe = tieneCompetencia(ra, comp.id);

  // 1. UI Optimista: Actualizamos visualmente antes de la llamada
  if (existe) {
    ra.competencias_tec = ra.competencias_tec.filter((c) => c.id !== comp.id);
  } else {
    ra.competencias_tec.push({ id: comp.id }); // Añadimos objeto mínimo
  }

  // 2. Activar Loading
  loadingState.value = { raId: ra.id, compId: comp.id };

  try {
    // 3. Llamada al Store (API)
    // Asumimos que el store devuelve true/false o lanza error
    await store.toggleRelacion(ra.id, comp.id);
  } catch (e) {
    // 4. Rollback si falla
    console.error("Error al guardar:", e);
    if (existe) ra.competencias_tec.push({ id: comp.id });
    else
      ra.competencias_tec = ra.competencias_tec.filter((c) => c.id !== comp.id);
  } finally {
    // 5. Desactivar Loading
    loadingState.value = { raId: null, compId: null };
  }
};

onMounted(async () => {
  await store.fetchCiclos();
});
</script>

<style scoped>
/* Colores personalizados (simulando Bootstrap custom) */

.text-purple {
  color: #511c5e; /* Morado del spinner */
}

/* Estilo de la cabecera */
.table-indigo {
  background-color: #f3e8ef; /* Fondo rosado/morado claro */
  color: #4b0082;
  border-bottom: 2px solid #511c5e;
}

.table-indigo th {
  background-color: #f3e8ef; /* Asegurar fondo en th */
  font-weight: 700;
  vertical-align: middle;
}

/* Celdas interactivas */
td.cell-icon {
  width: 60px;
  height: 50px; /* Un poco más alto para facilitar el click */
  cursor: pointer;
  vertical-align: middle;
  padding: 0;
  transition: background-color 0.2s;
  user-select: none;
}

/* Hover effect en la celda */
td.cell-icon:hover {
  background-color: #fce4ec; /* Color hover suave */
}

/* Spinner personalizado */
.spinner-border.text-purple {
  border-color: #511c5e !important;
  border-top-color: transparent !important;
}

/* Ajustes de tabla */
.th-competencia {
  font-size: 0.85rem;
  min-width: 100px;
}

.table-bordered {
  border-color: #dee2e6;
}
</style>
