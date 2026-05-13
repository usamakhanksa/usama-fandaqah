<template>
  <div class="p-6 space-y-6 bg-[#f8f9fa] min-h-full">
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-50">
      <h1 class="text-2xl font-bold text-[#2a273c]">No-Show Handling</h1>
      <p class="text-xs font-black text-[#e95a54] uppercase tracking-widest mt-1">Mark Reservation as No-Show</p>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-slate-50 p-6 space-y-4">
      <div><label class="label">Reservation ID</label><input v-model="reservationId" type="number" class="input w-64" placeholder="Reservation ID"></div>

      <div v-if="reservationId" class="bg-amber-50 border border-amber-100 rounded-2xl p-4">
        <p class="text-sm font-bold text-amber-700">⚠ This will mark the reservation as no-show.</p>
      </div>

      <div><label class="label">Reason *</label><textarea v-model="form.reason" class="input" rows="3" placeholder="Reason for no-show..."></textarea></div>

      <label class="flex items-center gap-3 cursor-pointer">
        <input v-model="form.cancel" type="checkbox" class="w-4 h-4 rounded" checked>
        <span class="text-xs font-black text-slate-600 uppercase tracking-widest">Cancel Reservation</span>
      </label>

      <div v-if="chargeAmount !== null" class="bg-slate-50 rounded-2xl p-4">
        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">No-Show Charge</p>
        <p class="text-2xl font-bold text-[#2a273c] mt-1">{{ chargeAmount }} SAR</p>
      </div>
    </div>

    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-50 flex justify-end gap-3">
      <button @click="$router.back()" class="bg-slate-100 text-[#2a273c] px-6 py-3 rounded-xl text-xs font-black uppercase tracking-widest">Cancel</button>
      <button @click="confirm" :disabled="processing || !reservationId || !form.reason" class="bg-[#e95a54] text-white px-6 py-3 rounded-xl text-xs font-black uppercase tracking-widest disabled:opacity-40">
        {{ processing ? 'Processing...' : 'Confirm No-Show' }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import api from '../services/api';

const router = useRouter();
const reservationId = ref('');
const chargeAmount = ref(null);
const processing = ref(false);
const form = reactive({ reason: '', cancel: true, charge_amount: 0 });

const confirm = async () => {
  processing.value = true;
  try {
    await api.post(`/front-desk/no-show/${reservationId.value}`, form);
    router.push('/reservations/cancellations');
  } finally {
    processing.value = false;
  }
};
</script>

<style scoped>
.label { @apply block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2; }
.input { @apply w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-bold text-[#2a273c] focus:ring-2 focus:ring-[#e95a54]; }
</style>
