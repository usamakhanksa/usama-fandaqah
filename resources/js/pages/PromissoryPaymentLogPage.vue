<template>
  <div class="p-6 space-y-6 bg-[#f8f9fa] min-h-full">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-3xl shadow-sm border border-slate-50">
      <div>
        <h1 class="text-2xl font-bold text-[#2a273c]">{{ $t('ar.promissory_payment_logs') || 'Promissory Payment Logs' }}</h1>
        <nav class="text-xs text-slate-400 mt-1 flex gap-2">
          <span>{{ $t('nav.ar_management') || 'AR Management' }}</span>
          <span>/</span>
          <span>{{ $t('ar.promissory_payment_logs') || 'Payment Logs' }}</span>
        </nav>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-50 flex flex-wrap gap-4 items-center">
      <div class="relative flex-1 min-w-[200px]">
        <input 
          v-model="filters.promissory_id" 
          class="w-full bg-slate-50 border-none rounded-xl py-3 px-10 text-sm focus:ring-2 focus:ring-[#e95a54] transition-all" 
          placeholder="Search by Promissory ID..."
        >
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
      </div>
      
      <select v-model="filters.payment_type" class="bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-medium text-slate-600 focus:ring-2 focus:ring-[#e95a54]">
        <option value="">All Payment Types</option>
        <option value="cash">Cash</option>
        <option value="bank_transfer">Bank Transfer</option>
        <option value="card">Credit Card</option>
        <option value="adjustment">Adjustment</option>
      </select>

      <select v-model="filters.is_reversed" class="bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-medium text-slate-600 focus:ring-2 focus:ring-[#e95a54]">
        <option value="">All Statuses</option>
        <option value="false">Active Payments</option>
        <option value="true">Reversed</option>
      </select>
      
      <button @click="load" class="bg-[#2a273c] text-white px-6 py-3 rounded-xl text-sm font-bold hover:bg-opacity-90 transition-all">
        Apply Filters
      </button>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-50 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="bg-slate-50 text-slate-400 font-bold uppercase text-[10px] tracking-widest border-b border-slate-100">
              <th class="px-6 py-4 text-start">Date</th>
              <th class="px-6 py-4 text-start">Promissory</th>
              <th class="px-6 py-4 text-start">Guest / Company</th>
              <th class="px-6 py-4 text-start">Amount</th>
              <th class="px-6 py-4 text-start">Method</th>
              <th class="px-6 py-4 text-start">Applied By</th>
              <th class="px-6 py-4 text-end">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <tr v-for="log in logs" :key="log.id" :class="['hover:bg-slate-50/50 transition-colors group', log.is_reversed ? 'opacity-50 grayscale' : '']">
              <td class="px-6 py-5">
                <div class="flex flex-col">
                  <span class="font-bold text-[#2a273c]">{{ formatDate(log.applied_at) }}</span>
                  <span class="text-[10px] text-slate-400 font-medium">{{ formatTime(log.applied_at) }}</span>
                </div>
              </td>
              <td class="px-6 py-5">
                <span class="bg-slate-100 text-[#2a273c] px-2 py-1 rounded text-[10px] font-bold">#PR-{{ log.promissory_id }}</span>
              </td>
              <td class="px-6 py-5">
                <div class="flex flex-col">
                  <span class="font-bold text-[#2a273c]">{{ log.promissory?.reservation?.guest?.name || 'Walk-in' }}</span>
                  <span class="text-xs text-slate-400">{{ log.promissory?.company?.name || 'Individual' }}</span>
                </div>
              </td>
              <td class="px-6 py-5">
                <span :class="['font-black', log.is_reversed ? 'text-slate-400 line-through' : 'text-emerald-600']">
                  {{ log.amount_applied }} SAR
                </span>
              </td>
              <td class="px-6 py-5">
                <span class="capitalize text-slate-600 font-medium">{{ log.payment_type.replace('_', ' ') }}</span>
              </td>
              <td class="px-6 py-5">
                <span class="text-slate-500">{{ log.applied_by?.name || 'System' }}</span>
              </td>
              <td class="px-6 py-5 text-end">
                <button 
                  v-if="!log.is_reversed"
                  @click="confirmReverse(log)" 
                  class="bg-rose-50 text-rose-600 p-2 rounded-lg opacity-0 group-hover:opacity-100 transition-all hover:bg-rose-600 hover:text-white"
                  title="Reverse Payment"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                </button>
                <span v-else class="text-[10px] font-black text-rose-500 uppercase tracking-widest">Reversed</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <!-- Pagination -->
      <div v-if="meta.last_page > 1" class="p-6 border-t border-slate-50 flex justify-center">
        <button 
          v-for="p in meta.last_page" 
          :key="p"
          @click="page = p; load()"
          :class="['w-8 h-8 rounded-lg mx-1 text-xs font-bold transition-all', page === p ? 'bg-[#e95a54] text-white shadow-lg shadow-rose-100' : 'bg-slate-50 text-slate-400 hover:bg-slate-100']"
        >
          {{ p }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref, watch } from 'vue';
import api from '../services/api';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const logs = ref([]);
const meta = ref({});
const page = ref(1);
const filters = reactive({
  promissory_id: '',
  payment_type: '',
  is_reversed: 'false',
});

const load = async () => {
  const { data } = await api.get('/promissories/payment-logs', { 
    params: { 
      page: page.value,
      ...filters
    } 
  });
  logs.value = data.data;
  meta.value = data;
};

const confirmReverse = async (log) => {
  if (!confirm('Are you sure you want to reverse this payment? This will decrease the collected amount on the promissory.')) return;
  
  try {
    await api.post(`/promissories/payment-logs/${log.id}/reverse`);
    load();
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to reverse payment');
  }
};

const formatDate = (date) => new Date(date).toLocaleDateString();
const formatTime = (date) => new Date(date).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

onMounted(load);
</script>
