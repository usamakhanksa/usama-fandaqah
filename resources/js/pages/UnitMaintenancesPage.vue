<template>
  <div class="p-6 space-y-6 bg-[#f8f9fa] min-h-full flex flex-col">
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-50 flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-[#2a273c]">Maintenance Requests</h1>
        <p class="text-xs font-black text-[#e95a54] uppercase tracking-widest mt-1">Unit Maintenance Workflow</p>
      </div>
      <div class="flex gap-3">
        <select v-model="filters.status" class="bg-slate-50 border-none rounded-xl py-3 px-4 text-xs font-bold text-slate-600 focus:ring-2 focus:ring-[#e95a54]">
          <option value="">All</option><option value="inprogress">In Progress</option><option value="completed">Completed</option>
        </select>
        <input v-model="filters.date" type="date" class="bg-slate-50 border-none rounded-xl py-3 px-4 text-xs font-bold text-slate-600 focus:ring-2 focus:ring-[#e95a54]">
        <button @click="showForm = true" class="bg-[#e95a54] text-white px-4 py-3 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-opacity-90 flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
          New Request
        </button>
      </div>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-slate-50 overflow-hidden flex-1 flex flex-col">
      <div class="overflow-x-auto flex-1">
        <table class="w-full border-collapse">
          <thead>
            <tr class="bg-slate-50/50 border-b border-slate-50">
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Room</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Status</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Note</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Expected</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Completed</span></th>
              <th class="p-4 text-end"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Actions</span></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <tr v-for="m in maintenances" :key="m.id" class="hover:bg-slate-50/30 transition-colors group">
              <td class="p-4"><span class="text-sm font-bold text-[#2a273c]">{{ m.unit?.unit_number || m.unit_id }}</span></td>
              <td class="p-4"><span :class="m.completed_at ? 'bg-emerald-100 text-emerald-600' : 'bg-red-100 text-red-600'" class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest">{{ m.completed_at ? 'Completed' : 'Open' }}</span></td>
              <td class="p-4"><span class="text-xs text-slate-500">{{ m.note || '—' }}</span></td>
              <td class="p-4"><span class="text-xs text-slate-600">{{ formatDate(m.expected_at) }}</span></td>
              <td class="p-4"><span class="text-xs text-slate-600">{{ formatDate(m.completed_at) }}</span></td>
              <td class="p-4 text-end">
                <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                  <button v-if="!m.completed_at" @click="complete(m)" class="bg-emerald-500 text-white px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest">Complete</button>
                </div>
              </td>
            </tr>
            <tr v-if="!maintenances.length"><td colspan="6" class="p-16 text-center text-slate-400 text-sm">No maintenance requests found.</td></tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- New Request Modal -->
    <div v-if="showForm" class="fixed inset-0 bg-black/60 flex items-center justify-center z-50">
      <div class="bg-white rounded-3xl p-8 w-full max-w-md space-y-4">
        <h3 class="text-xl font-bold text-[#2a273c]">New Maintenance Request</h3>
        <div><label class="label">Unit ID *</label><input v-model="form.unit_id" type="number" class="input" placeholder="Unit ID"></div>
        <div><label class="label">Note</label><textarea v-model="form.note" class="input" rows="3" placeholder="Describe the issue..."></textarea></div>
        <div><label class="label">Expected Completion</label><input v-model="form.expected_at" type="datetime-local" class="input"></div>
        <div class="flex gap-3">
          <button @click="showForm = false" class="flex-1 bg-slate-100 text-[#2a273c] py-3 rounded-xl text-xs font-black uppercase tracking-widest">Cancel</button>
          <button @click="store" :disabled="processing" class="flex-1 bg-[#e95a54] text-white py-3 rounded-xl text-xs font-black uppercase tracking-widest disabled:opacity-40">Submit</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, watch, onMounted } from 'vue';
import api from '../services/api';
import dayjs from 'dayjs';

const maintenances = ref([]);
const showForm = ref(false);
const processing = ref(false);
const filters = reactive({ status: '', date: '' });
const form = reactive({ unit_id: '', note: '', expected_at: '' });

const formatDate = (d) => d ? dayjs(d).format('DD MMM YYYY') : '—';

const load = async () => {
  const { data } = await api.get('/rooms-module/unit-maintenances', { params: filters });
  maintenances.value = data.data || [];
};

const store = async () => {
  processing.value = true;
  try {
    await api.post('/rooms-module/unit-maintenances', form);
    showForm.value = false;
    load();
  } finally { processing.value = false; }
};

const complete = async (m) => {
  await api.post(`/rooms-module/unit-maintenances/${m.id}/complete`, { note: m.note });
  load();
};

watch(filters, load);
onMounted(load);
</script>

<style scoped>
.label { @apply block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2; }
.input { @apply w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-bold text-[#2a273c] focus:ring-2 focus:ring-[#e95a54]; }
</style>
