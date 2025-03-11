<template>
    <div class="flex min-h-screen">
      <!-- Sidebar -->
      <aside 
        class="fixed h-screen transition-all duration-300 ease-in-out overflow-x-hidden z-50"
        :class="[
          isCollapsed ? 'w-16' : 'w-64',
          `bg-surface-100 dark:bg-surface-950 border-r border-surface-200 dark:border-surface-700`
        ]"
      >
        <div class="flex items-center bg-surface-900 justify-between p-4 border-b border-surface-200 dark:border-surface-700 dark:bg-surface-900">
          <img 
            v-if="!isCollapsed" 
            src="../assets/logo.png" 
            alt="Logo" 
            class="h-16" 
          />
          <button 
            class="p-2 rounded-full hover:bg-surface-200 dark:hover:bg-surface-800 transition-colors duration-200"
            @click="toggleSidebar"
          >
            <i :class="[isCollapsed ? 'pi pi-bars' : 'pi pi-arrow-left', 'text-surface-700 dark:text-surface-200']"></i>
          </button>
        </div>
        <nav class="p-4">
          <ul class="space-y-2">
            <li v-for="item in menuItems" :key="item.path">
              <RouterLink 
                :to="item.path"
                class="flex items-center p-2 rounded-lg transition-colors duration-200"
                :class="[
                  isCollapsed ? 'justify-center' : '',
                  'hover:bg-surface-200 dark:hover:bg-surface-800',
                  'text-surface-700 dark:text-surface-200',
                  'active:bg-primary-50 dark:active:bg-primary-900 active:text-primary-600 dark:active:text-primary-400'
                ]"
              >
                <i :class="[item.icon, 'text-xl']"></i>
                <span v-if="!isCollapsed" class="ml-3">{{ item.label }}</span>
              </RouterLink>
            </li>
          </ul>
        </nav>
        
        <!-- Logout Button -->
        <div class="absolute bottom-0 w-full p-4 border-t border-surface-200 dark:border-surface-700">
          <button
            @click="handleLogout"
            class="flex items-center w-full p-2 rounded-lg transition-colors duration-200 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20"
            :class="{ 'justify-center': isCollapsed }"
          >
            <i class="pi pi-sign-out text-xl"></i>
            <span v-if="!isCollapsed" class="ml-3">Logout</span>
          </button>
        </div>
      </aside>
  
      <!-- Main Content -->
      <main 
        class="flex-1 transition-all duration-300 ease-in-out p-8 bg-surface-50 dark:bg-surface-900"
        :class="{ 'ml-16': isCollapsed, 'ml-64': !isCollapsed }"
      >
        <slot></slot>
      </main>
    </div>
  </template>
  
  <script setup lang="ts">
  import { ref } from 'vue'
  import { RouterLink } from 'vue-router'
  import { useLayout } from '../composables/useLayout'
  import { useAuth } from '../composables/useAuth'
  
  const { isDarkMode, toggleDarkMode, primary, surface } = useLayout()
  const { logout } = useAuth()
  const isCollapsed = ref(false)
  
  const toggleSidebar = () => {
    isCollapsed.value = !isCollapsed.value
  }
  
  const handleLogout = () => {
    logout()
  }
  
  // Example menu items - you can modify these based on your routes
  const menuItems = [
    {
      label: 'Dashboard',
      path: '/',
      icon: 'pi pi-home'
    },
    // {
    //   label: 'Products',
    //   path: '/products',
    //   icon: 'pi pi-box'
    // },
    // {
    //   label: 'Orders',
    //   path: '/orders',
    //   icon: 'pi pi-shopping-cart'
    // },
    // {
    //   label: 'Settings',
    //   path: '/settings',
    //   icon: 'pi pi-cog'
    // }
  ]
  </script>
  
  <style scoped>
  
  </style>