<template>
  <div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-slate-900">City Ledger (AR Aging)</h1>
        <p class="text-slate-500 text-sm">Track corporate debt maturity and credit utilization</p>
      </div>
      <div class="flex gap-3">
        <button @click="exportReport" class="bg-white border border-slate-200 text-slate-700 px-4 py-2 rounded-xl flex items-center gap-2 hover:bg-slate-50 transition-all">
          <Download class="w-5 h-5" />
          Export CSV
        </button>
      </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
      <div class="bg-white p-6 rounded-[32px] border border-slate-100 shadow-sm">
        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Total Receivable</p>
        <p class="text-2xl font-black text-slate-900">{{ formatCurrency(stats.total_receivable || 0) }}</p>
        <div class="mt-2 flex items-center gap-1 text-rose-500 text-xs font-bold">
          <AlertTriangle class="w-4 h-4" />
          <span>{{ formatCurrency(stats.aging_summary?.['90plus'] || 0) }} Overdue 90+ Days</span>
        </div>
      </div>
      <div class="bg-white p-6 rounded-[32px] border border-slate-100 shadow-sm">
        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Total Credit Limit</p>
        <p class="text-2xl font-black text-slate-900">{{ formatCurrency(stats.total_limit || 0) }}</p>
        <p class="mt-2 text-slate-400 text-xs font-medium">Consolidated across groups</p>
      </div>
      <div class="bg-white p-6 rounded-[32px] border border-slate-100 shadow-sm">
        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Avg. Utilization</p>
        <p class="text-2xl font-black text-slate-900">{{ Math.round(stats.avg_utilization || 0) }}%</p>
        <div class="mt-2 w-full bg-slate-100 h-1.5 rounded-full overflow-hidden">
          <div class="bg-rose-500 h-full transition-all duration-1000" :style="{ width: stats.avg_utilization + '%' }"></div>
        </div>
      </div>
      <div class="bg-white p-6 rounded-[32px] border border-slate-100 shadow-sm">
        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Critical Accounts</p>
        <p class="text-2xl font-black text-slate-900">{{ stats.group_stats?.filter(g => g.utilization > 90).length || 0 }}</p>
        <p class="mt-2 text-rose-500 text-xs font-bold">Above 90% Limit</p>
      </div>
    </div>

    <!-- Aging Table -->
    <div class="bg-white rounded-[32px] border border-slate-100 shadow-sm overflow-hidden">
      <div class="p-6 border-b border-slate-50 flex items-center justify-between">
        <h3 class="font-bold text-slate-900">AR Aging Details</h3>
        <div class="flex gap-4">
          <select v-model="filters.company_group_id" @change="fetchAging" class="bg-slate-50 border-none rounded-xl px-4 py-2 text-sm focus:ring-2 ring-rose-300 outline-none">
            <option :value="null">All Groups</option>
            <option v-for="group in groups" :key="group.id" :value="group.id">{{ group.name }}</option>
          </select>
        </div>
      </div>
      
      <div class="overflow-x-auto">
        <table class="w-full text-left">
          <thead>
            <tr class="bg-slate-50/50">
              <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-widest">Company / Group</th>
              <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-widest text-right">0-30 Days</th>
              <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-widest text-right">31-60 Days</th>
              <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-widest text-right">61-90 Days</th>
              <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-widest text-right">90+ Days</th>
              <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-widest text-right">Total Balance</th>
              <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-widest">Utilization</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <tr v-for="item in agingData" :key="item.company_id" class="hover:bg-slate-50/50 transition-colors">
              <td class="px-6 py-4">
                <div class="font-bold text-slate-900">{{ item.company_name }}</div>
                <div class="text-[10px] text-slate-400 uppercase font-bold tracking-tight">{{ item.group_name }}</div>
              </td>
              <td class="px-6 py-4 text-right text-sm text-slate-600">{{ formatCurrency(item.buckets['30']) }}</td>
              <td class="px-6 py-4 text-right text-sm text-slate-600">{{ formatCurrency(item.buckets['60']) }}</td>
              <td class="px-6 py-4 text-right text-sm text-slate-600">{{ formatCurrency(item.buckets['90']) }}</td>
              <td class="px-6 py-4 text-right text-sm font-bold" :class="item.buckets['90plus'] > 0 ? 'text-rose-600' : 'text-slate-600'">
                {{ formatCurrency(item.buckets['90plus']) }}
              </td>
              <td class="px-6 py-4 text-right font-black text-slate-900">{{ formatCurrency(item.balance) }}</td>
              <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                  <div class="flex-1 bg-slate-100 h-1.5 rounded-full overflow-hidden min-w-[60px]">
                    <div :class="[item.utilization > 90 ? 'bg-rose-500' : 'bg-slate-900']" class="h-full transition-all" :style="{ width: Math.min(item.utilization, 100) + '%' }"></div>
                  </div>
                  <span class="text-xs font-bold text-slate-600">{{ Math.round(item.utilization) }}%</span>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Download, AlertTriangle } from 'lucide-vue-next';
import api from '../services/api';

const stats = ref({});
const agingData = ref([]);
const groups = ref([]);
const filters = ref({
  company_group_id: null,
});

const fetchData = async () => {
  try {
    const [statsRes, agingRes, groupsRes] = await Promise.all([
      api.get('/city-ledger/dashboard'),
      api.get('/city-ledger/aging'),
      api.get('/company-groups'),
    ]);
    stats.value = statsRes.data.data;
    agingData.value = agingRes.data.data;
    groups.value = groupsRes.data.data;
  } catch (err) {
    console.error('Failed to fetch ledger data', err);
  }
};

const fetchAging = async () => {
  try {
    const { data } = await api.get('/city-ledger/aging', { params: filters.value });
    agingData.value = data.data;
  } catch (err) {
    console.error('Failed to fetch aging', err);
  }
};

const exportReport = async () => {
  try {
    const { data } = await api.get('/city-ledger/export');
    const jsonString = JSON.stringify(data, null, 2);
    const blob = new Blob([jsonString], { type: 'application/json' });
    const url = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `city_ledger_${new Date().toISOString().split('T')[0]}.json`);
    document.body.appendChild(link);
    link.click();
    link.remove();
  } catch (err) {
    console.error('Export failed', err);
  }
};

const formatCurrency = (val) => {
  return new Intl.NumberFormat('en-SA', { style: 'currency', currency: 'SAR' }).format(val);
};

onMounted(fetchData);
</script>
