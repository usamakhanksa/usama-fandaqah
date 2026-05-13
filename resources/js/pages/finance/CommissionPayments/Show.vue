<template>
  <div class="p-6 bg-slate-50 min-h-screen">
    <div class="max-w-5xl mx-auto">
      <div class="flex items-center gap-4 mb-6">
        <Link :href="route('finance.commission-payments.index')" class="p-2 bg-white border border-slate-200 rounded-lg text-slate-400 hover:text-slate-600 transition-all">
          <ArrowLeft class="w-5 h-5" />
        </Link>
        <div class="flex-1">
          <h1 class="text-2xl font-bold text-slate-800">{{ payment.payment_number }}</h1>
          <p class="text-slate-500 text-sm">{{ $t('Commission Payment Detail') }}</p>
        </div>
        <div class="flex gap-2">
          <button class="px-4 py-2 bg-white border border-slate-200 rounded-lg text-slate-600 hover:bg-slate-50 transition-all flex items-center gap-2">
            <Printer class="w-4 h-4" />
            {{ $t('Print') }}
          </button>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Info Panel -->
        <div class="lg:col-span-1 space-y-6">
          <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
            <h3 class="font-bold text-slate-800 mb-4 border-b border-slate-100 pb-2">{{ $t('General Info') }}</h3>
            <div class="space-y-4">
              <div>
                <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">{{ $t('Agent') }}</p>
                <p class="text-slate-800 font-medium">{{ payment.travel_agent?.name || 'N/A' }}</p>
              </div>
              <div>
                <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">{{ $t('Period') }}</p>
                <p class="text-slate-800 font-medium">{{ formatDate(payment.commission_period_from) }} - {{ formatDate(payment.commission_period_to) }}</p>
              </div>
              <div>
                <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">{{ $t('Payment Date') }}</p>
                <p class="text-slate-800 font-medium">{{ formatDate(payment.payment_date) }}</p>
              </div>
              <div>
                <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">{{ $t('Status') }}</p>
                <span class="px-2 py-1 rounded-full text-xs font-medium uppercase" :class="statusClass(payment.status)">
                  {{ $t(payment.status) }}
                </span>
              </div>
            </div>
          </div>

          <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
            <h3 class="font-bold text-slate-800 mb-4 border-b border-slate-100 pb-2">{{ $t('Payment Info') }}</h3>
            <div class="space-y-4">
              <div>
                <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">{{ $t('Method') }}</p>
                <p class="text-slate-800 font-medium">{{ $t(payment.payment_method) }}</p>
              </div>
              <div v-if="payment.bank">
                <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">{{ $t('Bank') }}</p>
                <p class="text-slate-800 font-medium">{{ payment.bank.name }}</p>
              </div>
              <div>
                <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">{{ $t('Reference #') }}</p>
                <p class="text-slate-800 font-medium">{{ payment.reference_number || '-' }}</p>
              </div>
              <div class="pt-2 border-t border-slate-100">
                <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">{{ $t('Total Paid') }}</p>
                <p class="text-2xl font-bold text-emerald-600">{{ formatAmount(payment.total_paid) }}</p>
                <p v-if="payment.total_commission > payment.total_paid" class="text-xs text-rose-500 mt-1">
                  {{ $t('Shortfall:') }} {{ formatAmount(payment.total_commission - payment.total_paid) }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Details Panel -->
        <div class="lg:col-span-2">
          <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-100 bg-slate-50/50">
              <h3 class="font-bold text-slate-800">{{ $t('Included Reservations') }}</h3>
            </div>
            <div class="overflow-x-auto">
              <table class="w-full text-left text-sm">
                <thead>
                  <tr class="text-slate-500 font-semibold border-b border-slate-100">
                    <th class="px-6 py-4">{{ $t('Res Code') }}</th>
                    <th class="px-6 py-4">{{ $t('Guest') }}</th>
                    <th class="px-6 py-4 text-right">{{ $t('Room Revenue') }}</th>
                    <th class="px-6 py-4 text-right">{{ $t('Rate') }}</th>
                    <th class="px-6 py-4 text-right">{{ $t('Commission') }}</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                  <tr v-for="detail in payment.details" :key="detail.id" class="hover:bg-slate-50 transition-colors">
                    <td class="px-6 py-4 font-medium text-slate-700">{{ detail.reservation?.code || 'N/A' }}</td>
                    <td class="px-6 py-4 text-slate-600">{{ detail.reservation?.guest?.name || 'N/A' }}</td>
                    <td class="px-6 py-4 text-right font-mono">{{ formatAmount(detail.room_revenue) }}</td>
                    <td class="px-6 py-4 text-right text-slate-400">{{ detail.commission_rate }}%</td>
                    <td class="px-6 py-4 text-right font-bold text-slate-800">{{ formatAmount(detail.commission_amount) }}</td>
                  </tr>
                </tbody>
                <tfoot class="bg-slate-50 font-bold border-t border-slate-200">
                  <tr>
                    <td colspan="2" class="px-6 py-4 text-slate-800 text-right">{{ $t('Totals') }}</td>
                    <td class="px-6 py-4 text-right font-mono">{{ formatAmount(totalRevenue) }}</td>
                    <td></td>
                    <td class="px-6 py-4 text-right text-emerald-600">{{ formatAmount(payment.total_commission) }}</td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>

          <div v-if="payment.notes" class="mt-6 bg-white border border-slate-200 rounded-xl shadow-sm p-6">
            <h3 class="font-bold text-slate-800 mb-2">{{ $t('Notes') }}</h3>
            <p class="text-slate-600 whitespace-pre-wrap">{{ payment.notes }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { ArrowLeft, Printer } from 'lucide-vue-next';
import dayjs from 'dayjs';

const props = defineProps({
  payment: Object
});

const totalRevenue = computed(() => {
  return props.payment.details.reduce((sum, d) => sum + Number(d.room_revenue), 0);
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
</script>

<style scoped>
.text-primary { color: #e95a54; }
.bg-primary { background-color: #e95a54; }
</style>
