<template>
  <div class="p-4 space-y-4">
    <div class="card p-4">
      <h1 class="text-xl font-bold">New Reservation</h1>
      <p class="text-xs text-slate-400">Home &gt; Reservations Management &gt; Add New Reservation</p>
    </div>

    <div class="card p-4">
      <ReservationStepper :steps="steps" :current="step" />
    </div>

    <div v-if="!completed" class="card p-4 space-y-4">
      <template v-if="currentKey === 'insurance'">
        <div class="grid md:grid-cols-3 gap-3">
          <div><label class="label">Type</label><input v-model="insurance.type" class="input"></div>
          <div><label class="label">Date</label><input v-model="insurance.date" type="date" class="input"></div>
          <div><label class="label">Visitor name</label><input v-model="insurance.visitor" class="input"></div>
          <div><label class="label">Recovery amount</label><input v-model="insurance.recovery_amount" type="number" class="input"></div>
          <div><label class="label">Recovery for</label><input v-model="insurance.recovery_for" class="input"></div>
          <div><label class="label">Reason</label><input v-model="insurance.reason" class="input"></div>
          <div><label class="label">Amount deducted</label><input v-model="insurance.amount_deducted" type="number" class="input"></div>
          <div><label class="label">Remaining amount</label><input v-model="insurance.remaining_amount" type="number" class="input"></div>
          <div><label class="label">Payment method</label><select v-model="insurance.payment_method" class="input"><option>Cash</option><option>POS</option></select></div>
        </div>
        <div><label class="label">Note</label><textarea v-model="insurance.note" class="input" rows="3" /></div>
      </template>

      <template v-if="currentKey === 'payment'">
        <div class="grid lg:grid-cols-[1fr,260px] gap-4">
          <div class="space-y-4">
            <div class="grid md:grid-cols-2 gap-3">
              <label class="card p-4 flex justify-between items-center"><span>Balance</span><input v-model="payment.balance" type="checkbox"></label>
              <label class="card p-4 flex justify-between items-center"><span>Insurance</span><input v-model="payment.insurance" type="checkbox"></label>
            </div>
            <div>
              <label class="label">Discount Amount</label>
              <input v-model="payment.discount" type="number" class="input">
            </div>
            <div>
              <label class="label">Select Payment Method</label>
              <div class="grid grid-cols-2 gap-2">
                <button class="card p-3" :class="payment.method==='Cash'?'ring-2 ring-rose-400':''" @click="payment.method='Cash'">Cash</button>
                <button class="card p-3" :class="payment.method==='POS'?'ring-2 ring-rose-400':''" @click="payment.method='POS'">POS</button>
              </div>
            </div>
          </div>
          <div class="card p-4 text-sm space-y-2">
            <p class="text-2xl font-bold text-rose-500">{{ paymentTotal }} SAR <span class="text-slate-400 text-sm">/ Total</span></p>
            <div class="flex justify-between"><span>Room charge</span><span>350 SAR</span></div>
            <div class="flex justify-between"><span>Cleaning</span><span>25.50 SAR</span></div>
            <div class="flex justify-between"><span>Service</span><span>35.00 SAR</span></div>
            <div class="flex justify-between"><span>Insurance</span><span>{{ payment.insurance ? '-1500' : '0' }} SAR</span></div>
            <div class="flex justify-between"><span>Balance</span><span>{{ payment.balance ? '-50' : '0' }} SAR</span></div>
          </div>
        </div>
      </template>

      <template v-if="currentKey === 'checkout'">
        <div class="grid md:grid-cols-2 gap-3">
          <div><label class="label">Date From</label><input v-model="checkout.date" type="date" class="input"></div>
          <div><label class="label">Choose Time</label><input v-model="checkout.time" type="time" class="input"></div>
        </div>
        <div><label class="label">Note</label><textarea v-model="checkout.note" class="input" rows="3" placeholder="Write any comment" /></div>
      </template>
    </div>

    <div v-else class="card p-8 text-center space-y-4">
      <img src="/assets/illustrations/success.svg" alt="success" class="w-64 mx-auto">
      <h2 class="text-4xl font-bold">Check Out Has Been Successfull</h2>
      <p class="text-slate-500">You Have Successfully Checked Out From The Room Number (504)</p>
      <div class="flex justify-center gap-3">
        <router-link to="/dashboard" class="btn-primary">Back To Home</router-link>
        <button class="btn-outline" @click="window.print()">Print Receipt</button>
      </div>
    </div>

    <div class="card p-3 flex justify-between">
      <button class="btn-outline" @click="saveDraft">Save draft</button>
      <div class="flex gap-2">
        <button class="btn-outline" :disabled="step===1" @click="step = Math.max(1, step - 1)">Back</button>
        <button class="btn-primary" @click="next">{{ step === steps.length ? 'Finish' : 'Next' }}</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import ReservationStepper from '../components/ReservationStepper.vue';

const props = defineProps({ mode: { type: String, default: 'insurance' } });

const workflows = {
  insurance: ['Insurance Recovery', 'Payment of indebtedness', 'Check-Out'],
  payment: ['Payment of indebtedness', 'Check-Out'],
  checkout: ['Insurance Recovery', 'Check-Out'],
};

const step = ref(1);
const completed = ref(false);
const steps = computed(() => workflows[props.mode] || workflows.insurance);

const insurance = ref({ type: 'Insurance Recovery', date: new Date().toISOString().slice(0, 10), visitor: 'Ahmed Muhamed', recovery_amount: 2000, recovery_for: 'Unit Number 404', reason: 'Cracking', amount_deducted: 500, remaining_amount: 1500, payment_method: 'Cash', note: '' });
const payment = ref({ balance: false, insurance: true, discount: 2, method: 'Cash' });
const checkout = ref({ date: new Date().toISOString().slice(0, 10), time: '12:30', note: '' });

const paymentTotal = computed(() => {
  const base = 410;
  const discount = Number(payment.value.discount || 0);
  const insuranceDiscount = payment.value.insurance ? 1500 : 0;
  const balanceDiscount = payment.value.balance ? 50 : 0;
  return Math.max(0, base - discount - insuranceDiscount - balanceDiscount).toFixed(2);
});

const currentKey = computed(() => {
  const label = steps.value[step.value - 1]?.toLowerCase() || '';
  if (label.includes('insurance')) return 'insurance';
  if (label.includes('payment')) return 'payment';
  return 'checkout';
});

const saveDraft = async () => {
  localStorage.setItem('operations-workflow-draft', JSON.stringify({ mode: props.mode, step: step.value, insurance: insurance.value, payment: payment.value, checkout: checkout.value }));
};

const next = async () => {
  await saveDraft();
  if (step.value < steps.value.length) {
    step.value += 1;
    return;
  }
  completed.value = true;
};
</script>
