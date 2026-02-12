<script setup lang="ts">
import type { Empresa } from "@/interfaces/Empresa";
import type { Instructor } from "@/interfaces/Instructor";
import { ref, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useAuthStore } from "@/stores/auth";
import { useInstructorStore } from "@/stores/instructor";

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const instructorStore = useInstructorStore();
const baseURL = import.meta.env.VITE_API_BASE_URL;

const empresa = ref<Empresa | null>(null);
const isLoading = ref(true);
const error = ref<string | null>(null);
const todosInstructores = ref<Instructor[]>([]);

// Modal de asignar instructor
const showAsignarModal = ref(false);
const selectedInstructorId = ref<number | null>(null);

const empresaId = Number(route.params.empresaId);

onMounted(async () => {
  await cargarDetalleEmpresa();
  await cargarTodosInstructores();
});

// Cargar detalle de empresa
const cargarDetalleEmpresa = async () => {
  isLoading.value = true;
  error.value = null;
  try {
    const response = await fetch(`${baseURL}/api/tutorEgibide/empresa/${empresaId}`, {
      headers: {
        Authorization: authStore.token ? `Bearer ${authStore.token}` : "",
        Accept: "application/json",
      },
    });
    if (!response.ok) throw new Error("Error al cargar los datos de la empresa");
    empresa.value = await response.json();
  } catch (err) {
    console.error(err);
    error.value = "No se pudo cargar la información de la empresa";
  } finally {
    isLoading.value = false;
  }
};

// Cargar todos los instructores
const cargarTodosInstructores = async () => {
  todosInstructores.value = await instructorStore.obtenerPorEmpresa(empresaId);
};

// Abrir modal de asignación
const abrirAsignarModal = () => {
  selectedInstructorId.value = null;
  showAsignarModal.value = true;
};

// Asignar o cambiar instructor
const asignarInstructor = async () => {
  if (!selectedInstructorId.value) return;
  try {
    const response = await fetch(
      `${baseURL}/api/tutorEgibide/empresa/${empresaId}/asignar-instructor`,
      {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Authorization: authStore.token ? `Bearer ${authStore.token}` : "",
        },
        body: JSON.stringify({ instructor_id: selectedInstructorId.value }),
      }
    );
    if (!response.ok) throw new Error("Error al asignar el instructor");

    await cargarDetalleEmpresa();
    instructorStore.setMessage("Instructor asignado correctamente", "success");
    showAsignarModal.value = false;
  } catch (err) {
    console.error(err);
    instructorStore.setMessage("No se pudo asignar el instructor", "error");
  }
};

const volver = () => router.back();
</script>

