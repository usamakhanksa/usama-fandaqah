<template>
  <div class="p-6 space-y-6 bg-[#f8f9fa] min-h-full flex flex-col">
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-50 flex items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-[#2a273c]">Turnaway Logs</h1>
        <p class="text-xs font-black text-[#e95a54] uppercase tracking-widest mt-1">Walk-in Turnaway Records</p>
      </div>
      <div class="flex gap-3">
        <input v-model="filters.date" type="date" class="bg-slate-50 border-none rounded-xl py-3 px-4 text-xs font-bold text-slate-600 focus:ring-2 focus:ring-[#e95a54]">
        <button @click="showForm = true; resetForm()" class="bg-[#e95a54] text-white px-4 py-3 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-opacity-90 flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
          Log Turnaway
        </button>
      </div>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-slate-50 overflow-hidden flex-1 flex flex-col">
      <div class="overflow-x-auto flex-1">
        <table class="w-full border-collapse">
          <thead>
            <tr class="bg-slate-50/50 border-b border-slate-50">
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Date</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Guest Name</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Phone</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Reason</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Room Type</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Notes</span></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <tr v-for="l in logs" :key="l.id" class="hover:bg-slate-50/30 transition-colors">
              <td class="p-4"><span class="text-sm font-bold text-[#2a273c]">{{ formatDate(l.date) }}</span></td>
              <td class="p-4"><span class="text-sm font-bold text-[#2a273c]">{{ l.guest_name }}</span></td>
              <td class="p-4"><span class="text-xs text-slate-600">{{ l.guest_phone || '—' }}</span></td>
              <td class="p-4"><span class="text-xs text-slate-600">{{ l.reason?.name || '—' }}</span></td>
              <td class="p-4"><span class="text-xs text-slate-600">{{ l.room_type_requested || '—' }}</span></td>
              <td class="p-4"><span class="text-xs text-slate-500">{{ l.notes || '—' }}</span></td>
            </tr>
            <tr v-if="!logs.length"><td colspan="6" class="p-16 text-center text-slate-400 text-sm">No turnaway logs found.</td></tr>
          </tbody>
        </table>
      </div>
    </div>

    <div v-if="showForm" class="fixed inset-0 bg-black/60 flex items-center justify-center z-50">
      <div class="bg-white rounded-3xl p-8 w-full max-w-md space-y-4">
        <h3 class="text-xl font-bold text-[#2a273c]">Log Turnaway</h3>
        <div class="grid grid-cols-2 gap-4">
          <div><label class="label">Date *</label><input v-model="form.date" type="date" class="input"></div>
          <div><label class="label">Guest Name *</label><input v-model="form.guest_name" class="input" placeholder="Guest name"></div>
          <div><label class="label">Phone</label><input v-model="form.guest_phone" class="input" placeholder="+966..."></div>
          <div><label class="label">Room Type Requested</label><input v-model="form.room_type_requested" class="input" placeholder="Room type"></div>
          <div class="col-span-2"><label class="label">Notes</label><textarea v-model="form.notes" class="input" rows="2"></textarea></div>
        </div>
        <div class="flex gap-3">
          <button @click="showForm = false" class="flex-1 bg-slate-100 text-[#2a273c] py-3 rounded-xl text-xs font-black uppercase tracking-widest">Cancel</button>
          <button @click="store" :disabled="processing" class="flex-1 bg-[#e95a54] text-white py-3 rounded-xl text-xs font-black uppercase tracking-widest disabled:opacity-40">Save</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, watch, onMounted } from 'vue';
import api from '../services/api';
import dayjs from 'dayjs';

const logs = ref([]);
const showForm = ref(false);
const processing = ref(false);
const filters = reactive({ date: '' });
const form = reactive({ date: dayjs().format('YYYY-MM-DD'), guest_name: '', guest_phone: '', room_type_requested: '', notes: '' });

const resetForm = () => Object.assign(form, { date: dayjs().format('YYYY-MM-DD'), guest_name: '', guest_phone: '', room_type_requested: '', notes: '' });
const formatDate = (d) => d ? dayjs(d).format('DD MMM YYYY') : '—';

const load = async () => {
  const { data } = await api.get('/guests-module/turnaway-logs', { params: filters });
  logs.value = data.data || [];
};

const store = async () => {
  processing.value = true;
  try {
    await api.post('/guests-module/turnaway-logs', form);
    showForm.value = false;
    load();
  } finally { processing.value = false; }
};

watch(filters, load);
onMounted(load);
</script>

<style scoped>
.label { @apply block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2; }
.input { @apply w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-bold text-[#2a273c] focus:ring-2 focus:ring-[#e95a54]; }
</style>
