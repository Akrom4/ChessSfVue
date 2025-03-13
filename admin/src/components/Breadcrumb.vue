<template>
  <nav class="p-2 bg-surface-0 dark:bg-surface-800 rounded-lg shadow-sm mb-2 gap-2">
    <ol class="flex flex-wrap items-center">
      <li v-for="(item, index) in breadcrumbItems" :key="index" class="flex items-center">
        <!-- Icon (if provided) -->
        <i v-if="item.icon" :class="[item.icon, 'text-primary mr-2']"></i>
        
        <!-- Link for non-active items -->
        <router-link 
          v-if="index !== breadcrumbItems.length - 1" 
          :to="item.to"
          class="text-primary hover:text-primary-600 transition-colors"
        >
          {{ item.text }}
        </router-link>
        
        <!-- Text for active (last) item -->
        <span 
          v-else
          class="text-surface-700 dark:text-surface-300 font-medium"
        >
          {{ item.text }}
        </span>
        
        <!-- Separator between items -->
        <i 
          v-if="index !== breadcrumbItems.length - 1" 
          class="pi pi-angle-right mx-2 text-surface-400 dark:text-surface-500"
        ></i>
      </li>
    </ol>
  </nav>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useBreadcrumb, BreadcrumbItem } from '../composables/useBreadcrumb';

// Props
const props = defineProps<{
  items?: BreadcrumbItem[];
}>();

// Get breadcrumbs from the composable if not provided
const { breadcrumbs } = useBreadcrumb();

// Compute the breadcrumb items based on props or the breadcrumbs from the composable
const breadcrumbItems = computed(() => props.items || breadcrumbs.value);
</script> 