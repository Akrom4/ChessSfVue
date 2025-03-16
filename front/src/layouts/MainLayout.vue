<template>
    <div class="flex flex-col md:flex-row min-h-screen">
        <!-- Sidebar - fixed position on desktop -->
        <Sidebar />

        <!-- Main content - adjusted to account for fixed sidebar width on desktop -->
        <main class="flex-1 bg-gray-100 p-6 md:ml-48 overflow-y-auto pt-16 md:pt-6">
            <div class="view-container">
                <router-view v-slot="{ Component }">
                    <keep-alive>
                        <component :is="Component" />
                    </keep-alive>
                </router-view>
            </div>
        </main>
    </div>
</template>

<script setup lang="ts">
import { RouterView } from 'vue-router'
import Sidebar from '../components/Sidebar.vue'
</script>