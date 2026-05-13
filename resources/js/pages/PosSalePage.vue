<template>
  <div class="p-6 space-y-6 bg-[#f8f9fa] min-h-full flex flex-col">
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-50">
      <h1 class="text-2xl font-bold text-[#2a273c]">New POS Sale</h1>
      <p class="text-xs font-black text-[#e95a54] uppercase tracking-widest mt-1">Point of Sale</p>
      <!-- Progress -->
      <div class="flex items-center gap-2 mt-6 overflow-x-auto pb-1">
        <template v-for="(label, i) in steps" :key="i">
          <div class="flex items-center gap-2 shrink-0">
            <div :class="['w-7 h-7 rounded-full flex items-center justify-center text-[10px] font-black transition-all', step > i ? 'bg-emerald-500 text-white' : step === i ? 'bg-[#e95a54] text-white' : 'bg-slate-100 text-slate-400']">
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
        <h2 class="text-lg font-bold text-[#2a273c] mb-4">Select Reservation (Optional)</h2>
        <div><label class="label">Reservation ID</label><input v-model="form.reservation_id" type="number" class="input w-64" placeholder="Leave blank for direct sale"></div>
      </div>

      <!-- Step 1: Add Services -->
      <div v-if="step === 1">
        <h2 class="text-lg font-bold text-[#2a273c] mb-4">Add Services</h2>
        <div class="flex gap-3 mb-4">
          <div class="relative flex-1"><input v-model="serviceSearch" type="text" placeholder="Search services..." class="input pl-10"><svg class="w-4 h-4 text-slate-400 absolute left-3 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg></div>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-3 max-h-64 overflow-y-auto mb-4">
          <div v-for="s in filteredServices" :key="s.id" @click="addItem(s)" class="p-3 rounded-2xl border-2 border-slate-100 hover:border-[#e95a54] cursor-pointer transition-all">
            <p class="text-sm font-bold text-[#2a273c]">{{ s.name_en || s.name }}</p>
            <p class="text-xs font-bold text-[#e95a54] mt-1">{{ s.price || 0 }} SAR</p>
          </div>
        </div>
        <!-- Cart Items -->
        <div v-if="form.items.length" class="space-y-2">
          <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest">Selected Items</h3>
          <div v-for="(item, i) in form.items" :key="i" class="flex items-center justify-between bg-slate-50 rounded-2xl p-3">
            <span class="text-sm font-bold text-[#2a273c]">{{ item.name }}</span>
            <div class="flex items-center gap-3">
              <input v-model.number="item.quantity" type="number" min="1" class="w-16 bg-white border-none rounded-xl py-1.5 px-3 text-sm font-bold text-center focus:ring-2 focus:ring-[#e95a54]">
              <span class="text-xs font-bold text-[#e95a54]">{{ (item.quantity * item.price).toFixed(2) }} SAR</span>
              <button @click="form.items.splice(i, 1)" class="text-red-400 hover:text-red-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg></button>
            </div>
          </div>
        </div>
      </div>

      <!-- Step 2: Review Order -->
      <div v-if="step === 2">
        <h2 class="text-lg font-bold text-[#2a273c] mb-4">Review Order</h2>
        <div class="space-y-2">
          <div v-for="item in form.items" :key="item.service_id" class="flex justify-between py-2 border-b border-slate-50 text-sm">
            <span class="text-slate-600">{{ item.name }} × {{ item.quantity }}</span>
            <span class="font-bold text-[#2a273c]">{{ (item.quantity * item.price).toFixed(2) }} SAR</span>
          </div>
          <div class="flex justify-between py-2 text-sm font-bold text-[#2a273c] border-t border-slate-200 mt-2">
            <span>Total</span>
            <span class="text-[#e95a54]">{{ total.toFixed(2) }} SAR</span>
          </div>
        </div>
      </div>

      <!-- Step 3: Payment Method -->
      <div v-if="step === 3">
        <h2 class="text-lg font-bold text-[#2a273c] mb-4">Select Payment Method</h2>
        <div class="grid grid-cols-3 gap-3">
          <div v-for="m in paymentMethods" :key="m.value" @click="form.payment_method = m.value" :class="['p-4 rounded-2xl border-2 cursor-pointer text-center transition-all', form.payment_method === m.value ? 'border-[#e95a54] bg-red-50' : 'border-slate-100 hover:border-slate-200']">
            <p class="text-sm font-bold text-[#2a273c]">{{ m.label }}</p>
          </div>
        </div>
      </div>

      <!-- Step 4: Collect Payment -->
      <div v-if="step === 4">
        <h2 class="text-lg font-bold text-[#2a273c] mb-4">Collect Payment</h2>
        <div class="bg-emerald-50 border border-emerald-100 rounded-2xl p-8 text-center">
          <p class="text-3xl font-bold text-[#2a273c]">{{ total.toFixed(2) }} SAR</p>
          <p class="text-sm text-slate-500 mt-2">via {{ form.payment_method }}</p>
        </div>
      </div>

      <!-- Step 5: Receipt -->
      <div v-if="step === 5">
        <h2 class="text-lg font-bold text-[#2a273c] mb-4">Sale Complete</h2>
        <div class="bg-emerald-50 border border-emerald-100 rounded-2xl p-8 text-center">
          <div class="w-16 h-16 bg-emerald-500 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round" stroke-width="3"/></svg>
          </div>
          <p class="text-xl font-bold text-[#2a273c]">Payment Received</p>
          <p class="text-sm text-slate-500 mt-2">{{ total.toFixed(2) }} SAR</p>
        </div>
        <button @click="window.print()" class="mt-4 bg-[#2a273c] text-white px-4 py-2 rounded-xl text-xs font-black uppercase tracking-widest">Print Receipt</button>
      </div>
    </div>

    <!-- Navigation -->
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-50 flex items-center justify-between">
      <button v-if="step > 0" @click="step--" class="bg-slate-100 text-[#2a273c] px-6 py-3 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-slate-200">Back</button>
      <div v-else></div>
      <button v-if="step < steps.length - 1" @click="nextStep" :disabled="!canProceed" class="bg-[#e95a54] text-white px-6 py-3 rounded-xl text-xs font-black uppercase tracking-widest disabled:opacity-40">Next</button>
      <button v-else @click="$router.push('/pos/dashboard')" class="bg-emerald-500 text-white px-6 py-3 rounded-xl text-xs font-black uppercase tracking-widest">Done</button>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, computed, onMounted } from 'vue';
