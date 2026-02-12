<script setup lang="ts">
import ModalEditarEstancia from "@/components/Estancias/ModalEditarEstancia.vue";
import type { Alumno } from "@/interfaces/Alumno";
import type { Estancia } from "@/interfaces/Estancia";
import { useAlumnosStore } from "@/stores/alumnos";
import { computed, onMounted, ref } from "vue";
import { useRoute, useRouter } from "vue-router";

const route = useRoute();
const router = useRouter();
const alumnosStore = useAlumnosStore();

const alumnoId = Number(route.params.alumnoId);

const isLoading = ref(true);
const error = ref<string | null>(null);
const estanciaSeleccionada = ref<Estancia | null>(null);
const showModalEditar = ref(false);
const isDeleting = ref(false);

const alumno = computed<Alumno | null>(() => alumnosStore.alumnoDetalle);

const estanciaActual = computed<Estancia | null>(() => {
  const estancias = alumno.value?.estancias ?? [];
  if (estancias.length === 0) return null;

  const ordenadas = [...estancias].sort((a, b) => {
    const da = a.fecha_inicio ? new Date(a.fecha_inicio).getTime() : 0;
    const db = b.fecha_inicio ? new Date(b.fecha_inicio).getTime() : 0;
    return db - da;
  });

  return ordenadas[0] ?? null;
});

const formatDate = (dateString: string) => {
  const date = new Date(dateString);
  return date.toLocaleDateString("es-ES", {
    year: "numeric",
    month: "long",
    day: "numeric",
  });
};

const volver = () => router.back();

const abrirModalEditar = () => {
  if (estanciaActual.value) {
    estanciaSeleccionada.value = estanciaActual.value;
    showModalEditar.value = true;
  }
};

const cerrarModalEditar = () => {
  showModalEditar.value = false;
  estanciaSeleccionada.value = null;
};

const confirmarEliminar = async () => {
  if (!estanciaActual.value) return;

  const confirmar = confirm(
    "¿Estás seguro de que deseas eliminar esta estancia? Esta acción no se puede deshacer."
  );

  if (!confirmar) return;

  await eliminarEstancia();
};

const eliminarEstancia = async () => {
  if (!estanciaActual.value) return;

  isDeleting.value = true;
  try {
    const baseURL = import.meta.env.VITE_API_BASE_URL;
    const token = localStorage.getItem("token");

    const response = await fetch(
      `${baseURL}/api/estancias/${estanciaActual.value.id}`,
      {
        method: "DELETE",
        headers: {
          Authorization: `Bearer ${token}`,
          Accept: "application/json",
        },
      }
    );

    if (!response.ok) throw new Error("Error al eliminar la estancia");

    // Recargar datos del alumno
    await alumnosStore.fetchAlumnoDetalleAdmin(alumnoId);

    alert("Estancia eliminada exitosamente");
  } catch (e) {
    console.error(e);
    alert("Error al eliminar la estancia");
  } finally {
    isDeleting.value = false;
  }
};

const handleEstanciaActualizada = async () => {
  cerrarModalEditar();
  await alumnosStore.fetchAlumnoDetalleAdmin(alumnoId);
};

onMounted(async () => {
  try {
    isLoading.value = true;
    error.value = null;

    if (!Number.isFinite(alumnoId)) {
      error.value = "ID de alumno inválido";
      return;
    }

    await alumnosStore.fetchAlumnoDetalleAdmin(alumnoId);

    if (!alumno.value) {
      error.value = "Alumno no encontrado";
    }
  } catch (e) {
    console.error(e);
    error.value = "Error al cargar los datos del alumno";
  } finally {
    isLoading.value = false;
  }
});
</script>

