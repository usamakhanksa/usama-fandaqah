<template>
  <div class="p-6 space-y-4">
    <div class="card p-4">
      <h1 class="text-2xl font-bold">Add {{ modeLabel }}</h1>
      <p class="text-xs text-slate-500">Home > Reservations Management > {{ modeLabel }} Management > Add New {{ modeLabel }}</p>
    </div>

    <div class="card p-5 space-y-4">
      <div class="flex items-center gap-3 text-sm justify-center">
        <div class="w-8 h-8 rounded-full flex items-center justify-center" :class="step>=1?'bg-rose-500 text-white':'bg-slate-200'">1</div>
        <div class="w-32 h-1" :class="step===2?'bg-rose-500':'bg-slate-200'"></div>
        <div class="w-8 h-8 rounded-full flex items-center justify-center" :class="step===2?'bg-rose-500 text-white':'bg-slate-200'">2</div>
      </div>
      <div v-if="step===1" class="grid md:grid-cols-3 gap-3">
        <div><label class="label">Amount</label><input v-model="form.amount" class="input" type="number"/></div>
        <div><label class="label">{{ modeLabel }} Type</label><input v-model="form.receipt_type" class="input"/></div>
        <div><label class="label">{{ modeLabel }} Code</label><input v-model="form.receipt_code" class="input"/></div>
        <div><label class="label">Reason</label><input v-model="form.reason" class="input"/></div>
        <div><label class="label">Date</label><input v-model="form.date" class="input" type="date"/></div>
        <div><label class="label">Payment Method</label><select v-model="form.payment_method" class="input"><option>Cash</option><option>Credit Card</option><option>Bank Transfer</option><option>Mada</option><option>Agal</option></select></div>
        <div><label class="label">Received By</label><input v-model="form.received_by" class="input"/></div>
      </div>
      <div v-else class="space-y-4">
        <div><label class="label">Attach a copy of the receipt</label><input type="file" @change="onFile" class="input"/></div>
        <div><label class="label">Note</label><textarea v-model="form.note" class="input h-32"></textarea></div>
      </div>
      <p v-if="error" class="text-sm text-rose-500">{{ error }}</p>
    </div>

    <div class="card p-4 flex justify-between">
      <button class="btn-outline" @click="saveDraft">Save draft</button>
      <div class="flex gap-2">
        <button v-if="step===2" class="btn-outline" @click="step=1">Back</button>
        <button class="btn-primary" @click="next">{{ step===1?'Next':'Confirm' }}</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '../services/api';

const route = useRoute();
const router = useRouter();
const mode = computed(() => route.path.includes('/expenses/') ? 'expense' : 'receipt');
const modeLabel = computed(() => mode.value === 'expense' ? 'Expense' : 'Receipt');
const step = ref(1);
const error = ref('');

const form = ref({ amount: 2000, receipt_type: '', receipt_code: '213124123412', reason: '', date: new Date().toISOString().slice(0, 10), payment_method: 'Cash', received_by: 'Ahmed Muhamed', attachment_path: '', note: '' });

const onFile = (e) => {
  const file = e.target.files?.[0];
  if (file) form.value.attachment_path = `/uploads/${file.name}`;
};

const saveDraft = async () => {
  await api.post(`/financial/${mode.value}/drafts`, { payload: form.value, current_step: step.value });
};

const next = async () => {
  error.value = '';
  if (!form.value.amount || !form.value.date || !form.value.payment_method) {
    error.value = 'Please fill required fields';
    return;
  }

  if (step.value === 1) {
    step.value = 2;
    return;
  }

  const res = await api.post(`/financial/${mode.value}/confirm`, form.value);
  router.push(`${mode.value === 'expense' ? '/financial/expenses' : '/financial/receipts'}/success/${res.data.id}`);
};
</script>
