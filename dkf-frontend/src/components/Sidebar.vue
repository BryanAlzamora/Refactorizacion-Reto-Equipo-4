<script setup lang="ts">
import { computed } from "vue";
import { useAuthStore } from "@/stores/auth";

interface SidebarOption {
  label: string;
  icon?: string;
  route?: string;
}

const authStore = useAuthStore();

const optionsByRole: Record<string, SidebarOption[]> = {
  Alumno: [
    { label: "📁 Subir cuaderno" },
    { label: "ℹ️ Ver información" },
    { label: "📝 Consultar notas" },
  ],
  TutorEmpresa: [
    { label: "✅ Elegir competencias" },
    { label: "📝 Evaluarlas" },
    { label: "ℹ️ Ver información personas" },
  ],
  TutorCentro: [
    { label: "🗓️ Asignar horario/calendario" },
    { label: "🏢 Asignar empresa" },
    { label: "ℹ️ Ver información" },
    { label: "📊 Seguimiento" },
  ],
  Admin: [
    { label: "➕ Añadir ciclos/personas/empresas/competencias" },
    { label: "🌐 Ver todo" },
  ],
};

const sidebarOptions = computed(() => {
  if (!authStore.currentUser) return [];
  return optionsByRole[authStore.currentUser.role] || [];
});
</script>

<template>
  <aside class="sidebar p-3 rounded shadow-sm bg-white">
    <ul class="nav flex-column gap-2">
      <li v-for="(option, index) in sidebarOptions" :key="index" class="nav-item">
        <a class="nav-link text-black" href="#">{{ option.label }}</a>
      </li>
    </ul>
  </aside>
</template>

<style scoped>
.sidebar {
  width: 280px;
  min-width: 240px;
}
</style>
