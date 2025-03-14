<template>
  <!-- Render Mobile Navigation on small screens -->
  <MobileNavigation :userName="userName" :userRole="userRole" class="md:hidden" />

  <!-- Render Desktop Sidebar on medium screens and up -->
  <DesktopSidebar :userName="userName" :userRole="userRole" class="hidden md:flex" />
</template>

<script setup lang="ts">
import MobileNavigation from '@/components/MobileNavigation.vue'
import DesktopSidebar from '@/components/DesktopNavigation.vue'
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useAuth } from '../composables/useAuth'

// Get auth functionality from our composable
const { user, isAuthenticated, isAdmin, onAuthStateChange } = useAuth();

// Default values for guest users
const defaultUserName = 'Guest';
const defaultUserRole = '';

// Compute userName based on user data
const userName = computed(() => {
  if (!isAuthenticated.value || !user.value) return defaultUserName;
  return user.value.username || user.value.email || defaultUserName;
});

// Compute userRole based on user data
const userRole = computed(() => {
  if (!isAuthenticated.value || !user.value) return defaultUserRole;

  // Check for admin role first (takes precedence)
  if (user.value.roles.includes('ROLE_ADMIN')) return 'role_admin';

  // Then check for user role
  if (user.value.roles.includes('ROLE_USER')) return 'role_user';

  return defaultUserRole;
});

// Set up listener for auth state changes
let removeAuthListener: (() => void) | null = null;

onMounted(() => {
  // Listen for auth state changes
  removeAuthListener = onAuthStateChange((isAuth) => {
    // Auth state is already handled by the useAuth composable
    console.log('Auth state changed:', isAuth);
  });
});

onUnmounted(() => {
  // Clean up listeners
  if (removeAuthListener) {
    removeAuthListener();
  }
});
</script>