<template>
  <div class="container mt-4">
    <!-- Loading -->
    <div v-if="isLoading" class="text-center py-5">
      <div class="spinner-border" style="color: #81045f" role="status">
        <span class="visually-hidden">Cargando...</span>
      </div>
      <p class="mt-3 text-muted fw-semibold">
        Cargando información del alumno...
      </p>
    </div>

    <!-- Error -->
    <div v-else-if="error" class="alert alert-danger d-flex align-items-center" role="alert">
      <i class="bi bi-exclamation-triangle-fill me-2"></i>
      <div>
        {{ error }}
        <button class="btn btn-sm btn-outline-danger ms-3" @click="volver">
          Volver
        </button>
      </div>
    </div>

    <!-- Not found -->
    <div v-else-if="!alumno" class="alert alert-warning d-flex align-items-center">
      <i class="bi bi-person-x-fill me-2"></i>
      <div>
        No se encontró información del alumno.
        <button class="btn btn-sm btn-outline-warning ms-3" @click="volver">
          Volver
        </button>
      </div>
    </div>

    <!-- Main -->
    <div v-else>
      <!-- Breadcrumb -->
      <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
          <li class="breadcrumb-item">
            <a href="#" @click.prevent="volver" class="text-decoration-none">
              <i class="bi bi-arrow-left me-1"></i>
              Alumnos
            </a>
          </li>
          <li class="breadcrumb-item active" aria-current="page">
            {{ alumno.nombre }} {{ alumno.apellidos }}
          </li>
        </ol>
      </nav>

      <!-- Card del alumno -->
      <div class="card mb-4 shadow-sm">
        <div class="card-body">
          <div class="d-flex align-items-center mb-3">
            <div class="avatar-large me-3">
              <i class="bi bi-person-fill"></i>
            </div>
            <div class="flex-grow-1">
              <h3 class="mb-1">{{ alumno.nombre }} {{ alumno.apellidos }}</h3>
            </div>
          </div>

          <div class="row g-3 mt-2">
            <div class="col-md-6" >
              <div class="info-item">
                <i class="bi bi-telephone-fill text-primary me-2"></i>
                <span class="text-muted">Teléfono:</span>
                <strong class="ms-2">{{ alumno.telefono ?? "No disponible" }}</strong>
              </div>
            </div>

            <div class="col-md-6" >
              <div class="info-item">
                <i class="bi bi-geo-alt-fill text-primary me-2"></i>
                <span class="text-muted">Ciudad:</span>
                <strong class="ms-2">{{ alumno.ciudad ?? "No disponible" }}</strong>
              </div>
            </div>

            <div class="col-md-6" >
              <div class="info-item">
                <i class="bi bi-building text-primary me-2"></i>
                <span class="text-muted">Empresa:</span>
                <strong class="ms-2">{{ estanciaActual?.empresa?.nombre ?? "No disponible" }}</strong>
              </div>
            </div>

            <div class="col-md-6">
              <div class="info-item">
                <i class="bi bi-briefcase-fill text-primary me-2"></i>
                <span class="text-muted">Puesto:</span>
                <strong class="ms-2">{{ estanciaActual?.puesto ?? "No disponible" }}</strong>
              </div>
            </div>

            <div class="col-md-6" >
              <div class="info-item">
                <i class="bi bi-clock-fill text-primary me-2"></i>
                <span class="text-muted">Horas totales:</span>
                <strong class="ms-2">{{ estanciaActual?.horas_totales ?? "No disponible" }}</strong>
              </div>
            </div>

            <div class="col-md-6">
              <div class="info-item">
                <i class="bi bi-calendar-range-fill text-primary me-2"></i>
                <span class="text-muted">Periodo:</span>
                <strong class="ms-2" v-if="estanciaActual?.fecha_inicio && estanciaActual?.fecha_fin">
                  {{
                    estanciaActual?.fecha_inicio
                      ? formatDate(estanciaActual.fecha_inicio)
                      : "Por definir"
                  }}
                  -
                  {{
                    estanciaActual?.fecha_fin
                      ? formatDate(estanciaActual.fecha_fin)
                      : "Por definir"
                  }}
                </strong>
                <strong v-else class="ms-2">No disponible</strong>
              </div>
            </div>
          </div>

          <!-- Botones de acción -->
          <div class="mt-4 d-flex gap-2" v-if="estanciaActual">
            <button class="btn btn-primary" @click="abrirModalEditar">
              <i class="bi bi-pencil-fill me-2"></i>
              Editar Estancia
            </button>
            <button class="btn btn-danger" @click="confirmarEliminar" :disabled="isDeleting">
              <i class="bi bi-trash-fill me-2"></i>
              {{ isDeleting ? "Eliminando..." : "Eliminar Estancia" }}
            </button>
          </div>

          <!-- Si no tiene estancias -->
          <div class="mt-3" v-if="!estanciaActual">
            <div class="alert alert-info mb-0">
              Este alumno todavía no tiene una estancia asignada.
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <ModalEditarEstancia v-if="showModalEditar && estanciaSeleccionada" :estancia="estanciaSeleccionada"
      :alumno-id="alumnoId" @close="cerrarModalEditar" @estancia-actualizada="handleEstanciaActualizada" />
  </div>
</template>

<style scoped>
.avatar-large {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background: linear-gradient(135deg, #81045f 0%, #4a90e2 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 2.5rem;
  flex-shrink: 0;
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
</style>
