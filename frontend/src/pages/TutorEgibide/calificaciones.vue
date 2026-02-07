<script setup lang="ts">
import Toast from "@/components/Notification/Toast.vue";
import type { Alumno } from "@/interfaces/Alumno";
import type { Asignatura } from "@/interfaces/Asignatura";
import type { NotaCompetenciaTecnica, NotaEgibide } from "@/interfaces/Notas";
import { useAlumnosStore } from "@/stores/alumnos";
import { useCompetenciasStore } from "@/stores/competencias";
import { useTutorEgibideStore } from "@/stores/tutorEgibide";
import { ref, onMounted, computed } from "vue";
import { useRoute, useRouter } from "vue-router";

// Props: modo de uso del componente
const props = withDefaults(
  defineProps<{
    modo: 'tutor' | 'alumno';
  }>(),
  {
    modo: 'tutor'
  }
);


const route = useRoute();
const router = useRouter();

const tutorEgibideStore = useTutorEgibideStore();
const alumnoStore = useAlumnosStore();
const competenciasStore = useCompetenciasStore();

const asignaturas = ref<Asignatura[]>([]);
const notasEgibide = ref<NotaEgibide[]>([]);
const notasTecnicas = ref<NotaCompetenciaTecnica[]>([]);
const notaTransversal = ref<number>(0);
const notaCuaderno = ref<number | null>(null);
const notasEgibidePorAsignatura = ref<Record<number, number>>({});

const isLoading = ref(true);
const editando = ref(false);
const error = ref<string | null>(null);

// Computed: alumno según modo
const alumno = computed<Alumno | null>(() => {
  if (props.modo === 'tutor') {
    const alumnoIdRuta = Number(route.params.alumnoId);
    return tutorEgibideStore.alumnosAsignados.find(a => Number(a.id) === alumnoIdRuta) || null;
  }
  // modo alumno
  return alumnoStore.inicio?.alumno ?? null;
});

// Computed: id del alumno
const alumnoId = computed(() => alumno.value?.id ?? null);

// On mounted
onMounted(async () => {
  try {
    if (!alumnoId.value) {
      error.value = "Alumno no encontrado";
      return;
    }

    // Asignaturas
    await alumnoStore.getAsignaturasAlumno(alumnoId.value);
    asignaturas.value = alumnoStore.asignaturas;

    // Notas técnicas
    const response = await competenciasStore.calcularNotasTecnicasByAlumno(alumnoId.value);
    notasTecnicas.value = response.notas_competenciasTec;

    // Nota transversal
    const responseTrans = await competenciasStore.getNotaTransversalByAlumno(alumnoId.value);
    notaTransversal.value = responseTrans.nota_media;

    // Notas Egibide
    const responseEgibide = await alumnoStore.getNotasEgibideByAlumno(alumnoId.value);
    if (responseEgibide) {
      notasEgibide.value = alumnoStore.notasEgibide;
      notasEgibide.value.forEach(n => {
        notasEgibidePorAsignatura.value[n.asignatura_id] = Number(n.nota ?? 0);
      });
    }

    // Nota cuaderno
    await alumnoStore.getNotaCuadernoByAlumno(alumnoId.value);
    notaCuaderno.value = alumnoStore.notaCuaderno ?? 0;

  } catch (err) {
    console.error("Error al cargar calificaciones:", err);
    error.value = "Error al cargar calificaciones";
  } finally {
    isLoading.value = false;
  }
});

// Guardado autosave
const guardarNotaEgibide = async (asignaturaId: number) => {
  try {
    const nota = notasEgibidePorAsignatura.value[asignaturaId] ?? 0;
    await alumnoStore.guardarNotasEgibideByAlumno(alumnoId.value!, nota, asignaturaId);
  } catch (err) {
    console.error(err);
    alert("Error al guardar la nota Egibide");
  }
};

const guardarNotaCuaderno = async () => {
  try {
    await alumnoStore.guardarNotaCuadernoByAlumno(alumnoId.value!, notaCuaderno.value ?? 0);
  } catch (err) {
    console.error(err);
    alert("Error al guardar la nota del cuaderno");
  }
};

// Computeds
const notasTecnicasPorAsignatura = computed(() => {
  const map: Record<number, number> = {};
  notasTecnicas.value.forEach(n => {
    map[n.asignatura_id] = n.nota_media;
  });
  return map;
});

