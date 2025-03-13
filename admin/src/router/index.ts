import { createRouter, createWebHistory } from 'vue-router'
import Dashboard from '../views/Dashboard.vue'
import Login from '../views/Login.vue'
import MainLayout from '../layouts/MainLayout.vue'
import NotFound from '../views/NotFound.vue'
import Users from '../views/Users.vue'
import CoursesList from '../views/Courses.vue'
import { useAuth } from '../composables/useAuth'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    // Public routes (no layout)
    {
      path: '/login',
      name: 'Login',
      component: Login,
      meta: { requiresAuth: false }
    },
    // Authenticated routes (with MainLayout)
    {
      path: '/',
      component: MainLayout,
      children: [
        {
          path: '',
          name: 'Dashboard',
          component: Dashboard,
          meta: { requiresAuth: true }
        },
        {
          path: 'users',
          name: 'Users',
          component: Users,
          meta: { requiresAuth: true, roles: ['ROLE_ADMIN'] }
        },
        // Courses routes
        {
          path: 'courses',
          name: 'Courses',
          component: CoursesList,
          meta: { requiresAuth: true, roles: ['ROLE_ADMIN'] }
        },
        // {
        //   path: 'courses/create',
        //   name: 'CourseCreate',
        //   component: () => import('../views/courses/CourseCreate.vue'),
        //   meta: { requiresAuth: true, roles: ['ROLE_ADMIN'] }
        // },
        // {
        //   path: 'courses/edit/:id',
        //   name: 'CourseEdit',
        //   component: () => import('../views/courses/CourseEdit.vue'),
        //   meta: { requiresAuth: true, roles: ['ROLE_ADMIN'] }
        // },
        // // Chapters routes (nested under a course)
        // {
        //   path: 'courses/:courseId/chapters',
        //   name: 'Chapters',
        //   component: () => import('../views/chapters/ChaptersList.vue'),
        //   meta: { requiresAuth: true, roles: ['ROLE_ADMIN'] }
        // },
        // {
        //   path: 'courses/:courseId/chapters/create',
        //   name: 'ChapterCreate',
        //   component: () => import('../views/chapters/ChapterCreate.vue'),
        //   meta: { requiresAuth: true, roles: ['ROLE_ADMIN'] }
        // },
        // {
        //   path: 'courses/:courseId/chapters/edit/:id',
        //   name: 'ChapterEdit',
        //   component: () => import('../views/chapters/ChapterEdit.vue'),
        //   meta: { requiresAuth: true, roles: ['ROLE_ADMIN'] }
        // },
        // // Add other authenticated routes here
      ]
    },
    // 404 Not Found route - must be last
    {
      path: '/:pathMatch(.*)*',
      name: 'NotFound',
      component: NotFound,
      meta: { requiresAuth: false }
    }
  ],
})

// Navigation guard
router.beforeEach(async (to, from, next) => {
  const { checkAuth, isAuthenticated, user } = useAuth()
  
  console.log('Navigating to:', to.path)
  console.log('Is authenticated:', isAuthenticated.value)

  // Allow access to login page regardless of authentication status
  if (to.name === 'Login') {
    console.log('Proceeding to login page')
    next()
    return
  }

  // For all other routes, check authentication first
  if (!isAuthenticated.value) {
    console.log('User not authenticated, checking auth...')
    const authResult = await checkAuth()
    if (!authResult) {
      console.log('Auth check failed, redirecting to login')
      next({ name: 'Login', query: { redirect: to.fullPath } })
      return
    } else {
      console.log('Auth check successful, user is authenticated')
      isAuthenticated.value = true
    }
  }

  // At this point, user is authenticated
  // Handle routes with undefined meta or non-existent routes
  if (to.matched.length === 0) {
    console.log('No matching route found, redirecting to NotFound')
    next({ name: 'NotFound' })
    return
  }
  
  // Check for role-based access
  if (to.meta.roles && Array.isArray(to.meta.roles) && to.meta.roles.length > 0) {
    const hasRequiredRole = to.meta.roles.some(role => user.value?.roles.includes(role))
    
    if (!hasRequiredRole) {
      console.log('User does not have required role, redirecting to dashboard')
      next({ name: 'Dashboard' })
      return
    }
  }

  // Check if the authenticated user has access to this route
  if (to.meta.requiresAuth === false && to.name !== 'NotFound') {
    console.log('Authenticated user trying to access non-auth route, redirecting to dashboard')
    next({ name: 'Dashboard' })
    return
  }

  // Proceed to the requested route
  console.log('User authenticated, proceeding to route')
  next()
})

export default router
