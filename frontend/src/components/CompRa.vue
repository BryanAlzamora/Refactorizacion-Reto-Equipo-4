<template>
  <div class="container py-4">

    <h2 class="mb-4 text-indigo fw-bold">
      Matriz Competencias Técnicas vs RAs
    </h2>

    <div class="mb-4 col-12 col-md-5">
     <label class="form-label text-muted small">Selecciona un Ciclo Formativo</label>
     <BuscadorSelect
        v-model="cicloSeleccionado"
        :options="ciclos"
        label-key="nombre"
        value-key="id"
        placeholder="Buscar ciclo..."
        @change="cargarMatriz"
      />
    </div>

    <div v-if="!cicloSeleccionado" class="alert alert-light border text-center text-muted">
        Selecciona un ciclo para ver la matriz de competencias.
    </div>

    <div v-else-if="asignaturas.length" class="table-responsive shadow-sm rounded">
      <table class="table table-bordered text-center align-middle mb-0 bg-white">

        <thead class="table-indigo text-white">
          <tr>
            <th rowspan="2" class="align-middle" style="min-width: 200px;">Módulo / Asignatura</th>
            <th rowspan="2" class="align-middle" style="min-width: 300px;">Resultado de Aprendizaje (RA)</th>
            <th :colspan="competencias.length" class="py-2">Competencias Técnicas</th>
          </tr>
          <tr>
            <th v-for="comp in competencias" :key="comp.id" class="small fw-normal px-1" style="writing-mode: vertical-rl; transform: rotate(180deg); min-height: 150px;">
              {{ comp.descripcion }}
            </th>
          </tr>
        </thead>

        <tbody>
          <template v-for="asignatura in asignaturas" :key="asignatura.id">
            <tr v-for="(ra, i) in asignatura.resultados_aprendizaje" :key="ra.id">
              
              <td v-if="i === 0" :rowspan="asignatura.resultados_aprendizaje.length" class="fw-bold text-start bg-light">
                {{ asignatura.nombre }}
              </td>

              <td class="text-start small">
                {{ ra.descripcion }}
              </td>

              <td v-for="comp in competencias" :key="comp.id" class="cell-icon position-relative"
                @click="!ra.loading && toggleCompRa(ra, comp)" 
                @mouseenter="ra.hoverCompId = comp.id"
                @mouseleave="ra.hoverCompId = null"
                :class="{'bg-light-hover': ra.hoverCompId === comp.id}">

                <div v-if="ra.loading && ra.loadingCompId === comp.id" class="position-absolute top-50 start-50 translate-middle">
                    <span class="spinner-border spinner-border-sm text-primary"></span>
                </div>

                <template v-else>
                    <span v-if="ra.hoverCompId === comp.id" class="text-muted opacity-50">
                        <i :class="tieneCompetencia(ra, comp.id) ? 'bi bi-x-lg text-danger' : 'bi bi-check-lg text-success'"></i>
                    </span>

                    <span v-else-if="tieneCompetencia(ra, comp.id)">
                        <i class="bi bi-check-circle-fill text-success fs-5"></i>
                    </span>
                </template>
              </td>

            </tr>
          </template>
        </tbody>

      </table>
    </div>
    
    <div v-else-if="cicloSeleccionado" class="alert alert-warning mt-3">
        No se han encontrado asignaturas o competencias para este ciclo.
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
const baseURL = import.meta.env.VITE_API_BASE_URL;
import BuscadorSelect from '@/components/BuscadorSelect.vue'

// Estado
const ciclos = ref([])
const cicloSeleccionado = ref('')
const competencias = ref([]) // Competencias Técnicas
const asignaturas = ref([])  // Asignaturas con sus RAs

// Verificar si existe la relación en el array cargado
const tieneCompetencia = (ra, compId) => {
    // Nota: 'competencias_tec' es el nombre de la relación que definimos en el Controller ("competenciasTec" -> JSON "competencias_tec")
    if (!ra || !ra.competencias_tec) return false;
    return ra.competencias_tec.some(c => c.id === compId);
}

// Carga inicial de Ciclos
onMounted(async () => {
  try {
    const { data } = await axios.get(baseURL+'/api/ciclos')
    // Ajustar si tu API devuelve paginación o data wrapper
    ciclos.value = Array.isArray(data) ? data : data.data || []; 
  } catch (error) {
    console.error("Error cargando ciclos:", error);
  }
})

// Cargar la Matriz cuando cambia el select
const cargarMatriz = async () => {
  if (!cicloSeleccionado.value) return;

  competencias.value = [];
  asignaturas.value = [];

  try {
    // ESTA RUTA DEBES CREARLA EN TU API.PHP (ver abajo)
    const { data } = await api.get(`/ciclo/${cicloSeleccionado.value}/matriz-competencias`);

    competencias.value = data.competencias;
    asignaturas.value = data.asignaturas;

    // Inicializar estados de UI para cada RA
    asignaturas.value.forEach(asig => {
      // Nota: usa 'resultados_aprendizaje' según el nuevo nombre de tabla
      if(asig.resultados_aprendizaje) {
          asig.resultados_aprendizaje.forEach(ra => {
            ra.hoverCompId = null;
            ra.loading = false;
            ra.loadingCompId = null;
            // Asegurar que el array existe para evitar errores
            if(!ra.competencias_tec) ra.competencias_tec = [];
          })
      }
    })
  } catch (error) {
    console.error("Error cargando matriz:", error);
  }
}

// Guardar / Eliminar relación
async function toggleCompRa(ra, comp) {
  const tiene = tieneCompetencia(ra, comp.id)

  // 1. Optimistic Update (Actualizar UI antes de respuesta del servidor)
  if (tiene) {
    ra.competencias_tec = ra.competencias_tec.filter(c => c.id !== comp.id)
  } else {
    // Empujamos un objeto simple con el ID para simular la relación
    ra.competencias_tec.push({ id: comp.id })
  }

  // 2. Estado de carga
  ra.loading = true
  ra.loadingCompId = comp.id

  try {
    // ESTA RUTA DEBES CREARLA EN TU API.PHP
    await api.post('/competencia-tec-ra/toggle', {
      competencia_tec_id: comp.id,
      resultado_aprendizaje_id: ra.id
      // Ya no enviamos ID_Asignatura, no es necesario en la nueva BBDD
    })
  } catch (err) {
    console.error(err)
    // 3. Revertir cambios si falla (Rollback)
    if (tiene) {
      ra.competencias_tec.push({ id: comp.id })
    } else {
      ra.competencias_tec = ra.competencias_tec.filter(c => c.id !== comp.id)
    }
    alert("Error al guardar la relación.");
  } finally {
    ra.loading = false
    ra.loadingCompId = null
  }
}
</script>

<style scoped>
.table-indigo {
  background-color: #6610f2; /* Color corporativo aproximado */
  color: white;
}

td.cell-icon {
  width: 50px;
  cursor: pointer;
  transition: background-color 0.2s;
}

.bg-light-hover {
    background-color: #f8f9fa;
}

/* Ajuste para que las cabeceras verticales se lean bien */
th {
    vertical-align: bottom;
}
</style>