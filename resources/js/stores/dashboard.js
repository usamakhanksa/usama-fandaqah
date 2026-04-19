import { defineStore } from 'pinia';
import api from '../services/api';
export const useDashboardStore = defineStore('dash',{
  state:()=>({summary:null,reservations:{data:[]},notifications:[]}),
  actions:{
    async load(){
      const [summary,res,notifications]=await Promise.all([
        api.get('/dashboard/summary'),api.get('/reservations'),api.get('/notifications')
      ]);
      this.summary=summary.data; this.reservations=res.data; this.notifications=notifications.data;
    }
  }
});
