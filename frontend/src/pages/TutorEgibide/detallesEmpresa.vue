<script setup lang="ts">
import type { Empresa } from "@/interfaces/Empresa";
import { ref, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useAuthStore } from "@/stores/auth";
import { useTutorEgibideStore } from "@/stores/tutorEgibide";

interface Instructor {
  id: number;
  nombre: string;
  apellidos: string;
  telefono: string | null;
  ciudad: string | null;
}

interface EmpresaDetalle extends Empresa {
  instructores?: Instructor[];
}

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const tutorEgibideStore = useTutorEgibideStore();

const baseURL = import.meta.env.VITE_API_BASE_URL;

const empresa = ref<EmpresaDetalle | null>(null);
const isLoading = ref(true);
const error = ref<string | null>(null);

// Modal de instructor
const showInstructorModal = ref(false);
const instructorForm = ref({
  nombre: "",
  apellidos: "",
  telefono: "",
  ciudad: "",
});
const isEditingInstructor = ref(false);
const isSavingInstructor = ref(false);

const empresaId = Number(route.params.empresaId);

onMounted(async () => {
  await cargarDetalleEmpresa();
});

const cargarDetalleEmpresa = async () => {
  isLoading.value = true;
  error.value = null;

  try {
    const response = await fetch(
      `${baseURL}/api/tutorEgibide/empresa/${empresaId}`,
      {
        headers: {
          Authorization: authStore.token ? `Bearer ${authStore.token}` : "",
          Accept: "application/json",
        },
      }
    );

    if (!response.ok) {
      throw new Error("Error al cargar los datos de la empresa");
    }

    const data = await response.json();
    empresa.value = data;
  } catch (err) {
    console.error(err);
    error.value = "No se pudo cargar la información de la empresa";
  } finally {
    isLoading.value = false;
  }
};

const abrirModalInstructor = () => {
  if (empresa.value?.instructores && empresa.value.instructores.length > 0) {
    // Modo edición
    const instructor = empresa.value.instructores[0];
    instructorForm.value = {
      nombre: instructor.nombre,
      apellidos: instructor.apellidos,
      telefono: instructor.telefono || "",
      ciudad: instructor.ciudad || "",
    };
    isEditingInstructor.value = true;
  } else {
    // Modo creación
    instructorForm.value = {
      nombre: "",
      apellidos: "",
      telefono: "",
      ciudad: "",
    };
    isEditingInstructor.value = false;
  }
  showInstructorModal.value = true;
};

const guardarInstructor = async () => {
  isSavingInstructor.value = true;
  
  const success = await tutorEgibideStore.asignarInstructor(
    empresaId,
    instructorForm.value
  );

  isSavingInstructor.value = false;

  if (success) {
    showInstructorModal.value = false;
    await cargarDetalleEmpresa(); // Recargar datos
  }
};

const cerrarModal = () => {
  showInstructorModal.value = false;
};

const volver = () => {
  router.back();
};
</script>

