<template>
  <div class="p-6 space-y-6 bg-[#f8f9fa] min-h-full flex flex-col">
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-50 flex items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-[#2a273c]">POS Transactions</h1>
        <p class="text-xs font-black text-[#e95a54] uppercase tracking-widest mt-1">Transaction History</p>
      </div>
      <input v-model="filters.date" type="date" class="bg-slate-50 border-none rounded-xl py-3 px-4 text-xs font-bold text-slate-600 focus:ring-2 focus:ring-[#e95a54]">
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-slate-50 overflow-hidden flex-1 flex flex-col">
      <div class="overflow-x-auto flex-1">
        <table class="w-full border-collapse">
          <thead>
            <tr class="bg-slate-50/50 border-b border-slate-50">
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Transaction #</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Type</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Amount</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Date</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Status</span></th>
              <th class="p-4 text-end"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Actions</span></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <tr v-for="tx in transactions" :key="tx.id" class="hover:bg-slate-50/30 transition-colors group">
              <td class="p-4"><span class="text-sm font-bold text-[#e95a54]">{{ tx.number || '#' + tx.id }}</span></td>
              <td class="p-4"><span class="text-xs text-slate-600 capitalize">{{ tx.type || '—' }}</span></td>
              <td class="p-4"><span class="text-xs font-bold text-[#2a273c]">{{ ((tx.amount || 0) / 100).toFixed(2) }} SAR</span></td>
              <td class="p-4"><span class="text-xs text-slate-600">{{ formatDate(tx.created_at) }}</span></td>
              <td class="p-4"><span :class="tx.is_freezed ? 'bg-red-100 text-red-600' : 'bg-emerald-100 text-emerald-600'" class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest">{{ tx.is_freezed ? 'Voided' : 'Active' }}</span></td>
              <td class="p-4 text-end">
                <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                  <button v-if="!tx.is_freezed" @click="openVoid(tx)" class="bg-red-100 text-red-600 px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest">Void</button>
                  <button v-if="!tx.is_freezed" @click="openRefund(tx)" class="bg-amber-100 text-amber-600 px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest">Refund</button>
                </div>
              </td>
            </tr>
            <tr v-if="!transactions.length"><td colspan="6" class="p-16 text-center text-slate-400 text-sm">No transactions found.</td></tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Void Modal -->
    <div v-if="voidTarget" class="fixed inset-0 bg-black/60 flex items-center justify-center z-50">
      <div class="bg-white rounded-3xl p-8 w-full max-w-md space-y-4">
        <h3 class="text-xl font-bold text-[#2a273c]">Void Transaction</h3>
        <p class="text-sm text-slate-600">Transaction: <strong>{{ voidTarget.number }}</strong></p>
        <div><label class="label">Reason *</label><textarea v-model="voidReason" class="input" rows="2" placeholder="Reason for voiding..."></textarea></div>
        <div class="flex gap-3">
          <button @click="voidTarget = null" class="flex-1 bg-slate-100 text-[#2a273c] py-3 rounded-xl text-xs font-black uppercase tracking-widest">Cancel</button>
          <button @click="confirmVoid" :disabled="!voidReason || processing" class="flex-1 bg-red-500 text-white py-3 rounded-xl text-xs font-black uppercase tracking-widest disabled:opacity-40">Void</button>
        </div>
      </div>
    </div>

    <!-- Refund Modal -->
    <div v-if="refundTarget" class="fixed inset-0 bg-black/60 flex items-center justify-center z-50">
      <div class="bg-white rounded-3xl p-8 w-full max-w-md space-y-4">
        <h3 class="text-xl font-bold text-[#2a273c]">Refund Transaction</h3>
        <div><label class="label">Amount (SAR) *</label><input v-model="refundForm.amount" type="number" class="input"></div>
        <div><label class="label">Reason *</label><textarea v-model="refundForm.reason" class="input" rows="2"></textarea></div>
        <div class="flex gap-3">
          <button @click="refundTarget = null" class="flex-1 bg-slate-100 text-[#2a273c] py-3 rounded-xl text-xs font-black uppercase tracking-widest">Cancel</button>
          <button @click="confirmRefund" :disabled="!refundForm.amount || !refundForm.reason || processing" class="flex-1 bg-amber-500 text-white py-3 rounded-xl text-xs font-black uppercase tracking-widest disabled:opacity-40">Refund</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, watch, onMounted } from 'vue';
import api from '../services/api';
import dayjs from 'dayjs';

const transactions = ref([]);
const filters = reactive({ date: '' });
const voidTarget = ref(null);
const voidReason = ref('');
const refundTarget = ref(null);
const refundForm = reactive({ amount: '', reason: '' });
const processing = ref(false);

const formatDate = (d) => d ? dayjs(d).format('DD MMM YYYY HH:mm') : '—';

const load = async () => {
  const { data } = await api.get('/pos-module/pos-transactions', { params: filters });
  transactions.value = data.data || [];
};

const openVoid = (tx) => { voidTarget.value = tx; voidReason.value = ''; };
const openRefund = (tx) => { refundTarget.value = tx; Object.assign(refundForm, { amount: ((tx.amount || 0) / 100).toFixed(2), reason: '' }); };

const confirmVoid = async () => {
  processing.value = true;
  try {
    await api.post(`/pos-module/pos-transactions/${voidTarget.value.id}/void`, { reason: voidReason.value });
    voidTarget.value = null;
    load();
  } finally { processing.value = false; }
};

const confirmRefund = async () => {
  processing.value = true;
  try {
    await api.post(`/pos-module/pos-transactions/${refundTarget.value.id}/refund`, refundForm);
    refundTarget.value = null;
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
