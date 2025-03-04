import { createRouter, createWebHistory } from 'vue-router'
import Dashboard from '../views/Dashboard.vue'
import Login from '../views/Login.vue'
import { useAuth } from '../composables/useAuth'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/login',
      name: 'Login',
      component: Login,
      meta: { requiresAuth: false }
    },
    {
      path: '/',
      name: 'Dashboard',
      component: Dashboard,
      meta: { requiresAuth: true }
    },
    {
      path: '/chess',
      name: 'Chess',
      component: () => import('../views/Dashboard.vue'),
      meta: { requiresAuth: true }
    },
  ],
})

// Navigation guard
router.beforeEach((to, from, next) => {
  const { checkAuth, isAuthenticated } = useAuth()
  
  // Check if authentication is required
  if (to.meta.requiresAuth) {
    // Check if user is authenticated
    if (!isAuthenticated.value && !checkAuth()) {
      // Redirect to login if not authenticated
      next({ name: 'Login', query: { redirect: to.fullPath } })
    } else {
      next()
    }
  } else {
    // If route doesn't require auth, allow access
    next()
  }
})

export default router
