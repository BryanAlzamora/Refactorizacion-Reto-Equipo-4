<script setup lang="ts">
  import Header from '@/components/Header.vue'
  import Sidebar from '@/components/Sidebar.vue'
  import MainContent from '@/components/MainContent.vue'
</script>

<template>
  <div class="d-flex flex-column vh-100 gap-3 p-3">
    <Header />
    <div class="d-flex flex-grow-1 gap-3">
      <Sidebar />
      <MainContent />
    </div>
  </div>
</template>

<style scoped></style>
