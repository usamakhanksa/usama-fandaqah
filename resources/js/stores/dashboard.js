import { defineStore } from 'pinia';
import api from '../services/api';
export const useDashboardStore = defineStore('dash',{
  state:()=>({summary:null,reservations:{data:[]},notifications:[]}),
  actions:{
    async load(){
      const [summary,res,notifications]=await Promise.allSettled([
        api.get('/dashboard/summary'),api.get('/reservations'),api.get('/notifications')
      ]);
      this.summary = summary.status === 'fulfilled' ? summary.value.data : { stats: {}, recent_status: {} };
      this.reservations = res.status === 'fulfilled' ? res.value.data : { data: [] };
      this.notifications = notifications.status === 'fulfilled' ? notifications.value.data : [];
    }
  }
});
