<template>
  <div class="p-6 space-y-6 bg-[#f8f9fa] min-h-full flex flex-col">
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-50 flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-[#2a273c]">Unit Cleanings</h1>
        <p class="text-xs font-black text-[#e95a54] uppercase tracking-widest mt-1">Cleaning Workflow</p>
      </div>
      <div class="flex gap-3">
        <select v-model="filters.status" class="bg-slate-50 border-none rounded-xl py-3 px-4 text-xs font-bold text-slate-600 focus:ring-2 focus:ring-[#e95a54]">
          <option value="">All</option><option value="inprogress">In Progress</option><option value="completed">Completed</option>
        </select>
        <input v-model="filters.date" type="date" class="bg-slate-50 border-none rounded-xl py-3 px-4 text-xs font-bold text-slate-600 focus:ring-2 focus:ring-[#e95a54]">
      </div>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-slate-50 overflow-hidden flex-1 flex flex-col">
      <div class="overflow-x-auto flex-1">
        <table class="w-full border-collapse">
          <thead>
            <tr class="bg-slate-50/50 border-b border-slate-50">
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Room</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Status</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Started At</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Completed At</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Note</span></th>
              <th class="p-4 text-end"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Actions</span></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <tr v-for="c in cleanings" :key="c.id" class="hover:bg-slate-50/30 transition-colors group">
              <td class="p-4"><span class="text-sm font-bold text-[#2a273c]">{{ c.unit?.unit_number || c.unit_id }}</span></td>
              <td class="p-4"><span :class="c.completed_at ? 'bg-emerald-100 text-emerald-600' : 'bg-amber-100 text-amber-600'" class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest">{{ c.completed_at ? 'Completed' : 'In Progress' }}</span></td>
              <td class="p-4"><span class="text-xs text-slate-600">{{ formatDate(c.start_at) }}</span></td>
              <td class="p-4"><span class="text-xs text-slate-600">{{ formatDate(c.completed_at) }}</span></td>
              <td class="p-4"><span class="text-xs text-slate-500">{{ c.note || '—' }}</span></td>
              <td class="p-4 text-end">
                <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                  <button v-if="!c.start_at" @click="start(c)" class="bg-amber-100 text-amber-600 px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest">Start</button>
                  <button v-if="!c.completed_at" @click="complete(c)" class="bg-emerald-500 text-white px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest">Complete</button>
                </div>
              </td>
            </tr>
            <tr v-if="!cleanings.length"><td colspan="6" class="p-16 text-center text-slate-400 text-sm">No cleaning records found.</td></tr>
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

const cleanings = ref([]);
const filters = reactive({ status: '', date: '' });

const formatDate = (d) => d ? dayjs(d).format('DD MMM YYYY HH:mm') : '—';

const load = async () => {
  const { data } = await api.get('/rooms-module/unit-cleanings', { params: filters });
  cleanings.value = data.data || [];
};

const start    = async (c) => { await api.post(`/rooms-module/unit-cleanings/${c.id}/start`); load(); };
const complete = async (c) => { await api.post(`/rooms-module/unit-cleanings/${c.id}/complete`, { note: '' }); load(); };

watch(filters, load);
onMounted(load);
</script>
