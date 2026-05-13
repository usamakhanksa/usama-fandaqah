<template>
  <div class="p-6 space-y-6 bg-[#f8f9fa] min-h-full">
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-50 flex items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-[#2a273c]">POS Dashboard</h1>
        <p class="text-xs font-black text-[#e95a54] uppercase tracking-widest mt-1">Today's Sales Overview</p>
      </div>
      <router-link to="/pos/sale" class="bg-[#e95a54] text-white px-4 py-3 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-opacity-90 flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
        New Sale
      </router-link>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-50">
        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Today's Sales</p>
        <p class="text-3xl font-bold text-[#2a273c] mt-2">{{ stats.today_sales || 0 }} <span class="text-sm text-slate-400">SAR</span></p>
      </div>
      <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-50">
        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Transactions</p>
        <p class="text-3xl font-bold text-[#2a273c] mt-2">{{ stats.recent_sales?.length || 0 }}</p>
      </div>
    </div>

    <!-- Recent Sales -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-50 p-6">
      <h2 class="text-sm font-black text-[#2a273c] uppercase tracking-widest mb-4">Recent Sales</h2>
      <div class="space-y-2">
        <div v-for="log in stats.recent_sales" :key="log.id" class="flex items-center justify-between py-3 border-b border-slate-50">
          <div>
            <p class="text-sm font-bold text-[#2a273c]">{{ log.number }}</p>
            <p class="text-[10px] text-slate-400 font-medium">{{ formatDate(log.created_at) }}</p>
          </div>
          <span class="text-sm font-bold text-[#e95a54]">{{ (log.amount / 100).toFixed(2) }} SAR</span>
        </div>
        <p v-if="!stats.recent_sales?.length" class="text-center text-slate-400 text-sm py-8">No sales today.</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../services/api';
import dayjs from 'dayjs';

const stats = ref({});
const formatDate = (d) => dayjs(d).format('DD MMM HH:mm');

onMounted(async () => {
  const { data } = await api.get('/pos-module/dashboard');
  stats.value = data.data || {};
});
</script>
