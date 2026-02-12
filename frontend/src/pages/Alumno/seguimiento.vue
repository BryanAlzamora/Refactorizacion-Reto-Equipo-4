<template>
    <div class="bg-light min-vh-100 py-5">
        <div class="container">

            <div class="row mb-5 align-items-center">
                <div class="col-md-8">
                    <h1 class="fw-bold text-dark mb-2">Mis Cuadernos</h1>
                    <p class="text-muted lead mb-0">Gestiona tus entregas y revisa el feedback docente.</p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <div class="d-inline-block bg-white px-4 py-2 rounded-3 shadow-sm border">
                        <span class="d-block h3 fw-bold text-primary mb-0">{{ alumnosStore.entregas.length }}</span>
                        <span class="small text-muted text-uppercase fw-bold">Tareas</span>
                    </div>
                </div>
            </div>

            <transition name="fade">
                <div v-if="alumnosStore.message" :class="['alert d-flex align-items-center shadow-sm',
                    alumnosStore.messageType === 'success' ? 'alert-success' : 'alert-danger']" role="alert">
                    <span class="fs-5 me-2">
                        {{ alumnosStore.messageType === 'success' ? '✓' : '⚠' }}
                    </span>
                    <div>{{ alumnosStore.message }}</div>
                </div>
            </transition>

            <div v-if="alumnosStore.loadingEntregas" class="text-center py-5">
                <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                    <span class="visually-hidden">Cargando...</span>
                </div>
                <p class="mt-3 text-muted">Sincronizando entregas...</p>
            </div>

            <div v-else>

                <div v-if="alumnosStore.entregas.length === 0" class="card border-0 shadow-sm py-5 text-center">
                    <div class="card-body">
                        <h3 class="h5 text-muted">No tienes tareas asignadas</h3>
                    </div>
                </div>

                <div v-for="entrega in alumnosStore.entregas" :key="entrega.id"
                    class="card border-0 shadow-sm mb-4 overflow-hidden hover-effect">

                    <div class="card-header bg-white py-3 px-4 border-bottom">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-3">
                                <h2 class="h5 fw-bold mb-0 text-dark">{{ entrega.descripcion }}</h2>
                                <span
                                    :class="['badge rounded-pill', isPastDeadline(entrega.fecha_limite) ? 'bg-secondary' : 'bg-success']">
                                    {{ isPastDeadline(entrega.fecha_limite) ? 'Cerrada' : 'Activa' }}
                                </span>
                            </div>
                            <small class="text-muted">
                                <i class="bi bi-clock me-1"></i> Límite: <strong>{{ formatDateTime(entrega.fecha_limite)
                                    }}</strong>
                            </small>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <div class="row g-4">

                            <div class="col-lg-4">
                                <h6 class="text-uppercase text-muted small fw-bold mb-3">Tu Entrega</h6>

