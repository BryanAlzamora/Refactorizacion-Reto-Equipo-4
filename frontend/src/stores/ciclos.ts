import type { Ciclo } from "@/interfaces/Ciclo";
import { defineStore } from "pinia";
import { ref } from "vue";
import { useAuthStore } from "./auth";

export const useCiclosStore = defineStore("ciclos", () => {
  const ciclos = ref<Ciclo[]>([]);
  const authStore = useAuthStore();
  const error = ref<string | null>(null);

  // Obtener todos los ciclos
  async function fetchCiclos() {
    const response = await fetch("http://localhost:8000/api/ciclos", {
      method: "GET",
      headers: authStore.token
        ? {
            Authorization: `Bearer ${authStore.token}`,
            Accept: "application/json",
          }
        : {
            Accept: "application/json",
          },
    });

    const data = await response.json();
    ciclos.value = data as Ciclo[];
  }

  async function createCiclo(nombre: string, familia_profesional_id: number) {
    const response = await fetch("http://localhost:8000/api/ciclos", {
      method: "POST",
      headers: {
        Authorization: authStore.token ? `Bearer ${authStore.token}` : "",
        Accept: "application/json",
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ nombre, familia_profesional_id }),
    });

    const data = await response.json();

    if (!response.ok) {
      error.value = data.message || "Error desconocido, intentalo más tarde";
      setTimeout(() => {
        error.value = null;
      }, 5000);
      return false;
    } else {
    }
  }

  function getCiclosPorFamilia(id_familia: number) {
    return ciclos.value.filter(
      (ciclo) => ciclo.familia_profesional_id === id_familia,
    );
  }

  return { ciclos, error, fetchCiclos, createCiclo, getCiclosPorFamilia };
});
