<template>
  <div class="p-6 space-y-6 bg-[#f8f9fa] min-h-full flex flex-col">
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-50 flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-[#2a273c]">IPTV Guest Needs</h1>
        <p class="text-xs font-black text-[#e95a54] uppercase tracking-widest mt-1">Guest Requests from IPTV System</p>
      </div>
      <div class="flex flex-wrap gap-3">
        <select v-model="filters.status" class="bg-slate-50 border-none rounded-xl py-3 px-4 text-xs font-bold text-slate-600 focus:ring-2 focus:ring-[#e95a54]">
          <option value="">All Status</option>
          <option value="pending">Pending</option>
          <option value="in_progress">In Progress</option>
          <option value="completed">Completed</option>
          <option value="cancelled">Cancelled</option>
        </select>
        <input type="date" v-model="filters.date" class="bg-slate-50 border-none rounded-xl py-3 px-4 text-xs font-bold text-slate-600 focus:ring-2 focus:ring-[#e95a54]">
        <div class="relative">
          <input type="text" v-model="filters.type" class="bg-slate-50 border-none rounded-xl py-3 px-4 text-xs font-bold text-slate-600 focus:ring-2 focus:ring-[#e95a54] pl-10" placeholder="Filter by type...">
          <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
        </div>
      </div>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-slate-50 overflow-hidden flex-1 flex flex-col">
      <div class="overflow-x-auto flex-1">
        <table class="w-full border-collapse">
          <thead>
            <tr class="bg-slate-50/50 border-b border-slate-50">
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Time</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Reservation</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Request Type</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Details</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Status</span></th>
              <th class="p-4 text-end"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Actions</span></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <tr v-for="need in needs" :key="need.id" class="hover:bg-slate-50/30 transition-colors group">
              <td class="p-4"><span class="text-sm font-bold text-[#2a273c]">{{ formatTime(need.created_at) }}</span><span class="text-[10px] text-slate-400 block">{{ formatDate(need.created_at) }}</span></td>
              <td class="p-4"><span class="text-sm font-bold text-[#e95a54]">#{{ need.reservation_id }}</span></td>
              <td class="p-4"><span class="text-sm font-bold text-[#2a273c]">{{ need.request_type }}</span></td>
              <td class="p-4"><span class="text-xs text-slate-600 font-medium">{{ need.request_details || '—' }}</span></td>
              <td class="p-4">
                <span :class="statusClass(need.status)" class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest">{{ need.status }}</span>
              </td>
              <td class="p-4 text-end">
                <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                  <select @change="updateStatus(need, $event.target.value)" class="bg-slate-100 border-none rounded-lg py-1.5 px-2 text-[10px] font-black text-slate-600">
                    <option value="">Update Status</option>
                    <option value="in_progress">In Progress</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                  </select>
                  <button @click="remove(need)" class="bg-red-100 text-red-600 px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest">Delete</button>
                </div>
              </td>
            </tr>
            <tr v-if="!needs.length"><td colspan="6" class="p-16 text-center text-slate-400 text-sm">No IPTV requests found.</td></tr>
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

const needs = ref([]);
const filters = reactive({ date: '', type: '', status: '' });

const statusClass = (s) => ({ pending: 'bg-amber-100 text-amber-600', in_progress: 'bg-blue-100 text-blue-600', completed: 'bg-emerald-100 text-emerald-600', cancelled: 'bg-slate-100 text-slate-500' }[s] || 'bg-slate-100 text-slate-600');
const formatDate = (d) => dayjs(d).format('DD MMM YYYY');
const formatTime = (d) => dayjs(d).format('hh:mm A');

const load = async () => {
  const { data } = await api.get('/front-desk/iptv-needs', { params: filters });
  needs.value = data.data || [];
};

const updateStatus = async (need, status) => {
  if (!status) return;
  await api.put(`/front-desk/iptv-needs/${need.id}`, { status });
  load();
};

const remove = async (need) => {
  await api.delete(`/front-desk/iptv-needs/${need.id}`);
  load();
};

watch(filters, load);
onMounted(load);
</script>
