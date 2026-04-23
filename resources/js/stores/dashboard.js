import { defineStore } from 'pinia';
import api from '../services/api';

export const useDashboardStore = defineStore('dashboard', {
  state: () => ({
    kpis: [],
    quickStats: [],
    recentActivities: [],
    dailyRevenue: [],
    loading: false,
    error: null
  }),
  actions: {
    async load() {
      this.loading = true;
      try {
        const { data } = await api.get('/dashboard');
        this.kpis = data.kpis || [];
        this.quickStats = data.quickStats || [];
        this.recentActivities = data.recentActivities || [];
        this.dailyRevenue = data.dailyRevenue || [];
      } catch (err) {
        console.error('Dashboard load failed:', err);
        this.error = 'Failed to load dashboard data';
      } finally {
        this.loading = false;
      }
    }
  }
});
