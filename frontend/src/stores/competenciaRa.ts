import { defineStore } from "pinia";
import { ref } from "vue";
import { useAuthStore } from "./auth"; // Asegúrate de que la ruta a auth sea correcta

const baseURL = import.meta.env.VITE_API_BASE_URL;

export const useCompetenciaRaStore = defineStore("competenciaRa", () => {
  const authStore = useAuthStore();

  // Estados
  const ciclos = ref([]);
  const competencias = ref([]);
  const asignaturas = ref([]);
  const loading = ref(false);

  // Mensajes
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

  // 1. Cargar Ciclos
  async function fetchCiclos() {
    loading.value = true;
    console.log("Store: Iniciando fetchCiclos..."); 

    try {
      const response = await fetch(`${baseURL}/api/ciclos`, {
        method: "GET",
        headers: {
          Authorization: authStore.token ? `Bearer ${authStore.token}` : "",
          Accept: "application/json",
        },
      });

      const data = await response.json();
      console.log("Store: Respuesta Ciclos:", data);

      if (!response.ok) {
        setMessage(data.message || "Error al cargar ciclos", "error");
        return false;
      }

      // IMPORTANTE: Manejar si Laravel devuelve { data: [...] } o [...]
      ciclos.value = Array.isArray(data) ? data : (data.data || []);
      
      return true;
    } catch (e) {
      console.error("Store Error:", e);
      setMessage("Error de conexión", "error");
      return false;
    } finally {
      loading.value = false;
    }
  }

  // 2. Cargar Matriz (Competencias + Asignaturas + RAs)
  async function fetchMatriz(cicloId: string | number) {
    loading.value = true;
    competencias.value = [];
    asignaturas.value = [];
    
    console.log(`Store: Buscando matriz para ciclo ${cicloId}...`);

    try {
      const response = await fetch(`${baseURL}/api/ciclo/${cicloId}/matriz-competencias`, {
        method: "GET",
        headers: {
          Authorization: authStore.token ? `Bearer ${authStore.token}` : "",
          Accept: "application/json",
        },
      });

      const data = await response.json();
      console.log("Store: Respuesta Matriz:", data);

      if (!response.ok) {
        setMessage("Error al cargar la matriz", "error");
        return false;
      }

      competencias.value = data.competencias || [];
      
      // Procesar RAs para asegurar que existen los arrays
      const asignaturasProcesadas = (data.asignaturas || []).map((asig: any) => {
        if (asig.resultados_aprendizaje) {
          asig.resultados_aprendizaje.forEach((ra: any) => {
            if (!ra.competencias_tec) ra.competencias_tec = [];
          });
        }
        return asig;
      });

      asignaturas.value = asignaturasProcesadas;
      return true;

    } catch (e) {
      console.error("Store Error Matriz:", e);
      setMessage("Error de conexión al cargar matriz", "error");
      return false;
    } finally {
      loading.value = false;
    }
  }

  // 3. Toggle (Guardar/Borrar)
async function toggleRelacion(raId: number, compId: number) {
  try {
    const response = await fetch(
      `${baseURL}/api/competenciasTecnicas/asignar-ra`,
      {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Authorization: authStore.token
            ? `Bearer ${authStore.token}`
            : "",
          Accept: "application/json",
        },
        body: JSON.stringify({
          competencia_tec_id: compId,
          resultado_aprendizaje_id: raId,
        }),
      }
    );

    const data = await response.json();

    if (!response.ok) {
      console.error("API error:", data);
      setMessage(
        data.message || "No se pudo guardar la relación",
        "error"
      );
      return false;
    }

    // Éxito silencioso (la UI ya se actualizó)
    return true;

  } catch (error) {
    console.error("Error conexión toggleRelacion:", error);
    setMessage("Error de conexión con el servidor", "error");
    return false;
  }
}



  return {
    ciclos,
    competencias,
    asignaturas,
    loading,
    message,
    messageType,
    fetchCiclos,
    fetchMatriz,
    toggleRelacion
  };
});