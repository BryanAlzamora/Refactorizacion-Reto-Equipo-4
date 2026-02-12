<script setup>
import { computed } from "vue";
import { useAuthStore } from "@/stores/auth";

const authStore = useAuthStore();

const userRole = computed(() => authStore.currentUser?.role);
const userId = computed(() => authStore.currentUser?.id);
</script>

<template>
  <aside class="sidebar card shadow-sm border-0">
    <div class="card-body p-3">

      <!-- ALUMNO -->
      <nav v-if="userRole === 'alumno'">
        <h6 class="text-uppercase text-muted mb-3">Alumno</h6>
        <ul class="nav flex-column gap-1">
          <li class="nav-item">
            <RouterLink to="/alumno/inicio" class="nav-link">Inicio</RouterLink>
          </li>
          <li class="nav-item">
            <RouterLink to="/alumno/mis-datos" class="nav-link">Mis Datos</RouterLink>
          </li>
          <li class="nav-item">
            <RouterLink to="/alumno/empresa" class="nav-link">Empresa</RouterLink>
          </li>
          <li class="nav-item">
            <RouterLink to="/alumno/seguimiento" class="nav-link">Entrega</RouterLink>
          </li>
          <li class="nav-item">
            <RouterLink
              :to="`/alumno/${userId}/calificacion`"
              class="nav-link fw-semibold"
            >
              Calificación
            </RouterLink>
          </li>
        </ul>
      </nav>

      <!-- TUTOR EGIBIDE -->
      <nav v-else-if="userRole === 'tutor_egibide'">
        <h6 class="text-uppercase text-muted mb-3">Tutor Egibide</h6>
        <ul class="nav flex-column gap-1">
          <li class="nav-item">
            <RouterLink to="/tutor_egibide/inicio" class="nav-link">Inicio</RouterLink>
          </li>
          <li class="nav-item">
            <RouterLink to="/tutor_egibide/alumnos" class="nav-link">Mis Alumnos</RouterLink>
          </li>
          <li class="nav-item">
            <RouterLink to="/tutor_egibide/empresas" class="nav-link">Empresas</RouterLink>
          </li>
          <li class="nav-item">
            <RouterLink to="/tutor_egibide/mis-entregas" class="nav-link">Mis Entregas</RouterLink>
          </li>
          <li class="nav-item">
            <RouterLink to="/tutor_egibide/grados" class="nav-link">Mis Grados</RouterLink>
          </li>
           <li class="nav-item">
            <RouterLink to="/tutor_egibide/instructores" class="nav-link">
              Instructores
            </RouterLink>
          </li>
        </ul>
      </nav>

      <!-- TUTOR EMPRESA -->
      <nav v-else-if="userRole === 'tutor_empresa'">
        <h6 class="text-uppercase text-muted mb-3">Empresa</h6>
        <ul class="nav flex-column gap-1">
          <li class="nav-item">
            <RouterLink to="/tutor_empresa/inicio" class="nav-link">Inicio</RouterLink>
          </li>
          <li class="nav-item">
            <RouterLink to="/tutor_empresa/alumnos-asignados" class="nav-link">
              Alumnos Asignados
            </RouterLink>
          </li>
        </ul>
      </nav>

      <!-- ADMIN -->
      <nav v-else-if="userRole === 'admin'">
        <h6 class="text-uppercase text-muted mb-3">Administrador</h6>
        <ul class="nav flex-column gap-1">
          <li class="nav-item" v-for="(item, i) in [
            ['/admin/inicio','Inicio'],
            ['/admin/ciclos','Ciclos'],
            ['/admin/competencias','Competencias'],
            ['/admin/alumnos','Alumnos'],
            ['/admin/empresas','Empresas'],
            ['/admin/matriz-competencias','Competencias vs Ras'],
            ['/admin/nuevo-ciclo','Nuevo Ciclo'],
            ['/admin/nueva-competencia','Nueva Competencia'],
            ['/admin/nueva-empresa','Nueva Empresa'],
            ['/admin/nuevo-alumno','Nuevo Alumno'],
            ['/admin/importar','Importar Datos']
          ]" :key="i">
            <RouterLink :to="item[0]" class="nav-link">
              {{ item[1] }}
            </RouterLink>
          </li>
        </ul>
      </nav>

      <!-- DEFAULT -->
      <div v-else class="text-center text-muted py-4">
        <i class="bi bi-exclamation-circle fs-4"></i>
        <p class="mt-2 mb-0">Usuario no identificado</p>
      </div>

    </div>
  </aside>
</template>

<style scoped>
.sidebar {
  min-height: 100vh;
  border-radius: 1rem;
}

.nav-link {
  border-radius: 0.5rem;
  color: #495057;
}

.nav-link:hover {
  background-color: #f1f3f5;
}

.router-link-active {
  background-color: #0d6efd;
  color: #fff !important;
}
</style>
