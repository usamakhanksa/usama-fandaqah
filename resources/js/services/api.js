import axios from 'axios';

const api = axios.create({baseURL: window.location.origin + '/api'});

// Request interceptor to handle authentication
api.interceptors.request.use(
  config => {
    // Get the token from localStorage where you store the Sanctum token
    const token = localStorage.getItem('sanctum_token');
    
    if (token) {
      // For Sanctum, we use Bearer token authentication
      config.headers.Authorization = `Bearer ${token}`;
    }
    
    // Set content type
    config.headers['Content-Type'] = 'application/json';
    
    // Include XSRF token for Laravel Sanctum
    const csrfToken = document.head.querySelector('meta[name="csrf-token"]')?.content;
    if (csrfToken) {
      config.headers['X-XSRF-TOKEN'] = csrfToken;
    }
    
    return config;
  },
  error => {
    return Promise.reject(error);
  }
);

// Response interceptor to handle token expiration
api.interceptors.response.use(
  response => {
    return response;
  },
  error => {
    if (error.response?.status === 401) {
      // Remove the token since it's no longer valid
      localStorage.removeItem('sanctum_token');
      // Redirect to login page
      window.location.href = '/login';
    }

    return Promise.reject(error);
  }
);

export default api;