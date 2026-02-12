<script setup lang="ts">
import Toast from "@/components/Notification/Toast.vue";
import type { Alumno } from "@/interfaces/Alumno";
import { useTutorEgibideStore } from "@/stores/tutorEgibide";
import { ref, onMounted, computed, watch } from "vue";
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

const alumnoId = Number(route.params.alumnoId);

/* DIAS LABORABLES */
const diasSemana = ["Lunes", "Martes", "Miercoles", "Jueves", "Viernes"];

/* HORARIO */
const horarios = ref(
  diasSemana.map((dia) => ({
    dia_semana: dia,
    tramos: [{ hora_inicio: "", hora_fin: "" }],
    pausa_minutos: 0,
  }))
);

/* HORARIO BASE (L-J) */
const baseInicio = ref("08:15");
const baseFin = ref("17:00");
const basePausa = ref(30);

/* ---------------------------------------- */
/* FUNCIONES */
/* ---------------------------------------- */

function timeToMinutes(time: string) {
  if (!time) return 0;
  const [h, m] = time.split(":").map(Number);
  return h * 60 + m;
}

/* Horas efectivas por día */
function calcularHorasDia(index: number) {
  const dia = horarios.value[index];
  const tramo = dia.tramos[0];

  const inicio = timeToMinutes(tramo.hora_inicio);
  const fin = timeToMinutes(tramo.hora_fin);

  if (fin <= inicio) return 0;

  const total = fin - inicio;
  const efectivo = total - (dia.pausa_minutos || 0);

  return efectivo / 60;
}

/* ---------------------------------------- */
/*HORAS SEMANALES */
/* ---------------------------------------- */

const horasSemanales = computed(() => {
  let total = 0;
  for (let i = 0; i < 5; i++) {
    total += calcularHorasDia(i);
  }
  return total;
});

/* ---------------------------------------- */
/* SEMANAS ENTRE FECHAS */
/* ---------------------------------------- */

function calcularSemanasEntreFechas() {
  if (!calendarioInicio.value || !calendarioFin.value) return 0;

  const start = new Date(calendarioInicio.value);
  const end = new Date(calendarioFin.value);

  if (end < start) return 0;

  const diffMs = end.getTime() - start.getTime();
  const diffDias = diffMs / (1000 * 60 * 60 * 24);

  return diffDias / 7;
}

/* ---------------------------------------- */
/* HORAS TOTALES CORRECTAS */
/* ---------------------------------------- */

const horasTotales = computed(() => {
  const semanas = calcularSemanasEntreFechas();
  return Math.round(semanas * horasSemanales.value);
});

/* ---------------------------------------- */
/* APLICAR HORARIO BASE A L-J */
/* ---------------------------------------- */

function aplicarHorarioBase() {
  for (let i = 0; i < 4; i++) {
    horarios.value[i].tramos[0].hora_inicio = baseInicio.value;
    horarios.value[i].tramos[0].hora_fin = baseFin.value;
    horarios.value[i].pausa_minutos = basePausa.value;
  }
}

/* Watch para que se actualicen L-J */
watch([baseInicio, baseFin, basePausa], aplicarHorarioBase);

/* ---------------------------------------- */
/* MOUNT */
/* ---------------------------------------- */

onMounted(() => {
  alumno.value =
    tutorEgibideStore.alumnosAsignados.find(
      (a) => Number(a.id) === alumnoId
    ) || null;

  /* Valores iniciales */
  aplicarHorarioBase();

  /* Viernes */
  horarios.value[4].tramos[0].hora_inicio = "08:00";
  horarios.value[4].tramos[0].hora_fin = "15:00";
  horarios.value[4].pausa_minutos = 0;

  isLoading.value = false;
});

/* ---------------------------------------- */
/* GUARDAR */
/* ---------------------------------------- */

const guardarHorario = async () => {
  if (!alumno.value?.estancias?.[0]?.id) return;

  const estanciaId = alumno.value.estancias[0].id;

  await tutorEgibideStore.guardarHorarioAlumno(
    alumnoId,
    calendarioInicio.value,
    calendarioFin.value,
    horasTotales.value
  );

  await tutorEgibideStore.guardarHorarioSemanal(estanciaId, horarios.value);

  tutorEgibideStore.message = "Horario guardado correctamente";
  tutorEgibideStore.messageType = "success";

  setTimeout(() => router.back(), 1200);
};

const volver = () => router.back();
</script>

<template>
  <Toast v-if="message" :message="message" :messageType="messageType" />

  <div class="container mt-4">
    <div v-if="isLoading" class="text-center py-5">
      <div class="spinner-border"></div>
    </div>

    <div v-else>
      <div class="card shadow-sm">
        <div class="card-header">
          <h3>Horas y periodo</h3>
        </div>

        <div class="card-body">
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

          <hr />

          <!-- HORARIO BASE -->
          <h5>Horario base (Lunes a Jueves)</h5>

          <div class="row g-2 mb-4">
            <div class="col-md-3">
              <label>Inicio</label>
              <input type="time" class="form-control" v-model="baseInicio" />
            </div>

            <div class="col-md-3">
              <label>Fin</label>
              <input type="time" class="form-control" v-model="baseFin" />
            </div>

            <div class="col-md-3">
              <label>Pausa</label>
              <input type="number" class="form-control" v-model.number="basePausa" />
            </div>
          </div>

          <!-- Horario semanal editable -->
          <h5>Horario semanal completo</h5>

          <div v-for="(h, i) in horarios" :key="i" class="mb-3">
            <label class="fw-bold">{{ h.dia_semana }}</label>

            <div class="row g-2">
              <div class="col-md-3">
                <input type="time" class="form-control" v-model="h.tramos[0].hora_inicio" />
              </div>

              <div class="col-md-3">
                <input type="time" class="form-control" v-model="h.tramos[0].hora_fin" />
              </div>

              <div class="col-md-3">
                <input type="number" class="form-control" v-model.number="h.pausa_minutos" />
              </div>

              <div class="col-md-3 text-center">
                <span class="badge bg-secondary">
                  {{ calcularHorasDia(i).toFixed(2) }}h
                </span>
              </div>
            </div>
          </div>

          <hr />

          <!-- Totales -->
          <div class="alert alert-info">
            <strong>Horas semanales:</strong> {{ horasSemanales.toFixed(2) }}h<br />
            <strong>Horas totales del periodo:</strong> {{ horasTotales }}h
          </div>

          <!-- Botones -->
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
