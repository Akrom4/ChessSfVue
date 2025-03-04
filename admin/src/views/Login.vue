<template>
  <div class="min-h-screen flex items-center justify-center bg-surface-50 dark:bg-surface-900">
    <div class="w-full max-w-md p-8 bg-surface-0 dark:bg-surface-800 rounded-xl shadow-lg">
      <div class="text-center mb-8">
        <h1 class="text-2xl font-bold text-surface-900 dark:text-surface-0">Echecs Plus - Admin</h1>
        <p class="text-surface-600 dark:text-surface-400 mt-2">Veuillez vous connecter</p>
      </div>
      
      <form @submit.prevent="handleLogin" class="space-y-6">
        <div>
          <label for="username" class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-2">
            Nom d'utilisateur
          </label>
          <input
            id="username"
            v-model="username"
            type="text"
            autocomplete="username"
            class="w-full p-2 border border-surface-300 dark:border-surface-600 rounded-lg bg-surface-0 dark:bg-surface-900 text-surface-900 dark:text-surface-0 focus:ring-2 focus:ring-primary-500 focus:border-transparent"
            required
          />
        </div>
        
        <div>
          <label for="password" class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-2">
            Mot de passe
          </label>
          <input
            id="password"
            v-model="password"
            type="password"
            autocomplete="current-password"
            class="w-full p-2 border border-surface-300 dark:border-surface-600 rounded-lg bg-surface-0 dark:bg-surface-900 text-surface-900 dark:text-surface-0 focus:ring-2 focus:ring-primary-500 focus:border-transparent"
            required
          />
        </div>

        <div v-if="error" class="text-red-500 text-sm">
          {{ error }}
        </div>

        <button
          type="submit"
          class="w-full py-2 px-4 bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition-colors duration-200"
          :disabled="loading"
        >
          {{ loading ? 'Connexion en cours...' : 'Connexion' }}
        </button>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useAuth } from '../composables/useAuth'

const { login } = useAuth()
const username = ref('')
const password = ref('')
const error = ref('')
const loading = ref(false)

const handleLogin = async () => {
  loading.value = true
  error.value = ''
  
  try {
    const success = await login(username.value, password.value)
    if (!success) {
      error.value = 'Identifiants invalides'
    }
  } catch (e) {
    error.value = 'Une erreur est survenue lors de la connexion'
  } finally {
    loading.value = false
  }
}
</script> 