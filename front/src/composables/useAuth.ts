import { ref, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import api from '../api'

interface User {
  id: number
  username: string
  email: string
  roles: string[]
  createdAt: string
  updatedAt?: string
}

// Shared state across all instances of the composable
const isAuthenticated = ref(false)
const user = ref<User | null>(null)

// Custom event for auth state changes
const AUTH_STATE_CHANGE_EVENT = 'auth-state-change'

// Helper to dispatch auth state change events
const dispatchAuthStateChangeEvent = (authState: boolean) => {
  // Create and dispatch a custom event that components can listen for
  const event = new CustomEvent(AUTH_STATE_CHANGE_EVENT, { 
    detail: { isAuthenticated: authState }
  })
  window.dispatchEvent(event)
}

export function useAuth() {
  const router = useRouter()
  const route = useRoute()

  const verifyExistingAuth = async () => {
    try {
      console.log('Verifying auth via GET /me')
      const { data } = await api.get('/me')
      isAuthenticated.value = true
      user.value = data
      dispatchAuthStateChangeEvent(true)
      console.log('Auth verified, user:', user.value)
      return true
    } catch (error) {
      console.error('verifyExistingAuth error:', error)
      isAuthenticated.value = false
      user.value = null
      dispatchAuthStateChangeEvent(false)
      return false
    }
  }

  const login = async (username: string, password: string, rememberMe: boolean = false) => {
    try {
      console.log('Attempting login with username:', username, 'Remember me:', rememberMe)
      // Pass rememberMe to the backend
      const response = await api.post('/login', { 
        username, 
        password,
        rememberMe // Add this to your request
      })
      
      if (response.status === 200) {
        console.log('Login successful, token received in cookie')
        try {
          const { data: userData } = await api.get('/me')
          user.value = userData
          isAuthenticated.value = true
          dispatchAuthStateChangeEvent(true)
          
          // Check if we have a redirect query parameter and navigate there
          const redirectPath = route.query.redirect as string || '/'
          console.log(`Redirecting to: ${redirectPath}`)
          router.push(redirectPath)
          
          return { success: true }
        } catch (error) {
          isAuthenticated.value = false
          dispatchAuthStateChangeEvent(false)
          return { 
            success: false, 
            message: 'Login succeeded but failed to retrieve user data' 
          }
        }
      } else {
        isAuthenticated.value = false
        dispatchAuthStateChangeEvent(false)
        return { 
          success: false, 
          message: response.data.message || 'Invalid credentials' 
        }
      }
    } catch (error: any) {
      if (!(error.response?.status === 401)) {
        console.error('login error:', error)
      }
      
      isAuthenticated.value = false
      dispatchAuthStateChangeEvent(false)
      let errorMessage = 'An error occurred during login'
      
      if (error.response) {
        if (error.response.status === 401) {
          errorMessage = 'Invalid credentials'
        } else if (error.response.data?.error) {
          errorMessage = error.response.data.error
        } else if (error.response.data?.message) {
          errorMessage = error.response.data.message
        }
      }
      
      return { success: false, message: errorMessage }
    }
  }

  const logout = async () => {
    console.log('Logging out user')
    try {
      // Call the logout endpoint that clears the authentication cookie
      await api.post('/api/logout')
      console.log('Logout API call successful')
    } catch (error) {
      console.error('Error during logout API call:', error)
    } finally {
      // Clear authenticated state
      isAuthenticated.value = false
      user.value = null
      dispatchAuthStateChangeEvent(false)
      
      // Redirect to login page
      router.push('/login')
      console.log('Redirected to login page')
    }
  }

  const checkAuth = async () => {
    try {
      console.log('Performing auth check via GET /me')
      const { data } = await api.get('/me')
      isAuthenticated.value = true
      user.value = data
      dispatchAuthStateChangeEvent(true)
      console.log('Auth check successful, user:', user.value)
      return true
    } catch (error) {
      console.error('checkAuth error:', error)
      isAuthenticated.value = false
      user.value = null
      dispatchAuthStateChangeEvent(false)
      return false
    }
  }

  // Listen for auth state changes
  const onAuthStateChange = (callback: (isAuth: boolean) => void): (() => void) => {
    const handleAuthChange = (event: Event) => {
      const customEvent = event as CustomEvent
      callback(customEvent.detail?.isAuthenticated || false)
    }
    
    window.addEventListener(AUTH_STATE_CHANGE_EVENT, handleAuthChange)
    
    // Return a function to remove the listener
    return () => {
      window.removeEventListener(AUTH_STATE_CHANGE_EVENT, handleAuthChange)
    }
  }

  const isAdmin = computed(() => user.value?.roles.includes('ROLE_ADMIN') || false)
  const isUser = computed(() => user.value?.roles.includes('ROLE_USER') || false)

  return {
    isAuthenticated,
    user,
    isAdmin,
    isUser,
    verifyExistingAuth,
    login,
    logout,
    checkAuth,
    onAuthStateChange
  }
} 