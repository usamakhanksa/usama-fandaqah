<template>
  <div class="p-6 space-y-6 bg-[#f8f9fa] min-h-full">
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-50">
      <h1 class="text-2xl font-bold text-[#2a273c]">{{ isEarly ? 'Early Check-in' : 'Late Checkout' }}</h1>
      <p class="text-xs font-black text-[#e95a54] uppercase tracking-widest mt-1">{{ isEarly ? 'Early Check-in Charge Calculation' : 'Late Checkout Charge Calculation' }}</p>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-slate-50 p-6 space-y-4">
      <div class="grid grid-cols-2 gap-4">
        <div><label class="label">Reservation ID</label><input v-model="form.reservation_id" type="number" class="input" placeholder="Reservation ID"></div>
        <div><label class="label">Actual Time</label><input v-model="form.actual_time" type="time" class="input"></div>
      </div>

      <button @click="calculateCharge" :disabled="!form.reservation_id || !form.actual_time" class="bg-slate-100 text-[#2a273c] px-4 py-2 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-slate-200 disabled:opacity-40">
        Calculate Charge
      </button>

      <!-- Charge Display -->
      <div v-if="chargeAmount !== null" class="bg-amber-50 border border-amber-100 rounded-2xl p-6">
        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Calculated Charge</p>
        <p class="text-3xl font-bold text-[#2a273c] mt-1">{{ chargeAmount }} SAR</p>
      </div>

      <!-- Override Option -->
      <div v-if="chargeAmount !== null">
        <label class="flex items-center gap-3 cursor-pointer">
          <input v-model="form.override" type="checkbox" class="w-4 h-4 rounded">
          <span class="text-xs font-black text-slate-600 uppercase tracking-widest">Override Charge</span>
        </label>
        <div v-if="form.override" class="mt-3 space-y-3">
          <div><label class="label">Override Amount (SAR)</label><input v-model="form.charge_amount" type="number" class="input"></div>
          <div><label class="label">Override Reason *</label><textarea v-model="form.override_reason" class="input" rows="2" placeholder="Mandatory reason for override"></textarea></div>
        </div>
      </div>
    </div>

    <div v-if="chargeAmount !== null" class="bg-white p-6 rounded-3xl shadow-sm border border-slate-50 flex justify-end gap-3">
      <button @click="$router.back()" class="bg-slate-100 text-[#2a273c] px-6 py-3 rounded-xl text-xs font-black uppercase tracking-widest">Cancel</button>
      <button @click="apply" :disabled="processing" class="bg-[#e95a54] text-white px-6 py-3 rounded-xl text-xs font-black uppercase tracking-widest disabled:opacity-40">
        {{ processing ? 'Applying...' : 'Accept Charge' }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, computed } from 'vue';
import { useRoute } from 'vue-router';
import api from '../services/api';

const route = useRoute();
const isEarly = computed(() => route.path.includes('early'));
const chargeAmount = ref(null);
const processing = ref(false);
const form = reactive({ reservation_id: '', actual_time: '', override: false, override_reason: '', charge_amount: '' });

const endpoint = computed(() => isEarly.value ? '/front-desk/early-check-in' : '/front-desk/late-checkout');

const calculateCharge = async () => {
  const { data } = await api.get(`${endpoint.value}/charge`, { params: { reservation_id: form.reservation_id, actual_time: form.actual_time } });
  chargeAmount.value = data.data.charge_amount;
  form.charge_amount = chargeAmount.value;
};

const apply = async () => {
  processing.value = true;
  try {
    await api.post(endpoint.value, { ...form, charge_amount: form.override ? form.charge_amount : chargeAmount.value });
  } finally {
    processing.value = false;
  }
};
</script>

<style scoped>
.label { @apply block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2; }
.input { @apply w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-bold text-[#2a273c] focus:ring-2 focus:ring-[#e95a54]; }
</style>
