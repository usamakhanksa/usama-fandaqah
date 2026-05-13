<template>
  <BaseModal :model-value="modelValue" @update:modelValue="$emit('update:modelValue',$event)" title="Check Out">
    <form class="space-y-4 p-2" @submit.prevent="submit">
      <div class="grid grid-cols-2 gap-4">
        <div class="col-span-2">
          <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Check-out Date</label>
          <input type="date" v-model="form.date" class="input w-full" required>
        </div>
        <div class="col-span-2">
          <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Check-out Time</label>
          <TimeChipSelector v-model="form.time"/>
        </div>
      </div>

      <!-- Calculated Charge Info -->
      <div v-if="calculatedCharge > 0 || loadingCharge" class="p-4 rounded-2xl bg-orange-50 border border-orange-100 animate-in fade-in">
        <div class="flex justify-between items-center">
          <span class="text-sm font-semibold text-orange-900">Calculated Late Checkout Charge:</span>
          <span v-if="loadingCharge" class="text-xs text-orange-400">Calculating...</span>
          <span v-else class="text-lg font-black text-orange-600">{{ calculatedCharge }} SAR</span>
        </div>
        
        <!-- Override Section -->
        <div class="mt-4 pt-4 border-t border-orange-200 space-y-3">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-orange-800 uppercase">Override Amount?</span>
            <button type="button" @click="showOverride = !showOverride" class="text-xs font-black text-orange-600 hover:underline">
              {{ showOverride ? 'Cancel Override' : 'Override' }}
            </button>
          </div>
          
          <div v-if="showOverride" class="space-y-3 animate-in slide-in-from-top-2">
            <input 
              type="number" 
              v-model="form.override_amount" 
              placeholder="New Amount (SAR)" 
              class="input w-full bg-white border-orange-200 focus:ring-orange-500"
            >
            <textarea 
              v-model="form.override_reason" 
              placeholder="Reason for override (Mandatory)" 
              class="input w-full h-20 bg-white border-orange-200 focus:ring-orange-500 text-sm"
              :required="showOverride"
            ></textarea>
          </div>
        </div>
      </div>

      <div class="space-y-1">
        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Additional Final Charges</label>
        <input type="number" class="input w-full" v-model.number="form.final_charges" placeholder="Final Charges">
      </div>

      <!-- Balance Info -->
      <div class="p-4 rounded-2xl bg-[#2a273c] text-white space-y-2">
        <div class="flex justify-between items-center text-[10px] opacity-70 uppercase font-bold tracking-widest">
          <span>Current Balance</span>
          <span>Projected Final</span>
        </div>
        <div class="flex justify-between items-end">
          <span class="text-xl font-black">{{ currentBalance }} SAR</span>
          <div class="text-right">
            <span class="block text-[10px] opacity-50">Incl. Late Fees & Extra</span>
            <span :class="['text-2xl font-black', projectedBalance === 0 ? 'text-emerald-400' : 'text-rose-400']">
              {{ projectedBalance }} SAR
            </span>
          </div>
        </div>
      </div>

      <!-- Resolution Section -->
      <div v-if="projectedBalance !== 0" class="p-4 rounded-2xl bg-rose-50 border border-rose-100 space-y-4 animate-in slide-in-from-bottom-4">
        <div class="flex items-center gap-2 text-rose-800">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
          <span class="text-xs font-bold uppercase tracking-wider">Mandatory Balance Resolution</span>
        </div>

        <div class="space-y-1">
          <label class="block text-[10px] font-bold text-rose-700 uppercase">Resolution Method</label>
          <select v-model="form.resolution_type" class="input w-full border-rose-200 bg-white" required>
            <option value="">Select Method...</option>
            <template v-if="projectedBalance > 0">
              <option value="collect_now">Collect Now (Cash/Card)</option>
              <option value="signed_promissory">Signed Promissory Note</option>
              <option value="unsigned_promissory">Unsigned Promissory Note</option>
              <option value="corporate_transfer">Transfer to Corporate Ledger</option>
            </template>
            <template v-else>
              <option value="refund_now">Refund Now</option>
              <option value="credit_note">Issue Credit Note</option>
            </template>
          </select>
        </div>

        <!-- Promissory Details -->
        <div v-if="isPromissory" class="space-y-3 p-3 bg-white rounded-xl border border-rose-100 animate-in fade-in">
          <div>
            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Due Date</label>
            <input type="date" v-model="form.promissory_due_date" class="input w-full" :required="isPromissory">
          </div>
          <div v-if="form.resolution_type === 'unsigned_promissory'">
            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Reason for Unsigned</label>
            <textarea v-model="form.unsigned_reason" class="input w-full h-16 text-sm" placeholder="Guest refused to sign, absent, etc." :required="form.resolution_type === 'unsigned_promissory'"></textarea>
          </div>
        </div>

        <div>
          <label class="block text-[10px] font-bold text-rose-700 uppercase mb-1">Resolution Notes</label>
          <textarea v-model="form.resolution_notes" class="input w-full h-16 border-rose-200 bg-white text-sm" placeholder="Add any details about this transfer..."></textarea>
        </div>
      </div>

      <div class="space-y-1">
        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">General Notes</label>
        <textarea class="input w-full h-20 text-sm" v-model="form.note" placeholder="General checkout notes"></textarea>
      </div>

      <button 
        class="bg-red-500 text-white rounded-xl py-4 w-full font-black uppercase tracking-widest shadow-xl shadow-red-100 hover:bg-red-600 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
        :disabled="projectedBalance !== 0 && !form.resolution_type"
      >
        Complete Check Out
      </button>
    </form>
  </BaseModal>
