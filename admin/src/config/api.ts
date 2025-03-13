/**
 * API Configuration
 * Contains settings for the API connection
 */

// Development API URL
const DEV_API_URL = 'http://localhost:8000';
// Production API URL - update this when deploying
const PROD_API_URL = 'https://api.example.com';

// Determine the current API URL based on environment
export const API_BASE_URL = process.env.NODE_ENV === 'production' 
  ? PROD_API_URL 
  : DEV_API_URL;

// Path configurations
export const API_PATHS = {
  IMAGES: {
    COURSES: `${API_BASE_URL}/images/courses/`
  }
};

/**
 * Axios request configuration with CORS settings
 * Use this when creating axios instances
 */
export const API_CONFIG = {
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest'
  },
  withCredentials: true // Set to true since your server has allow_credentials: true
};

/**
 * Axios request configuration for form data uploads
 * Use this when sending FormData (file uploads)
 */
export const FORM_DATA_CONFIG = {
  baseURL: API_BASE_URL,
  headers: {
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest'
    // Do NOT set Content-Type here - it will be set automatically with the correct boundary
  },
  withCredentials: true,
  // This ensures that the proper Content-Type with boundary is set
  transformRequest: [(data: any) => data]
}; 