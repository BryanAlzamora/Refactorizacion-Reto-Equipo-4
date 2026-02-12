<script setup lang="ts">
import type { Ciclo } from "@/interfaces/Ciclo";
import type { Asignatura } from "@/interfaces/Asignatura";
import { useRoute, useRouter } from "vue-router";
import { useAuthStore } from "@/stores/auth";
import { useFamiliaProfesionalesStore } from "@/stores/familiasProfesionales";
import { ref, onMounted, computed } from "vue";
import UploadCiclosCSV from "@/components/Ciclos/UploadCiclosCSV.vue";

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const familiasStore = useFamiliaProfesionalesStore();

const baseURL = import.meta.env.VITE_API_BASE_URL;

const ciclo = ref<Ciclo | null>(null);
const asignaturas = ref<Asignatura[]>([]);
const isLoading = ref(true);
const isLoadingAsignaturas = ref(false);
const error = ref<string | null>(null);
const busqueda = ref(""); // Estado para el buscador

// Obtener parámetro de la ruta
const cicloId = Number(route.params.cicloId);

// Computed para obtener el nombre de la familia profesional
const nombreFamiliaProfesional = computed(() => {
  if (!ciclo.value || !ciclo.value.familia_profesional_id) return "";
  const fam = familiasStore.familiasProfesionales.find(
    (f) => f.id === ciclo.value!.familia_profesional_id,
  );
  return fam ? fam.nombre : "Familia desconocida";
});

// Computed para filtrar asignaturas
const asignaturasFiltradas = computed(() => {
  if (!busqueda.value) return asignaturas.value;
  const term = busqueda.value.toLowerCase();
  return asignaturas.value.filter(
    (a) =>
      a.nombre_asignatura.toLowerCase().includes(term) ||
      a.codigo_asignatura.toLowerCase().includes(term)
  );
});

const cargarDetalleCiclo = async () => {
  isLoading.value = true;
  error.value = null;

  try {
    const response = await fetch(`${baseURL}/api/admin/ciclos/${cicloId}`, {
      headers: {
        Authorization: authStore.token ? `Bearer ${authStore.token}` : "",
        Accept: "application/json",
      },
    });

    if (!response.ok) throw new Error("Error al cargar los datos del ciclo");
    ciclo.value = await response.json();

    if (familiasStore.familiasProfesionales.length === 0) {
      await familiasStore.fetchFamiliasProfesionales();
    }
    await cargarAsignaturas();
  } catch (err) {
    console.error(err);
    error.value = "No se pudo cargar la información del ciclo";
  } finally {
    isLoading.value = false;
  }
};

const cargarAsignaturas = async () => {
  isLoadingAsignaturas.value = true;
  try {
    const response = await fetch(`${baseURL}/api/ciclo/${cicloId}/asignaturas`, {
      headers: {
        Authorization: authStore.token ? `Bearer ${authStore.token}` : "",
        Accept: "application/json",
      },
    });
    if (!response.ok) throw new Error("Error al cargar las asignaturas");
    const data = await response.json();
    asignaturas.value = data.asignaturas || [];
  } catch (err) {
    console.error(err);
  } finally {
    isLoadingAsignaturas.value = false;
  }
};

onMounted(() => cargarDetalleCiclo());

const nuevaRA = ref<Record<number, string>>({});
const creatingRA = ref<Record<number, boolean>>({}); // Estado de carga por asignatura

const crearRA = async (asignaturaId: number) => {
  const descripcion = nuevaRA.value[asignaturaId];
  if (!descripcion) return;

  creatingRA.value[asignaturaId] = true;

  try {
    const response = await fetch(`${baseURL}/api/ra`, {
      method: "POST",
      headers: {
        Authorization: authStore.token ? `Bearer ${authStore.token}` : "",
        "Content-Type": "application/json",
        Accept: "application/json",
      },
      body: JSON.stringify({
        descripcion,
        asignatura_id: asignaturaId,
      }),
    });

    if (!response.ok) throw new Error();

    await cargarAsignaturas();
    nuevaRA.value[asignaturaId] = "";
  } catch (err) {
    console.error(err);
  } finally {
    creatingRA.value[asignaturaId] = false;
  }
};

const eliminarRA = async (raId: number, asignaturaId: number) => {
  if(!confirm("¿Estás seguro de eliminar este Resultado de Aprendizaje?")) return;
  
  try {
    const response = await fetch(`${baseURL}/api/ra/${raId}`, {
      method: "DELETE",
      headers: {
        Authorization: authStore.token ? `Bearer ${authStore.token}` : "",
        Accept: "application/json",
      },
    });

    if (!response.ok) throw new Error();
    await cargarAsignaturas();
  } catch (err) {
    console.error(err);
  }
};

const volver = () => router.back();
</script>

