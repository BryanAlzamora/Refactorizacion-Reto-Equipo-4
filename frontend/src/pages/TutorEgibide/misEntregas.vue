<script setup lang="ts">
import type { AlumnoEntrega } from '@/interfaces/AlumnoEntrega';
import { useAuthStore } from '@/stores/auth';
import { useTutorEgibideStore } from '@/stores/tutorEgibide';
import { onMounted, ref } from 'vue';
import Toast from '@/components/Notification/Toast.vue';
const authStore = useAuthStore();
const tutorStore = useTutorEgibideStore();
const baseURL = import.meta.env.VITE_API_BASE_URL;
// --- EDICIÓN ---
const editandoId = ref<number | null>(null);
const editData = ref<{ observaciones: string; feedback: string }>({
    observaciones: '',
    feedback: ''
});

function editar(cuaderno: AlumnoEntrega) {
    editandoId.value = cuaderno.id;
    editData.value.observaciones = cuaderno.observaciones || '';
    editData.value.feedback = cuaderno.feedback || '';
}

async function guardar(cuaderno: AlumnoEntrega) {
    await tutorStore.actualizarEntregaAlumno(
        cuaderno.id,
        editData.value.observaciones,
        editData.value.feedback
    );
    await tutorStore.fetchEntregas(String(tutorStore.tutor?.id));

    editandoId.value = null;
}

// --- CREACIÓN NUEVA ENTREGA ---
const nuevaEntrega = ref<{ descripcion: string; fecha_limite: string }>({
    descripcion: '',
    fecha_limite: ''
});
const mostrandoFormulario = ref(false);

async function crearNuevaEntrega() {
    if (!nuevaEntrega.value.descripcion || !nuevaEntrega.value.fecha_limite) {
        alert('Rellena todos los campos');
        return;
    }

    await tutorStore.crearEntrega(nuevaEntrega.value.descripcion, nuevaEntrega.value.fecha_limite);
    nuevaEntrega.value.descripcion = '';
    nuevaEntrega.value.fecha_limite = '';
    mostrandoFormulario.value = false;
    tutorStore.fetchEntregas(String(tutorStore.tutor?.id));

}
const fileURL = (filename: string) => 
    `${baseURL}/storage/entregas/${filename}`;
onMounted(async () => {
    await tutorStore.fetchInicioTutor();

    const tutorId = tutorStore.tutor?.id;
    if (tutorId) {
        await tutorStore.fetchEntregas(String(tutorId));
    }
});
</script>

<template>
    <div class="container mt-4">
        <Toast v-if="tutorStore.message" :message="tutorStore.message" :type="tutorStore.messageType" />
        <!-- CABECERA -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Mis Entregas</h2>
            <button class="btn btn-primary" @click="mostrandoFormulario = true">
                + Nueva Entrega
            </button>
        </div>

        <!-- CARGANDO -->
        <div v-if="tutorStore.loading" class="alert alert-info">
            Cargando...
        </div>

        <!-- SIN ENTREGAS -->
        <div v-else-if="tutorStore.entregas.length === 0" class="alert alert-warning">
            No hay entregas creadas.
        </div>

        <!-- LISTA DE ENTREGAS -->
        <div v-for="entrega in tutorStore.entregas" :key="entrega.id" class="card mb-4 shadow-sm">
            <div class="card-header bg-primary text-white">
                <strong>{{ entrega.descripcion }}</strong>
                <div class="small">
                    Creada: {{ new Date(entrega.fecha_creacion).toLocaleDateString() }} |
                    Límite: {{ new Date(entrega.fecha_limite).toLocaleDateString() }}
                </div>
            </div>

            <div class="card-body">

                <div v-if="entrega.entregas.length === 0" class="text-muted">
                    Ningún alumno ha entregado aún.
                </div>

                <div v-for="cuaderno in entrega.entregas" :key="cuaderno.id" class="border rounded p-3 mb-3">
                    <div class="d-flex justify-content-between">
                        <h5>
                            {{ cuaderno.alumno.nombre }} {{ cuaderno.alumno.apellidos }}
                        </h5>

                        <a :href="fileURL(cuaderno.url_entrega)" target="_blank"
                            class="btn btn-sm btn-outline-primary">
                            Ver PDF
                        </a>
                    </div>

                    <p class="mb-1">
                        Fecha entrega: {{ cuaderno.fecha_entrega }}
                    </p>

                    <!-- MODO EDICIÓN -->
                    <div v-if="editandoId === cuaderno.id">

                        <div class="mb-2">
                            <label class="form-label">Observaciones</label>
                            <textarea v-model="editData.observaciones" class="form-control"></textarea>
                        </div>

                        <div class="mb-2">
                            <label class="form-label">Feedback</label>
                            <select v-model="editData.feedback" class="form-select">
                                <option value="">Sin valorar</option>
                                <option value="Bien">Bien</option>
                                <option value="Regular">Regular</option>
                                <option value="Debe mejorar">Debe mejorar</option>
                            </select>
                        </div>

                        <button class="btn btn-success btn-sm me-2" @click="guardar(cuaderno)">
                            Guardar
                        </button>
                        <button class="btn btn-secondary btn-sm" @click="editandoId = null">
                            Cancelar
                        </button>
                    </div>

                    <!-- MODO NORMAL -->
                    <div v-else>
                        <p>
                            Observaciones: {{ cuaderno.observaciones || 'Sin observaciones' }}
                        </p>
                        <p>
                            Feedback:
                            <span class="badge" :class="{
                                'bg-success': cuaderno.feedback === 'Bien',
                                'bg-warning text-dark': cuaderno.feedback === 'Regular',
                                'bg-danger': cuaderno.feedback === 'Debe mejorar',
                                'bg-secondary': !cuaderno.feedback
                            }">
                                {{ cuaderno.feedback || 'Sin valorar' }}
                            </span>
                        </p>

                        <button class="btn btn-outline-dark btn-sm" @click="editar(cuaderno)">
                            Editar
                        </button>
                    </div>

                </div>
            </div>
        </div>

        <!-- MODAL NUEVA ENTREGA -->
        <div v-if="mostrandoFormulario" class="modal d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Nueva Entrega</h5>
                        <button type="button" class="btn-close" @click="mostrandoFormulario = false"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Descripción</label>
                            <input type="text" class="form-control" v-model="nuevaEntrega.descripcion">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Fecha límite</label>
                            <input type="date" class="form-control" v-model="nuevaEntrega.fecha_limite">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" @click="mostrandoFormulario = false">Cancelar</button>
                        <button class="btn btn-primary" @click="crearNuevaEntrega">Crear</button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>
