<script setup lang="ts">
import Toast from "@/components/Notification/Toast.vue";
import type { Alumno } from "@/interfaces/Alumno";
import { useTutorEgibideStore } from "@/stores/tutorEgibide";
import { ref, onMounted, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import { storeToRefs } from "pinia";

const route = useRoute();
const router = useRouter();

const tutorEgibideStore = useTutorEgibideStore();
const { message, messageType } = storeToRefs(tutorEgibideStore);

const alumno = ref<Alumno | null>(null);
const isLoading = ref(true);

const calendarioInicio = ref("");
const calendarioFin = ref("");
const horasSemanales = ref<number>(40);

const alumnoId = Number(route.params.alumnoId);

/* DIAS SEMANA */
const diasSemana = ["Lunes", "Martes", "Miercoles", "Jueves", "Viernes"];

/* HORARIO */
const horarios = ref(
  diasSemana.map((dia) => ({
    dia_semana: dia,
    tramos: [{ hora_inicio: "08:00", hora_fin: "17:00" }],
  }))
);

/* Días editados manualmente */
const diasEditados = ref<{ [dia: string]: boolean }>({});


/* FUNCIONES HORAS */
function timeToMinutes(time: string) {
  if (!time) return 0;
  const [h, m] = time.split(":").map(Number);
  return h * 60 + m;
}

function minutesToTime(mins: number) {
  const h = Math.floor(mins / 60);
  const m = mins % 60;
  return `${String(h).padStart(2, "0")}:${String(m).padStart(2, "0")}`;
}

/* Calcular horas totales semanales desde horario */
function calcularHorasSemanales() {
  let total = 0;
  horarios.value.forEach((dia) => {
    dia.tramos.forEach((t) => {
      total += timeToMinutes(t.hora_fin) - timeToMinutes(t.hora_inicio);
    });
  });
  return total / 60;
}

/* Generar horario automático según horas semanales */
function generarHorarioAutomatico() {
  const minutosPorDia = Math.round((horasSemanales.value * 60) / 5);
  horarios.value.forEach((dia) => {
    if (!diasEditados.value[dia.dia_semana]) {
      dia.tramos = [
        {
          hora_inicio: "08:00",
          hora_fin: minutesToTime(480 + minutosPorDia),
        },
      ];
    }
  });
  calcularHorasTotalesPeriodo();
}

/* Calcular horas totales del periodo desde fechas y horario diario */
const horasTotalesPeriodo = ref<number>(0);
function calcularHorasTotalesPeriodo() {
  if (!calendarioInicio.value || !calendarioFin.value) {
    horasTotalesPeriodo.value = 0;
    return;
  }

  const start = new Date(calendarioInicio.value);
  const end = new Date(calendarioFin.value);
  if (end < start) {
    horasTotalesPeriodo.value = 0;
    return;
  }

  let dias = 0;
  const temp = new Date(start);
  while (temp <= end) {
    const diaSemana = temp.getDay();
    if (diaSemana >= 1 && diaSemana <= 5) dias++;
    temp.setDate(temp.getDate() + 1);
  }

  const horasPorDia = calcularHorasSemanales() / 5;
  horasTotalesPeriodo.value = Math.round(horasPorDia * dias);
}

/* Marcar día como editado */
function marcarDiaEditado(dia: string) {
  diasEditados.value[dia] = true;
  calcularHorasTotalesPeriodo();
}


watch(horasSemanales, generarHorarioAutomatico);
watch([horarios, calendarioInicio, calendarioFin], calcularHorasTotalesPeriodo, { deep: true });


onMounted(() => {
  alumno.value =
    tutorEgibideStore.alumnosAsignados.find(
      (a) => Number(a.id) === alumnoId
    ) || null;

  isLoading.value = false;
  generarHorarioAutomatico();
});


/* GUARDAR */
const guardarHorario = async () => {
  if (!alumno.value?.estancias?.[0]?.id) {
    alert("Este alumno no tiene estancia asignada");
    return;
  }

  const estanciaId = alumno.value.estancias[0].id;

  /* Guardar periodo + horas semanales */
  const ok = await tutorEgibideStore.guardarHorarioAlumno(
    alumnoId,
    calendarioInicio.value,
    calendarioFin.value,
    horasTotalesPeriodo.value
  );
  if (!ok) return;

  /*  Guardar horario semanal usando el mismo store de tutorEgibide */
  const horarioGuardado = await tutorEgibideStore.guardarHorarioSemanal(
    estanciaId,
    horarios.value
  );

  if (horarioGuardado) {
    setTimeout(() => router.back(), 1200);
  }
};

/* Volver */
const volver = () => router.back();
const volverAlumnos = () => {
  router.back();
  router.back();
};
</script>


<template>
  <Toast v-if="message" :message="message" :messageType="messageType" />

  <div class="container mt-4">
    <div v-if="isLoading" class="text-center py-5">
      <div class="spinner-border" style="color: #81045f;" role="status">
        <span class="visually-hidden">Cargando...</span>
      </div>
      <p class="mt-3 text-muted fw-semibold">
        Cargando información del alumno...
      </p>
    </div>

    <div v-else-if="!alumno" class="alert alert-danger d-flex align-items-center">
      <i class="bi bi-person-x-fill me-2"></i>
      <div>
        Alumno no encontrado
        <button class="btn btn-sm btn-outline-danger ms-3" @click="volver">
          Volver
        </button>
      </div>
    </div>

    <div v-else>
      <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
          <li class="breadcrumb-item">
            <i class="bi bi-arrow-left me-1"></i>
            <a href="#" @click.prevent="volverAlumnos">Alumnos</a>
          </li>
          <li class="breadcrumb-item">
            <a href="#" @click.prevent="volver">
              {{ alumno?.nombre }} {{ alumno?.apellidos }}
            </a>
          </li>
          <li class="breadcrumb-item active">Asignar horas y periodo</li>
        </ol>
      </nav>

      <div class="card shadow-sm">
        <div class="card-header">
          <h3 class="mb-1">Horas y periodo</h3>
        </div>

        <div class="card-body">
          <p>
            Introduce las horas y periodo del alumno
            <b>{{ alumno?.nombre }}</b>
          </p>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label>Fecha inicio *</label>
              <input type="date" class="form-control" v-model="calendarioInicio" />
            </div>
            <div class="col-md-6 mb-3">
              <label>Fecha fin</label>
              <input type="date" class="form-control" v-model="calendarioFin" />
            </div>
          </div>

          <div class="mb-3">
            <label>Horas semanales *</label>
            <input type="number" min="1" class="form-control" v-model="horasSemanales" />
          </div>

          <div class="mb-3">
            <label>Horas totales del periodo</label>
            <input type="number" class="form-control" :value="horasTotalesPeriodo" readonly />
          </div>

          <hr />
          <h5 class="mt-3">Horario semanal</h5>

          <div v-for="(h, i) in horarios" :key="i" class="mb-3">
            <label class="fw-bold">{{ h.dia_semana }}</label>
            <div v-for="(t, j) in h.tramos" :key="j" class="row mt-2">
              <div class="col-md-6">
                <input
                  type="time"
                  class="form-control"
                  v-model="t.hora_inicio"
                  @input="marcarDiaEditado(h.dia_semana)"
                />
              </div>
              <div class="col-md-6">
                <input
                  type="time"
                  class="form-control"
                  v-model="t.hora_fin"
                  @input="marcarDiaEditado(h.dia_semana)"
                />
              </div>
            </div>
          </div>

          <div class="d-flex justify-content-end gap-2 mt-4">
            <button class="btn btn-outline-secondary" @click="volver">Cancelar</button>
            <button class="btn btn-primary" @click="guardarHorario">Guardar</button>
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
