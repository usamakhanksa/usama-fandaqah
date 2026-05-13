<template>
  <div class="p-6 bg-slate-50 min-h-screen">
    <div class="max-w-4xl mx-auto">
      <div class="flex items-center gap-4 mb-6">
        <Link :href="route('finance.payments.index')" class="p-2 bg-white border border-slate-200 rounded-lg text-slate-400 hover:text-slate-600 transition-all shadow-sm">
          <ArrowLeft class="w-5 h-5" />
        </Link>
        <div>
          <h1 class="text-2xl font-bold text-slate-800">{{ $t('Edit Payment') }}</h1>
          <p class="text-slate-500 text-sm mt-1">{{ payment.payment_number }}</p>
        </div>
      </div>

      <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <form @submit.prevent="submit">
          <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Left Column: Basic Info -->
            <div class="space-y-6">
              <div class="p-4 bg-slate-50 rounded-xl border border-slate-100">
                <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">{{ $t('Paying for') }}</p>
                <p class="font-bold text-slate-700">
                  {{ payment.guest?.name || payment.company?.name || 'N/A' }}
                </p>
                <p v-if="payment.reservation" class="text-xs text-slate-500">
                  {{ $t('Reservation') }}: {{ payment.reservation.reservation_number }}
                </p>
              </div>

              <div class="space-y-2">
                <label class="block text-sm font-bold text-slate-700 uppercase tracking-wider">{{ $t('Payment Type') }}</label>
                <select v-model="form.payment_type" class="w-full px-4 py-3 bg-slate-50 border-none rounded-xl focus:ring-2 focus:ring-primary/20">
                  <option v-for="type in paymentTypes" :key="type" :value="type">
                    {{ type.charAt(0).toUpperCase() + type.slice(1).replace('_', ' ') }}
                  </option>
                </select>
              </div>

              <div class="space-y-2">
                <label class="block text-sm font-bold text-slate-700 uppercase tracking-wider">{{ $t('Amount') }}</label>
                <div class="relative">
                  <span class="absolute left-4 top-1/2 -translate-y-1/2 font-bold text-slate-400">{{ form.currency }}</span>
                  <input 
                    v-model="form.amount" 
                    type="number" step="0.01" 
                    class="w-full pl-16 pr-4 py-4 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-primary/20 transition-all text-xl font-bold text-slate-800"
                  >
                </div>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                  <label class="block text-sm font-bold text-slate-700 uppercase tracking-wider">{{ $t('Currency') }}</label>
                  <select v-model="form.currency" class="w-full px-4 py-3 bg-slate-50 border-none rounded-xl focus:ring-2 focus:ring-primary/20">
                    <option value="SAR">SAR</option>
                    <option value="USD">USD</option>
                    <option value="EUR">EUR</option>
                  </select>
                </div>
                <div class="space-y-2" v-if="form.currency !== 'SAR'">
                  <label class="block text-sm font-bold text-slate-700 uppercase tracking-wider">{{ $t('Exchange Rate') }}</label>
                  <input v-model="form.exchange_rate" type="number" step="0.0001" class="w-full px-4 py-3 bg-slate-50 border-none rounded-xl focus:ring-2 focus:ring-primary/20 font-bold">
                </div>
              </div>
            </div>

            <!-- Right Column: Method & Specifics -->
            <div class="space-y-6">
              <div class="space-y-2">
                <label class="block text-sm font-bold text-slate-700 uppercase tracking-wider">{{ $t('Payment Method') }}</label>
                <select v-model="form.payment_method" class="w-full px-4 py-3 bg-slate-50 border-none rounded-xl focus:ring-2 focus:ring-primary/20">
                  <option v-for="method in paymentMethods" :key="method" :value="method">
                    {{ method.charAt(0).toUpperCase() + method.slice(1).replace('_', ' ') }}
                  </option>
                </select>
              </div>

              <div class="space-y-2">
                <label class="block text-sm font-bold text-slate-700 uppercase tracking-wider">{{ $t('Reference Number') }}</label>
                <input v-model="form.reference_number" type="text" class="w-full px-4 py-3 bg-slate-50 border-none rounded-xl focus:ring-2 focus:ring-primary/20">
              </div>

              <div class="space-y-2">
                <label class="block text-sm font-bold text-slate-700 uppercase tracking-wider">{{ $t('Notes') }}</label>
                <textarea v-model="form.notes" rows="4" class="w-full px-4 py-3 bg-slate-50 border-none rounded-xl focus:ring-2 focus:ring-primary/20" :placeholder="$t('Add any internal notes...')"></textarea>
              </div>
            </div>
          </div>

          <div class="p-8 border-t border-slate-100 flex justify-end bg-slate-50/50">
            <button 
              type="submit"
              :disabled="form.processing"
              class="px-12 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-all shadow-lg shadow-primary/20 font-bold disabled:opacity-50"
            >
              {{ $t('Update Payment') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';

const props = defineProps({
  payment: Object,
});

const form = useForm({
  amount: props.payment.amount,
  payment_date: props.payment.payment_date,
  payment_method: props.payment.payment_method,
  payment_type: props.payment.payment_type,
  reference_number: props.payment.reference_number || '',
  notes: props.payment.notes || '',
  currency: props.payment.currency,
  exchange_rate: props.payment.exchange_rate,
});

const paymentMethods = ['cash', 'visa', 'mastercard', 'mada', 'apple_pay', 'bank_transfer', 'cheque', 'online', 'other'];
const paymentTypes = ['payment', 'deposit', 'partial_payment', 'advance', 'refund', 'adjustment'];

function submit() {
  form.put(route('finance.payments.update', props.payment.id));
}
</script>

<style scoped>
.text-primary { color: #e95a54; }
.bg-primary { background-color: #e95a54; }
</style>
