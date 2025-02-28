<script setup lang="ts">
import { RouterLink, RouterView } from 'vue-router'
import Sidebar from './components/Sidebar.vue';
</script>

<template>
  <div id="app" class="flex flex-col md:flex-row h-screen">
    <Sidebar />
    <main class="flex-1 bg-gray-100 p-6">
      <div class="view-container flex flex-col justify-center items-center h-full">
        <RouterView />
      </div>
    </main>
  </div>
</template>

<style scoped></style>