</template>

<script setup>
import { reactive, watch, ref, computed } from 'vue';
import BaseModal from './BaseModal.vue';
import TimeChipSelector from './TimeChipSelector.vue';
import api from '../services/api';

const props = defineProps({
  modelValue: Boolean,
  rooms: Array,
  reservationId: Number,
  unitId: Number
});

const emit = defineEmits(['update:modelValue', 'submitted']);

const form = reactive({
  reservation_id: null,
  unit_id: null,
  date: new Date().toISOString().slice(0,10),
  time: '01:00 PM',
  note: '',
  final_charges: 0,
  override_amount: null,
  override_reason: '',
  // Resolution fields
  resolution_type: '',
  resolution_notes: '',
  promissory_due_date: new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString().slice(0,10),
  unsigned_reason: ''
});

const currentBalance = ref(0);
const calculatedCharge = ref(0);
const loadingCharge = ref(false);
const showOverride = ref(false);

const projectedBalance = computed(() => {
  const lateFee = showOverride.value ? (form.override_amount || 0) : calculatedCharge.value;
  return Number((currentBalance.value + lateFee + (form.final_charges || 0)).toFixed(2));
});

const isPromissory = computed(() => ['signed_promissory', 'unsigned_promissory'].includes(form.resolution_type));

watch(() => [props.reservationId, props.unitId], () => {
  form.reservation_id = props.reservationId;
  form.unit_id = props.unitId;
}, { immediate: true });

let debounceTimer = null;
const fetchData = () => {
  if (!props.reservationId) return;
  
  if (debounceTimer) clearTimeout(debounceTimer);
  debounceTimer = setTimeout(async () => {
    loadingCharge.value = true;
    try {
      // Fetch balance
      const balanceRes = await api.get(`/reservations/${props.reservationId}/balance`);
      currentBalance.value = balanceRes.data.balance;

      // Fetch late checkout charge
      const chargeRes = await api.get('/stay-charge-configs/calculate', {
        params: {
          reservation_id: props.reservationId,
          time: form.time,
          type: 'late_checkout'
        }
      });
      calculatedCharge.value = chargeRes.data.amount;
    } catch (e) {
      console.error(e);
    } finally {
      loadingCharge.value = false;
    }
  }, 500);
};

watch(() => [form.time, form.date], fetchData);
watch(() => props.modelValue, (val) => {
  if (val) fetchData();
});

const submit = () => {
  const data = { ...form };
  if (!showOverride.value) {
    delete data.override_amount;
    delete data.override_reason;
  }
  
  if (projectedBalance.value === 0) {
    delete data.resolution_type;
    delete data.resolution_notes;
    delete data.promissory_due_date;
    delete data.unsigned_reason;
  }
  
  emit('submitted', data);
};
</script>
