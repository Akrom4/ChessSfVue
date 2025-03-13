import axios from 'axios'
import router from '../router'

// Create the API client
const api = axios.create({
  baseURL: 'http://localhost:8000/api', // Point directly to your Symfony backend
  withCredentials: true, // ensures cookies are sent with each request
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/ld+json' // API Platform requires application/ld+json
  }
})

// Request interceptor - add any request handling here
api.interceptors.request.use(
  config => {
    console.log(`Making ${config.method?.toUpperCase()} request to ${config.baseURL}${config.url}`);
    
    // If the request data is FormData, remove the Content-Type header
    // This allows the browser to set the correct multipart boundary
    if (config.data instanceof FormData) {
      console.log('FormData detected, removing Content-Type header');
      // Delete the Content-Type header to let the browser set it with the boundary
      delete config.headers['Content-Type'];
    }
    
    return config;
  },
  error => {
    console.error('Request error:', error);
    return Promise.reject(error);
  }
);

// Response interceptor for better error handling
api.interceptors.response.use(
  response => {
    console.log(`Response from ${response.config.url}:`, response.status);
    return response;
  },
  error => {
    // Extract the most useful error information
    const errorInfo = {
      status: error.response?.status,
      data: error.response?.data,
      url: error.config?.url,
      method: error.config?.method?.toUpperCase()
    };
    
    console.error('API Response error:', errorInfo);
    
    // For 401 Unauthorized errors, redirect to login
    if (error.response?.status === 401) {
      console.warn('Authentication error detected, redirecting to login');
      
      // Store the current path to redirect back after login
      const currentPath = router.currentRoute.value.fullPath;
      
      // Don't redirect if already on login page to avoid redirect loops
      if (router.currentRoute.value.path !== '/login') {
        router.push({ 
          name: 'Login', 
          query: { redirect: currentPath }
        });
      }
    }
    
    return Promise.reject(error);
  }
)

export default api
