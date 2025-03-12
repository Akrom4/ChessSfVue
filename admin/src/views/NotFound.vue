<template>
  <div class="flex flex-col items-center justify-center h-screen bg-surface-50 dark:bg-surface-900 px-4">
    <div class="text-9xl font-bold text-primary mb-4">404</div>
    <h1 class="text-3xl font-semibold text-surface-900 dark:text-surface-50 mb-2">Page non trouvée</h1>
    <p class="text-surface-600 dark:text-surface-400 text-center mb-8">La page que vous recherchez n'existe pas ou a été déplacée</p>
    <router-link 
      to="/" 
      class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary-600 transition-colors duration-200"
    >
      Retour à l'accueil
    </router-link>
  </div>
</template>

<script setup lang="ts">
// No additional script needed
</script> 