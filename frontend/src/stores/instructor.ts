import { defineStore } from "pinia";
import { ref } from "vue";
import { useAuthStore } from "./auth";
import type { Instructor } from "@/interfaces/Instructor"; // Nueva interface

const baseURL = import.meta.env.VITE_API_BASE_URL;

export const useInstructorStore = defineStore("instructor", () => {
  const authStore = useAuthStore();
  const loading = ref(false);
  const message = ref<string | null>(null);
  const messageType = ref<"success" | "error">("success");

  function setMessage(text: string, type: "success" | "error", timeout = 5000) {
    message.value = text;
    messageType.value = type;
    setTimeout(() => {
      message.value = null;
    }, timeout);
  }

async function obtenerPorEmpresa(empresaId: number) {
  loading.value = true;

  try {
    const response = await fetch(
      `${baseURL}/api/instructores?empresa_id=${empresaId}`,
      {
        headers: {
          Authorization: authStore.token ? `Bearer ${authStore.token}` : "",
          Accept: "application/json",
        },
      }
    );

    const data = await response.json();

    if (!response.ok) {
      setMessage("Error al obtener instructores", "error");
      return [];
    }

    return data as Instructor[];

  } catch (err) {
    console.error(err);
    return [];

  } finally {
    loading.value = false;
  }
}

  async function crear(instructorData: {
    nombre: string;
    apellidos: string;
    telefono?: string;
    ciudad?: string;
    empresa_id: number;
    email?: string;
  }) {
    loading.value = true;
    try {
      const response = await fetch(`${baseURL}/api/instructores`, {
        method: "POST",
        headers: {
          Authorization: authStore.token ? `Bearer ${authStore.token}` : "",
          Accept: "application/json",
          "Content-Type": "application/json",
        },
        body: JSON.stringify(instructorData),
      });

      const data = await response.json();

      if (!response.ok) {
        setMessage(data.message || "Error al crear instructor", "error");
        return { success: false, data: null };
      }

      setMessage(data.message || "Instructor creado correctamente", "success");
      return { success: true, data: data.instructor };
    } catch (err) {
      console.error(err);
      setMessage("Error de conexión", "error");
      return { success: false, data: null };
    } finally {
      loading.value = false;
    }
  }

  async function actualizar(
    instructorId: number,
    instructorData: {
      nombre: string;
      apellidos: string;
      telefono?: string;
      ciudad?: string;
      empresa_id: number;
    }
  ) {
    loading.value = true;
    try {
      const response = await fetch(
        `${baseURL}/api/instructores/${instructorId}`,
        {
          method: "PUT",
          headers: {
            Authorization: authStore.token ? `Bearer ${authStore.token}` : "",
            Accept: "application/json",
            "Content-Type": "application/json",
          },
          body: JSON.stringify(instructorData),
        }
      );

      const data = await response.json();

      if (!response.ok) {
        setMessage(data.message || "Error al actualizar instructor", "error");
        return false;
      }

      setMessage(
        data.message || "Instructor actualizado correctamente",
        "success"
      );
      return true;
    } catch (err) {
      console.error(err);
      setMessage("Error de conexión", "error");
      return false;
    } finally {
      loading.value = false;
    }
  }

  async function eliminar(instructorId: number) {
    loading.value = true;
    try {
      const response = await fetch(
        `${baseURL}/api/instructores/${instructorId}`,
        {
          method: "DELETE",
          headers: {
            Authorization: authStore.token ? `Bearer ${authStore.token}` : "",
            Accept: "application/json",
          },
        }
      );

      const data = await response.json();

      if (!response.ok) {
        setMessage(data.message || "Error al eliminar instructor", "error");
        return false;
      }

      setMessage(
        data.message || "Instructor eliminado correctamente",
        "success"
      );
      return true;
    } catch (error) {
      console.error(error);
      setMessage("Error de conexión", "error");
      return false;
    } finally {
      loading.value = false;
    }
  }


  return {
    loading,
    message,
    messageType,
    obtenerPorEmpresa,
    crear,
    actualizar,
    eliminar,
    setMessage,
  };
});