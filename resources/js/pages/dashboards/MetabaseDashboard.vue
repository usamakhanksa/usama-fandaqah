<template>
  <div class="h-screen flex flex-col bg-slate-50">
    <!-- Header -->
    <div class="p-6 bg-white border-b border-slate-100 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-black text-slate-900 tracking-tight">{{ currentDashboard.title }}</h1>
        <p class="text-slate-500 text-sm font-medium">Real-time analytical insights from Data Warehouse</p>
      </div>
      
      <div class="flex gap-2">
        <div class="relative">
          <select 
            v-model="selectedId" 
            class="bg-white border border-slate-100 rounded-xl px-4 py-2 text-sm font-bold text-slate-700 outline-none focus:ring-2 ring-rose-300 appearance-none pr-10"
          >
            <option v-for="d in dashboards" :key="d.id" :value="d.id">{{ d.label }}</option>
          </select>
          <ChevronDown class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" />
        </div>
        <button @click="fetchDashboardUrl" class="p-2 text-slate-400 hover:text-slate-900 hover:bg-slate-50 rounded-xl transition-all">
          <RefreshCw class="w-4 h-4" :class="{ 'animate-spin': loading }" />
        </button>
      </div>
    </div>

    <!-- Content -->
    <div class="flex-1 p-6 relative overflow-hidden">
      <div v-if="loading" class="absolute inset-0 flex items-center justify-center bg-slate-50/50 backdrop-blur-sm z-10">
        <div class="flex flex-col items-center gap-4">
          <div class="w-12 h-12 border-4 border-rose-500 border-t-transparent rounded-full animate-spin"></div>
          <p class="text-sm font-black text-slate-400 uppercase tracking-widest">Generating Secure Access...</p>
        </div>
      </div>

      <div v-if="error" class="absolute inset-0 flex items-center justify-center p-6">
        <div class="bg-white p-8 rounded-[32px] border border-slate-100 shadow-xl max-w-md text-center space-y-4">
          <div class="w-16 h-16 bg-rose-50 text-rose-500 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <ShieldAlert class="w-8 h-8" />
          </div>
          <h2 class="text-xl font-black text-slate-900">Access Denied or Connection Failed</h2>
          <p class="text-slate-500 text-sm font-medium">{{ error }}</p>
          <button @click="fetchDashboardUrl" class="w-full bg-slate-900 text-white py-3 rounded-2xl font-bold hover:bg-rose-600 transition-all">Retry Connection</button>
        </div>
      </div>

      <iframe 
        v-if="embedUrl" 
        :src="embedUrl" 
        class="w-full h-full rounded-[32px] border-none shadow-2xl bg-white"
        allowtransparency
      ></iframe>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { ShieldAlert, ChevronDown, RefreshCw } from 'lucide-vue-next';
import api from '../../services/api';

const dashboards = [
  { id: 1, title: 'Daily Operations Dashboard', label: 'Daily Operations' },
  { id: 2, title: 'Occupancy & Inventory Analysis', label: 'Occupancy' },
  { id: 3, title: 'Financial Revenue Insights', label: 'Revenue' },
  { id: 4, title: 'Accounts Receivable Aging', label: 'AR Aging' },
  { id: 5, title: 'Real-time Trial Balance', label: 'Trial Balance' },
  { id: 6, title: 'Cashier Shift Variance', label: 'Cashier Variance' },
  { id: 7, title: 'Travel Agent Commissions', label: 'Commissions' },
  { id: 8, title: 'Chain-Wide Cross-Hotel Performance', label: 'Chain Dashboard' },
];

const selectedId = ref(1);
const embedUrl = ref('');
const loading = ref(false);
const error = ref('');

const currentDashboard = computed(() => dashboards.find(d => d.id === selectedId.value));

const fetchDashboardUrl = async () => {
  loading.value = true;
  error.value = '';
  embedUrl.value = '';
  
  try {
    const res = await api.get(`/reports/metabase/${selectedId.value}`);
    embedUrl.value = res.data.url;
  } catch (err) {
    error.value = err.response?.data?.error || 'Could not establish connection to the reporting server.';
  } finally {
    setTimeout(() => {
      loading.value = false;
    }, 800);
  }
};

watch(selectedId, fetchDashboardUrl);

onMounted(fetchDashboardUrl);
</script>
