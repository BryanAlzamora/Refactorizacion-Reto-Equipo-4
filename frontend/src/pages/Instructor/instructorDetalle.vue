<script setup lang="ts">
import { ref, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useAuthStore } from "@/stores/auth";
import type { Instructor } from "@/interfaces/Instructor";

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();

const instructor = ref<Instructor | null>(null);
const loading = ref(false);
const error = ref<string | null>(null);
const baseURL = import.meta.env.VITE_API_BASE_URL;

// Cargar detalles del instructor
const cargarInstructor = async () => {
  loading.value = true;
  error.value = null;

  try {
    const instructorId = route.params.id;
    console.log('Cargando instructor ID:', instructorId);
    console.log('URL:', `${baseURL}/api/instructores/${instructorId}`);
    
    const response = await fetch(
      `${baseURL}/api/instructores/${instructorId}`,
      {
        headers: {
          Authorization: authStore.token ? `Bearer ${authStore.token}` : "",
          Accept: "application/json",
        },
      }
    );

    console.log('Response status:', response.status);

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const data = await response.json();
    console.log('Data recibida:', data);

    // El backend retorna { success: true, instructor: {...} }
    if (data.success && data.instructor) {
      instructor.value = data.instructor;
    } else {
      // Si el backend retorna directamente el instructor (sin success)
      instructor.value = data;
    }

  } catch (err) {
    console.error('Error completo:', err);
    error.value = "Error al cargar los detalles del instructor";
  } finally {
    loading.value = false;
  }
};

const volver = () => {
  router.push("/tutor_egibide/instructores");
};

onMounted(() => {
  cargarInstructor();
});
</script>

<template>
  <div class="container mt-4">
    <!-- Loading -->
    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Cargando...</span>
      </div>
      <p class="mt-2">Cargando detalles del instructor...</p>
    </div>

    <!-- Error -->
    <div v-else-if="error" class="alert alert-danger">
      {{ error }}
      <button class="btn btn-sm btn-outline-danger ms-3" @click="volver">
        Volver
      </button>
    </div>

    <!-- Detalles del Instructor -->
    <div v-else-if="instructor" class="card">
      <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h4 class="mb-0">Detalles del Instructor</h4>
      </div>

      <div class="card-body">
        <div class="row">
          <!-- Información Personal -->
          <div class="col-md-6 mb-4">
            <h5 class="border-bottom pb-2 mb-3">Información Personal</h5>
            
            <div class="mb-3">
              <label class="text-muted small">Nombre completo</label>
              <p class="fw-bold mb-0">
                {{ instructor.nombre }} {{ instructor.apellidos }}
              </p>
            </div>

            <div class="mb-3" v-if="instructor.telefono">
              <label class="text-muted small">Teléfono</label>
              <p class="mb-0">
                📞 {{ instructor.telefono }}
              </p>
            </div>

            <div class="mb-3" v-if="instructor.ciudad">
              <label class="text-muted small">Ciudad</label>
              <p class="mb-0">
                📍 {{ instructor.ciudad }}
              </p>
            </div>
          </div>

          <!-- Información de Empresa -->
          <div class="col-md-6 mb-4">
            <h5 class="border-bottom pb-2 mb-3">Información de Empresa</h5>
            
            <div class="mb-3" v-if="instructor.empresa">
              <label class="text-muted small">Empresa</label>
              <p class="fw-bold mb-0">
                {{ instructor.empresa.nombre }}
              </p>
            </div>

            <div class="mb-3">
              <label class="text-muted small">ID de Instructor</label>
              <p class="mb-0">
                <span class="badge bg-secondary">#{{ instructor.id }}</span>
              </p>
            </div>

            <div class="mb-3" v-if="instructor.user_id">
              <label class="text-muted small">Cuenta de usuario</label>
              <p class="mb-0">
                <span class="badge bg-success">
                  ✓ Cuenta activa
                </span>
              </p>
            </div>
            <div class="mb-3" v-else>
              <label class="text-muted small">Cuenta de usuario</label>
              <p class="mb-0">
                <span class="badge bg-warning text-dark">
                  ⚠ Sin cuenta
                </span>
              </p>
            </div>
          </div>
        </div>

        <!-- Información de Sistema -->
        <div class="row mt-3" v-if="instructor.created_at">
          <div class="col-12">
            <h5 class="border-bottom pb-2 mb-3">Información del Sistema</h5>
            
            <div class="row">
              <div class="col-md-6 mb-2">
                <label class="text-muted small">Fecha de registro</label>
                <p class="mb-0">
                  {{ new Date(instructor.created_at).toLocaleString('es-ES') }}
                </p>
              </div>
              
              <div class="col-md-6 mb-2" v-if="instructor.updated_at">
                <label class="text-muted small">Última actualización</label>
                <p class="mb-0">
                  {{ new Date(instructor.updated_at).toLocaleString('es-ES') }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Acciones -->
        <div class="row mt-4">
          <div class="col-12">
            <div class="d-flex gap-2">
              <button class="btn btn-outline-secondary" @click="volver">
                ← Volver a la lista
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.card {
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.card-header {
  font-weight: 500;
}

label.text-muted {
  font-size: 0.85rem;
  margin-bottom: 0.25rem;
  display: block;
  font-weight: 600;
}

.border-bottom {
  border-color: #dee2e6 !important;
}
</style>