<template>
  <div class="p-6 bg-slate-50 min-h-screen">
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-2xl font-bold text-slate-800">{{ $t('Commission Payments') }}</h1>
        <p class="text-slate-500 text-sm mt-1">{{ $t('Track and manage travel agent commissions') }}</p>
      </div>
      <div class="flex gap-3">
        <Link 
          :href="route('finance.commission-payments.create')"
          class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-all shadow-md font-medium"
        >
          <Plus class="w-4 h-4" />
          {{ $t('New Commission Payment') }}
        </Link>
      </div>
    </div>

    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-slate-50 border-b border-slate-200">
            <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $t('Payment #') }}</th>
            <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $t('Agent') }}</th>
            <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $t('Period') }}</th>
            <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $t('Amount') }}</th>
            <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $t('Status') }}</th>
            <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">{{ $t('Actions') }}</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <tr v-for="payment in payments.data" :key="payment.id" class="hover:bg-slate-50/50 transition-colors">
            <td class="px-6 py-4">
              <div class="font-medium text-slate-800">{{ payment.payment_number }}</div>
              <div class="text-xs text-slate-400">{{ formatDate(payment.payment_date) }}</div>
            </td>
            <td class="px-6 py-4">
              <div class="text-sm font-medium text-slate-700">{{ payment.travel_agent?.name || 'N/A' }}</div>
            </td>
            <td class="px-6 py-4">
              <div class="text-xs text-slate-600">
                {{ formatDate(payment.commission_period_from) }} - {{ formatDate(payment.commission_period_to) }}
              </div>
            </td>
            <td class="px-6 py-4">
              <div class="text-sm font-bold text-slate-800">{{ formatAmount(payment.total_paid) }}</div>
              <div v-if="payment.total_commission > payment.total_paid" class="text-[10px] text-rose-500">
                {{ $t('Total:') }} {{ formatAmount(payment.total_commission) }}
              </div>
            </td>
            <td class="px-6 py-4">
              <span class="px-2 py-1 rounded-full text-xs font-medium uppercase" :class="statusClass(payment.status)">
                {{ $t(payment.status) }}
              </span>
            </td>
            <td class="px-6 py-4 text-right">
              <div class="flex justify-end gap-2">
                <Link 
                  :href="route('finance.commission-payments.show', payment.id)"
                  class="p-1.5 text-slate-400 hover:text-primary transition-colors"
                >
                  <Eye class="w-4 h-4" />
                </Link>
                <button 
                  @click="deletePayment(payment.id)"
                  class="p-1.5 text-slate-400 hover:text-rose-600 transition-colors"
                >
                  <Trash2 class="w-4 h-4" />
                </button>
              </div>
            </td>
          </tr>
           <tr v-if="!payments || !payments.data || payments.data.length === 0">
            <td colspan="6" class="px-6 py-12 text-center text-slate-400">
              <div class="flex flex-col items-center gap-2">
                <PercentCircle class="w-12 h-12 text-slate-200" />
                <p>{{ $t('No commission payments found') }}</p>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3';
import { Plus, Eye, Trash2, PercentCircle } from 'lucide-vue-next';
import dayjs from 'dayjs';

const props = defineProps({
  payments: Object
});

function formatDate(date) {
  return dayjs(date).format('MMM DD, YYYY');
}

function formatAmount(amount) {
  return '﷼ ' + Number(amount).toLocaleString('en-US', { minimumFractionDigits: 2 });
}

function statusClass(status) {
  switch (status) {
    case 'paid': return 'bg-emerald-100 text-emerald-600';
    case 'pending': return 'bg-amber-100 text-amber-600';
    case 'partial': return 'bg-blue-100 text-blue-600';
    case 'cancelled': return 'bg-rose-100 text-rose-600';
    default: return 'bg-slate-100 text-slate-600';
  }
}

function deletePayment(id) {
  if (confirm('Are you sure you want to delete this payment record?')) {
    router.delete(route('finance.commission-payments.destroy', id));
  }
}
</script>

<style scoped>
.text-primary { color: #e95a54; }
.bg-primary { background-color: #e95a54; }
</style>
