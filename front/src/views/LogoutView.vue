<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8 text-center">
            <div
                class="animate-spin rounded-full h-16 w-16 border-t-2 border-b-2 border-[var(--color-nav-start)] mx-auto">
            </div>
            <p class="mt-2 text-center text-lg text-gray-600">Déconnexion en cours...</p>
        </div>
    </div>
</template>

<script lang="ts">
import { defineComponent, onMounted } from 'vue'
import { useAuth } from '../composables/useAuth'

export default defineComponent({
    name: 'LogoutView',
    setup() {
        const { logout } = useAuth()

        onMounted(() => {
            // Log out using the auth service
            logout()
        })

        return {}
    }
})
</script>