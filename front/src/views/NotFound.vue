<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8 text-center">
            <h1 class="text-9xl font-bold text-[var(--color-nav-start)]">404</h1>
            <h2 class="mt-6 text-3xl font-extrabold text-gray-900">Page Non Trouvée</h2>
            <p class="mt-2 text-lg text-gray-600">
                La page que vous recherchez n'existe pas ou a été déplacée.
            </p>
            <div class="mt-6">
                <RouterLink to="/"
                    class="text-[var(--color-nav-start)] hover:text-[var(--color-nav-hover)] font-medium">
                    Retour à l'accueil
                </RouterLink>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { RouterLink } from 'vue-router'
</script>