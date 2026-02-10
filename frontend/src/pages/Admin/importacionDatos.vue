<script setup lang="ts">
import { ref } from "vue";
import axios from "axios";
import { useAuthStore } from "@/stores/auth";

const authStore = useAuthStore();
const baseURL = import.meta.env.VITE_API_BASE_URL;

//alumnos
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

//asignaciones
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
  <div class="card mb-4 shadow-sm card-small-text">
    <div class="card-body d-flex justify-content-between align-items-center">
      <h3 class="mb-0">Importar Alumnos</h3>

      <div>
        <input
          type="file"
          class="form-control mb-2"
          @change="handleFileAlumnos"
          accept=".xls, .xlsx"
        />

        <button v-if="archivoAlumnos"
          type="button"
          class="btn btn-primary"
          @click="subirAlumnos"

          >
        <span
          v-if="isImportingAlumnos"
          class="spinner-border spinner-border-sm me-2"
        ></span>
          {{ isImportingAlumnos ? "Procesando..." : "Subir Archivo" }}
        </button>

      </div>
    </div>
  </div>
  <div class="card mb-4 shadow-sm card-small-text">
    <div class="card-body d-flex justify-content-between align-items-center">
      <h3 class="mb-0">Importar Asignaciones</h3>

      <div>
        <input
            type="file"
            class="form-control mb-2"
            @change="handleFileAsignaciones"
            accept=".csv"
          />

          <button v-if ="archivoAsignaciones"
            type="button"
            class="btn btn-primary"
            @click="subirAsignaciones"            >
            <span
              v-if="isImportingAsignaciones"
              class="spinner-border spinner-border-sm me-2"
            ></span>
            {{ isImportingAsignaciones ? "Procesando..." : "Subir Archivo" }}
          </button>

      </div>
    </div>
  </div>
</template>
