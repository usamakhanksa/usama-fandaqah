<template>
  <div class="p-6 space-y-6 bg-[#f8f9fa] min-h-full">
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-50 flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-[#2a273c]">Room Status Board</h1>
        <p class="text-xs font-black text-[#e95a54] uppercase tracking-widest mt-1">Visual Room Status Grid</p>
      </div>
      <div class="flex gap-3">
        <input v-model="filters.floor" type="text" placeholder="Floor" class="bg-slate-50 border-none rounded-xl py-3 px-4 text-xs font-bold text-slate-600 focus:ring-2 focus:ring-[#e95a54] w-24">
      </div>
    </div>

    <!-- Legend -->
    <div class="flex gap-4 flex-wrap">
      <div v-for="s in statuses" :key="s.value" class="flex items-center gap-2">
        <div :class="s.bg" class="w-4 h-4 rounded"></div>
        <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest">{{ s.label }}</span>
      </div>
    </div>

    <!-- Grid -->
    <div class="grid grid-cols-4 md:grid-cols-6 lg:grid-cols-8 gap-3">
      <div v-for="unit in units" :key="unit.id"
        @click="changeStatus(unit)"
        :class="[getStatusBg(unit.status), 'rounded-2xl p-4 cursor-pointer transition-all hover:scale-105 hover:shadow-md']">
        <p class="text-lg font-bold text-white">{{ unit.unit_number }}</p>
        <p class="text-[10px] font-black text-white/70 uppercase tracking-widest">{{ getStatusLabel(unit.status) }}</p>
        <p class="text-[10px] text-white/60 mt-1">Fl.{{ unit.floor || '—' }}</p>
      </div>
    </div>

    <!-- Status Change Modal -->
    <div v-if="selected" class="fixed inset-0 bg-black/60 flex items-center justify-center z-50">
      <div class="bg-white rounded-3xl p-8 w-full max-w-sm space-y-4">
        <h3 class="text-xl font-bold text-[#2a273c]">Change Status — Room {{ selected.unit_number }}</h3>
        <div>
          <label class="label">New Status</label>
          <select v-model="newStatus" class="input">
            <option v-for="s in statuses" :key="s.value" :value="s.value">{{ s.label }}</option>
          </select>
        </div>
        <div><label class="label">Reason</label><input v-model="reason" class="input" placeholder="Optional reason"></div>
        <div class="flex gap-3">
          <button @click="selected = null" class="flex-1 bg-slate-100 text-[#2a273c] py-3 rounded-xl text-xs font-black uppercase tracking-widest">Cancel</button>
          <button @click="applyStatus" :disabled="processing" class="flex-1 bg-[#e95a54] text-white py-3 rounded-xl text-xs font-black uppercase tracking-widest disabled:opacity-40">Apply</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, watch, onMounted } from 'vue';
import api from '../services/api';

const units = ref([]);
const selected = ref(null);
const newStatus = ref(1);
const reason = ref('');
const processing = ref(false);
const filters = reactive({ floor: '' });

const statuses = [
  { value: 1, label: 'Available',   bg: 'bg-emerald-500' },
  { value: 2, label: 'Dirty',       bg: 'bg-amber-500' },
  { value: 3, label: 'Maintenance', bg: 'bg-red-500' },
  { value: 4, label: 'Cleaning',    bg: 'bg-orange-500' },
  { value: 5, label: 'Occupied',    bg: 'bg-blue-500' },
  { value: 6, label: 'Out of Order',bg: 'bg-slate-600' },
];

const getStatusBg = (s) => statuses.find(x => x.value === s)?.bg || 'bg-slate-400';
const getStatusLabel = (s) => statuses.find(x => x.value === s)?.label || 'Unknown';

const load = async () => {
  const { data } = await api.get('/rooms-module/status-board', { params: filters });
  units.value = data.data || [];
};

const changeStatus = (unit) => { selected.value = unit; newStatus.value = unit.status; reason.value = ''; };

const applyStatus = async () => {
  processing.value = true;
  try {
    await api.put(`/rooms-module/units/${selected.value.id}/status`, { status: newStatus.value, reason: reason.value });
    selected.value = null;
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
