<template>
  <div class="p-6 space-y-6 bg-[#f8f9fa] min-h-full flex flex-col">
    <!-- Header -->
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-50">
      <h1 class="text-2xl font-bold text-[#2a273c]">Check-in</h1>
      <p class="text-xs font-black text-[#e95a54] uppercase tracking-widest mt-1">Guest Check-in Workflow</p>

      <!-- Progress -->
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

    <!-- Step Content -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-50 p-8 flex-1">

      <!-- Step 0: Select Reservation -->
      <div v-if="step === 0">
        <h2 class="text-lg font-bold text-[#2a273c] mb-4">Select Reservation</h2>
        <div class="flex gap-3 mb-4">
          <input v-model="search" type="text" placeholder="Search by reservation #..." class="flex-1 bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-bold text-[#2a273c] focus:ring-2 focus:ring-[#e95a54]">
          <input v-model="date" type="date" class="bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-bold text-slate-600 focus:ring-2 focus:ring-[#e95a54]">
        </div>
        <div class="space-y-2 max-h-96 overflow-y-auto">
          <div v-for="res in arrivals" :key="res.id"
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
          <p v-if="arrivals.length === 0" class="text-center text-slate-400 text-sm py-8">No arrivals found for selected date.</p>
        </div>
      </div>

      <!-- Step 1: Verify Guest ID -->
      <div v-if="step === 1">
        <h2 class="text-lg font-bold text-[#2a273c] mb-4">Verify Guest ID</h2>
        <div class="grid grid-cols-2 gap-4">
          <div><label class="label">ID Type</label><input v-model="form.id_type" class="input" placeholder="National ID / Passport"></div>
          <div><label class="label">ID Number</label><input v-model="form.id_number" class="input" placeholder="ID Number"></div>
          <div><label class="label">Full Name</label><input v-model="form.full_name" class="input" placeholder="Full Name"></div>
          <div><label class="label">Nationality</label><input v-model="form.nationality" class="input" placeholder="Nationality"></div>
          <div><label class="label">Date of Birth</label><input v-model="form.date_of_birth" type="date" class="input"></div>
          <div><label class="label">Phone</label><input v-model="form.phone" class="input" placeholder="Phone"></div>
        </div>
      </div>

      <!-- Step 2: Assign Room -->
      <div v-if="step === 2">
        <h2 class="text-lg font-bold text-[#2a273c] mb-4">Assign Room</h2>
        <p v-if="selected?.unit_id" class="text-sm text-emerald-600 font-bold mb-4">✓ Room already assigned: Unit #{{ selected.unit_id }}</p>
        <div v-else class="grid grid-cols-3 gap-3">
          <div v-for="unit in availableUnits" :key="unit.id"
            @click="form.unit_id = unit.id"
            :class="['p-4 rounded-2xl border-2 cursor-pointer transition-all text-center', form.unit_id === unit.id ? 'border-[#e95a54] bg-red-50' : 'border-slate-100 hover:border-slate-200']">
            <p class="text-lg font-bold text-[#2a273c]">{{ unit.unit_number }}</p>
            <p class="text-[10px] text-slate-400 font-black uppercase">Floor {{ unit.floor || '—' }}</p>
          </div>
        </div>
      </div>

      <!-- Step 3: Collect Deposit -->
      <div v-if="step === 3">
        <h2 class="text-lg font-bold text-[#2a273c] mb-4">Collect Deposit / Payment</h2>
        <div class="grid grid-cols-2 gap-4">
          <div><label class="label">Deposit Amount (SAR)</label><input v-model="form.deposit_amount" type="number" class="input" placeholder="0.00"></div>
          <div>
            <label class="label">Payment Method</label>
            <select v-model="form.payment_method" class="input">
              <option value="cash">Cash</option>
              <option value="card">Card</option>
              <option value="bank_transfer">Bank Transfer</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Step 4: Digital Signature -->
      <div v-if="step === 4">
        <h2 class="text-lg font-bold text-[#2a273c] mb-4">Digital Signature</h2>
        <div class="border-2 border-dashed border-slate-200 rounded-2xl h-48 flex items-center justify-center bg-slate-50">
          <p class="text-slate-400 text-sm font-medium">Signature pad area — guest signs here</p>
        </div>
        <button @click="form.signature_data = 'signed'" class="mt-4 bg-emerald-500 text-white px-4 py-2 rounded-xl text-xs font-black uppercase tracking-widest">Capture Signature</button>
        <p v-if="form.signature_data" class="text-emerald-600 text-xs font-bold mt-2">✓ Signature captured</p>
      </div>

      <!-- Step 5: Print Registration Card -->
      <div v-if="step === 5">
        <h2 class="text-lg font-bold text-[#2a273c] mb-4">Print Registration Card</h2>
        <div class="bg-slate-50 rounded-2xl p-6 border border-slate-100">
          <p class="text-sm font-bold text-[#2a273c]">Reservation: #{{ selected?.code }}</p>
          <p class="text-sm text-slate-600 mt-1">Guest: {{ form.full_name || '—' }}</p>
          <p class="text-sm text-slate-600">Check-in: {{ formatDate(selected?.check_in) }}</p>
          <p class="text-sm text-slate-600">Check-out: {{ formatDate(selected?.check_out) }}</p>
        </div>
        <button @click="printCard" class="mt-4 bg-[#2a273c] text-white px-4 py-2 rounded-xl text-xs font-black uppercase tracking-widest">Print Card</button>
      </div>

      <!-- Step 6: Issue Key -->
      <div v-if="step === 6">
        <h2 class="text-lg font-bold text-[#2a273c] mb-4">Issue Key / Room Number</h2>
        <div class="bg-emerald-50 border border-emerald-100 rounded-2xl p-8 text-center">
          <div class="w-16 h-16 bg-emerald-500 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
          </div>
          <p class="text-2xl font-bold text-[#2a273c]">Room {{ selected?.unit_id || form.unit_id || '—' }}</p>
          <p class="text-sm text-slate-500 mt-2">Hand key card to guest</p>
        </div>
      </div>
    </div>

    <!-- Navigation -->
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-50 flex items-center justify-between">
      <button v-if="step > 0" @click="step--" class="bg-slate-100 text-[#2a273c] px-6 py-3 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-slate-200 transition-colors">Back</button>
      <div v-else></div>
      <button v-if="step < steps.length - 1" @click="nextStep" :disabled="!canProceed" class="bg-[#e95a54] text-white px-6 py-3 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-opacity-90 transition-all disabled:opacity-40">Next</button>
      <button v-else @click="completeCheckIn" :disabled="processing" class="bg-emerald-500 text-white px-6 py-3 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-opacity-90 transition-all disabled:opacity-40">
        {{ processing ? 'Processing...' : 'Complete Check-in' }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue';
import api from '../services/api';
import dayjs from 'dayjs';

const steps = ['Select Reservation', 'Verify Guest', 'Assign Room', 'Deposit', 'Signature', 'Print Card', 'Issue Key'];
const step = ref(0);
const search = ref('');
const date = ref(dayjs().format('YYYY-MM-DD'));
const arrivals = ref([]);
const availableUnits = ref([]);
const selected = ref(null);
const processing = ref(false);
const form = reactive({ id_type: '', id_number: '', full_name: '', nationality: '', date_of_birth: '', phone: '', unit_id: null, deposit_amount: '', payment_method: 'cash', signature_data: '' });

const canProceed = computed(() => {
  if (step.value === 0) return !!selected.value;
  return true;
});

const formatDate = (d) => d ? dayjs(d).format('DD MMM YYYY') : '—';

const loadArrivals = async () => {
  const { data } = await api.get('/front-desk/arrivals', { params: { date: date.value, search: search.value } });
  arrivals.value = data.data || [];
};

const loadAvailableRooms = async () => {
  const { data } = await api.get('/front-desk/available-rooms');
  availableUnits.value = data.data || [];
};

const selectReservation = (res) => { selected.value = res; };

const nextStep = async () => {
  if (step.value === 1 && form.id_number) {
    await api.post(`/front-desk/registration/${selected.value.id}`, form).catch(() => {});
  }
  if (step.value === 1) await loadAvailableRooms(); // always reload on step 2
  step.value++;
};

const printCard = () => window.print();

const completeCheckIn = async () => {
  processing.value = true;
  try {
    await api.post(`/front-desk/check-in/${selected.value.id}`, {
      unit_id: form.unit_id || selected.value.unit_id,
      signature_data: form.signature_data,
      deposit_amount: form.deposit_amount,
      payment_method: form.payment_method,
    });
    step.value = steps.length; // done
  } finally {
    processing.value = false;
  }
};

watch([search, date], loadArrivals);
onMounted(loadArrivals);
</script>

<style scoped>
.label { @apply block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2; }
.input { @apply w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-bold text-[#2a273c] focus:ring-2 focus:ring-[#e95a54]; }
</style>
