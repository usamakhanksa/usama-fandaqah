import { defineStore } from 'pinia';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: localStorage.getItem('sanctum_token') || localStorage.getItem('auth_fandaqah') || null,
    permissions: JSON.parse(localStorage.getItem('permissions') || '[]'),
  }),
  getters: {
    isAuthenticated: (state) => Boolean(state.token),
  },
  actions: {
    login(token, permissions = []) {
      this.token = token;
      this.permissions = permissions.length ? permissions : ['*'];
      localStorage.setItem('auth_fandaqah', 'true');
      localStorage.setItem('sanctum_token', token);
      localStorage.setItem('permissions', JSON.stringify(this.permissions));
    },
    logout() {
      this.token = null;
      this.permissions = [];
      localStorage.removeItem('auth_fandaqah');
      localStorage.removeItem('sanctum_token');
      localStorage.removeItem('permissions');
    },
    can(permission) {
      if (!permission) return true;
      return this.permissions.includes('*') || this.permissions.includes(permission);
    },
  },
});
