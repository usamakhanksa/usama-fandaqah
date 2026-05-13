<template>
  <div class="p-6 space-y-6 bg-[#f8f9fa] min-h-full">
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-50 flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-[#2a273c]">Availability Board</h1>
        <p class="text-xs font-black text-[#e95a54] uppercase tracking-widest mt-1">Visual Room Availability Grid</p>
      </div>
      <div class="flex gap-3">
        <input v-model="start" type="date" class="bg-slate-50 border-none rounded-xl py-3 px-4 text-xs font-bold text-slate-600 focus:ring-2 focus:ring-[#e95a54]">
        <input v-model="end" type="date" class="bg-slate-50 border-none rounded-xl py-3 px-4 text-xs font-bold text-slate-600 focus:ring-2 focus:ring-[#e95a54]">
        <button @click="load" class="bg-[#2a273c] text-white px-4 py-3 rounded-xl text-xs font-black uppercase tracking-widest">Apply</button>
      </div>
    </div>

    <!-- Legend -->
    <div class="flex gap-4 flex-wrap">
      <div v-for="l in legend" :key="l.label" class="flex items-center gap-2">
        <div :class="l.color" class="w-4 h-4 rounded"></div>
        <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest">{{ l.label }}</span>
      </div>
    </div>

    <!-- Grid -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-50 overflow-auto">
      <div v-if="board.units?.length" class="min-w-max">
        <!-- Header: dates -->
        <div class="flex border-b border-slate-100">
          <div class="w-32 shrink-0 p-3 text-[10px] font-black text-slate-400 uppercase">Room</div>
          <div v-for="d in dates" :key="d" class="w-16 shrink-0 p-3 text-center text-[10px] font-black text-slate-400 uppercase">{{ formatShort(d) }}</div>
        </div>
        <!-- Rows: units -->
        <div v-for="unit in board.units" :key="unit.id" class="flex border-b border-slate-50 hover:bg-slate-50/30">
          <div class="w-32 shrink-0 p-3">
            <p class="text-xs font-bold text-[#2a273c]">{{ unit.unit_number }}</p>
            <p class="text-[10px] text-slate-400">Fl.{{ unit.floor || '—' }}</p>
          </div>
          <div v-for="d in dates" :key="d" class="w-16 shrink-0 p-1">
            <div :class="getCellClass(unit.id, d)" class="h-10 rounded-lg flex items-center justify-center text-[9px] font-black uppercase tracking-widest cursor-pointer" :title="getCellTitle(unit.id, d)">
              {{ getCellLabel(unit.id, d) }}
            </div>
          </div>
        </div>
      </div>
      <div v-else class="p-16 text-center text-slate-400 text-sm">Loading availability data...</div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import api from '../services/api';
import dayjs from 'dayjs';

const start = ref(dayjs().format('YYYY-MM-DD'));
const end   = ref(dayjs().addDays ? dayjs().add(13, 'day').format('YYYY-MM-DD') : dayjs().format('YYYY-MM-DD'));
const board = ref({});

const legend = [
  { label: 'Available', color: 'bg-emerald-400' },
  { label: 'Occupied',  color: 'bg-red-400' },
  { label: 'Maintenance', color: 'bg-slate-400' },
];

const dates = computed(() => {
  const result = [];
  let d = dayjs(start.value);
  const e = dayjs(end.value);
  while (d.isBefore(e) || d.isSame(e, 'day')) {
    result.push(d.format('YYYY-MM-DD'));
    d = d.add(1, 'day');
  }
  return result;
});

const formatShort = (d) => dayjs(d).format('DD/MM');

const getCellClass = (unitId, date) => {
  const res = (board.value.reservations || []).find(r => r.unit_id === unitId && date >= r.check_in && date < r.check_out);
  if (res) return 'bg-red-100 text-red-600';
  const unit = (board.value.units || []).find(u => u.id === unitId);
  if (unit?.status === 3) return 'bg-slate-200 text-slate-500';
  return 'bg-emerald-100 text-emerald-600';
};

const getCellLabel = (unitId, date) => {
  const res = (board.value.reservations || []).find(r => r.unit_id === unitId && date >= r.check_in && date < r.check_out);
  return res ? 'OCC' : '';
};

const getCellTitle = (unitId, date) => {
  const res = (board.value.reservations || []).find(r => r.unit_id === unitId && date >= r.check_in && date < r.check_out);
  return res ? `#${res.code}` : 'Available';
};

const load = async () => {
  const { data } = await api.get('/rooms-module/availability-board', { params: { start: start.value, end: end.value } });
  board.value = data.data || {};
};

onMounted(load);
</script>
