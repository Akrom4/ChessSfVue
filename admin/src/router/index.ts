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

router.beforeEach(async (to, from, next) => {
  console.log('router.beforeEach:', { from: from.path, to: to.path })
  const { isAuthenticated, checkAuth } = useAuth()

  if (to.meta.requiresAuth) {
    console.log('Route requires auth:', to.path)
    if (!isAuthenticated.value) {
      console.log('User not authenticated, running checkAuth...')
      const validAuth = await checkAuth()
      if (!validAuth) {
        console.log('checkAuth failed, redirecting to login')
        next({ name: 'Login', query: { redirect: to.fullPath } })
      } else {
        console.log('checkAuth succeeded, proceeding to route')
        next()
      }
    } else {
      console.log('User already authenticated, proceeding')
      next()
    }
  } else {
    console.log('Route does not require auth, proceeding')
    next()
  }
})

export default router
