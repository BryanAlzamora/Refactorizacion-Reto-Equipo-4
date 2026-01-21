<script setup lang="ts">
import Toast from "@/components/Notification/Toast.vue";
import type { Alumno } from "@/interfaces/Alumno";
import { useTutorEgibideStore } from "@/stores/tutorEgibide";
import { useAuthStore } from "@/stores/auth";
import { ref, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";

const route = useRoute();
const router = useRouter();

const tutorEgibideStore = useTutorEgibideStore();
const authStore = useAuthStore();

const alumno = ref<Alumno | null>(null);
const isLoading = ref(true);
const error = ref<string | null>(null);

// Horario y calendario
const calendarioInicio = ref("");
const calendarioFin = ref("");
const horasTotales = ref<number | null>(null);

// Obtener ID de alumno desde la ruta
const alumnoId = Number(route.params.alumnoId);

onMounted(async () => {
  try {
    alumno.value =
      tutorEgibideStore.alumnosAsignados.find((a: Alumno) => {
        return Number(a.pivot?.alumno_id) === alumnoId || Number(a.id) === alumnoId;
      }) || null;

    if (!alumno.value) {
      error.value = "Alumno no encontrado";
    }
  } catch (err) {
    console.error(err);
    error.value = "Error al cargar el alumno";
  } finally {
    isLoading.value = false;
  }
});

// Funciones de navegación
const volver = () => router.back();
const volverAlumnos = () => {
  router.back();
  router.back();
};

// Guardar horario y calendario
const guardarHorario = async () => {
  try {
    if (!calendarioInicio.value || !horasTotales.value) {
      return;
    }

    const payload = {
      alumno_id: alumnoId,
      fecha_inicio: calendarioInicio.value,
      fecha_fin: calendarioFin.value || null,
      horas_totales: horasTotales.value,
    };

    const response = await fetch("http://localhost:8000/api/horasperiodo", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Authorization: authStore.token ? `Bearer ${authStore.token}` : "",
      },
      body: JSON.stringify(payload),
    });

    let data: any = null;
    const contentType = response.headers.get("content-type") || "";
    if (contentType.includes("application/json")) {
      data = await response.json();
    }

    if (!response.ok) {
      console.error("Error backend:", data || response.statusText);
      return;
    }

    // Actualizar store local
    const alumnoStore = tutorEgibideStore.alumnosAsignados.find(
      (a) => Number(a.pivot?.alumno_id) === alumnoId || Number(a.id) === alumnoId
    );

    if (alumnoStore) {
      (alumnoStore as any).pivot = {
        ...(alumnoStore.pivot ?? { alumno_id: alumnoId }),
        fecha_inicio: calendarioInicio.value,
        fecha_fin: calendarioFin.value,
        horas_totales: horasTotales.value,
      };
    }

    volver();
  } catch (err) {
    console.error(err);
  }
};
</script>

<template>

  <Toast
    v-if="tutorEgibideStore.message"
    :message="tutorEgibideStore.message"
    :messageType="tutorEgibideStore.messageType"
  />

  <div class="container mt-4">
    <!-- Loading -->
    <div v-if="isLoading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status"></div>
      <p class="mt-3 text-muted">Cargando datos del alumno...</p>
    </div>

    <!-- Error -->
    <div v-else-if="error" class="alert alert-danger d-flex align-items-center">
      <i class="bi bi-exclamation-triangle-fill me-2"></i>
      <div>
        {{ error }}
        <button class="btn btn-sm btn-outline-danger ms-3" @click="volver">
          Volver
        </button>
      </div>
    </div>

    <!-- Contenido -->
    <div v-else>
      <!-- Breadcrumb -->
      <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
          <li class="breadcrumb-item">
            <a href="#" @click.prevent="volverAlumnos" class="text-decoration-none">
              <i class="bi bi-arrow-left"></i> Alumnos
            </a>
          </li>
          <li class="breadcrumb-item">
            <a href="#" @click.prevent="volver" class="text-decoration-none">
              {{ alumno?.nombre }} {{ alumno?.apellidos }}
            </a>
          </li>
          <li class="breadcrumb-item active">Asignar horas y periodo</li>
        </ol>
      </nav>

      <!-- Horario y calendario -->
      <div class="card shadow-sm">
        <div class="card-body">
          <p class="mb-4">
            Introduce las horas y periodo del alumno
            <b>{{ alumno?.nombre }} {{ alumno?.apellidos }}</b>
          </p>

          <!-- Calendario -->
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Fecha inicio *</label>
              <input
                type="date"
                class="form-control"
                v-model="calendarioInicio"
                required
              />
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label">Fecha fin</label>
              <input
                type="date"
                class="form-control"
                v-model="calendarioFin"
              />
            </div>
          </div>

          <!-- Horas totales -->
          <div class="mb-3">
            <label class="form-label">Horas totales *</label>
            <input
              type="number"
              min="1"
              class="form-control"
              v-model="horasTotales"
              placeholder="Ej: 370"
              required
            />
          </div>

          <!-- Acciones -->
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
