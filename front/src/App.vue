<script setup lang="ts">
import { RouterView } from 'vue-router'
import { onMounted, ref } from 'vue';
import { useAuth } from './composables/useAuth';
import { useRouter } from 'vue-router';

// Get auth functionality from our composable
const { verifyExistingAuth } = useAuth();
const router = useRouter();
const appInitialized = ref(false);

// Check for existing authentication on app start
onMounted(async () => {
  try {
    await verifyExistingAuth();
  } catch (error) {
    console.error('Error during auth verification:', error);
  } finally {
    // Mark app as initialized even if auth check fails
    appInitialized.value = true;

    // If on login page but authenticated, redirect to home
    const currentRoute = router.currentRoute.value;
    if (currentRoute.name === 'Login' && appInitialized.value) {
      router.replace('/');
    }
  }
});
</script>

<template>
  <RouterView v-if="appInitialized" />
  <div v-else class="min-h-screen flex items-center justify-center">
    <div class="animate-pulse">
      <img src="/images/logo/logo.png" alt="Loading..." class="h-16 w-auto" />
    </div>
  </div>
</template>

<style scoped></style>
