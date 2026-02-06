<template>
  <div class="buscador-select-wrapper" ref="wrapperRef">
    <div class="input-group">
      <input
        type="text"
        class="form-control"
        :placeholder="placeholder"
        v-model="busqueda"
        @focus="abrirMenu"
        @input="filtrar"
      />
      <button class="btn btn-outline-secondary" type="button" @click="toggleMenu">
        <i class="bi bi-chevron-down"></i>
      </button>
    </div>

    <ul v-if="abierto" class="dropdown-menu show w-100 shadow" style="max-height: 250px; overflow-y: auto;">
      <li v-if="opcionesFiltradas.length === 0">
        <span class="dropdown-item disabled text-muted">No hay coincidencias</span>
      </li>
      
      <li v-for="opcion in opcionesFiltradas" :key="opcion[valueKey]">
        <button 
          class="dropdown-item" 
          :class="{ active: modelValue === opcion[valueKey] }"
          @click="seleccionarOpcion(opcion)"
          type="button"
        >
          {{ opcion[labelKey] }}
        </button>
      </li>
    </ul>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';

const props = defineProps({
  modelValue: [String, Number], 
  options: { type: Array, default: () => [] }, 
  placeholder: { type: String, default: 'Selecciona una opción' },
  labelKey: { type: String, default: 'nombre' }, 
  valueKey: { type: String, default: 'id' }      
});

const emit = defineEmits(['update:modelValue', 'change']);

const abierto = ref(false);
const busqueda = ref('');
const wrapperRef = ref(null);

// Filtrado dinámico
const opcionesFiltradas = computed(() => {
  if (!busqueda.value) return props.options;
  
  // Si coincide exactamente, mostrar todas para permitir cambiar
  const seleccionado = props.options.find(o => o[props.valueKey] === props.modelValue);
  if (seleccionado && seleccionado[props.labelKey] === busqueda.value) {
    return props.options;
  }

  return props.options.filter(opcion => 
    String(opcion[props.labelKey]).toLowerCase().includes(busqueda.value.toLowerCase())
  );
});

// Watch para sincronizar si el valor cambia desde fuera (ej: cargar datos iniciales)
watch(() => props.modelValue, (newVal) => {
  const seleccionado = props.options.find(o => o[props.valueKey] === newVal);
  busqueda.value = seleccionado ? seleccionado[props.labelKey] : '';
}, { immediate: true });

function abrirMenu() {
  abierto.value = true;
}

function toggleMenu() {
  abierto.value = !abierto.value;
}

function filtrar() {
  if (!abierto.value) abierto.value = true;
}

function seleccionarOpcion(opcion) {
  busqueda.value = opcion[props.labelKey];
  emit('update:modelValue', opcion[props.valueKey]);
  emit('change', opcion[props.valueKey]); 
  abierto.value = false;
}

// Cerrar al hacer clic fuera
function handleClickOutside(event) {
  if (wrapperRef.value && !wrapperRef.value.contains(event.target)) {
    abierto.value = false;
    // Restaurar texto correcto si se sale sin seleccionar
    const seleccionado = props.options.find(o => o[props.valueKey] === props.modelValue);
    busqueda.value = seleccionado ? seleccionado[props.labelKey] : '';
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>

<style scoped>
.buscador-select-wrapper {
  position: relative;
}
.dropdown-menu {
  position: absolute;
  z-index: 1050; 
}
/* Estilo activo personalizado para coincidir con tu tema */
.dropdown-item.active, .dropdown-item:active {
    background-color: #6610f2; /* Indigo/Purple de Bootstrap */
    color: white;
}
</style>