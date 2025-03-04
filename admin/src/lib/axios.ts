import axios, { InternalAxiosRequestConfig } from 'axios'
import { shouldRefreshToken, isTokenExpired } from './jwt'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL,
  headers: {
    'Content-Type': 'application/json'
  },
  withCredentials: true // Important for CORS with cookies  
})

let isRefreshing = false
let failedQueue: any[] = []

const processQueue = (error: any = null) => {
  failedQueue.forEach(prom => {
    if (error) {
      prom.reject(error)
    } else {
      prom.resolve()
    }
  })
  failedQueue = []
}

const handleLogout = () => {
  localStorage.removeItem('isAuthenticated')
  localStorage.removeItem('user')
  localStorage.removeItem('token')
  localStorage.removeItem('refresh_token')
  window.location.href = '/login'
}

// Request interceptor
api.interceptors.request.use(
  async (config: InternalAxiosRequestConfig) => {
    const token = localStorage.getItem('token')
    if (!token) return config

    // Check if token is expired or about to expire
    if (isTokenExpired(token) || shouldRefreshToken(token)) {
      // If we're already refreshing, queue the request
      if (isRefreshing) {
        return new Promise<InternalAxiosRequestConfig>((resolve, reject) => {
          failedQueue.push({ resolve, reject })
        })
          .then(() => {
            const newToken = localStorage.getItem('token')
            if (newToken) {
              config.headers.Authorization = `Bearer ${newToken}`
            }
            return config
          })
          .catch(err => Promise.reject(err))
      }

      isRefreshing = true

      try {
        const refreshToken = localStorage.getItem('refresh_token')
        if (!refreshToken) {
          throw new Error('No refresh token available')
        }

        const { data } = await api.post('/token/refresh', {
          refresh_token: refreshToken
        })

        localStorage.setItem('token', data.token)
        localStorage.setItem('refresh_token', data.refresh_token)

        // Update the request's authorization header
        config.headers.Authorization = `Bearer ${data.token}`
        processQueue()
      } catch (refreshError) {
        processQueue(refreshError)
        handleLogout()
        return Promise.reject(refreshError)
      } finally {
        isRefreshing = false
      }
    } else {
      config.headers.Authorization = `Bearer ${token}`
    }

    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Response interceptor
api.interceptors.response.use(
  (response) => response,
  async (error) => {
    const originalRequest = error.config

    // If error is not 401 or request is for refresh token, reject
    if (error.response?.status !== 401 || originalRequest.url === '/token/refresh') {
      return Promise.reject(error)
    }

    // If we're already refreshing, queue the request
    if (isRefreshing) {
      return new Promise((resolve, reject) => {
        failedQueue.push({ resolve, reject })
      })
        .then(() => api(originalRequest))
        .catch(err => Promise.reject(err))
    }

    isRefreshing = true

    try {
      const refreshToken = localStorage.getItem('refresh_token')
      if (!refreshToken) {
        throw new Error('No refresh token available')
      }

      const { data } = await api.post('/token/refresh', {
        refresh_token: refreshToken
      })

      localStorage.setItem('token', data.token)
      localStorage.setItem('refresh_token', data.refresh_token)

      // Update the failed request's authorization header
      originalRequest.headers.Authorization = `Bearer ${data.token}`

      processQueue()
      return api(originalRequest)
    } catch (refreshError) {
      processQueue(refreshError)
      handleLogout()
      return Promise.reject(refreshError)
    } finally {
      isRefreshing = false
    }
  }
)

export default api 