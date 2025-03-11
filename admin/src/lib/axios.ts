import axios from 'axios'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL, // your backend URL (e.g. "http://localhost:8000/api")
  withCredentials: true, // ensures cookies are sent with each request
  headers: {
    'Content-Type': 'application/json'
  }
})

// Optional: simple response error logging interceptor
api.interceptors.response.use(
  response => response,
  error => {
    console.error('API response error:', error)
    return Promise.reject(error)
  }
)

export default api
