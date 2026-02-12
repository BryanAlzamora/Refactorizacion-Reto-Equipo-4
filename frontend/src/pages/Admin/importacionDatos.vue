<script setup lang="ts">
import { ref } from "vue";
import axios from "axios";
import { useAuthStore } from "@/stores/auth";

const authStore = useAuthStore();
const baseURL = import.meta.env.VITE_API_BASE_URL;

/* =====================
   SWITCH DE VISTA
===================== */
const vistaActiva = ref<"alumnos" | "asignaciones">("alumnos");

/* =====================
   ALUMNOS
===================== */
const archivoAlumnos = ref<File | null>(null);
const isImportingAlumnos = ref(false);

const handleFileAlumnos = (event: Event) => {
  const target = event.target as HTMLInputElement;
  archivoAlumnos.value = target.files?.[0] || null;
};

const subirAlumnos = async () => {
  if (!archivoAlumnos.value) return;

  isImportingAlumnos.value = true;
  const formData = new FormData();
  formData.append("file", archivoAlumnos.value);

  try {
    const response = await axios.post(
      `${baseURL}/api/importar-alumnos`,
      formData,
      {
        headers: {
          "Content-Type": "multipart/form-data",
          Authorization: `Bearer ${authStore.token}`,
        },
      }
    );

    alert("¡Éxito!: " + response.data.message);
    archivoAlumnos.value = null;
  } catch (err: any) {
    alert("Error: " + (err.response?.data?.message || "Error al importar"));
  } finally {
    isImportingAlumnos.value = false;
  }
};

/* =====================
   ASIGNACIONES
===================== */
const archivoAsignaciones = ref<File | null>(null);
const isImportingAsignaciones = ref(false);

const handleFileAsignaciones = (event: Event) => {
  const target = event.target as HTMLInputElement;
  archivoAsignaciones.value = target.files?.[0] || null;
};

const subirAsignaciones = async () => {
  if (!archivoAsignaciones.value) return;

  isImportingAsignaciones.value = true;
  const formData = new FormData();
  formData.append("file", archivoAsignaciones.value);

  try {
    const response = await axios.post(
      `${baseURL}/api/importar-asignaciones`,
      formData,
      {
        headers: {
          "Content-Type": "multipart/form-data",
          Authorization: `Bearer ${authStore.token}`,
        },
      }
    );

    alert("¡Éxito!: " + response.data.message);
    archivoAsignaciones.value = null;
  } catch (err: any) {
    alert("Error: " + (err.response?.data?.message || "Error al importar"));
  } finally {
    isImportingAsignaciones.value = false;
  }
};
</script>

<template>
  <!-- SWITCH -->
  <div class="d-flex justify-content-center mb-4">
    <div class="btn-group">
      <button
        class="btn"
        :class="vistaActiva === 'alumnos' ? 'btn-primary' : 'btn-outline-primary'"
        @click="vistaActiva = 'alumnos'"
      >
        <i class="bi bi-people-fill me-1"></i>
        Datos
      </button>

      <button
        class="btn"
        :class="vistaActiva === 'asignaciones' ? 'btn-primary' : 'btn-outline-primary'"
        @click="vistaActiva = 'asignaciones'"
      >
        <i class="bi bi-diagram-3-fill me-1"></i>
        Asignaciones
      </button>
    </div>
  </div>

  <!-- AVISO WARNING (FUERA DE LA CARD) -->
  <div
    v-if="vistaActiva === 'alumnos'"
    class="alert alert-warning d-flex align-items-center mb-3 shadow-sm aviso-previo"
    role="alert"
  >
    <i class="bi bi-exclamation-triangle-fill me-2"></i>
    <span>
      Antes de importar datos, se recomienda añadir primero las
      <strong>asignaciones</strong>.
    </span>
  </div>

  <!-- CARD ALUMNOS -->
  <div v-if="vistaActiva === 'alumnos'" class="card shadow-sm import-card">
    <div class="card-body d-flex justify-content-between align-items-center">
      <div>
        <h4 class="mb-1">
          <i class="bi bi-people-fill me-2"></i>
          Importar Datos
        </h4>
        <small class="text-muted">
          Archivo Excel (.xls o .xlsx)
        </small>
      </div>

      <div class="text-end">
        <input
          type="file"
          class="form-control form-control-sm mb-2"
          @change="handleFileAlumnos"
          accept=".xls, .xlsx"
        />

        <button
          class="btn btn-primary btn-sm w-100"
          :disabled="!archivoAlumnos || isImportingAlumnos"
          @click="subirAlumnos"
        >
          <span
            v-if="isImportingAlumnos"
            class="spinner-border spinner-border-sm me-2"
          ></span>
          {{ isImportingAlumnos ? "Procesando..." : "Subir archivo" }}
        </button>
      </div>
    </div>
  </div>

  <!-- CARD ASIGNACIONES -->
  <div v-if="vistaActiva === 'asignaciones'" class="card shadow-sm import-card">
    <div class="card-body d-flex justify-content-between align-items-center">
      <div>
        <h4 class="mb-1">
          <i class="bi bi-diagram-3-fill me-2"></i>
          Importar Asignaciones
        </h4>
        <small class="text-muted">
          Archivo CSV
        </small>
      </div>

      <div class="text-end">
        <input
          type="file"
          class="form-control form-control-sm mb-2"
          @change="handleFileAsignaciones"
          accept=".csv"
        />

        <button
          class="btn btn-primary btn-sm w-100"
          :disabled="!archivoAsignaciones || isImportingAsignaciones"
          @click="subirAsignaciones"
        >
          <span
            v-if="isImportingAsignaciones"
            class="spinner-border spinner-border-sm me-2"
          ></span>
          {{ isImportingAsignaciones ? "Procesando..." : "Subir archivo" }}
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.import-card {
  border-left: 4px solid #0d6efd;
  animation: fade 0.25s ease-in;
}

.aviso-previo {
  border-left: 4px solid #ffc107;
  background-color: #fff8e1;
}

@keyframes fade {
  from {
    opacity: 0;
    transform: translateY(6px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