import api from '../services/api';

const steps = ['Reservation', 'Add Services', 'Review', 'Payment', 'Collect', 'Receipt'];
const step = ref(0);
const serviceSearch = ref('');
const allServices = ref([]);
const processing = ref(false);
const form = reactive({ reservation_id: '', items: [], payment_method: 'cash' });
const paymentMethods = [{ value: 'cash', label: 'Cash' }, { value: 'card', label: 'Card' }, { value: 'bank_transfer', label: 'Bank Transfer' }];

const filteredServices = computed(() => allServices.value.filter(s => !serviceSearch.value || (s.name_en || s.name || '').toLowerCase().includes(serviceSearch.value.toLowerCase())));
const total = computed(() => form.items.reduce((sum, i) => sum + i.quantity * i.price, 0));
const canProceed = computed(() => step.value !== 1 || form.items.length > 0);

const addItem = (s) => {
  const existing = form.items.find(i => i.service_id === s.id);
  if (existing) existing.quantity++;
  else form.items.push({ service_id: s.id, name: s.name_en || s.name, quantity: 1, price: parseFloat(s.price || 0) });
};

const nextStep = async () => {
  if (step.value === 4) {
    processing.value = true;
    try {
      await api.post('/pos-module/sale', { ...form, items: form.items.map(i => ({ service_id: i.service_id, quantity: i.quantity, price: i.price })) });
    } finally { processing.value = false; }
  }
  step.value++;
};

onMounted(async () => {
  const { data } = await api.get('/pos-module/services', { params: { per_page: 100 } });
  allServices.value = data.data || [];
});
</script>

<style scoped>
.label { @apply block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2; }
.input { @apply w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-bold text-[#2a273c] focus:ring-2 focus:ring-[#e95a54]; }
</style>
