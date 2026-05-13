<template>
  <div class="p-6 space-y-6 bg-[#f8f9fa] min-h-full flex flex-col">
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-50 flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-[#2a273c]">Room Status Log</h1>
        <p class="text-xs font-black text-[#e95a54] uppercase tracking-widest mt-1">Status Change History</p>
      </div>
      <div class="flex gap-3">
        <input v-model="filters.unit_id" type="number" placeholder="Room ID" class="bg-slate-50 border-none rounded-xl py-3 px-4 text-xs font-bold text-slate-600 focus:ring-2 focus:ring-[#e95a54] w-28">
        <input v-model="filters.date_from" type="date" class="bg-slate-50 border-none rounded-xl py-3 px-4 text-xs font-bold text-slate-600 focus:ring-2 focus:ring-[#e95a54]">
        <input v-model="filters.date_to" type="date" class="bg-slate-50 border-none rounded-xl py-3 px-4 text-xs font-bold text-slate-600 focus:ring-2 focus:ring-[#e95a54]">
      </div>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-slate-50 overflow-hidden flex-1 flex flex-col">
      <div class="overflow-x-auto flex-1">
        <table class="w-full border-collapse">
          <thead>
            <tr class="bg-slate-50/50 border-b border-slate-50">
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Room</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">From</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">To</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Changed By</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Reason</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Date / Time</span></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <tr v-for="log in logs" :key="log.id" class="hover:bg-slate-50/30 transition-colors">
              <td class="p-4"><span class="text-sm font-bold text-[#2a273c]">{{ log.unit?.unit_number || log.unit_id }}</span></td>
              <td class="p-4"><span class="text-xs text-slate-500">{{ log.from_status }}</span></td>
              <td class="p-4"><span class="text-xs font-bold text-[#2a273c]">{{ log.to_status }}</span></td>
              <td class="p-4"><span class="text-xs text-slate-600">{{ log.user?.name || '—' }}</span></td>
              <td class="p-4"><span class="text-xs text-slate-500">{{ log.change_reason || '—' }}</span></td>
              <td class="p-4"><span class="text-xs text-slate-600">{{ formatDate(log.changed_at) }}</span></td>
            </tr>
            <tr v-if="!logs.length"><td colspan="6" class="p-16 text-center text-slate-400 text-sm">No status logs found.</td></tr>
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
const filters = reactive({ unit_id: '', date_from: '', date_to: '' });

const formatDate = (d) => d ? dayjs(d).format('DD MMM YYYY HH:mm') : '—';

const load = async () => {
  const { data } = await api.get('/rooms-module/room-status-log', { params: filters });
  logs.value = data.data || [];
};

watch(filters, load);
onMounted(load);
</script>
