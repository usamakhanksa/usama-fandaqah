import { defineStore } from 'pinia';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: localStorage.getItem('auth_fandaqah') || null,
    permissions: JSON.parse(localStorage.getItem('permissions') || '[]'),
  }),
  getters: {
    isAuthenticated: (state) => Boolean(state.token),
  },
  actions: {
    login(token, permissions = []) {
      this.token = token;
      this.permissions = permissions;
      localStorage.setItem('auth_fandaqah', token);
      localStorage.setItem('permissions', JSON.stringify(permissions));
    },
    logout() {
      this.token = null;
      this.permissions = [];
      localStorage.removeItem('auth_fandaqah');
      localStorage.removeItem('permissions');
    },
    can(permission) {
      if (!permission) return true;
      return this.permissions.includes(permission);
    },
  },
});
