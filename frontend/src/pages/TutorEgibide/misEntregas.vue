<script setup>
    import { useTutorEgibideStore } from '@/stores/tutorEgibide';
import { onMounted } from 'vue';
    const tutorEgibideStore = useTutorEgibideStore();
    onMounted(() => {
        tutorEgibideStore.fetchEntregas();
    });
</script>

<template>

</template>
