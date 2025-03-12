<template>
  <nav class="p-4 bg-surface-0 dark:bg-surface-800 rounded-lg shadow-sm mb-4">
    <ol class="flex flex-wrap items-center">
      <li v-for="(item, index) in items" :key="index" class="flex items-center">
        <!-- Icon (if provided) -->
        <i v-if="item.icon" :class="[item.icon, 'text-primary mr-2']"></i>
        
        <!-- Link for non-active items -->
        <router-link 
          v-if="index !== items.length - 1" 
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
          v-if="index !== items.length - 1" 
          class="pi pi-angle-right mx-2 text-surface-400 dark:text-surface-500"
        ></i>
      </li>
    </ol>
  </nav>
</template>

<script setup lang="ts">
import { useBreadcrumb, BreadcrumbItem } from '../composables/useBreadcrumb';

// Props
const props = defineProps<{
  items?: BreadcrumbItem[];
}>();

// Get breadcrumbs from the composable if not provided
const { breadcrumbs } = useBreadcrumb();
const items = props.items || breadcrumbs.value;
</script> 