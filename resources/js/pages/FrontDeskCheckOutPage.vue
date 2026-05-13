<template>
  <div class="p-6 space-y-6 bg-[#f8f9fa] min-h-full flex flex-col">
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-50">
      <h1 class="text-2xl font-bold text-[#2a273c]">Check-out</h1>
      <p class="text-xs font-black text-[#e95a54] uppercase tracking-widest mt-1">Guest Check-out Workflow</p>
      <div class="flex items-center gap-2 mt-6 overflow-x-auto pb-1">
        <template v-for="(label, i) in steps" :key="i">
          <div class="flex items-center gap-2 shrink-0">
            <div :class="['w-7 h-7 rounded-full flex items-center justify-center text-[10px] font-black transition-all',
              step > i ? 'bg-emerald-500 text-white' : step === i ? 'bg-[#e95a54] text-white' : 'bg-slate-100 text-slate-400']">
              <svg v-if="step > i" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round" stroke-width="3"/></svg>
              <span v-else>{{ i + 1 }}</span>
            </div>
            <span :class="['text-[10px] font-black uppercase tracking-widest', step === i ? 'text-[#e95a54]' : 'text-slate-400']">{{ label }}</span>
          </div>
          <div v-if="i < steps.length - 1" class="flex-1 h-px bg-slate-100 min-w-4"></div>
        </template>
      </div>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-slate-50 p-8 flex-1">

      <!-- Step 0: Select Reservation -->
      <div v-if="step === 0">
        <h2 class="text-lg font-bold text-[#2a273c] mb-4">Select Reservation</h2>
        <div class="flex gap-3 mb-4">
          <input v-model="search" type="text" placeholder="Search by reservation #..." class="flex-1 bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-bold text-[#2a273c] focus:ring-2 focus:ring-[#e95a54]">
          <input v-model="date" type="date" class="bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-bold text-slate-600 focus:ring-2 focus:ring-[#e95a54]">
        </div>
        <div class="space-y-2 max-h-96 overflow-y-auto">
          <div v-for="res in departures" :key="res.id"
            @click="selectReservation(res)"
            :class="['p-4 rounded-2xl border-2 cursor-pointer transition-all', selected?.id === res.id ? 'border-[#e95a54] bg-red-50' : 'border-slate-100 hover:border-slate-200']">
            <div class="flex items-center justify-between">
              <div>
                <span class="text-sm font-bold text-[#e95a54]">#{{ res.code }}</span>
                <span class="text-xs text-slate-500 ml-3">{{ formatDate(res.check_in) }} → {{ formatDate(res.check_out) }}</span>
              </div>
              <span class="text-[10px] font-black text-slate-400 uppercase">{{ res.status }}</span>
            </div>
          </div>
          <p v-if="departures.length === 0" class="text-center text-slate-400 text-sm py-8">No departures found.</p>
        </div>
      </div>

      <!-- Step 1: Review Folio -->
      <div v-if="step === 1">
        <h2 class="text-lg font-bold text-[#2a273c] mb-4">Review Folio</h2>
        <div v-if="folio" class="space-y-3">
          <div class="grid grid-cols-3 gap-4">
            <div class="bg-slate-50 rounded-2xl p-4 text-center">
              <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Charges</p>
              <p class="text-xl font-bold text-[#2a273c] mt-1">{{ folio.charges }} SAR</p>
            </div>
            <div class="bg-slate-50 rounded-2xl p-4 text-center">
              <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Payments</p>
              <p class="text-xl font-bold text-emerald-600 mt-1">{{ folio.payments }} SAR</p>
            </div>
            <div :class="['rounded-2xl p-4 text-center', folio.balance > 0 ? 'bg-red-50' : 'bg-emerald-50']">
              <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Balance</p>
              <p :class="['text-xl font-bold mt-1', folio.balance > 0 ? 'text-red-600' : 'text-emerald-600']">{{ folio.balance }} SAR</p>
            </div>
          </div>
          <div class="max-h-48 overflow-y-auto space-y-1">
            <div v-for="tx in folio.transactions" :key="tx.id" class="flex justify-between py-2 border-b border-slate-50 text-sm">
              <span class="text-slate-600">{{ tx.description || tx.kind }}</span>
              <span :class="tx.kind === 'payment' ? 'text-emerald-600 font-bold' : 'text-[#2a273c] font-bold'">{{ tx.amount }} SAR</span>
            </div>
          </div>
        </div>
        <div v-else class="text-center py-8 text-slate-400">Loading folio...</div>
      </div>

      <!-- Step 2: Collect Balance -->
      <div v-if="step === 2">
        <h2 class="text-lg font-bold text-[#2a273c] mb-4">Collect Outstanding Balance</h2>
        <div v-if="folio?.balance > 0" class="space-y-4">
          <div class="bg-amber-50 border border-amber-100 rounded-2xl p-4">
            <p class="text-sm font-bold text-amber-700">Outstanding balance: {{ folio.balance }} SAR</p>
          </div>
          <div><label class="label">Payment Method</label>
            <select v-model="form.payment_method" class="input">
              <option value="cash">Cash</option><option value="card">Card</option><option value="bank_transfer">Bank Transfer</option>
            </select>
          </div>
        </div>
        <div v-else class="bg-emerald-50 border border-emerald-100 rounded-2xl p-6 text-center">
          <p class="text-emerald-700 font-bold">No outstanding balance — account is settled.</p>
        </div>
      </div>

      <!-- Step 3: Balance Transfer -->
      <div v-if="step === 3">
        <h2 class="text-lg font-bold text-[#2a273c] mb-4">Handle Balance Transfer</h2>
        <div class="space-y-4">
          <div><label class="label">Transfer Type</label>
            <select v-model="form.balance_action" class="input">
              <option value="">None</option>
              <option value="to_promissory">Promissory Note</option>
              <option value="refunded">Refund</option>
              <option value="waived">Waive</option>
              <option value="to_credit_note">Credit Note</option>
            </select>
          </div>
          <div v-if="form.balance_action"><label class="label">Amount (SAR)</label><input v-model="form.balance_amount" type="number" class="input"></div>
          <div v-if="form.balance_action"><label class="label">Notes</label><textarea v-model="form.balance_notes" class="input" rows="2"></textarea></div>
        </div>
      </div>

      <!-- Step 4: Generate Invoice -->
      <div v-if="step === 4">
        <h2 class="text-lg font-bold text-[#2a273c] mb-4">Generate Invoice</h2>
        <div class="bg-slate-50 rounded-2xl p-6 border border-slate-100">
          <p class="text-sm font-bold text-[#2a273c]">Reservation: #{{ selected?.code }}</p>
          <p class="text-sm text-slate-600 mt-1">Total: {{ folio?.charges || 0 }} SAR</p>
          <p class="text-sm text-slate-600">Paid: {{ folio?.payments || 0 }} SAR</p>
        </div>
        <button @click="generateInvoice" class="mt-4 bg-[#2a273c] text-white px-4 py-2 rounded-xl text-xs font-black uppercase tracking-widest">Generate Invoice</button>
      </div>

      <!-- Step 5: Print Invoice -->
      <div v-if="step === 5">
        <h2 class="text-lg font-bold text-[#2a273c] mb-4">Print Invoice</h2>
        <button @click="window.print()" class="bg-[#2a273c] text-white px-4 py-2 rounded-xl text-xs font-black uppercase tracking-widest">Print Invoice</button>
      </div>

      <!-- Step 6: Collect Key -->
      <div v-if="step === 6">
        <h2 class="text-lg font-bold text-[#2a273c] mb-4">Collect Key</h2>
        <div class="bg-emerald-50 border border-emerald-100 rounded-2xl p-8 text-center">
          <p class="text-2xl font-bold text-[#2a273c]">Collect key card from guest</p>
          <p class="text-sm text-slate-500 mt-2">Room {{ selected?.unit_id || '—' }}</p>
        </div>
      </div>
    </div>

    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-50 flex items-center justify-between">
      <button v-if="step > 0" @click="step--" class="bg-slate-100 text-[#2a273c] px-6 py-3 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-slate-200">Back</button>
      <div v-else></div>
      <button v-if="step < steps.length - 1" @click="nextStep" :disabled="!canProceed" class="bg-[#e95a54] text-white px-6 py-3 rounded-xl text-xs font-black uppercase tracking-widest disabled:opacity-40">Next</button>
      <button v-else @click="completeCheckOut" :disabled="processing" class="bg-emerald-500 text-white px-6 py-3 rounded-xl text-xs font-black uppercase tracking-widest disabled:opacity-40">
        {{ processing ? 'Processing...' : 'Complete Check-out' }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue';
import api from '../services/api';
import dayjs from 'dayjs';

const steps = ['Select Reservation', 'Review Folio', 'Collect Balance', 'Balance Transfer', 'Generate Invoice', 'Print Invoice', 'Collect Key'];
const step = ref(0);
const search = ref('');
const date = ref(dayjs().format('YYYY-MM-DD'));
const departures = ref([]);
const selected = ref(null);
const folio = ref(null);
const processing = ref(false);
const form = reactive({ payment_method: 'cash', balance_action: '', balance_amount: '', balance_notes: '' });

const canProceed = computed(() => step.value === 0 ? !!selected.value : true);
const formatDate = (d) => d ? dayjs(d).format('DD MMM YYYY') : '—';

const loadDepartures = async () => {
  const { data } = await api.get('/front-desk/departures', { params: { date: date.value, search: search.value } });
  departures.value = data.data || [];
};

const selectReservation = (res) => { selected.value = res; };

const loadFolio = async () => {
  const { data } = await api.get(`/front-desk/folio/${selected.value.id}`);
  folio.value = data.data;
};

const nextStep = async () => {
  if (step.value === 0) await loadFolio();
  step.value++;
};

const generateInvoice = () => {};

const completeCheckOut = async () => {
  processing.value = true;
  try {
    await api.post(`/front-desk/check-out/${selected.value.id}`, {
      balance_action: form.balance_action || null,
      balance_amount: form.balance_amount || 0,
      balance_notes: form.balance_notes,
    });
    step.value = steps.length;
  } finally {
    processing.value = false;
  }
};

watch([search, date], loadDepartures);
onMounted(loadDepartures);
</script>

<style scoped>
.label { @apply block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2; }
.input { @apply w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-bold text-[#2a273c] focus:ring-2 focus:ring-[#e95a54]; }
</style>
