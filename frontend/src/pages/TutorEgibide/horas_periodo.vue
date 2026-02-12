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

/* ✅ Horas totales reales que mete el tutor */
const horasTotales = ref<number>(420);

/* Total calculado */
const horasTotalesPeriodo = ref<number>(0);

const alumnoId = Number(route.params.alumnoId);

/* ✅ SOLO LABORABLES */
const diasSemana = ["Lunes", "Martes", "Miercoles", "Jueves", "Viernes"];

/* ✅ Horario editable independiente */
const horarios = ref(
  diasSemana.map((dia) => ({
    dia_semana: dia,
    tramos: [{ hora_inicio: "08:00", hora_fin: "15:00" }],
  }))
);

/* ---------------------------------------- */
/* FUNCIONES */
/* ---------------------------------------- */

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

/* Horas de un día */
function calcularHorasDia(index: number) {
  const dia = horarios.value[index];
  if (!dia) return 0;

  const tramo = dia.tramos[0];

  const inicio = timeToMinutes(tramo.hora_inicio);
  const fin = timeToMinutes(tramo.hora_fin);

  if (fin <= inicio) return 0;

  return (fin - inicio) / 60;
}

/* Contar días laborables entre fechas */
function contarDiasLaborables() {
  if (!calendarioInicio.value || !calendarioFin.value) return 0;

  const start = new Date(calendarioInicio.value);
  const end = new Date(calendarioFin.value);

  let dias = 0;
  const temp = new Date(start);

  while (temp <= end) {
    const day = temp.getDay();

    if (day >= 1 && day <= 5) dias++; // lunes-viernes

    temp.setDate(temp.getDate() + 1);
  }

  return dias;
}

/* ✅ Repartir horas automáticamente */
function repartirHoras() {
  const diasLaborables = contarDiasLaborables();
  if (diasLaborables === 0) return;

  const horasPorDia = horasTotales.value / diasLaborables;
  const minutosPorDia = Math.round(horasPorDia * 60);

  horarios.value.forEach((dia) => {
    dia.tramos[0].hora_inicio = "08:00";
    dia.tramos[0].hora_fin = minutesToTime(480 + minutosPorDia);
  });

  horasTotalesPeriodo.value = Math.round(horasTotales.value);
}

/* ✅ Si modificas manualmente un día → recalcula total real */
function recalcularTotalDesdeHorario() {
  const diasLaborables = contarDiasLaborables();
  if (diasLaborables === 0) return;

  let total = 0;

  for (let i = 0; i < 5; i++) {
    total += calcularHorasDia(i);
  }

  horasTotalesPeriodo.value = Math.round(total * (diasLaborables / 5));
}

/* ---------------------------------------- */
/* WATCHERS */
/* ---------------------------------------- */

/* Si cambias horas totales o fechas → repartir */
watch([horasTotales, calendarioInicio, calendarioFin], repartirHoras);

/* Si cambias un día manualmente → recalcular */
watch(horarios, recalcularTotalDesdeHorario, { deep: true });

/* ---------------------------------------- */
/* MOUNT */
/* ---------------------------------------- */

onMounted(() => {
  alumno.value =
    tutorEgibideStore.alumnosAsignados.find(
      (a) => Number(a.id) === alumnoId
    ) || null;

  isLoading.value = false;
});

/* ---------------------------------------- */
/* GUARDAR */
/* ---------------------------------------- */

const guardarHorario = async () => {
  if (!alumno.value?.estancias?.[0]?.id) {
    alert("Este alumno no tiene estancia asignada");
    return;
  }

  const estanciaId = alumno.value.estancias[0].id;

  const ok = await tutorEgibideStore.guardarHorarioAlumno(
    alumnoId,
    calendarioInicio.value,
    calendarioFin.value,
    horasTotalesPeriodo.value
  );

  if (!ok) return;

  await tutorEgibideStore.guardarHorarioSemanal(estanciaId, horarios.value);

  tutorEgibideStore.message = "Horario guardado correctamente";
  tutorEgibideStore.messageType = "success";

  setTimeout(() => router.back(), 1200);
};

/* Volver */
const volver = () => router.back();
</script>

<template>
  <Toast v-if="message" :message="message" :messageType="messageType" />

  <div class="container mt-4">
    <div v-if="isLoading" class="text-center py-5">
      <div class="spinner-border" style="color: #81045f;" role="status"></div>
    </div>

    <div v-else-if="!alumno" class="alert alert-danger">
      Alumno no encontrado
    </div>

    <div v-else>
      <div class="card shadow-sm">
        <div class="card-header">
          <h3>Horas y periodo</h3>
        </div>

        <div class="card-body">
          <p>
            Introduce el horario del alumno <b>{{ alumno.nombre }}</b>
          </p>

          <!-- Fechas -->
          <div class="row">
            <div class="col-md-6 mb-3">
              <label>Fecha inicio *</label>
              <input type="date" class="form-control" v-model="calendarioInicio" />
            </div>

            <div class="col-md-6 mb-3">
              <label>Fecha fin *</label>
              <input type="date" class="form-control" v-model="calendarioFin" />
            </div>
          </div>

          <!-- Horas totales -->
          <div class="mb-3">
            <label>Horas totales reales *</label>
            <input
              type="number"
              min="1"
              class="form-control"
              v-model="horasTotales"
            />
          </div>

          <hr />

          <!-- Horario -->
          <h5>Horario semanal (Lunes a Viernes)</h5>

          <div v-for="(h, i) in horarios" :key="i" class="mb-3">
            <label class="fw-bold">{{ h.dia_semana }}</label>

            <div class="row mt-2">
              <div class="col-md-5">
                <input type="time" class="form-control" v-model="h.tramos[0].hora_inicio" />
              </div>

              <div class="col-md-5">
                <input type="time" class="form-control" v-model="h.tramos[0].hora_fin" />
              </div>

              <div class="col-md-2 d-flex align-items-center">
                <span class="badge bg-secondary">
                  {{ calcularHorasDia(i).toFixed(2) }}h
                </span>
              </div>
            </div>
          </div>

          <hr />

          <div class="alert alert-info">
            <strong>Horas totales del periodo:</strong>
            {{ horasTotalesPeriodo }} horas
          </div>

          <div class="d-flex justify-content-end gap-2">
            <button class="btn btn-outline-secondary" @click="volver">
              Cancelar
            </button>

            <button class="btn btn-primary" @click="guardarHorario">
              Guardar
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
