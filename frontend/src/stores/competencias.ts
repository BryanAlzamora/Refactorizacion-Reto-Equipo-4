import type { Competencia } from "@/interfaces/Competencia";
import { defineStore } from "pinia";
import { ref } from "vue";
import { useAuthStore } from "./auth";
import type { CompetenciaCalificada } from "@/interfaces/CompetenciaCalificada";

export const useCompetenciasStore = defineStore("competencias", () => {
  const competencias = ref<Competencia[]>([]);
  const competenciasTransversales = ref<Competencia[]>([]);
  const authStore = useAuthStore();

  const message = ref<string | null>(null);
  const messageType = ref<"success" | "error">("success");

  function setMessage(text: string, type: "success" | "error", timeout = 5000) {
    message.value = text;
    messageType.value = type;

    setTimeout(() => {
      message.value = null;
      messageType.value = "success";
    }, timeout);
  }

  async function fetchCompetencias() {
    const response = await fetch("http://localhost:8000/api/competencias", {
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
    competenciasTransversales.value = data as Competencia[];
  }

  async function fetchCompetenciasTecnicasByAlumno(alumno_id: number) {
    const response = await fetch(
      `http://localhost:8000/api/competenciasTecnicas/alumno/${alumno_id}`,
      {
        method: "GET",
        headers: {
          Authorization: authStore.token ? `Bearer ${authStore.token}` : "",
          Accept: "application/json",
        },
      },
    );

    const data = await response.json();
    competencias.value = data as Competencia[];
  }

  async function fetchCompetenciasTransversalesByAlumno(alumno_id: number) {
    const response = await fetch(
      `http://localhost:8000/api/competenciasTransversales/alumno/${alumno_id}`,
      {
        method: "GET",
        headers: {
          Authorization: authStore.token ? `Bearer ${authStore.token}` : "",
          Accept: "application/json",
        },
      },
    );

    const data = await response.json();
    competencias.value = data as Competencia[];
  }

  async function createCompetenciaTecnica(
    ciclo_id: number,
    descripcion: string,
  ) {
    const response = await fetch(
      "http://localhost:8000/api/competencia/tecnica",
      {
        method: "POST",
        headers: {
          Authorization: authStore.token ? `Bearer ${authStore.token}` : "",
          Accept: "application/json",
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ ciclo_id, descripcion }),
      },
    );

    const data = await response.json();

    if (!response.ok) {
      setMessage(
        data.message || "Error desconocido, inténtalo más tarde",
        "error",
      );
      return false;
    }

    // Success
    setMessage(data.message || "Competencia creada correctamente", "success");
    return true;
  }

  async function createCompetenciaTransversal(
    familia_profesional_id: number,
    descripcion: string,
  ) {
    const response = await fetch(
      "http://localhost:8000/api/competencia/transversal",
      {
        method: "POST",
        headers: {
          Authorization: authStore.token ? `Bearer ${authStore.token}` : "",
          Accept: "application/json",
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ familia_profesional_id, descripcion }),
      },
    );

    const data = await response.json();

    if (!response.ok) {
      setMessage(
        data.message || "Error desconocido, inténtalo más tarde",
        "error",
      );
      return false;
    }

    setMessage(data.message || "Competencia creada correctamente", "success");
    return true;
  }

  async function asignarCompetenciasTecnica(
    alumno_id: number,
    competencias: number[],
  ) {
    const response = await fetch(
      "http://localhost:8000/api/competenciasTecnicas/asignar",
      {
        method: "POST",
        headers: {
          Authorization: authStore.token ? `Bearer ${authStore.token}` : "",
          Accept: "application/json",
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ alumno_id, competencias }),
      },
    );

    const data = await response.json();

    if (!response.ok) {
      setMessage(
        data.message || "Error desconocido, inténtalo más tarde",
        "error",
      );
      return false;
    }

    setMessage(
      data.message || "Competencia técnica asignada correctamente",
      "success",
    );
    return true;
  }

  async function calificarCompetenciasTecnicas(
    alumno_id: number,
    competencias: CompetenciaCalificada[],
  ) {
    const response = await fetch(
      "http://localhost:8000/api/competenciasTecnicas/calificar",
      {
        method: "POST",
        headers: {
          Authorization: authStore.token ? `Bearer ${authStore.token}` : "",
          Accept: "application/json",
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ alumno_id, competencias }),
      },
    );

    const data = await response.json();

    if (!response.ok) {
      setMessage(
        data.message || "Error desconocido, inténtalo más tarde",
        "error",
      );
      return false;
    }

    setMessage(
      data.message || "Competencias técnicas calificadas correctamente",
      "success",
    );
    return true;
  }

  async function calificarCompetenciasTransversales(
    alumno_id: number,
    competencias: CompetenciaCalificada[],
  ) {
    const response = await fetch(
      "http://localhost:8000/api/competenciasTransversales/calificar",
      {
        method: "POST",
        headers: {
          Authorization: authStore.token ? `Bearer ${authStore.token}` : "",
          Accept: "application/json",
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ alumno_id, competencias }),
      },
    );

    const data = await response.json();

    if (!response.ok) {
      setMessage(
        data.message || "Error desconocido, inténtalo más tarde",
        "error",
      );
      return false;
    }

    setMessage(
      data.message || "Competencias transversal calificadas correctamente",
      "success",
    );
    return true;
  }

  async function getCalificacionesCompetenciasTecnicas(alumno_id: number) {
    const response = await fetch(
      `http://localhost:8000/api/competenciasTecnicas/calificaciones/${alumno_id}`,
      {
        method: "GET",
        headers: {
          Authorization: authStore.token ? `Bearer ${authStore.token}` : "",
          Accept: "application/json",
        },
      },
    );

    const data = await response.json();

    if (!response.ok) {
      setMessage(
        data.message || "Error desconocido, inténtalo más tarde",
        "error",
      );
    }

    return data as Record<number, number>;
  }

  async function getCalificacionesCompetenciasTransversales(alumno_id: number) {
    const response = await fetch(
      `http://localhost:8000/api/competenciasTransversales/calificaciones/${alumno_id}`,
      {
        method: "GET",
        headers: {
          Authorization: authStore.token ? `Bearer ${authStore.token}` : "",
          Accept: "application/json",
        },
      },
    );

    const data = await response.json();

    if (!response.ok) {
      setMessage(
        data.message || "Error desconocido, inténtalo más tarde",
        "error",
      );
    }

    return data as Record<number, number>;
  }

  return {
    competencias,
    message,
    messageType,
    fetchCompetencias,
    fetchCompetenciasTecnicasByAlumno,
    fetchCompetenciasTransversalesByAlumno,
    createCompetenciaTecnica,
    createCompetenciaTransversal,
    asignarCompetenciasTecnica,
    calificarCompetenciasTecnicas,
    calificarCompetenciasTransversales,
    getCalificacionesCompetenciasTecnicas,
    getCalificacionesCompetenciasTransversales,
  };
});
