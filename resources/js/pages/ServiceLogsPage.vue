<template>
  <div class="p-6 space-y-6 bg-[#f8f9fa] min-h-full flex flex-col">
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-50 flex items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-[#2a273c]">Service Logs</h1>
        <p class="text-xs font-black text-[#e95a54] uppercase tracking-widest mt-1">Service Transaction History</p>
      </div>
      <div class="flex gap-3">
        <input v-model="filters.date" type="date" class="bg-slate-50 border-none rounded-xl py-3 px-4 text-xs font-bold text-slate-600 focus:ring-2 focus:ring-[#e95a54]">
        <input v-model="filters.type" type="text" placeholder="Type..." class="bg-slate-50 border-none rounded-xl py-3 px-4 text-xs font-bold text-slate-600 focus:ring-2 focus:ring-[#e95a54] w-32">
      </div>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-slate-50 overflow-hidden flex-1 flex flex-col">
      <div class="overflow-x-auto flex-1">
        <table class="w-full border-collapse">
          <thead>
            <tr class="bg-slate-50/50 border-b border-slate-50">
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Log #</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Type</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Amount</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Date</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Status</span></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <tr v-for="log in logs" :key="log.id" class="hover:bg-slate-50/30 transition-colors">
              <td class="p-4"><span class="text-sm font-bold text-[#e95a54]">{{ log.number || '#' + log.id }}</span></td>
              <td class="p-4"><span class="text-xs text-slate-600 capitalize">{{ log.type || '—' }}</span></td>
              <td class="p-4"><span class="text-xs font-bold text-[#2a273c]">{{ ((log.amount || 0) / 100).toFixed(2) }} SAR</span></td>
              <td class="p-4"><span class="text-xs text-slate-600">{{ formatDate(log.created_at) }}</span></td>
              <td class="p-4"><span :class="log.is_freezed ? 'bg-red-100 text-red-600' : 'bg-emerald-100 text-emerald-600'" class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest">{{ log.is_freezed ? 'Voided' : 'Active' }}</span></td>
            </tr>
            <tr v-if="!logs.length"><td colspan="5" class="p-16 text-center text-slate-400 text-sm">No service logs found.</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, watch, onMounted } from 'vue';
import api from '../services/api';
import dayjs from 'dayjs';

const logs = ref([]);
const filters = reactive({ date: '', type: '' });
const formatDate = (d) => d ? dayjs(d).format('DD MMM YYYY HH:mm') : '—';

const load = async () => {
  const { data } = await api.get('/pos-module/service-logs', { params: filters });
  logs.value = data.data || [];
};

watch(filters, load);
onMounted(load);
</script>
