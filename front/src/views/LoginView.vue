<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div>
                <h1 class="text-center text-4xl font-extrabold text-[var(--color-custom-yellow)]">ChessSfVue</h1>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">Connectez-vous à votre compte</h2>
                <p v-if="redirectPath" class="mt-2 text-center text-sm text-[var(--color-nav-start)]">
                    Vous devez vous connecter pour accéder à cette zone
                </p>
                <p v-else class="mt-2 text-center text-sm text-gray-600">
                    Bienvenue sur ChessSfVue
                </p>
            </div>
            <form class="mt-8 space-y-6" @submit.prevent="handleLogin">
                <div v-if="errorMessage" class="p-4 bg-red-100 border border-red-400 text-red-700 rounded mb-4">
                    {{ errorMessage }}
                </div>
                <div class="rounded-md shadow-sm -space-y-px">
                    <div>
                        <label for="username" class="sr-only">Nom d'utilisateur</label>
                        <input id="username" v-model="username" name="username" type="text" autocomplete="username"
                            required
                            class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-[var(--color-nav-start)] focus:border-[var(--color-nav-start)] focus:z-10 sm:text-sm"
                            placeholder="Nom d'utilisateur ou Email" />
                    </div>
                    <div>
                        <label for="password" class="sr-only">Mot de passe</label>
                        <input id="password" v-model="password" name="password" type="password"
                            autocomplete="current-password" required
                            class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-[var(--color-nav-start)] focus:border-[var(--color-nav-start)] focus:z-10 sm:text-sm"
                            placeholder="Mot de passe" />
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember-me" v-model="rememberMe" name="remember-me" type="checkbox"
                            class="h-4 w-4 text-[var(--color-nav-start)] focus:ring-[var(--color-nav-hover)] border-gray-300 rounded" />
                        <label for="remember-me" class="ml-2 block text-sm text-gray-900">
                            Se souvenir de moi
                        </label>
                        <span class="ml-1 text-xs text-gray-500" title="Rester connecté pendant 30 jours">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </span>
                    </div>
                </div>

                <div>
                    <button type="submit" :disabled="isLoading"
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-gradient-to-b from-[var(--color-nav-start)] to-[var(--color-nav-end)] hover:from-[var(--color-nav-hover)] hover:to-[var(--color-nav-end)] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--color-nav-start)] disabled:opacity-50 disabled:cursor-not-allowed">
                        <span v-if="isLoading" class="absolute left-0 inset-y-0 flex items-center pl-3">
                            <!-- Simplified loading spinner -->
                            <svg class="animate-spin h-5 w-5 text-[var(--color-custom-yellow)]"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                        </span>
                        Connexion
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script lang="ts">
import { defineComponent, ref, computed } from 'vue'
import { useRoute } from 'vue-router'
import { useAuth } from '../composables/useAuth'

export default defineComponent({
    name: 'LoginView',
    setup() {
        const route = useRoute()
        const { login } = useAuth()

        const username = ref('')
        const password = ref('')
        const rememberMe = ref(false)
        const isLoading = ref(false)
        const errorMessage = ref('')

        // Check if we have a redirect path from the route
        const redirectPath = computed(() => route.query.redirect as string || '')

        const handleLogin = async () => {
            errorMessage.value = ''
            isLoading.value = true

            try {
                // Use the auth service to login and pass the rememberMe value
                const result = await login(username.value, password.value, rememberMe.value)

                if (!result.success) {
                    // Translate error messages to French
                    if (result.message === 'Invalid credentials') {
                        errorMessage.value = 'Identifiants invalides'
                    } else if (result.message === 'Login succeeded but failed to retrieve user data') {
                        errorMessage.value = 'Connexion réussie mais impossible de récupérer les données utilisateur'
                    } else {
                        errorMessage.value = result.message || 'Échec de la connexion'
                    }
                }
            } catch (error: any) {
                // This shouldn't happen as errors are handled in the login function,
                // but just in case
                errorMessage.value = 'Une erreur inattendue est survenue'
                console.error('Unexpected login error:', error)
            } finally {
                isLoading.value = false
            }
        }

        return {
            username,
            password,
            rememberMe,
            isLoading,
            errorMessage,
            redirectPath,
            handleLogin
        }
    }
})
</script>