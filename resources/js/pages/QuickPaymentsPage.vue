<template>
  <div class="p-6 space-y-6 bg-[#f8f9fa] min-h-full flex flex-col">
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-50 flex items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-[#2a273c]">Quick Payments</h1>
        <p class="text-xs font-black text-[#e95a54] uppercase tracking-widest mt-1">Direct Payment Records</p>
      </div>
      <div class="flex gap-3">
        <input v-model="filters.date" type="date" class="bg-slate-50 border-none rounded-xl py-3 px-4 text-xs font-bold text-slate-600 focus:ring-2 focus:ring-[#e95a54]">
        <button @click="showForm = true; resetForm()" class="bg-[#e95a54] text-white px-4 py-3 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-opacity-90 flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
          New Payment
        </button>
      </div>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-slate-50 overflow-hidden flex-1 flex flex-col">
      <div class="overflow-x-auto flex-1">
        <table class="w-full border-collapse">
          <thead>
            <tr class="bg-slate-50/50 border-b border-slate-50">
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Date</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Amount</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Method</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Reference</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">By</span></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <tr v-for="p in payments" :key="p.id" class="hover:bg-slate-50/30 transition-colors">
              <td class="p-4"><span class="text-sm font-bold text-[#2a273c]">{{ formatDate(p.created_at) }}</span></td>
              <td class="p-4"><span class="text-sm font-bold text-[#e95a54]">{{ p.amount }} SAR</span></td>
              <td class="p-4"><span class="text-xs text-slate-600 capitalize">{{ p.payment_method }}</span></td>
              <td class="p-4"><span class="text-xs font-mono text-slate-500">{{ p.reference || '—' }}</span></td>
              <td class="p-4"><span class="text-xs text-slate-600">{{ p.created_by?.name || '—' }}</span></td>
            </tr>
            <tr v-if="!payments.length"><td colspan="5" class="p-16 text-center text-slate-400 text-sm">No quick payments found.</td></tr>
          </tbody>
        </table>
      </div>
    </div>

    <div v-if="showForm" class="fixed inset-0 bg-black/60 flex items-center justify-center z-50">
      <div class="bg-white rounded-3xl p-8 w-full max-w-md space-y-4">
        <h3 class="text-xl font-bold text-[#2a273c]">New Quick Payment</h3>
        <div><label class="label">Amount (SAR) *</label><input v-model="form.amount" type="number" class="input" placeholder="0.00"></div>
        <div><label class="label">Payment Method *</label>
          <select v-model="form.payment_method" class="input"><option value="cash">Cash</option><option value="card">Card</option><option value="bank_transfer">Bank Transfer</option></select>
        </div>
        <div><label class="label">Reference</label><input v-model="form.reference" class="input" placeholder="Reference number"></div>
        <div><label class="label">Notes</label><textarea v-model="form.notes" class="input" rows="2"></textarea></div>
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

const payments = ref([]);
const showForm = ref(false);
const processing = ref(false);
const filters = reactive({ date: '' });
const form = reactive({ amount: '', payment_method: 'cash', reference: '', notes: '' });

const resetForm = () => Object.assign(form, { amount: '', payment_method: 'cash', reference: '', notes: '' });
const formatDate = (d) => d ? dayjs(d).format('DD MMM YYYY HH:mm') : '—';

const load = async () => {
  const { data } = await api.get('/pos-module/quick-payments', { params: filters });
  payments.value = data.data || [];
};

const store = async () => {
  processing.value = true;
  try {
    await api.post('/pos-module/quick-payments', form);
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
