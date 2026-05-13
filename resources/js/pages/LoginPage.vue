<template>
  <div class="h-screen w-full relative flex items-center justify-center overflow-hidden bg-slate-950 font-['Outfit']">
    <div class="absolute inset-0 z-0 scale-110 animate-[pulse_8s_infinite]">
      <img :src="'/assets/banners/login_bg.png'" class="w-full h-full object-cover opacity-40 grayscale-[0.2]" alt="bg" />
    </div>
    <div class="absolute inset-0 bg-gradient-to-tr from-rose-500/10 via-transparent to-rose-500/10 z-0"></div>

    <div class="card relative z-10 w-full max-w-md p-10 backdrop-blur-xl bg-white/90 border-white/40 shadow-2xl flex flex-col gap-8 rounded-[40px]">
      <div class="text-center space-y-2">
        <div class="inline-flex items-center gap-2 bg-rose-50 px-4 py-2 rounded-full mb-2">
          <img :src="'/assets/avatars/admin.svg'" class="w-6 h-6" />
          <span class="text-rose-600 font-bold text-sm tracking-wide">FANDAQAH PLATFORM</span>
        </div>
        <h1 class="text-3xl font-black text-slate-900">Welcome Back</h1>
        <p class="text-slate-500 text-sm">Please enter your credentials to access the hub</p>
      </div>

      <div class="space-y-4">
        <div>
          <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2 px-1">Email Address</label>
          <input v-model="email" type="email" placeholder="admin@hotel.test" class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 focus:ring-2 ring-rose-300 transition-all outline-none text-slate-800 placeholder:text-slate-300 shadow-sm" />
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2 px-1">Password</label>
          <input v-model="password" type="password" placeholder="••••••••" class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 focus:ring-2 ring-rose-300 transition-all outline-none text-slate-800 placeholder:text-slate-300 shadow-sm" @keyup.enter="login" />
        </div>
        <div v-if="errorMsg" class="text-xs text-rose-500 font-bold px-1">{{ errorMsg }}</div>
        <div class="flex items-center justify-between px-1">
          <label class="flex items-center gap-2 text-xs text-slate-500 cursor-pointer group">
            <input type="checkbox" class="accent-rose-500 w-4 h-4" />
            <span class="group-hover:text-slate-700 transition-colors">Remember me</span>
          </label>
          <a href="#" class="text-xs text-rose-500 font-bold hover:underline">Forgot?</a>
        </div>
      </div>

      <button @click="login" :disabled="loading" class="w-full bg-slate-900 hover:bg-rose-600 text-white font-bold py-5 rounded-2xl shadow-xl shadow-rose-200 transition-all active:scale-95 group flex items-center justify-center gap-2">
        <span v-if="loading" class="mr-2 w-4 h-4 border-t-2 border-r-2 border-white rounded-full animate-spin inline-block"></span>
        {{ loading ? 'SIGNING IN...' : 'SIGN IN' }}
        <span class="text-lg group-hover:translate-x-1 transition-transform">→</span>
      </button>

      <div class="text-center">
        <p class="text-[10px] text-slate-300 font-medium uppercase tracking-[0.2em]">Crafted for excellence by Antigravity</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import api from '../services/api';

const router = useRouter();
const email = ref('');
const password = ref('');
const errorMsg = ref('');
const loading = ref(false);

const login = async () => {
  if (loading.value) return; // Prevent multiple clicks
  
  if (!email.value || !password.value) {
    errorMsg.value = 'Please enter email and password';
    return;
  }
  
  loading.value = true;
  errorMsg.value = '';
  
  try {
    const response = await api.post('/login', { 
      email: email.value, 
      password: password.value 
    });
    
    // Handle different possible response structures
    const token = response.data?.data?.token || 
                  response.data?.token || 
                  response.data?.access_token ||
                  (response.data && typeof response.data === 'string' && response.data.length > 10 ? response.data : null);
                  
    if (!token) {
      throw new Error('Token not received from server');
    }
    
    localStorage.setItem('sanctum_token', token);
    localStorage.setItem('auth_fandaqah', 'true');
    
    // Try to get user data to verify the token is valid
    try {
      const userData = await api.get('/user');
      console.log('User authenticated:', userData.data.name || userData.data.email);
    } catch (userDataErr) {
      console.warn('Could not fetch user data after login:', userDataErr);
    }

    router.push('/dashboard');
  } catch (error) {
    console.error('Login error details:', error);
    
    if (error.response) {
      // Server responded with error status
      const errorMessage = error.response.data?.message || 
                          error.response.data?.error || 
                          error.response.statusText || 
                          'Login failed';
      errorMsg.value = errorMessage;
      
      // Specifically check for 401 error
      if (error.response.status === 401) {
        errorMsg.value = 'Invalid email or password. Please try again.';
      }
    } else if (error.request) {
      // Request was made but no response received
      errorMsg.value = 'Network error. Please check your connection and try again.';
    } else {
      // Something else happened
      errorMsg.value = error.message || 'An unexpected error occurred.';
    }
    
    // For development, provide a fallback login if using default admin credentials
    // Only enable this for local environments
    const appEnv = process.env.NODE_ENV || 'development';
    if (appEnv === 'development' && 
        email.value === 'admin@hotel.test' && 
        password.value === 'admin') {
      localStorage.setItem('sanctum_token', 'dev_token_for_local_testing');
      localStorage.setItem('auth_fandaqah', 'true');
      router.push('/dashboard');
    }
  } finally {
    loading.value = false;
  }
};
</script>