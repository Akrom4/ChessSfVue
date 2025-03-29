import axios from 'axios'
import type { AxiosResponse, AxiosError, InternalAxiosRequestConfig } from 'axios'
import router from '../router'

// Environment variables
const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'
const ENABLE_LOGGING = import.meta.env.VITE_ENABLE_API_LOGGING !== 'false'

// Create the API client
const api = axios.create({
  baseURL: API_URL,
  withCredentials: true, // ensures cookies are sent with each request
  headers: {
    'Content-Type': 'application/ld+json', // API Platform requires application/ld+json for both requests and responses
    'Accept': 'application/ld+json'
  }
})

// Request interceptor - add any request handling here
api.interceptors.request.use(
  (config: InternalAxiosRequestConfig) => {
    if (ENABLE_LOGGING) {
      console.log(`Making ${config.method?.toUpperCase()} request to ${config.baseURL}${config.url}`);
      
      // Log request details for debugging
      console.log('Request headers:', config.headers);
      if (config.data) {
        console.log('Request payload:', config.data);
      }
    }
    
    return config;
  },
  (error: AxiosError) => {
    if (ENABLE_LOGGING) {
      console.error('Request error:', error);
    }
    return Promise.reject(error);
  }
);

// Response interceptor for better error handling
api.interceptors.response.use(
  (response: AxiosResponse) => {
    if (ENABLE_LOGGING) {
      console.log(`Response from ${response.config.url}:`, response.status);
    }
    return response;
  },
  (error: AxiosError) => {
    // Extract the most useful error information
    const errorInfo = {
      status: error.response?.status,
      statusText: error.response?.statusText,
      data: error.response?.data,
      url: error.config?.url,
      method: error.config?.method?.toUpperCase(),
      headers: error.config?.headers
    };
    
    // Log with appropriate level based on error status
    if (error.response?.status === 415) {
      console.error('API Media Type Error - Check Content-Type header:', errorInfo);
      console.error('Server expects application/ld+json for both request and response');
    } else if (error.response?.status === 500) {
      console.error('API Server Error:', errorInfo);
    } else {
      console.error('API Response error:', errorInfo);
    }
    
    // For 401 Unauthorized errors, redirect to login
    if (error.response?.status === 401) {
      console.warn('Authentication error detected, will redirect to login');
      
      // Reset authentication state in memory
      window.dispatchEvent(new CustomEvent('auth-state-change', { 
        detail: { isAuthenticated: false }
      }));
      
      // Store the current path to redirect back after login
      const currentPath = router.currentRoute.value.fullPath;
      const isLoginPage = currentPath === '/login' || router.currentRoute.value.name === 'Login';
      const isAuthRelatedRequest = error.config?.url?.includes('/api/me') ||
                                   error.config?.url?.includes('/api/login_check');
      
      // Only redirect if we're not already on the login page and not in a redirect loop
      if (!isLoginPage && !isAuthRelatedRequest) {
        // Add a small delay to ensure the auth state has been updated
        setTimeout(() => {
          // Double-check we're still not on login page (could have changed during delay)
          if (router.currentRoute.value.name !== 'Login') {
            router.push({ 
              name: 'Login', 
              query: { redirect: currentPath !== '/login' ? currentPath : undefined }
            }).catch((err) => {
              // If navigation error happens, log it but don't force a reload
              // to prevent potential redirect loops
              console.error('Navigation error:', err);
            });
          }
        }, 100);
      }
    }
    
    return Promise.reject(error);
  }
)

// Export constants for use in components
export const ASSETS_URL = import.meta.env.VITE_ASSETS_URL || 'http://localhost:8000'

export default api 