<script setup lang="ts">
import Toast from "@/components/Notification/Toast.vue";
import { useCiclosStore } from "@/stores/ciclos";
import { useCompetenciasStore } from "@/stores/competencias";
import { useFamiliaProfesionalesStore } from "@/stores/familiasProfesionales";
import { ref, onMounted, watch } from "vue";

const familiaProfesionalStore = useFamiliaProfesionalesStore();
const cicloStore = useCiclosStore();
const competenciaStore = useCompetenciasStore();

const tipoCompetencia = ref<string>("tecnica");
const familiaProfesional = ref<number>(0);
const ciclo = ref<number>(0);
const descripcion = ref<string>("");

const ciclosFamilia = ref<any[]>([]);

watch(familiaProfesional, (newVal) => {
  if (!newVal) {
    ciclosFamilia.value = [];
    ciclo.value = 0;
    return;
  }
  ciclosFamilia.value = cicloStore.getCiclosPorFamilia(newVal);
  ciclo.value = 0;
});

onMounted(async () => {
  await familiaProfesionalStore.fetchFamiliasProfesionales();
  await cicloStore.fetchCiclos();
});

async function agregarCompetencia() {
  let ok = false;

  if (tipoCompetencia.value === "tecnica") {
    if (!ciclo.value) {
      alert("Selecciona un ciclo antes de continuar");
      return;
    }
    ok = await competenciaStore.createCompetenciaTecnica(
      ciclo.value,
      descripcion.value,
    );
  } else {
    ok = await competenciaStore.createCompetenciaTransversal(
      familiaProfesional.value,
      descripcion.value,
    );
  }

  if (ok) {
    resetForm();
  }
}

function resetForm() {
  tipoCompetencia.value = "tecnica";
  familiaProfesional.value = 0;
  ciclo.value = 0;
  descripcion.value = "";
  ciclosFamilia.value = [];
}
</script>

<template>
  <h2>NUEVA COMPETENCIA</h2>
  <hr />

  <Toast
    v-if="competenciaStore.message"
    :message="competenciaStore.message"
    :messageType="competenciaStore.messageType"
  />

  <form @submit.prevent="agregarCompetencia" class="row-cols-1">
    <!-- Tipo de competencia -->
    <div class="mb-3">
      <p>Tipo de competencia</p>
      <div class="form-check form-check-inline">
        <input
          class="form-check-input"
          type="radio"
          name="tipoCompetencia"
          id="tecnica"
          value="tecnica"
          v-model="tipoCompetencia"
        />
        <label class="form-check-label" for="tecnica">Técnica</label>
      </div>
      <div class="form-check form-check-inline">
        <input
          class="form-check-input"
          type="radio"
          name="tipoCompetencia"
          id="transversal"
          value="transversal"
          v-model="tipoCompetencia"
        />
        <label class="form-check-label" for="transversal">Transversal</label>
      </div>
    </div>

    <!-- Familia profesional -->
    <div class="mb-3 col-5">
      <label for="familiaProfesional" class="form-label"
        >Familia profesional:</label
      >
      <select
        class="form-select"
        v-model.number="familiaProfesional"
        id="familia"
        required
      >
        <option :value="0" disabled>-- Selecciona una opción --</option>
        <option
          v-for="familia in familiaProfesionalStore.familiasProfesionales"
          :key="familia.id"
          :value="familia.id"
        >
          {{ familia.nombre }}
        </option>
      </select>
    </div>

    <!-- Descripción -->
    <div class="mb-3 col-5">
      <label for="descripcion" class="form-label">Descripción:</label>
      <div class="form-floating">
        <textarea
          class="form-control"
          placeholder="Deja un comentario aquí"
          id="descripcion"
          style="height: 120px; max-height: 280px"
          v-model="descripcion"
        ></textarea>
        <label for="descripcion">Descripción de la competencia</label>
      </div>
    </div>

    <!-- Ciclo solo si la competencia es técnica y hay ciclos -->
    <div
      v-if="tipoCompetencia === 'tecnica' && ciclosFamilia.length"
      class="mb-3 col-5"
    >
      <label for="ciclo" class="form-label">Ciclo:</label>
      <select
        class="form-select"
        v-model.number="ciclo"
        :key="familiaProfesional"
        id="ciclo"
        required
      >
        <option :value="0" disabled>-- Selecciona una opción --</option>
        <option v-for="c in ciclosFamilia" :key="c.id" :value="c.id">
          {{ c.nombre }}
        </option>
      </select>
    </div>

    <button type="submit" class="btn btn-primary col-2">Agregar</button>
  </form>
</template>

<style scoped></style>
