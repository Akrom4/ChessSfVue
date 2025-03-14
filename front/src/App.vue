<script setup lang="ts">
import { RouterView } from 'vue-router'
import { onMounted } from 'vue';
import { useAuth } from './composables/useAuth';

// Get auth functionality from our composable
const { verifyExistingAuth } = useAuth();

// Check for existing authentication on app start
onMounted(async () => {
  await verifyExistingAuth();
});
</script>

<template>
  <RouterView />
</template>

<style scoped></style>
