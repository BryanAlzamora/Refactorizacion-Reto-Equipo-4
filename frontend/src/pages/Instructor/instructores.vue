  <script setup lang="ts">
  import { ref, onMounted } from "vue";
  import { useInstructorStore } from "@/stores/instructor";
  import { useAuthStore } from "@/stores/auth";
  import { useRouter } from "vue-router";
  import type { Instructor } from "@/interfaces/Instructor";

  const instructorStore = useInstructorStore();
  const authStore = useAuthStore();
  const router = useRouter();

  const empresas = ref<{ id: number; nombre: string }[]>([]);
  const selectedEmpresa = ref<number | null>(null);
  const instructores = ref<Instructor[]>([]);
  const loading = ref(false);
  const baseURL = import.meta.env.VITE_API_BASE_URL;


  // Mensajes globales
  const message = ref<string | null>(null);
  const messageType = ref<"success" | "error">("success");

  const setMessage = (text: string, type: "success" | "error", timeout = 5000) => {
    message.value = text;
    messageType.value = type;
    setTimeout(() => (message.value = null), timeout);
  };

  // Modal
  const showModal = ref(false);
  const isEditing = ref(false);
  const instructorForm = ref({
    id: null as number | null,
    nombre: "",
    apellidos: "",
    telefono: "",
    ciudad: "",
    email: "",
  });
  const credencialesGeneradas = ref<{ email: string; password: string } | null>(null);

  // --- Funciones ---

  // Cargar empresas
  const cargarEmpresas = async () => {
    loading.value = true;
    try {
      const res = await fetch(`${baseURL}/api/empresas`, {
        headers: {
          Authorization: authStore.token ? `Bearer ${authStore.token}` : "",
          Accept: "application/json",
        },
      });
      empresas.value = await res.json();
    } catch (err) {
      console.error(err);
      setMessage("Error al cargar empresas", "error");
    } finally {
      loading.value = false;
    }
  };

  // Cargar instructores de empresa
  const cargarInstructores = async (empresaId: number) => {
    selectedEmpresa.value = empresaId;
    instructores.value = await instructorStore.obtenerPorEmpresa(empresaId);
  };

  // Abrir modal crear
  const abrirCrear = () => {
    instructorForm.value = { id: null, nombre: "", apellidos: "", telefono: "", ciudad: "", email: "" };
    isEditing.value = false;
    credencialesGeneradas.value = null;
    showModal.value = true;
  };

  // Abrir modal editar
  const abrirEditar = (instr: Instructor) => {
    instructorForm.value = {
      id: instr.id,
      nombre: instr.nombre,
      apellidos: instr.apellidos,
      telefono: instr.telefono || "",
      ciudad: instr.ciudad || "",
      email: "",
    };
    isEditing.value = true;
    credencialesGeneradas.value = null;
    showModal.value = true;
  };

  // Guardar instructor
  const guardarInstructor = async () => {
    if (!selectedEmpresa.value) return;

    if (isEditing.value && instructorForm.value.id) {
      const ok = await instructorStore.actualizar(instructorForm.value.id, {
        nombre: instructorForm.value.nombre,
        apellidos: instructorForm.value.apellidos,
        telefono: instructorForm.value.telefono,
        ciudad: instructorForm.value.ciudad,
        empresa_id: selectedEmpresa.value,
      });
      if (ok) {
        setMessage("Instructor actualizado correctamente", "success");
        showModal.value = false;
        if (selectedEmpresa.value) cargarInstructores(selectedEmpresa.value);
      }
    } else {
      const emailFinal =
        instructorForm.value.email.trim() || `${instructorForm.value.nombre.toLowerCase()}.instructor@demo.com`;
      const { success, data } = await instructorStore.crear({
        nombre: instructorForm.value.nombre,
        apellidos: instructorForm.value.apellidos,
        telefono: instructorForm.value.telefono,
        ciudad: instructorForm.value.ciudad,
        empresa_id: selectedEmpresa.value,
        email: emailFinal,
      });
      if (success && data) {
        credencialesGeneradas.value = { email: emailFinal, password: "12345Abcde" };
        setMessage("Instructor creado correctamente", "success");
        // Recargar instructores pero mantener modal abierto para mostrar credenciales
        if (selectedEmpresa.value) cargarInstructores(selectedEmpresa.value);
      } else {
        // Si falla, cerrar modal
        showModal.value = false;
      }
    }
  };

  // Cerrar modal de credenciales
  const cerrarModalCredenciales = () => {
    showModal.value = false;
    credencialesGeneradas.value = null;
  };

  // Eliminar instructor
  const eliminarInstructor = async (id: number) => {
    if (confirm("¿Seguro que deseas eliminar este instructor?")) {
      const ok = await instructorStore.eliminar(id);
      if (ok) {
        setMessage("Instructor eliminado correctamente", "success");
        if (selectedEmpresa.value) cargarInstructores(selectedEmpresa.value);
      }
    }
  };

  // Ver detalle
  const verDetalle = (id: number) => {
    router.push(`/tutor_egibide/instructores/${id}`);
  };

  onMounted(() => {
    cargarEmpresas();
  });
  </script>

  <template>
    <div class="container mt-4">
      <h3>Instructores</h3>

      <!-- Mensaje -->
      <div v-if="message" :class="['alert', messageType==='success'?'alert-success':'alert-danger']">
        {{ message }}
      </div>

      <div v-if="loading" class="text-center py-3">Cargando empresas...</div>

      <!-- Empresas -->
      <div v-else class="mb-4">
        <h5>Empresas</h5>
        <ul class="list-group">
          <li
            v-for="empresa in empresas"
            :key="empresa.id"
            class="list-group-item d-flex justify-content-between align-items-center"
          >
            <span>{{ empresa.nombre }}</span>
            <button class="btn btn-sm btn-primary" @click="cargarInstructores(empresa.id)">
              Ver Instructores
            </button>
          </li>
        </ul>
      </div>

      <!-- Instructores -->
      <div v-if="selectedEmpresa">
        <h5 class="mt-4">Instructores de la empresa</h5>
        <button class="btn btn-success mb-2" @click="abrirCrear">Crear Instructor</button>

        <div v-if="instructores.length">
          <div v-for="instr in instructores" :key="instr.id" class="card mb-2 p-2">
            <div class="d-flex justify-content-between align-items-center">
              <div @click="verDetalle(instr.id)" style="cursor:pointer;" class="text-primary">
                <strong>{{ instr.nombre }} {{ instr.apellidos }}</strong>
              </div>
              <div>
                <button class="btn btn-sm btn-outline-secondary me-1" @click="abrirEditar(instr)">
                  Modificar
                </button>
                <button class="btn btn-sm btn-outline-danger" @click="eliminarInstructor(instr.id)">
                  Eliminar
                </button>
              </div>
            </div>
          </div>
        </div>
        <div v-else class="alert alert-info">No hay instructores en esta empresa</div>
      </div>

      <!-- Modal Crear/Editar -->
      <div class="modal fade" tabindex="-1" :class="{ show: showModal }" style="display: block;" v-if="showModal">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">{{ isEditing ? 'Modificar Instructor' : 'Crear Instructor' }}</h5>
              <button type="button" class="btn-close" @click="cerrarModalCredenciales"></button>
            </div>
            <div class="modal-body">
              <!-- Mostrar credenciales si fueron generadas -->
              <div v-if="credencialesGeneradas" class="alert alert-success">
                <h6 class="alert-heading">✅ Instructor creado con éxito</h6>
                <hr>
                <p class="mb-1"><strong>Email:</strong> {{ credencialesGeneradas.email }}</p>
                <p class="mb-0"><strong>Contraseña temporal:</strong> {{ credencialesGeneradas.password }}</p>
                <hr>
                <small class="text-muted">Guarda estas credenciales. El instructor deberá cambiar su contraseña en el primer inicio de sesión.</small>
                <div class="mt-3">
                  <button type="button" class="btn btn-primary w-100" @click="cerrarModalCredenciales">
                    Cerrar
                  </button>
                </div>
              </div>

              <!-- Formulario -->
              <form v-else @submit.prevent="guardarInstructor">
                <div class="mb-3">
                  <label class="form-label">Nombre *</label>
                  <input v-model="instructorForm.nombre" type="text" class="form-control" required />
                </div>
                <div class="mb-3">
                  <label class="form-label">Apellidos *</label>
                  <input v-model="instructorForm.apellidos" type="text" class="form-control" required />
                </div>
                <div class="mb-3">
                  <label class="form-label">Teléfono</label>
                  <input v-model="instructorForm.telefono" type="text" class="form-control" />
                </div>
                <div class="mb-3">
                  <label class="form-label">Ciudad</label>
                  <input v-model="instructorForm.ciudad" type="text" class="form-control" />
                </div>
                <div class="mb-3" v-if="!isEditing">
                  <label class="form-label">Email</label>
                  <input v-model="instructorForm.email" type="email" class="form-control" />
                  <small class="text-muted">Si no se indica, se generará automáticamente</small>
                </div>
                <button type="submit" class="btn btn-primary w-100">
                  {{ isEditing ? 'Guardar cambios' : 'Crear Instructor' }}
                </button>
              </form>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-backdrop fade show" v-if="showModal"></div>
    </div>
  </template>

  <style scoped>
  .card {
    cursor: default;
  }
  .card:hover {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  }
  .modal-backdrop {
    z-index: 1040;
  }
  .modal {
    z-index: 1050;
  }
  </style>