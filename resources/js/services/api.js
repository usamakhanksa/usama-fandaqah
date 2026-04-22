import axios from 'axios';

const api = axios.create({baseURL:'/api'});

// Request interceptor to handle authentication
api.interceptors.request.use(
  config => {
    // Get the token from localStorage or sessionStorage where you store the Sanctum token
    const token = localStorage.getItem('sanctum_token') || localStorage.getItem('auth_fandaqah');
    
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    
    // Also include CSRF token as backup for non-sanctum routes
    const csrfToken = document.head.querySelector('meta[name="csrf-token"]')?.content;
    if (csrfToken) {
      config.headers['X-CSRF-TOKEN'] = csrfToken;
      config.headers['X-Requested-With'] = 'XMLHttpRequest';
    }
    
    return config;
  },
  error => {
    return Promise.reject(error);
  }
);

// Response interceptor to handle authentication errors
api.interceptors.response.use(
  response => response,
  error => {
    if (error.response?.status === 401) {
      // Store the token if user needs to re-authenticate
      localStorage.removeItem('sanctum_token');
      localStorage.removeItem('auth_fandaqah');
      localStorage.removeItem('permissions');
      // Redirect to login if unauthorized
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

export default api;
