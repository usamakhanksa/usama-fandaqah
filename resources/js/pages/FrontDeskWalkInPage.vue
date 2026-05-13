<template>
  <div class="p-6 space-y-6 bg-[#f8f9fa] min-h-full">
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-50">
      <h1 class="text-2xl font-bold text-[#2a273c]">Walk-in Booking</h1>
      <p class="text-xs font-black text-[#e95a54] uppercase tracking-widest mt-1">Quick Guest Registration</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Guest Form -->
      <div class="bg-white rounded-3xl shadow-sm border border-slate-50 p-6 space-y-4">
        <h2 class="text-sm font-black text-[#2a273c] uppercase tracking-widest">Guest Details</h2>
        <div><label class="label">Full Name *</label><input v-model="form.guest_name" class="input" placeholder="Guest full name"></div>
        <div><label class="label">Phone</label><input v-model="form.guest_phone" class="input" placeholder="+966..."></div>
        <div><label class="label">ID Number</label><input v-model="form.id_number" class="input" placeholder="National ID / Passport"></div>
        <div class="grid grid-cols-2 gap-3">
          <div><label class="label">Check-in *</label><input v-model="form.check_in" type="date" class="input"></div>
          <div><label class="label">Check-out *</label><input v-model="form.check_out" type="date" class="input"></div>
        </div>
        <div><label class="label">Total Price (SAR)</label><input v-model="form.total_price" type="number" class="input" placeholder="0.00"></div>
        <label class="flex items-center gap-3 cursor-pointer">
          <input v-model="form.direct_checkin" type="checkbox" class="w-4 h-4 rounded">
          <span class="text-xs font-black text-slate-600 uppercase tracking-widest">Direct Check-in</span>
        </label>
      </div>

      <!-- Available Rooms -->
      <div class="bg-white rounded-3xl shadow-sm border border-slate-50 p-6">
        <h2 class="text-sm font-black text-[#2a273c] uppercase tracking-widest mb-4">Available Rooms</h2>
        <div class="grid grid-cols-3 gap-2 max-h-80 overflow-y-auto">
          <div v-for="unit in units" :key="unit.id"
            @click="form.unit_id = unit.id"
            :class="['p-3 rounded-2xl border-2 cursor-pointer transition-all text-center', form.unit_id === unit.id ? 'border-[#e95a54] bg-red-50' : 'border-slate-100 hover:border-slate-200']">
            <p class="text-base font-bold text-[#2a273c]">{{ unit.unit_number }}</p>
            <p class="text-[10px] text-slate-400 font-black uppercase">Fl. {{ unit.floor || '—' }}</p>
          </div>
        </div>
        <p v-if="!units.length" class="text-center text-slate-400 text-sm py-8">No available rooms.</p>
      </div>
    </div>

    <!-- Actions -->
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-50 flex items-center justify-end gap-3">
      <button @click="$router.back()" class="bg-slate-100 text-[#2a273c] px-6 py-3 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-slate-200">Cancel</button>
      <button @click="submit(false)" :disabled="processing" class="bg-[#2a273c] text-white px-6 py-3 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-opacity-90 disabled:opacity-40">Save</button>
      <button @click="submit(true)" :disabled="processing" class="bg-[#e95a54] text-white px-6 py-3 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-opacity-90 disabled:opacity-40">Save & Check-in</button>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '../services/api';
import dayjs from 'dayjs';

const router = useRouter();
const units = ref([]);
const processing = ref(false);
const form = reactive({ guest_name: '', guest_phone: '', id_number: '', unit_id: null, check_in: dayjs().format('YYYY-MM-DD'), check_out: dayjs().add(1, 'day').format('YYYY-MM-DD'), total_price: '', direct_checkin: false });

const loadRooms = async () => {
  const { data } = await api.get('/front-desk/available-rooms');
  units.value = data.data || [];
};

const submit = async (directCheckin) => {
  if (!form.guest_name || !form.unit_id || !form.check_in || !form.check_out) return;
  processing.value = true;
  try {
    form.direct_checkin = directCheckin;
    const { data } = await api.post('/front-desk/walk-in', form);
    router.push(`/reservations/${data.data.id}`);
  } finally {
    processing.value = false;
  }
};

onMounted(loadRooms);
</script>

<style scoped>
.label { @apply block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2; }
.input { @apply w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-bold text-[#2a273c] focus:ring-2 focus:ring-[#e95a54]; }
</style>
