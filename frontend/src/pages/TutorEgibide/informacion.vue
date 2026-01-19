<script setup></script>

<template>
  <h2>Información</h2>
  <hr />
  <div class="info-links">
    <RouterLink
      to="/tutor-egibide/alumnos"
      class="info-card"
    >
      <h4>Mis Alumnos</h4>
      <p>Ver los alumnos asignados</p>
    </RouterLink>

    <RouterLink
      to="/tutor-egibide/empresas"
      class="info-card"
    >
      <h4>Empresas</h4>
      <p>Ver empresas colaboradoras</p>
    </RouterLink>
  </div>
</template>


<style scoped>
  .info-links {
    display: grid;
    gap: 1.5rem; /* separación entre cards */
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  }

  /* Card */
  .info-card {
    padding: 1.5rem;
    border: 2px solid #81045f;
    border-radius: 10px;
    text-decoration: none;
    color: inherit;
    cursor: pointer;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    background-color: #e3e6ea;
  }

  .info-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 6px 14px rgba(0, 0, 0, 0.15);
  }
</style>