<div v-if="!isPastDeadline(entrega.fecha_limite) && !entregaYaEvaluada(entrega)">
                                <label
                                        class="upload-zone d-flex flex-column align-items-center justify-content-center w-100 p-4 rounded-3 cursor-pointer">
                                        <div class="text-center">
                                            <svg class="mb-3 text-secondary" width="40" height="40" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12">
                                                </path>
                                            </svg>
                                            <p class="mb-1 fw-bold text-dark">Haz clic para subir</p>
                                            <small class="text-muted">PDF, DOC, ZIP (Max 10MB)</small>
                                        </div>
                                        <input type="file" class="d-none" accept=".pdf,.doc,.docx,.zip"
                                            @change="(e) => onFileChange(e, entrega.id)" />
                                    </label>
                                </div>

                                <label v-else
                                        class="upload-zone d-flex flex-column align-items-center justify-content-center w-100 p-4 rounded-3 cursor-pointer h-auto">
                                        <div class="text-center">
                                    <span class="d-block mb-2">🔒</span>
                                    <small>La entrega está cerrada</small>
                                        </div>

                                    </label>
                            </div>

                            <div class="col-lg-8">
                                <h6 class="text-uppercase text-muted small fw-bold mb-3">Historial de versiones</h6>

                                <div v-if="entrega.entregas.length > 0" class="timeline-container">
                                    <div class="list-group list-group-flush border-start border-2 ps-3">

                                        <div v-for="(e, index) in entrega.entregas" :key="e.id"
                                            class="list-group-item border-0 ps-0 pb-4 bg-transparent position-relative">
                                            <div
                                                class="timeline-dot bg-white border border-2 border-primary position-absolute rounded-circle">
                                            </div>

                                            <div class="card border bg-light">
                                                <div class="card-body py-2 px-3">
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <a :href="fileURL(e.url_entrega)" target="_blank"
                                                            class="text-decoration-none fw-bold text-primary">
                                                            📄 Ver Archivo
                                                        </a>
                                                        <span v-if="index === entrega.entregas.length - 1"
                                                            class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill"
                                                            style="font-size: 0.65rem;">ACTUAL</span>
                                                    </div>
                                                    <div class="small text-muted mb-2">{{
                                                        formatDateTime(e.fecha_entrega) }}</div>

                                                    <div v-if="e.feedback || e.observaciones"
                                                        class="mt-2 pt-2 border-top border-secondary-subtle">
                                                        <div v-if="e.feedback"
                                                            class="d-flex align-items-center gap-2 mb-1">
                                                            <span class="badge bg-success">Nota: {{ e.feedback }}</span>
                                                        </div>
                                                        <div v-if="e.observaciones"
                                                            class="small fst-italic text-dark bg-white p-2 rounded border">
                                                            "{{ e.observaciones }}"
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                <div v-else class="text-center py-5 border border-dashed rounded-3 bg-light text-muted">
                                    <small>Aún no has subido ninguna versión.</small>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted } from "vue";
import { useAlumnosStore } from "@/stores/alumnos";
const baseURL = import.meta.env.VITE_API_BASE_URL;
const alumnosStore = useAlumnosStore();

onMounted(async () => {
    await alumnosStore.fetchMisEntregas();
});

const onFileChange = async (event, entregaId) => {
    const file = event.target.files[0];
    if (!file) return;
    try {
        await alumnosStore.fetchAlumno();
        await alumnosStore.subirEntrega(file, entregaId);
        await alumnosStore.fetchMisEntregas();
        event.target.value = '';
    } catch (e) {
        console.error(e);
    }
};
const entregaYaEvaluada = (entrega) => {
    if (!entrega.entregas || entrega.entregas.length === 0) return false

    const ultima = entrega.entregas[entrega.entregas.length - 1]

    return !!(ultima.feedback || ultima.observaciones)
}

const formatDateTime = (dateStr) => {
    if (!dateStr) return '';
    const date = new Date(dateStr);
    return date.toLocaleDateString('es-ES', {
        day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit'
    });
};

const fileURL = (filename) => 
    `${baseURL}/storage/entregas/${filename}`;
const isPastDeadline = (fecha_limite) => new Date() > new Date(fecha_limite);
</script>

<style scoped>
/* Estilos personalizados mínimos
   para cosas que Bootstrap no trae por defecto
*/

/* Efecto hover suave en la tarjeta */
.hover-effect {
    transition: box-shadow 0.3s ease;
}

.hover-effect:hover {
    box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .15) !important;
}

/* Zona de carga con borde discontinuo */
.upload-zone {
    border: 2px dashed #dee2e6;
    /* Borde gris discontinuo */
    background-color: #f8f9fa;
    transition: all 0.2s;
}

.upload-zone:hover {
    border-color: #0d6efd;
    /* Color primario al hover */
    background-color: #e9ecef;
}

.cursor-pointer {
    cursor: pointer;
}

/* Ajustes para el Timeline manual */
.timeline-dot {
    width: 12px;
    height: 12px;
    left: -22px;
    /* Ajustar posición respecto al borde izquierdo */
    top: 20px;
}

/* Transición de fade para alertas */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

/* Compatibilidad para badges sutiles en Bootstrap versiones viejas */
.bg-primary-subtle {
    background-color: #cfe2ff;
    color: #084298;
}

.border-secondary-subtle {
    border-color: #e2e3e5;
}
</style>
