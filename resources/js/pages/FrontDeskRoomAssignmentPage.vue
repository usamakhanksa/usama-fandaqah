<template>
  <div class="p-6 space-y-6 bg-[#f8f9fa] min-h-full">
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-50">
      <h1 class="text-2xl font-bold text-[#2a273c]">Room Assignment</h1>
      <p class="text-xs font-black text-[#e95a54] uppercase tracking-widest mt-1">Assign Rooms to Unassigned Reservations</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Unassigned Reservations -->
      <div class="bg-white rounded-3xl shadow-sm border border-slate-50 p-6">
        <h2 class="text-sm font-black text-[#2a273c] uppercase tracking-widest mb-4">Unassigned Reservations</h2>
        <div class="space-y-2 max-h-96 overflow-y-auto">
          <div v-for="res in reservations" :key="res.id"
            @click="selectedRes = res"
            :class="['p-4 rounded-2xl border-2 cursor-pointer transition-all', selectedRes?.id === res.id ? 'border-[#e95a54] bg-red-50' : 'border-slate-100 hover:border-slate-200']">
            <div class="flex justify-between items-center">
              <span class="text-sm font-bold text-[#e95a54]">#{{ res.code }}</span>
              <span class="text-xs text-slate-500">{{ formatDate(res.check_in) }} → {{ formatDate(res.check_out) }}</span>
            </div>
          </div>
          <p v-if="!reservations.length" class="text-center text-slate-400 text-sm py-8">All reservations are assigned.</p>
        </div>
      </div>

      <!-- Available Rooms Grid -->
      <div class="bg-white rounded-3xl shadow-sm border border-slate-50 p-6">
        <h2 class="text-sm font-black text-[#2a273c] uppercase tracking-widest mb-4">Available Rooms</h2>
        <div class="grid grid-cols-4 gap-2 max-h-96 overflow-y-auto">
          <div v-for="unit in units" :key="unit.id"
            @click="selectedUnit = unit"
            :class="['p-3 rounded-2xl border-2 cursor-pointer transition-all text-center', selectedUnit?.id === unit.id ? 'border-[#e95a54] bg-red-50' : 'border-slate-100 hover:border-slate-200']">
            <p class="text-base font-bold text-[#2a273c]">{{ unit.unit_number }}</p>
            <p class="text-[10px] text-slate-400 font-black uppercase">Fl.{{ unit.floor || '—' }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Confirmation -->
    <div v-if="selectedRes && selectedUnit" class="bg-white p-6 rounded-3xl shadow-sm border border-slate-50">
      <p class="text-sm font-bold text-[#2a273c]">Assign Room <span class="text-[#e95a54]">{{ selectedUnit.unit_number }}</span> to Reservation <span class="text-[#e95a54]">#{{ selectedRes.code }}</span></p>
      <div class="flex gap-3 mt-4">
        <button @click="assign" :disabled="processing" class="bg-[#e95a54] text-white px-6 py-3 rounded-xl text-xs font-black uppercase tracking-widest disabled:opacity-40">
          {{ processing ? 'Assigning...' : 'Confirm Assignment' }}
        </button>
        <button @click="selectedRes = null; selectedUnit = null" class="bg-slate-100 text-[#2a273c] px-6 py-3 rounded-xl text-xs font-black uppercase tracking-widest">Clear</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../services/api';
import dayjs from 'dayjs';

const reservations = ref([]);
const units = ref([]);
const selectedRes = ref(null);
const selectedUnit = ref(null);
const processing = ref(false);

const formatDate = (d) => d ? dayjs(d).format('DD MMM') : '—';

const load = async () => {
  const [r, u] = await Promise.all([
    api.get('/front-desk/unassigned-reservations'),
    api.get('/front-desk/available-rooms'),
  ]);
  reservations.value = r.data.data || [];
  units.value = u.data.data || [];
};

const assign = async () => {
  processing.value = true;
  try {
    await api.post('/front-desk/room-assignment', { reservation_id: selectedRes.value.id, unit_id: selectedUnit.value.id });
    reservations.value = reservations.value.filter(r => r.id !== selectedRes.value.id);
    selectedRes.value = null;
    selectedUnit.value = null;
  } finally {
    processing.value = false;
  }
};

onMounted(load);
</script>
