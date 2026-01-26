import { defineStore } from "pinia";
import { ref } from "vue";
import { useAuthStore } from "./auth";
import type { FamiliaProfesional } from "@/interfaces/FamiliaProfesional";

const baseURL = import.meta.env.VITE_API_BASE_URL;

export const useFamiliaProfesionalesStore = defineStore(
  "familiasProfesionales",
  () => {
    const familiasProfesionales = ref<FamiliaProfesional[]>([]);
    const authStore = useAuthStore();

    async function fetchFamiliasProfesionales() {
      const response = await fetch(
        `${baseURL}/api/familiasProfesionales`,
        {
          headers: authStore.token
            ? {
                Authorization: `Bearer ${authStore.token}`,
                Accept: "application/json",
              }
            : {
                Accept: "application/json",
              },
        },
      );

      if (!response.ok) return false;

      const data = await response.json();
      familiasProfesionales.value = data as FamiliaProfesional[];
      return true;
    }

    return { familiasProfesionales, fetchFamiliasProfesionales };
  },
);