const notaFinalPorAsignatura = computed<Record<number, number>>(() => {
  const map: Record<number, number> = {};
  asignaturas.value.forEach(asignatura => {
    const egibide = Number(notasEgibidePorAsignatura.value[asignatura.id] ?? 0);
    const tecnica = notasTecnicasPorAsignatura.value[asignatura.id];
    const transversal = notaTransversal.value ?? 0;
    const cuaderno = notaCuaderno.value ?? 0;

    let notaEmpresa: number;

    if (tecnica != null) {
      notaEmpresa = tecnica * 0.6 + transversal * 0.2 + cuaderno * 0.2;
    } else {
      notaEmpresa = transversal * 0.8 + cuaderno * 0.2;
    }

    map[asignatura.id] = Math.round((egibide * 0.8 + notaEmpresa * 0.2) * 100) / 100;
  });

  return map;
});

const getNotaFinal = (asignaturaId: number) => {
  const nota = notaFinalPorAsignatura.value[asignaturaId];
  return isNaN(nota ?? NaN) ? "-" : nota;
};

const volver = () => router.back();
const volverAlumnos = () => { router.back(); router.back(); };

// Computed: controla si puede editar (solo tutor)
const puedeEditar = computed(() => props.modo === 'tutor');

</script>

<template>
  <Toast v-if="alumnoStore.message" :message="alumnoStore.message" :messageType="alumnoStore.messageType" />

  <div class="container mt-4">
    <!-- Estado de carga -->
    <div v-if="isLoading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Cargando...</span>
      </div>
      <p class="mt-3 text-muted">Cargando calificaciones del alumno...</p>
    </div>

    <!-- Error -->
    <div v-else-if="error" class="alert alert-danger d-flex align-items-center" role="alert">
      <i class="bi bi-exclamation-triangle-fill me-2"></i>
      <div>
        {{ error }}
        <button class="btn btn-sm btn-outline-danger ms-3" @click="volver">Volver</button>
      </div>
    </div>

    <!-- Contenido principal -->
    <div v-else-if="alumno">
      <!-- Breadcrumb -->
      <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
          <li class="breadcrumb-item">
            <a href="#" @click.prevent="volverAlumnos" class="text-decoration-none">
              <i class="bi bi-arrow-left"></i> Alumnos
            </a>
          </li>
          <li class="breadcrumb-item active" aria-current="page">
            {{ alumno.nombre }} {{ alumno.apellidos }}
          </li>
        </ol>
      </nav>

      <!-- Cabecera del alumno -->
      <div class="card mb-4 shadow-sm">
        <div class="card-header d-flex justify-content-between">
          <h3>Calificaciones</h3>
          <button class="btn btn-warning me-2" @click="editando = !editando" v-if="puedeEditar">
            {{ editando ? 'Cancelar Edición' : 'Editar Calificaciones' }}
          </button>
        </div>

        <div class="card-body">
          <table v-if="asignaturas.length" class="table table-bordered text-center align-middle border-primary shadow">
            <thead>
              <tr>
                <th rowspan="2" class="bg-primary"></th>
                <th rowspan="2" class="bg-primary text-white align-middle">EGIBIDE <br> 80%</th>
                <th colspan="3" class="bg-warning">EMPRESA 20%</th>
                <th rowspan="2" class="bg-success text-white align-middle">Nota Final</th>
              </tr>
              <tr>
                <th class="bg-secondary text-light">Técnico <br> 60%</th>
                <th class="bg-secondary text-light">Transversal <br> 20%</th>
                <th class="bg-secondary text-light">Cuaderno <br> 20%</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(asignatura, index) in asignaturas" :key="asignatura.id">
                <td class="fw-bold">{{ asignatura.codigo_asignatura }}</td>
                <td>
                  <input
                    type="number"
                    step="0.01"
                    min="0"
                    max="10"
                    v-model.number="notasEgibidePorAsignatura[asignatura.id]"
                    :disabled="!editando || !puedeEditar"
                    @blur="guardarNotaEgibide(asignatura.id)"
                    class="form-control form-control-sm text-center"
                  />
                </td>
                <td>{{ notasTecnicasPorAsignatura[asignatura.id] ?? "-" }}</td>
                <td v-if="index === 0" :rowspan="asignaturas.length">{{ notaTransversal }}</td>
                <td v-if="index === 0" :rowspan="asignaturas.length">
                  <input
                    type="number"
                    step="0.01"
                    min="0"
                    max="10"
                    v-model.number="notaCuaderno"
                    :disabled="!editando || !puedeEditar"
                    @blur="guardarNotaCuaderno()"
                    class="form-control form-control-sm text-center"
                  />
                </td>
                <td>{{ getNotaFinal(asignatura.id) }}</td>
              </tr>
            </tbody>
          </table>

          <div v-else class="alert alert-warning">
            Este alumno no tiene calificaciones
            <button class="btn btn-sm btn-outline-warning ms-3" @click="volver">Volver</button>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<style scoped>
.breadcrumb-item a {
  color: var(--bs-primary);
}
.breadcrumb-item a:hover {
  color: var(--bs-primary);
  text-decoration: underline !important;
}
</style>
