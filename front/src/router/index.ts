import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import LoginView from '../views/LoginView.vue'
import LogoutView from '../views/LogoutView.vue'
import MainLayout from '../layouts/MainLayout.vue'
import NotFound from '../views/NotFound.vue'
import { useAuth } from '../composables/useAuth'

// Navigation guard
const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    // Public routes (no layout)
    {
      path: '/login',
      name: 'Login',
      component: LoginView,
      meta: { requiresAuth: false }
    },
    // Authenticated routes (with MainLayout)
    {
      path: '/',
      component: MainLayout,
      children: [
        {
          path: '',
          name: 'home',
          component: HomeView,
          meta: { requiresAuth: true }
        },
        {
          path: 'chess',
          name: 'Chess',
          component: () => import('../views/ChessView.vue'),
          meta: { requiresAuth: true }
        },
        {
          path: 'puzzles',
          name: 'Puzzles',
          component: () => import('../views/PuzzleView.vue'),
          meta: { requiresAuth: true }
        },
        {
          path: 'lessons',
          name: 'Lessons',
          component: () => import('../views/CoursesView.vue'),
          meta: { requiresAuth: true }
        },
        {
          path: 'lessons/:id',
          name: 'CourseDetail',
          component: () => import('../views/CourseDetailView.vue'),
          meta: { requiresAuth: true }
        },
        {
          path: 'my-lessons/:id/chapters',
          name: 'Chapters',
          component: () => import('../views/ChaptersView.vue'),
          meta: { requiresAuth: true, requiresFollowing: true }
        },
        {
          path: 'my-lessons/:courseId/chapter/:chapterId',
          name: 'ChapterView',
          component: () => import('../views/ChessView.vue'),
          meta: { requiresAuth: true, requiresFollowing: true }
        },
        {
          path: 'my-lessons',
          name: 'MyLessons',
          component: () => import('../views/MyLessonsView.vue'),
          meta: { requiresAuth: true }
        },
        {
          path: 'logout',
          name: 'Logout',
          component: LogoutView,
          meta: { requiresAuth: true }
        },
        {
          path: 'profile',
          name: 'Profile',
          component: () => import('../views/ProfileView.vue'),
          meta: { requiresAuth: true }
        },
        {
          path: 'settings',
          name: 'Settings',
          component: () => import('../views/SettingsView.vue'),
          meta: { requiresAuth: true }
        },
        {
          path: 'components',
          name: 'Components',
          component: () => import('../views/ComponentExamples.vue'),
          meta: { requiresAuth: true }
        }
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

  // For login page, allow access regardless of auth status
  if (to.name === 'Login') {
    console.log('Proceeding to login page')
    // If they're already authenticated, redirect to home instead
    if (isAuthenticated.value) {
      console.log('User already authenticated, redirecting to home')
      next({ name: 'home' })
      return
    }
    next()
    return
  }

  // For all other routes, check authentication first
  if (!isAuthenticated.value) {
    console.log('User not authenticated, checking auth...')
    try {
      // Only perform the check auth if we're not already on a transition to login
      // to avoid potential loops
      const authResult = from.name !== 'Login' ? await checkAuth() : false
      
      if (!authResult) {
        console.log('Auth check failed, redirecting to login')
        // If redirect from an auth error, clear any existing redirect param
        // to prevent potential redirect loops
        const shouldRedirect = !to.path.startsWith('/login')
        const query = shouldRedirect ? { redirect: to.fullPath } : {}
        
        next({ 
          name: 'Login', 
          query: query,
          replace: true // Replace the current entry rather than pushing a new one
        })
        return
      } else {
        console.log('Auth check successful, user is authenticated')
        isAuthenticated.value = true
      }
    } catch (error) {
      console.log('Auth check error, redirecting to login')
      next({ 
        name: 'Login', 
        query: { redirect: to.fullPath },
        replace: true
      })
      return
    }
  }

  // At this point, user is authenticated
  // Handle routes with undefined meta or non-existent routes
  if (to.matched.length === 0) {
    console.log('No matching route found, redirecting to NotFound')
    next({ name: 'NotFound' })
    return
  }
  
  // Special check for routes that require following a course
  if (to.meta.requiresFollowing) {
    // For our purposes, we'll let the component handle this check and redirect
    // since we need to fetch user courses data from the API
    console.log('Route requires following a course, component will verify')
  }
  
  // Check if the authenticated user has access to this route
  if (to.meta.requiresAuth === false && to.name !== 'NotFound') {
    console.log('Authenticated user trying to access non-auth route, redirecting to home')
    next({ name: 'home' })
    return
  }

  // Proceed to the requested route
  console.log('User authenticated, proceeding to route')
  next()
})

export default router