<template>
  <div class="container mt-4">
    <!-- Mensajes -->
    <div
      v-if="instructorStore.message"
      :class="[
        'alert',
        instructorStore.messageType === 'success' ? 'alert-success' : 'alert-danger',
        'd-flex',
        'align-items-center',
      ]"
      role="alert"
    >
      <i
        :class="[
          'bi',
          instructorStore.messageType === 'success'
            ? 'bi-check-circle-fill'
            : 'bi-exclamation-triangle-fill',
          'me-2',
        ]"
      ></i>
      <div>{{ instructorStore.message }}</div>
    </div>

    <!-- Estado de carga -->
    <div v-if="isLoading" class="text-center py-5">
      <div class="spinner-border" style="color: #81045f" role="status">
        <span class="visually-hidden">Cargando...</span>
      </div>
      <p class="mt-3 text-muted fw-semibold">Cargando información de la empresa...</p>
    </div>

    <!-- Error -->
    <div v-else-if="error" class="alert alert-danger d-flex align-items-center" role="alert">
      <i class="bi bi-exclamation-triangle-fill me-2"></i>
      <div>
        {{ error }}
        <button class="btn btn-sm btn-outline-danger ms-3" @click="volver">Volver a empresas</button>
      </div>
    </div>

    <!-- Sin empresa -->
    <div v-else-if="!empresa" class="alert alert-warning d-flex align-items-center">
      <i class="bi bi-building-x me-2"></i>
      <div>
        No se encontró información de la empresa
        <button class="btn btn-sm btn-outline-warning ms-3" @click="volver">Volver</button>
      </div>
    </div>

    <!-- Contenido principal -->
    <div v-else>
      <!-- Breadcrumb -->
      <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
          <li class="breadcrumb-item">
            <a href="#" @click.prevent="volver" class="text-decoration-none">
              <i class="bi bi-arrow-left me-1"></i> Empresas
            </a>
          </li>
          <li class="breadcrumb-item active" aria-current="page">{{ empresa.nombre }}</li>
        </ol>
      </nav>

      <!-- Cabecera de la empresa -->
      <div class="card mb-4 shadow-sm">
        <div class="card-body">
          <div class="d-flex align-items-center mb-3">
            <div class="avatar-large me-3">
              <i class="bi bi-building"></i>
            </div>
            <div class="flex-grow-1">
              <h3 class="mb-1">{{ empresa.nombre }}</h3>
            </div>
          </div>
          <div class="row g-3 mt-2">
            <div class="col-md-6" v-if="empresa.telefono">
              <div class="info-item"><i class="bi bi-telephone-fill text-primary me-2"></i><span class="text-muted">Teléfono:</span> <strong class="ms-2">{{ empresa.telefono }}</strong></div>
            </div>
            <div class="col-md-6" v-if="empresa.email">
              <div class="info-item"><i class="bi bi-envelope-fill text-primary me-2"></i><span class="text-muted">Email:</span> <strong class="ms-2">{{ empresa.email }}</strong></div>
            </div>
            <div class="col-md-6" v-if="empresa.cif">
              <div class="info-item"><i class="bi bi-card-text text-primary me-2"></i><span class="text-muted">CIF:</span> <strong class="ms-2">{{ empresa.cif }}</strong></div>
            </div>
            <div class="col-md-12" v-if="empresa.direccion">
              <div class="info-item"><i class="bi bi-geo-alt-fill text-primary me-2"></i><span class="text-muted">Dirección:</span> <strong class="ms-2">{{ empresa.direccion }}</strong></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Sección de instructores -->
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="bi bi-person-badge-fill me-2"></i>Instructores</h4>
        <button class="btn btn-primary" @click="abrirAsignarModal">
          <i class="bi bi-person-plus-fill me-2"></i> Asignar/Cambiar Instructor
        </button>
      </div>

      <!-- Lista de instructores actuales -->
      <div v-if="empresa.instructores && empresa.instructores.length > 0">
        <div v-for="instructor in empresa.instructores" :key="instructor.id" class="card shadow-sm instructor-card mb-3">
          <div class="card-body d-flex align-items-center">
            <div class="avatar-medium me-3"><i class="bi bi-person-fill"></i></div>
            <div class="flex-grow-1">
              <h5 class="mb-1">{{ instructor.nombre }} {{ instructor.apellidos }}</h5>
              <div v-if="instructor.telefono || instructor.ciudad">
                <small v-if="instructor.telefono" class="text-muted me-2"><i class="bi bi-telephone-fill me-1"></i>{{ instructor.telefono }}</small>
                <small v-if="instructor.ciudad" class="text-muted"><i class="bi bi-geo-alt-fill me-1"></i>{{ instructor.ciudad }}</small>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div v-else class="alert alert-info">
        <i class="bi bi-info-circle-fill me-2"></i>No hay instructores asignados a esta empresa.
      </div>

      <!-- Modal de asignar instructor -->
      <div v-if="showAsignarModal" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Asignar o cambiar instructor</h5>
              <button type="button" class="btn-close" @click="showAsignarModal = false"></button>
            </div>
            <div class="modal-body">
              <select v-model="selectedInstructorId" class="form-select">
                <option value="" disabled>Selecciona un instructor</option>
                <option v-for="inst in todosInstructores" :key="inst.id" :value="inst.id">
                  {{ inst.nombre }} {{ inst.apellidos }}
                </option>
              </select>
            </div>
            <div class="modal-footer">
              <button class="btn btn-secondary" @click="showAsignarModal = false">Cancelar</button>
              <button class="btn btn-primary" :disabled="!selectedInstructorId" @click="asignarInstructor">
                Guardar
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
    </div>
</template>

<style scoped>
.avatar-large {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background: linear-gradient(135deg, #81045f 0%, #2c3e50 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 2.5rem;
}
.avatar-medium {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  background: linear-gradient(135deg, #81045f 0%, #2c3e50 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1.8rem;
}
.instructor-card { border-left: 4px solid #81045f; }
.breadcrumb-item a { color: var(--bs-primary); }
.breadcrumb-item a:hover { color: var(--bs-primary); text-decoration: underline !important; }
.info-item { padding: 0.75rem; background-color: #f8f9fa; border-radius: 0.5rem; display: flex; align-items: center; }
</style>