<template>
  <div class="container-fluid bg-light min-vh-100 py-4">
    <div class="container">
      
      <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
          <li class="breadcrumb-item">
            <a href="#" @click.prevent="volver" class="text-decoration-none text-muted">
              <i class="bi bi-house-door me-1"></i> Ciclos
            </a>
          </li>
          <li class="breadcrumb-item active text-primary fw-semibold" aria-current="page" v-if="ciclo">
            {{ ciclo.nombre }}
          </li>
        </ol>
      </nav>

      <div v-if="isLoading" class="text-center py-5 fade-in">
        <div class="spinner-grow text-primary" role="status" style="width: 3rem; height: 3rem;"></div>
        <p class="mt-3 text-muted fw-light fs-5">Cargando ecosistema del ciclo...</p>
      </div>

      <div v-else-if="error" class="alert alert-danger shadow-sm border-0 d-flex align-items-center" role="alert">
        <i class="bi bi-exclamation-octagon-fill fs-3 me-3"></i>
        <div>
          <h5 class="alert-heading mb-1">Error de conexión</h5>
          <p class="mb-0">{{ error }}</p>
          <button class="btn btn-sm btn-outline-danger mt-2" @click="volver">Volver al listado</button>
        </div>
      </div>

      <div v-else-if="ciclo" class="fade-in">
        
        <div class="card border-0 shadow-sm mb-4 overflow-hidden rounded-4">
          <div class="card-body p-4 position-relative">
            <div class="decorative-circle"></div>
            <div class="row align-items-center position-relative z-1">
              <div class="col-md-8">
                <div class="d-flex align-items-center mb-2">
                  <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-2">
                    <i class="bi bi-mortarboard me-1"></i> {{ nombreFamiliaProfesional }}
                  </span>
                  <span class="badge bg-light text-secondary border ms-2 rounded-pill px-3 py-2">
                    ID: {{ ciclo.id }}
                  </span>
                </div>
                <h1 class="display-6 fw-bold text-dark mb-1">{{ ciclo.nombre }}</h1>
                <p class="text-muted mb-0">Gestión curricular y resultados de aprendizaje.</p>
              </div>
              <div class="col-md-4 text-md-end mt-3 mt-md-0 d-flex flex-column gap-2">
                 <div class="d-flex justify-content-md-end gap-2">
                    <UploadCiclosCSV :cicloId="cicloId" class="w-100" />
                 </div>
              </div>
            </div>
          </div>
        </div>

        <div class="row g-4">
          <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 h-100">
              
              <div class="card-header bg-white border-bottom-0 pt-4 px-4 pb-0 d-flex flex-wrap justify-content-between align-items-center">
                <div>
                  <h4 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-journal-bookmark me-2 text-primary"></i>Asignaturas
                  </h4>
                  <p class="text-muted small mt-1">
                    {{ asignaturas.length }} asignaturas registradas
                  </p>
                </div>
                
                <div class="search-box">
                  <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted rounded-start-pill ps-3">
                      <i class="bi bi-search"></i>
                    </span>
                    <input 
                      v-model="busqueda" 
                      type="text" 
                      class="form-control bg-light border-start-0 rounded-end-pill" 
                      placeholder="Buscar asignatura..."
                    >
                  </div>
                </div>
              </div>

              <div class="card-body px-4 pb-4">
                
                <div v-if="isLoadingAsignaturas" class="text-center py-5">
                  <div class="spinner-border text-primary" role="status"></div>
                </div>

                <div v-else-if="asignaturas.length === 0" class="text-center py-5 bg-light rounded-3 mt-3">
                  <i class="bi bi-folder-x display-4 text-muted opacity-50"></i>
                  <h5 class="mt-3 text-muted">No hay asignaturas</h5>
                  <p class="text-muted small">Intenta importar un CSV para comenzar.</p>
                </div>

                <div v-else-if="asignaturasFiltradas.length === 0" class="text-center py-4">
                  <p class="text-muted">No se encontraron asignaturas con "{{ busqueda }}"</p>
                </div>

                <div v-else class="accordion accordion-flush mt-3" id="accordionAsignaturas">
                  <div 
                    v-for="(asignatura, index) in asignaturasFiltradas" 
                    :key="asignatura.id" 
                    class="accordion-item border rounded-3 mb-3 shadow-sm overflow-hidden"
                  >
                    <h2 class="accordion-header" :id="'heading' + asignatura.id">
                      <button 
                        class="accordion-button collapsed fw-semibold py-3 px-4" 
                        type="button" 
                        data-bs-toggle="collapse" 
                        :data-bs-target="'#collapse' + asignatura.id" 
                        aria-expanded="false" 
                        :aria-controls="'collapse' + asignatura.id"
                      >
                        <div class="d-flex align-items-center w-100">
                          <div class="badge bg-dark text-white me-3 rounded-2" style="min-width: 60px;">
                            {{ asignatura.codigo_asignatura }}
                          </div>
                          <span class="text-truncate me-2">{{ asignatura.nombre_asignatura }}</span>
                          <span class="badge bg-secondary-subtle text-secondary ms-auto me-3 rounded-pill small-badge">
                            {{ asignatura.resultados_aprendizaje.length }} RAs
                          </span>
                        </div>
                      </button>
                    </h2>
                    
                    <div 
                      :id="'collapse' + asignatura.id" 
                      class="accordion-collapse collapse" 
                      :aria-labelledby="'heading' + asignatura.id"
                      data-bs-parent="#accordionAsignaturas"
                    >
                      <div class="accordion-body bg-light-subtle p-4">
                        
                        <h6 class="text-uppercase text-muted fs-7 fw-bold mb-3 ls-1">
                          Resultados de Aprendizaje
                        </h6>

                        <ul class="list-group mb-3 border-0">
                          <TransitionGroup name="list">
                            <li 
                              v-for="ra in asignatura.resultados_aprendizaje" 
                              :key="ra.id"
                              class="list-group-item d-flex justify-content-between align-items-center border-0 bg-white shadow-sm mb-2 rounded-3 px-3 py-2 ra-item"
                            >
                              <div class="d-flex align-items-start gap-3">
                                <i class="bi bi-check-circle-fill text-success mt-1"></i>
                                <span class="text-dark">{{ ra.descripcion }}</span>
                              </div>
                              <button 
                                class="btn btn-icon btn-outline-danger border-0 btn-sm rounded-circle opacity-0 transition-opacity" 
                                @click="eliminarRA(ra.id, asignatura.id)"
                                title="Eliminar RA"
                              >
                                <i class="bi bi-trash"></i>
                              </button>
                            </li>
                          </TransitionGroup>
                          
                          <li v-if="asignatura.resultados_aprendizaje.length === 0" class="text-muted fst-italic ps-2 mb-3 list-unstyled">
                            No hay resultados de aprendizaje registrados.
                          </li>
                        </ul>

                        <div class="bg-white p-3 rounded-3 border shadow-sm">
                          <label class="form-label small text-muted fw-bold mb-2">Añadir nuevo RA</label>
                          <div class="input-group">
                            <input 
                              v-model="nuevaRA[asignatura.id]" 
                              type="text" 
                              class="form-control border-end-0"
                              placeholder="Describe el resultado de aprendizaje..." 
                              @keyup.enter="crearRA(asignatura.id)"
                            />
                            <button 
                              class="btn btn-primary px-4" 
                              @click="crearRA(asignatura.id)"
                              :disabled="!nuevaRA[asignatura.id] || creatingRA[asignatura.id]"
                            >
                              <span v-if="creatingRA[asignatura.id]" class="spinner-border spinner-border-sm me-1"></span>
                              <span v-else><i class="bi bi-plus-lg me-1"></i> Añadir</span>
                            </button>
                          </div>
                        </div>

                      </div>
                    </div>
                  </div>
                </div>

              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>

