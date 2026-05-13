<template>
  <div class="p-6 space-y-6 bg-[#f8f9fa] min-h-full">
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-50">
      <h1 class="text-2xl font-bold text-[#2a273c]">Room Swap</h1>
      <p class="text-xs font-black text-[#e95a54] uppercase tracking-widest mt-1">Transfer Guest to Another Room</p>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-slate-50 p-6 space-y-4">
      <div><label class="label">Reservation ID</label><input v-model="form.reservation_id" type="number" class="input w-64" placeholder="Reservation ID"></div>
      <div>
        <label class="label">New Room</label>
        <div class="grid grid-cols-4 gap-2 max-h-48 overflow-y-auto">
          <div v-for="unit in units" :key="unit.id"
            @click="form.new_unit_id = unit.id"
            :class="['p-3 rounded-2xl border-2 cursor-pointer text-center transition-all', form.new_unit_id === unit.id ? 'border-[#e95a54] bg-red-50' : 'border-slate-100 hover:border-slate-200']">
            <p class="text-base font-bold text-[#2a273c]">{{ unit.unit_number }}</p>
          </div>
        </div>
      </div>
      <div><label class="label">Reason *</label><textarea v-model="form.reason" class="input" rows="2" placeholder="Reason for room swap"></textarea></div>
    </div>

    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-50 flex justify-end gap-3">
      <button @click="$router.back()" class="bg-slate-100 text-[#2a273c] px-6 py-3 rounded-xl text-xs font-black uppercase tracking-widest">Cancel</button>
      <button @click="swap" :disabled="processing || !form.reservation_id || !form.new_unit_id || !form.reason" class="bg-[#e95a54] text-white px-6 py-3 rounded-xl text-xs font-black uppercase tracking-widest disabled:opacity-40">
        {{ processing ? 'Swapping...' : 'Confirm Swap' }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue';
import api from '../services/api';

const units = ref([]);
const processing = ref(false);
const form = reactive({ reservation_id: '', new_unit_id: null, reason: '' });

const swap = async () => {
  processing.value = true;
  try {
    await api.post('/front-desk/room-swap', form);
  } finally {
    processing.value = false;
  }
};

onMounted(async () => {
  const { data } = await api.get('/front-desk/available-rooms');
  units.value = data.data || [];
});
</script>

<style scoped>
.label { @apply block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2; }
.input { @apply w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-bold text-[#2a273c] focus:ring-2 focus:ring-[#e95a54]; }
</style>
