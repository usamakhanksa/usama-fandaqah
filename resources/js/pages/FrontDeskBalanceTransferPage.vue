<template>
  <div class="p-6 space-y-6 bg-[#f8f9fa] min-h-full">
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-50">
      <h1 class="text-2xl font-bold text-[#2a273c]">Balance Transfer</h1>
      <p class="text-xs font-black text-[#e95a54] uppercase tracking-widest mt-1">Checkout Balance Transfer Options</p>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-slate-50 p-6 space-y-6">
      <!-- Balance Display -->
      <div>
        <label class="label">Reservation ID</label>
        <input v-model="form.reservation_id" type="number" class="input w-64" placeholder="Reservation ID">
      </div>

      <div v-if="form.reservation_id" class="bg-amber-50 border border-amber-100 rounded-2xl p-4">
        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Outstanding Balance</p>
        <p class="text-3xl font-bold text-[#2a273c] mt-1">{{ form.amount || '0.00' }} SAR</p>
      </div>

      <!-- Transfer Options -->
      <div>
        <label class="label">Transfer Type *</label>
        <div class="grid grid-cols-2 gap-3">
          <div v-for="opt in transferOptions" :key="opt.value"
            @click="form.transfer_type = opt.value"
            :class="['p-4 rounded-2xl border-2 cursor-pointer transition-all', form.transfer_type === opt.value ? 'border-[#e95a54] bg-red-50' : 'border-slate-100 hover:border-slate-200']">
            <p class="text-sm font-bold text-[#2a273c]">{{ opt.label }}</p>
            <p class="text-[10px] text-slate-400 font-medium mt-1">{{ opt.desc }}</p>
          </div>
        </div>
      </div>

      <!-- Amount -->
      <div><label class="label">Amount (SAR) *</label><input v-model="form.amount" type="number" class="input" placeholder="0.00"></div>

      <!-- Promissory: company selection -->
      <div v-if="form.transfer_type === 'to_promissory'">
        <label class="label">Promissory ID</label>
        <input v-model="form.promissory_id" type="number" class="input" placeholder="Promissory ID">
      </div>

      <!-- Notes -->
      <div><label class="label">Notes</label><textarea v-model="form.notes" class="input" rows="2" placeholder="Optional notes..."></textarea></div>
    </div>

    <!-- Confirmation -->
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-50 flex justify-end gap-3">
      <button @click="$router.back()" class="bg-slate-100 text-[#2a273c] px-6 py-3 rounded-xl text-xs font-black uppercase tracking-widest">Cancel</button>
      <button @click="showConfirm = true" :disabled="!form.reservation_id || !form.transfer_type || !form.amount" class="bg-[#e95a54] text-white px-6 py-3 rounded-xl text-xs font-black uppercase tracking-widest disabled:opacity-40">
        Proceed Transfer
      </button>
    </div>

    <!-- Confirm Modal -->
    <div v-if="showConfirm" class="fixed inset-0 bg-black/60 flex items-center justify-center z-50">
      <div class="bg-white rounded-3xl p-8 w-full max-w-md">
        <h3 class="text-xl font-bold text-[#2a273c] mb-4">Confirm Transfer</h3>
        <div class="space-y-2 text-sm">
          <div class="flex justify-between py-2 border-b border-slate-50"><span class="text-slate-400">Type</span><span class="font-bold text-[#2a273c]">{{ transferOptions.find(o => o.value === form.transfer_type)?.label }}</span></div>
          <div class="flex justify-between py-2 border-b border-slate-50"><span class="text-slate-400">Amount</span><span class="font-bold text-[#e95a54]">{{ form.amount }} SAR</span></div>
          <div v-if="form.notes" class="flex justify-between py-2"><span class="text-slate-400">Notes</span><span class="font-medium text-slate-600">{{ form.notes }}</span></div>
        </div>
        <div class="flex gap-3 mt-6">
          <button @click="showConfirm = false" class="flex-1 bg-slate-100 text-[#2a273c] py-3 rounded-xl text-xs font-black uppercase tracking-widest">Cancel</button>
          <button @click="submit" :disabled="processing" class="flex-1 bg-[#e95a54] text-white py-3 rounded-xl text-xs font-black uppercase tracking-widest disabled:opacity-40">
            {{ processing ? 'Processing...' : 'Confirm' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import api from '../services/api';

const router = useRouter();
const showConfirm = ref(false);
const processing = ref(false);
const form = reactive({ reservation_id: '', transfer_type: '', amount: '', promissory_id: '', notes: '' });

const transferOptions = [
  { value: 'to_promissory', label: 'Promissory Note', desc: 'Transfer to promissory with company' },
  { value: 'refunded',      label: 'Refund',          desc: 'Refund to guest payment method' },
  { value: 'waived',        label: 'Waive',           desc: 'Waive outstanding balance' },
  { value: 'to_credit_note',label: 'Credit Note',     desc: 'Issue credit note for future use' },
];

const submit = async () => {
  processing.value = true;
  try {
    await api.post('/front-desk/balance-transfer', form);
    showConfirm.value = false;
    router.back();
  } finally {
    processing.value = false;
  }
};
</script>

<style scoped>
.label { @apply block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2; }
.input { @apply w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-bold text-[#2a273c] focus:ring-2 focus:ring-[#e95a54]; }
</style>
