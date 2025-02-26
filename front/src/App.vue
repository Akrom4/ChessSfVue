<script setup lang="ts">
import { RouterLink, RouterView } from 'vue-router'
import Sidebar from './components/Sidebar.vue';
</script>

<template>
  
    <Sidebar />

  <main class="flex-1 bg-gray-100 p-6">
    <RouterView />
  </main>
</template>

<style scoped></style>