<template>
  <div class="container mt-4">
    <!-- Mensajes -->
    <div
      v-if="tutorEgibideStore.message"
      :class="[
        'alert',
        tutorEgibideStore.messageType === 'success' ? 'alert-success' : 'alert-danger',
        'd-flex',
        'align-items-center'
      ]"
      role="alert"
    >
      <i
        :class="[
          'bi',
          tutorEgibideStore.messageType === 'success'
            ? 'bi-check-circle-fill'
            : 'bi-exclamation-triangle-fill',
          'me-2'
        ]"
      ></i>
      <div>{{ tutorEgibideStore.message }}</div>
    </div>

    <!-- Estado de carga -->
    <div v-if="isLoading" class="text-center py-5">
      <div class="spinner-border" style="color: #81045f;" role="status">
        <span class="visually-hidden">Cargando...</span>
      </div>
      <p class="mt-3 text-muted fw-semibold">Cargando información de la empresa...</p>
    </div>

    <!-- Error -->
    <div v-else-if="error" class="alert alert-danger d-flex align-items-center" role="alert">
      <i class="bi bi-exclamation-triangle-fill me-2"></i>
      <div>
        {{ error }}
        <button class="btn btn-sm btn-outline-danger ms-3" @click="volver">
          Volver a empresas
        </button>
      </div>
    </div>

    <!-- Sin empresa -->
    <div v-else-if="!empresa" class="alert alert-warning d-flex align-items-center">
      <i class="bi bi-building-x me-2"></i>
      <div>
        No se encontró información de la empresa
        <button class="btn btn-sm btn-outline-warning ms-3" @click="volver">
          Volver
        </button>
      </div>
    </div>

    <!-- Contenido principal -->
    <div v-else>
      <!-- Breadcrumb -->
      <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
          <li class="breadcrumb-item">
            <a href="#" @click.prevent="volver" class="text-decoration-none">
              <i class="bi bi-arrow-left me-1"></i>
              Empresas
            </a>
          </li>
          <li class="breadcrumb-item active" aria-current="page">
            {{ empresa.nombre }}
          </li>
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

          <!-- Información adicional de la empresa -->
          <div class="row g-3 mt-2">
            <div class="col-md-6" v-if="empresa.telefono">
              <div class="info-item">
                <i class="bi bi-telephone-fill text-primary me-2"></i>
                <span class="text-muted">Teléfono:</span>
                <strong class="ms-2">{{ empresa.telefono }}</strong>
              </div>
            </div>
            <div class="col-md-6" v-if="empresa.email">
              <div class="info-item">
                <i class="bi bi-envelope-fill text-primary me-2"></i>
                <span class="text-muted">Email:</span>
                <strong class="ms-2">{{ empresa.email }}</strong>
              </div>
            </div>
            <div class="col-md-6" v-if="empresa.cif">
              <div class="info-item">
                <i class="bi bi-card-text text-primary me-2"></i>
                <span class="text-muted">CIF:</span>
                <strong class="ms-2">{{ empresa.cif }}</strong>
              </div>
            </div>
            <div class="col-md-12" v-if="empresa.direccion">
              <div class="info-item">
                <i class="bi bi-geo-alt-fill text-primary me-2"></i>
                <span class="text-muted">Dirección:</span>
                <strong class="ms-2">{{ empresa.direccion }}</strong>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Instructores -->
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">
          <i class="bi bi-person-badge-fill me-2"></i>
          Instructor
        </h4>
        <button
          class="btn btn-primary"
          @click="abrirModalInstructor"
        >
          <i
            :class="[
              'bi',
              empresa.instructores && empresa.instructores.length > 0
                ? 'bi-pencil-fill'
                : 'bi-plus-circle-fill',
              'me-2'
            ]"
          ></i>
          {{ empresa.instructores && empresa.instructores.length > 0 ? 'Modificar' : 'Añadir' }} Instructor
        </button>
      </div>

      <div v-if="empresa.instructores && empresa.instructores.length > 0">
        <div class="row g-3">
          <div
            v-for="instructor in empresa.instructores"
            :key="instructor.id"
            class="col-12"
          >
            <div class="card h-100 shadow-sm instructor-card">
              <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                  <div class="avatar-medium me-3">
                    <i class="bi bi-person-fill"></i>
                  </div>
                  <div>
                    <h5 class="mb-0">{{ instructor.nombre }} {{ instructor.apellidos }}</h5>
                  </div>
                </div>

                <div class="instructor-info">
                  <div v-if="instructor.telefono" class="info-item mb-2">
                    <i class="bi bi-telephone-fill text-primary me-2"></i>
                    <span class="text-muted">Teléfono:</span>
                    <strong class="ms-2">{{ instructor.telefono }}</strong>
                  </div>
                  <div v-if="instructor.ciudad" class="info-item">
                    <i class="bi bi-geo-alt-fill text-primary me-2"></i>
                    <span class="text-muted">Ciudad:</span>
                    <strong class="ms-2">{{ instructor.ciudad }}</strong>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Mensaje si no hay instructores -->
      <div v-else class="alert alert-info">
        <i class="bi bi-info-circle-fill me-2"></i>
        No hay instructor asignado a esta empresa. Haz clic en "Añadir Instructor" para agregar uno.
      </div>
    </div>

    <!-- Modal de Instructor -->
    <div
      v-if="showInstructorModal"
      class="modal fade show d-block"
      tabindex="-1"
      style="background-color: rgba(0,0,0,0.5);"
    >
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              {{ isEditingInstructor ? 'Modificar' : 'Añadir' }} Instructor
            </h5>
            <button
              type="button"
              class="btn-close"
              @click="cerrarModal"
              :disabled="isSavingInstructor"
            ></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="guardarInstructor">
              <div class="mb-3">
                <label for="nombre" class="form-label">
                  Nombre <span class="text-danger">*</span>
                </label>
                <input
                  type="text"
                  class="form-control"
                  id="nombre"
                  v-model="instructorForm.nombre"
                  required
                  :disabled="isSavingInstructor"
                />
              </div>

              <div class="mb-3">
                <label for="apellidos" class="form-label">
                  Apellidos <span class="text-danger">*</span>
                </label>
                <input
                  type="text"
                  class="form-control"
                  id="apellidos"
                  v-model="instructorForm.apellidos"
                  required
                  :disabled="isSavingInstructor"
                />
              </div>

              <div class="mb-3">
                <label for="telefono" class="form-label">Teléfono</label>
                <input
                  type="text"
                  class="form-control"
                  id="telefono"
                  v-model="instructorForm.telefono"
                  :disabled="isSavingInstructor"
                />
              </div>

              <div class="mb-3">
                <label for="ciudad" class="form-label">Ciudad</label>
                <input
                  type="text"
                  class="form-control"
                  id="ciudad"
                  v-model="instructorForm.ciudad"
                  :disabled="isSavingInstructor"
                />
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button
              type="button"
              class="btn btn-secondary"
              @click="cerrarModal"
              :disabled="isSavingInstructor"
            >
              Cancelar
            </button>
            <button
              type="button"
              class="btn btn-primary"
              @click="guardarInstructor"
              :disabled="isSavingInstructor"
            >
              <span
                v-if="isSavingInstructor"
                class="spinner-border spinner-border-sm me-2"
              ></span>
              {{ isEditingInstructor ? 'Actualizar' : 'Guardar' }}
            </button>
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
  flex-shrink: 0;
}

.avatar-medium {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  background: linear-gradient(135deg, #81045f 0%, #2c3e50 100%);
  display: flex;
  justify-content: center;
  color: white;
  font-size: 1.8rem;
  flex-shrink: 0;
}

.instructor-card {
  transition: all 0.3s ease;
  border: 2px solid transparent;
}

.instructor-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15) !important;
  border-color: var(--bs-primary);
}

.breadcrumb-item a {
  color: var(--bs-primary);
}

.breadcrumb-item a:hover {
  color: var(--bs-primary);
  text-decoration: underline !important;
}

.info-item {
  padding: 0.75rem;
  background-color: #f8f9fa;
  border-radius: 0.5rem;
  display: flex;
  align-items: center;
}

.instructor-info .info-item {
  background-color: transparent;
  padding: 0.5rem 0;
}

.modal.show {
  display: block;
}
</style>