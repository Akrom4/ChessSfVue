<template>
  <!-- Render Mobile Navigation on small screens -->
  <MobileNavigation :userName="userName" :userRole="userRole" class="md:hidden" />

  <!-- Render Desktop Sidebar on medium screens and up -->
  <DesktopSidebar :userName="userName" :userRole="userRole" class="hidden md:flex" />
</template>

<script setup lang="ts">
import MobileNavigation from '@/components/MobileNavigation.vue'
import DesktopSidebar from '@/components/DesktopNavigation.vue'
import { ref } from 'vue';

const userName = ref('Fafa');
const userRole = ref('role_admin');
</script>
