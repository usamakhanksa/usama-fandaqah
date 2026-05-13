<template>
  <div class="p-6 space-y-6 bg-[#f8f9fa] min-h-full flex flex-col">
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-50 flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-[#2a273c]">Promissories</h1>
        <p class="text-xs font-black text-[#e95a54] uppercase tracking-widest mt-1">Accounts Receivable — Promissory Notes</p>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
      <div class="bg-white rounded-2xl p-5 border border-slate-50 shadow-sm">
        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Outstanding</p>
        <p class="text-2xl font-bold text-[#2a273c] mt-1">{{ stats.total_outstanding?.toLocaleString() || 0 }} <span class="text-sm font-medium text-slate-400">SAR</span></p>
      </div>
      <div class="bg-white rounded-2xl p-5 border border-slate-50 shadow-sm">
        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Partially Paid</p>
        <p class="text-2xl font-bold text-amber-600 mt-1">{{ stats.partially_paid || 0 }}</p>
      </div>
      <div class="bg-white rounded-2xl p-5 border border-slate-50 shadow-sm">
        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Fully Paid</p>
        <p class="text-2xl font-bold text-emerald-600 mt-1">{{ stats.fully_paid || 0 }}</p>
      </div>
      <div class="bg-white rounded-2xl p-5 border border-slate-50 shadow-sm">
        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Overdue</p>
        <p class="text-2xl font-bold text-rose-600 mt-1">{{ stats.overdue || 0 }}</p>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white p-5 rounded-3xl shadow-sm border border-slate-50 flex flex-wrap gap-3">
      <div class="relative flex-1 min-w-[200px]">
        <input v-model="filters.search" type="text" placeholder="Search reservation, guest, company..." class="w-full bg-slate-50 border-none rounded-xl py-3 px-10 text-sm focus:ring-2 focus:ring-[#e95a54]">
        <svg class="absolute left-3 top-3.5 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
      </div>
      <select v-model="filters.status" class="bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-medium text-slate-600 focus:ring-2 focus:ring-[#e95a54]">
        <option value="">All Statuses</option>
        <option value="pending">Pending</option>
        <option value="partially_paid">Partially Paid</option>
        <option value="paid">Fully Paid</option>
        <option value="overdue">Overdue</option>
      </select>
      <input v-model="filters.date_from" type="date" class="bg-slate-50 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-[#e95a54]">
      <input v-model="filters.date_to" type="date" class="bg-slate-50 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-[#e95a54]">
    </div>

    <!-- Table -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-50 overflow-hidden flex-1">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="bg-slate-50 text-slate-400 font-bold uppercase text-[10px] tracking-widest border-b border-slate-100">
              <th class="px-6 py-4 text-start">Promissory #</th>
              <th class="px-6 py-4 text-start">Reservation</th>
              <th class="px-6 py-4 text-start">Guest / Company</th>
              <th class="px-6 py-4 text-start">Total Amount</th>
              <th class="px-6 py-4 text-start">Paid</th>
              <th class="px-6 py-4 text-start">Balance</th>
              <th class="px-6 py-4 text-start">Due Date</th>
              <th class="px-6 py-4 text-start">Status</th>
              <th class="px-6 py-4 text-end">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <tr v-for="p in promissories" :key="p.id" class="hover:bg-slate-50/50 transition-colors group">
              <td class="px-6 py-5 font-bold text-[#2a273c]">#PR-{{ p.id }}</td>
              <td class="px-6 py-5">
                <span class="bg-indigo-50 text-indigo-600 px-2 py-1 rounded text-[10px] font-bold">{{ p.reservation?.code || '—' }}</span>
              </td>
              <td class="px-6 py-5">
                <div class="flex flex-col">
                  <span class="font-bold text-[#2a273c]">{{ p.reservation?.guest?.name || 'Walk-in' }}</span>
                  <span class="text-xs text-slate-400">{{ p.company?.name || 'Individual' }}</span>
                </div>
              </td>
              <td class="px-6 py-5 font-bold text-[#2a273c]">{{ p.amount?.toLocaleString() }} SAR</td>
              <td class="px-6 py-5 text-emerald-600 font-bold">{{ p.paid_amount?.toLocaleString() || 0 }} SAR</td>
              <td class="px-6 py-5 font-bold" :class="p.balance > 0 ? 'text-rose-600' : 'text-emerald-600'">
                {{ p.balance?.toLocaleString() || 0 }} SAR
              </td>
              <td class="px-6 py-5 text-slate-600">{{ p.due_date || '—' }}</td>
              <td class="px-6 py-5">
                <span :class="statusClass(p.status)" class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest">{{ p.status }}</span>
              </td>
              <td class="px-6 py-5 text-end">
                <button v-if="p.balance > 0" @click="applyPayment(p)" class="bg-emerald-50 text-emerald-600 px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest opacity-0 group-hover:opacity-100 transition-all hover:bg-emerald-600 hover:text-white">
                  Apply Payment
                </button>
              </td>
            </tr>
            <tr v-if="!promissories.length">
              <td colspan="9" class="p-16 text-center text-slate-400 text-sm">No promissories found.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Apply Payment Modal -->
    <div v-if="paymentModal.show" class="fixed inset-0 bg-black/60 flex items-center justify-center z-50">
      <div class="bg-white rounded-3xl p-8 w-full max-w-md space-y-4">
        <h3 class="text-xl font-bold text-[#2a273c]">Apply Payment — #PR-{{ paymentModal.promissory?.id }}</h3>
        <p class="text-sm text-slate-500">Outstanding balance: <span class="font-bold text-rose-600">{{ paymentModal.promissory?.balance }} SAR</span></p>
        <div>
          <label class="label">Amount (SAR)</label>
          <input v-model="paymentModal.amount" type="number" class="input" :max="paymentModal.promissory?.balance" placeholder="0.00">
        </div>
        <div>
          <label class="label">Payment Method</label>
          <select v-model="paymentModal.method" class="input">
            <option value="cash">Cash</option>
            <option value="bank_transfer">Bank Transfer</option>
            <option value="card">Credit Card</option>
          </select>
        </div>
        <div class="flex gap-3 pt-2">
          <button @click="paymentModal.show = false" class="flex-1 bg-slate-100 text-[#2a273c] py-3 rounded-xl text-xs font-black uppercase tracking-widest">Cancel</button>
          <button @click="submitPayment" :disabled="processing" class="flex-1 bg-emerald-500 text-white py-3 rounded-xl text-xs font-black uppercase tracking-widest disabled:opacity-40">
            {{ processing ? 'Processing...' : 'Apply Payment' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, watch, onMounted } from 'vue';
import api from '../services/api';

const promissories = ref([]);
const processing = ref(false);
const stats = ref({});
const filters = reactive({ search: '', status: '', date_from: '', date_to: '' });
const paymentModal = reactive({ show: false, promissory: null, amount: '', method: 'cash' });

const statusClass = (s) => ({
  pending: 'bg-amber-50 text-amber-600',
  partially_paid: 'bg-blue-50 text-blue-600',
  paid: 'bg-emerald-50 text-emerald-600',
  overdue: 'bg-rose-50 text-rose-600',
}[s] || 'bg-slate-100 text-slate-500');

const load = async () => {
  const { data } = await api.get('/promissories', { params: filters });
  promissories.value = data.data || [];
  // Compute stats from data
  stats.value = {
    total_outstanding: promissories.value.reduce((s, p) => s + (p.balance || 0), 0),
    partially_paid: promissories.value.filter(p => p.status === 'partially_paid').length,
    fully_paid: promissories.value.filter(p => p.status === 'paid').length,
    overdue: promissories.value.filter(p => p.status === 'overdue').length,
  };
};

const applyPayment = (p) => {
  paymentModal.promissory = p;
  paymentModal.amount = p.balance;
  paymentModal.method = 'cash';
  paymentModal.show = true;
};

const submitPayment = async () => {
  processing.value = true;
  try {
    await api.post(`/promissories/${paymentModal.promissory.id}/apply-payment`, {
      amount: paymentModal.amount,
      payment_type: paymentModal.method,
    });
    paymentModal.show = false;
    load();
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to apply payment');
  } finally {
    processing.value = false;
  }
};

watch(filters, load, { deep: true });
onMounted(load);
</script>

<style scoped>
.label { @apply block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2; }
.input { @apply w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-bold text-[#2a273c] focus:ring-2 focus:ring-[#e95a54]; }
</style>
