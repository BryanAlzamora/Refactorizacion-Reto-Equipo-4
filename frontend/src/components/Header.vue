<script setup lang="ts">
import router from "@/router";
import { useAuthStore } from "@/stores/auth";

const authStore = useAuthStore();
const user = authStore.currentUser;
function logout() {
  authStore.logout();
}
</script>

<template>
  <nav class="navbar navbar-expand-lg island">
    <div class="container-fluid">
      <img src="../assets/images/logotipo.png" alt="logotipo" height="50" @click="router.push('/'+user?.role+'/inicio')" class=" cursor-pointer"/>
      <div class="dropdown">
        <button class="btn p-0 border-0 bg-transparent" type="button" data-bs-toggle="dropdown" aria-expanded="false">
          <i class="bi bi-three-dots-vertical fs-5"></i>
        </button>

        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
          <li>
            <button class="dropdown-item d-flex align-items-center gap-2 text-danger" @click="logout">
              <i class="bi bi-box-arrow-right"></i>
              Cerrar sesión
            </button>
          </li>
        </ul>
      </div>
    </div>
  </nav>
</template>

<style scoped></style>
