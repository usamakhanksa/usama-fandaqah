<template>
  <BaseModal :model-value="modelValue" @update:modelValue="$emit('update:modelValue',$event)" title="Check IN">
    <form class="space-y-4 p-2" @submit.prevent="submit">
      <div class="grid grid-cols-2 gap-4">
        <div class="col-span-2">
          <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Check-in Date</label>
          <input type="date" v-model="form.date" class="input w-full" required>
        </div>
        <div class="col-span-2">
          <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Check-in Time</label>
          <TimeChipSelector v-model="form.time"/>
        </div>
      </div>

      <!-- Calculated Charge Info -->
      <div v-if="calculatedCharge > 0 || loadingCharge" class="p-4 rounded-2xl bg-emerald-50 border border-emerald-100 animate-in fade-in">
        <div class="flex justify-between items-center">
          <span class="text-sm font-semibold text-emerald-900">Calculated Early Check-in Charge:</span>
          <span v-if="loadingCharge" class="text-xs text-emerald-400">Calculating...</span>
          <span v-else class="text-lg font-black text-emerald-600">{{ calculatedCharge }} SAR</span>
        </div>
        
        <!-- Override Section -->
        <div class="mt-4 pt-4 border-t border-emerald-200 space-y-3">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-emerald-800 uppercase">Override Amount?</span>
            <button type="button" @click="showOverride = !showOverride" class="text-xs font-black text-emerald-600 hover:underline">
              {{ showOverride ? 'Cancel Override' : 'Override' }}
            </button>
          </div>
          
          <div v-if="showOverride" class="space-y-3 animate-in slide-in-from-top-2">
            <input 
              type="number" 
              v-model="form.override_amount" 
              placeholder="New Amount (SAR)" 
              class="input w-full bg-white border-emerald-200 focus:ring-emerald-500"
            >
            <textarea 
              v-model="form.override_reason" 
              placeholder="Reason for override (Mandatory)" 
              class="input w-full h-20 bg-white border-emerald-200 focus:ring-emerald-500 text-sm"
              :required="showOverride"
            ></textarea>
          </div>
        </div>
      </div>
      <div v-else class="p-4 rounded-2xl bg-slate-50 border border-slate-100 text-center text-xs text-slate-500">
        No early check-in charge applies for this time.
      </div>

      <div class="space-y-1">
        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Room Assignment</label>
        <select class="input w-full" v-model="form.unit_id">
          <option v-for="r in rooms" :value="r.value" :key="r.value">{{ r.label }}</option>
        </select>
      </div>

      <div class="space-y-1">
        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Notes</label>
        <textarea class="input w-full h-24" v-model="form.note" placeholder="Write any comment"></textarea>
      </div>

      <button class="btn-primary w-full py-4 text-sm font-black uppercase tracking-widest shadow-xl shadow-rose-100">
        Confirm Check IN
      </button>
    </form>
  </BaseModal>
</template>

<script setup>
import { reactive, watch, ref } from 'vue';
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
  time: '10:00 AM',
  note: '',
  override_amount: null,
  override_reason: ''
});

const calculatedCharge = ref(0);
const loadingCharge = ref(false);
const showOverride = ref(false);

watch(() => [props.reservationId, props.unitId], () => {
  form.reservation_id = props.reservationId;
  form.unit_id = props.unitId;
}, { immediate: true });

let debounceTimer = null;
const fetchCharge = () => {
  if (!props.reservationId) return;
  
  if (debounceTimer) clearTimeout(debounceTimer);
  debounceTimer = setTimeout(async () => {
    loadingCharge.value = true;
    try {
      const res = await api.get('/stay-charge-configs/calculate', {
        params: {
          reservation_id: props.reservationId,
          time: form.time,
          type: 'early_checkin'
        }
      });
      calculatedCharge.value = res.data.amount;
    } catch (e) {
      calculatedCharge.value = 0;
    } finally {
      loadingCharge.value = false;
    }
  }, 500);
};

watch(() => [form.time, form.date], fetchCharge);
watch(() => props.modelValue, (val) => {
  if (val) fetchCharge();
});

const submit = () => {
  const data = { ...form };
  if (!showOverride.value) {
    delete data.override_amount;
    delete data.override_reason;
  }
  emit('submitted', data);
};
</script>
