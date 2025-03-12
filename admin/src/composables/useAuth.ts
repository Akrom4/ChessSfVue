import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import api from '../lib/axios'

interface User {
  id: number
  username: string
  email: string
  roles: string[]
  createdAt: string
  updatedAt?: string
}

const isAuthenticated = ref(false)
const user = ref<User | null>(null)

export function useAuth() {
  const router = useRouter()

  const verifyExistingAuth = async () => {
    try {
      console.log('Verifying auth via GET /me');
      const { data } = await api.get('/me');
      isAuthenticated.value = true;
      user.value = data;
      console.log('Auth verified, user:', user.value);
      return true;
    } catch (error) {
      console.error('verifyExistingAuth error:', error);
      isAuthenticated.value = false;
      user.value = null;
      return false;
    }
  };

  const login = async (username: string, password: string) => {
    try {
      const response = await api.post('/login', { username, password });
      
      if (response.status === 200) {
        try {
          const { data: userData } = await api.get('/me');
          user.value = userData;
          isAuthenticated.value = true;
          return { success: true };
        } catch (error) {
          isAuthenticated.value = false;
          return { 
            success: false, 
            message: 'Login succeeded but failed to retrieve user data' 
          };
        }
      } else {
        isAuthenticated.value = false;
        return { 
          success: false, 
          message: response.data.message || 'Identifiants invalides' 
        };
      }
    } catch (error: any) {
      if (!(error.response?.status === 401)) {
        console.error('login error:', error);
      }
      
      isAuthenticated.value = false;
      let errorMessage = 'Une erreur est survenue lors de la connexion';
      
      if (error.response) {
        if (error.response.status === 401) {
          errorMessage = 'Identifiants invalides';
        } else if (error.response.data?.error) {
          errorMessage = error.response.data.error;
        } else if (error.response.data?.message) {
          errorMessage = error.response.data.message;
        }
      }
      
      return { success: false, message: errorMessage };
    }
  };

  const logout = async () => {
    console.log('Logging out user');
    try {
      await api.post('/logout');
    } catch (error) {
      console.error('Logout error:', error);
    }
    isAuthenticated.value = false;
    user.value = null;
    router.push('/login');
    console.log('Redirected to login page');
  };

  const checkAuth = async () => {
    try {
      console.log('Performing auth check via GET /me');
      const { data } = await api.get('/me');
      isAuthenticated.value = true;
      user.value = data;
      console.log('Auth check successful, user:', user.value);
      return true;
    } catch (error) {
      console.error('checkAuth error:', error);
      isAuthenticated.value = false;
      user.value = null;
      return false;
    }
  };

  const isAdmin = computed(() => user.value?.roles.includes('ROLE_ADMIN') || false);

  return {
    isAuthenticated,
    user,
    isAdmin,
    verifyExistingAuth,
    login,
    logout,
    checkAuth
  };
}
