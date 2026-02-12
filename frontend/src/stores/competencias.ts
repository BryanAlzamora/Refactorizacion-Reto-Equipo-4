import type {
  Competencia,
  CompetenciaAsignada,
} from "@/interfaces/Competencia";
import { defineStore } from "pinia";
import { ref } from "vue";
import { useAuthStore } from "./auth";
import type { CompetenciaCalificada } from "@/interfaces/CompetenciaCalificada";
import type {
  CalculoNotasTecnicasResponse,
  NotaCompetenciaTransversal,
} from "@/interfaces/Notas";

const baseURL = import.meta.env.VITE_API_BASE_URL;

export const useCompetenciasStore = defineStore("competencias", () => {
  const competencias = ref<Competencia[]>([]);
  const competenciasAsignadas = ref<CompetenciaAsignada[]>([]);
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
    const response = await fetch(`${baseURL}/api/competencias`, {
      headers: authStore.token
        ? { Authorization: `Bearer ${authStore.token}`, Accept: "application/json" }
        : { Accept: "application/json" },
    });

    if (!response.ok) {
      setMessage("Error al cargar competencias", "error");
      return;
    }

    const data = (await response.json()) as Competencia[];
    competencias.value = data;
  }


  async function fetchCompetenciasTecnicasByAlumno(alumno_id: number) {
    const response = await fetch(
      `${baseURL}/api/competenciasTecnicas/alumno/${alumno_id}`,
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

  async function fetchCompetenciasTecnicasAsignadasByAlumno(alumno_id: number) {
    const response = await fetch(
      `${baseURL}/api/competenciasTecnicas/alumno/${alumno_id}/asignadas`,
      {
        method: "GET",
        headers: {
          Authorization: authStore.token ? `Bearer ${authStore.token}` : "",
          Accept: "application/json",
        },
      },
    );

    const data = await response.json();
    competenciasAsignadas.value = data as CompetenciaAsignada[];
  }

  async function fetchCompetenciasTransversalesByAlumno(alumno_id: number) {
    const response = await fetch(
      `${baseURL}/api/competenciasTransversales/alumno/${alumno_id}`,
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
      `${baseURL}/api/competencia/tecnica`,
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
      `${baseURL}/api/competencia/transversal`,
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
      `${baseURL}/api/competenciasTecnicas/asignar`,
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
      `${baseURL}/api/competenciasTecnicas/calificar`,
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
      `${baseURL}/api/competenciasTransversales/calificar`,
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
      `${baseURL}/api/competenciasTecnicas/calificaciones/${alumno_id}`,
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
      `${baseURL}/api/competenciasTransversales/calificaciones/${alumno_id}`,
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

  async function calcularNotasTecnicasByAlumno(
    alumno_id: number,
  ): Promise<CalculoNotasTecnicasResponse> {
    const response = await fetch(
      `${baseURL}/api/notas/alumno/${alumno_id}/tecnicas`,
      {
        method: "GET",
        headers: {
          Authorization: authStore.token ? `Bearer ${authStore.token}` : "",
          Accept: "application/json",
        },
      },
    );

    const data = await response.json();
    console.log("Respuesta del backend para cálculo de notas técnicas:", data);
    if (!response.ok) {
      setMessage(
        data.message || "Error desconocido, inténtalo más tarde",
        "error",
      );
    }

    return data as CalculoNotasTecnicasResponse;
  }

  async function getNotaTransversalByAlumno(alumno_id: number) {
    const response = await fetch(
      `${baseURL}/api/notas/alumno/${alumno_id}/transversal`,
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

    return data as NotaCompetenciaTransversal;
  }

  return {
    competencias,
    competenciasAsignadas,
    message,
    messageType,
    fetchCompetencias,
    fetchCompetenciasTecnicasByAlumno,
    fetchCompetenciasTecnicasAsignadasByAlumno,
    fetchCompetenciasTransversalesByAlumno,
    createCompetenciaTecnica,
    createCompetenciaTransversal,
    asignarCompetenciasTecnica,
    calificarCompetenciasTecnicas,
    calificarCompetenciasTransversales,
    getCalificacionesCompetenciasTecnicas,
    getCalificacionesCompetenciasTransversales,
    calcularNotasTecnicasByAlumno,
    getNotaTransversalByAlumno,
  };
});
