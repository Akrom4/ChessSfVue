import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import api from '../lib/axios'

interface User {
  id: number;
  username: string;
  email: string;
  roles: string[];
  createdAt: string;
  updatedAt?: string;
}

const isAuthenticated = ref(false)
const user = ref<User | null>(null)

export function useAuth() {
  const router = useRouter()

  const login = async (username: string, password: string) => {
    try {
      const { data } = await api.post('/authentication_token', { username, password })
      
      // Store the JWT token
      localStorage.setItem('token', data.token)
      
      // Get user data from the /api/me endpoint
      const { data: userData } = await api.get('/me')
      
      isAuthenticated.value = true
      user.value = userData
      
      localStorage.setItem('isAuthenticated', 'true')
      localStorage.setItem('user', JSON.stringify(user.value))
      router.push('/')
      return true
    } catch (error) {
      console.error('Login failed:', error)
      return false
    }
  }

  const logout = () => {
    isAuthenticated.value = false
    user.value = null
    localStorage.removeItem('isAuthenticated')
    localStorage.removeItem('user')
    localStorage.removeItem('token')
    localStorage.removeItem('refresh_token')
    router.push('/login')
  }

  const checkAuth = async () => {
    const token = localStorage.getItem('token')
    if (!token) return false

    try {
      const { data } = await api.get('/me')
      isAuthenticated.value = true
      user.value = data
      return true
    } catch (error) {
      console.error('Auth check failed:', error)
    }
    return false
  }

  const isAdmin = computed(() => user.value?.roles.includes('ROLE_ADMIN'))

  return {
    isAuthenticated,
    user,
    isAdmin,
    login,
    logout,
    checkAuth
  }
} 