<style scoped>
/* Transiciones Vue */
.fade-in {
  animation: fadeIn 0.5s ease-in-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

.list-enter-active,
.list-leave-active {
  transition: all 0.3s ease;
}
.list-enter-from,
.list-leave-to {
  opacity: 0;
  transform: translateX(-20px);
}

/* UI General */
.fs-7 { font-size: 0.8rem; }
.ls-1 { letter-spacing: 1px; }

/* Header Hero decoration */
.decorative-circle {
  position: absolute;
  top: -50px;
  right: -50px;
  width: 200px;
  height: 200px;
  background: radial-gradient(circle, rgba(13,110,253,0.1) 0%, rgba(255,255,255,0) 70%);
  border-radius: 50%;
  z-index: 0;
}

/* Accordion Customization */
.accordion-button:not(.collapsed) {
  background-color: rgba(13, 110, 253, 0.05);
  color: var(--bs-primary);
  box-shadow: none;
}
.accordion-button:focus {
  box-shadow: none;
  border-color: rgba(13, 110, 253, 0.2);
}
.accordion-item {
  border-color: rgba(0,0,0,0.08);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.accordion-item:hover {
  transform: translateY(-2px);
  box-shadow: 0 .5rem 1rem rgba(0,0,0,.08)!important;
}

/* Search Box */
.search-box {
  width: 100%;
  max-width: 300px;
}
@media (max-width: 768px) {
  .search-box {
    max-width: 100%;
    margin-top: 1rem;
  }
}

/* RA List Items */
.ra-item:hover .opacity-0 {
  opacity: 1 !important;
}
.btn-icon {
  width: 32px;
  height: 32px;
  padding: 0;
  display: flex;
  align-items: center;
  justify-content: center;
}

.bg-light-subtle {
  background-color: #f8f9fa;
}
</style>