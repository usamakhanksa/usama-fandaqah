<template>
  <div class="p-6 bg-slate-50 min-h-screen">
    <div class="max-w-6xl mx-auto">
      <div class="flex justify-between items-center mb-6">
        <div class="flex items-center gap-4">
          <Link :href="route('finance.payments.index')" class="p-2 bg-white border border-slate-200 rounded-lg text-slate-400 hover:text-slate-600 transition-all shadow-sm">
            <ArrowLeft class="w-5 h-5" />
          </Link>
          <div>
            <h1 class="text-2xl font-bold text-slate-800">{{ payment.payment_number }}</h1>
            <p class="text-slate-500 text-sm">{{ $t('Created on') }} {{ formatDate(payment.created_at) }}</p>
          </div>
        </div>
        <div class="flex gap-3">
          <button @click="printPayment" class="flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 rounded-lg text-slate-600 hover:bg-slate-50 transition-all shadow-sm">
            <Printer class="w-4 h-4" />
            {{ $t('Print Receipt') }}
          </button>
          
          <template v-if="payment.status === 'pending'">
            <button @click="confirmPayment" class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-all shadow-sm font-medium">
              {{ $t('Confirm Payment') }}
            </button>
            <button @click="showCancelModal = true" class="px-4 py-2 bg-rose-600 text-white rounded-lg hover:bg-rose-700 transition-all shadow-sm font-medium">
              {{ $t('Cancel') }}
            </button>
          </template>

          <button v-if="payment.status === 'confirmed'" @click="showReverseModal = true" class="px-4 py-2 bg-slate-800 text-white rounded-lg hover:bg-slate-900 transition-all shadow-sm font-medium">
            {{ $t('Reverse Payment') }}
          </button>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Payment Details -->
        <div class="lg:col-span-2 space-y-6">
          <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
              <h2 class="font-bold text-slate-800 uppercase tracking-wider text-sm">{{ $t('Payment Information') }}</h2>
              <span class="px-3 py-1 rounded-full text-xs font-bold uppercase" :class="statusClass(payment.status)">
                {{ payment.status }}
              </span>
            </div>
            <div class="p-6 grid grid-cols-2 gap-8">
              <div class="space-y-4">
                <div>
                  <p class="text-[10px] font-bold text-slate-400 uppercase">{{ $t('Amount') }}</p>
                  <p class="text-2xl font-black text-slate-800">{{ formatAmount(payment.amount, payment.currency) }}</p>
                  <p v-if="payment.currency !== 'SAR'" class="text-xs text-slate-500 font-medium">
                    ≈ {{ formatAmount(payment.amount * payment.exchange_rate, 'SAR') }} (Rate: {{ payment.exchange_rate }})
                  </p>
                </div>
                <div>
                  <p class="text-[10px] font-bold text-slate-400 uppercase">{{ $t('Method') }}</p>
                  <p class="font-bold text-slate-700 uppercase">{{ payment.payment_method }}</p>
                </div>
                <div>
                  <p class="text-[10px] font-bold text-slate-400 uppercase">{{ $t('Type') }}</p>
                  <p class="font-bold text-slate-700 uppercase">{{ payment.payment_type }}</p>
                </div>
              </div>
              <div class="space-y-4">
                <div>
                  <p class="text-[10px] font-bold text-slate-400 uppercase">{{ $t('Date') }}</p>
                  <p class="font-bold text-slate-700">{{ formatDate(payment.payment_date) }}</p>
                </div>
                <div>
                  <p class="text-[10px] font-bold text-slate-400 uppercase">{{ $t('Reference') }}</p>
                  <p class="font-bold text-slate-700">{{ payment.reference_number || '-' }}</p>
                </div>
                <div v-if="payment.card_last_four">
                  <p class="text-[10px] font-bold text-slate-400 uppercase">{{ $t('Card Info') }}</p>
                  <p class="font-bold text-slate-700">**** **** **** {{ payment.card_last_four }}</p>
                  <p v-if="payment.card_authorization" class="text-xs text-slate-500">Auth: {{ payment.card_authorization }}</p>
                </div>
              </div>
            </div>
            <div v-if="payment.notes" class="p-6 bg-slate-50 border-t border-slate-100">
              <p class="text-[10px] font-bold text-slate-400 uppercase mb-2">{{ $t('Notes') }}</p>
              <p class="text-sm text-slate-600 leading-relaxed">{{ payment.notes }}</p>
            </div>
          </div>

          <!-- Transaction Info -->
          <div v-if="payment.transaction" class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-100 bg-slate-50/50">
              <h2 class="font-bold text-slate-800 uppercase tracking-wider text-sm">{{ $t('Linked Ledger Entry') }}</h2>
            </div>
            <div class="p-6">
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                  <div class="p-3 bg-blue-50 rounded-xl">
                    <FileText class="w-6 h-6 text-blue-600" />
                  </div>
                  <div>
                    <p class="font-bold text-slate-800">{{ payment.transaction.transaction_number || 'TXN-' + payment.transaction.id }}</p>
                    <p class="text-xs text-slate-500">{{ formatDate(payment.transaction.transaction_date) }}</p>
                  </div>
                </div>
                <div class="text-right">
                  <p class="font-bold" :class="payment.transaction.type === 'credit' ? 'text-emerald-600' : 'text-rose-600'">
                    {{ payment.transaction.type === 'credit' ? '+' : '-' }}{{ formatAmount(payment.transaction.amount, payment.transaction.currency) }}
                  </p>
                  <p class="text-[10px] font-bold text-slate-400 uppercase">{{ payment.transaction.category }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar Cards -->
        <div class="space-y-6">
          <!-- Reservation Card -->
          <div v-if="payment.reservation" class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden p-6 space-y-4">
            <div class="flex justify-between items-start">
              <h3 class="font-bold text-slate-800">{{ $t('Reservation') }}</h3>
              <Link :href="route('reservations.show', payment.reservation.id)" class="text-primary hover:underline text-xs font-bold uppercase">
                {{ $t('View Details') }}
              </Link>
            </div>
            <div class="space-y-3">
              <div class="flex justify-between text-sm">
                <span class="text-slate-500">{{ $t('Res #') }}</span>
                <span class="font-bold text-slate-700">{{ payment.reservation.reservation_number }}</span>
              </div>
              <div class="flex justify-between text-sm">
                <span class="text-slate-500">{{ $t('Status') }}</span>
                <span class="px-2 py-0.5 bg-slate-100 rounded text-[10px] font-bold uppercase text-slate-600">{{ payment.reservation.status }}</span>
              </div>
            </div>
          </div>

          <!-- Guest/Company Card -->
          <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden p-6 space-y-4">
            <h3 class="font-bold text-slate-800">{{ payment.company_id ? $t('Company') : $t('Guest') }}</h3>
            <div class="flex items-center gap-4">
              <div class="w-12 h-12 bg-slate-100 rounded-full flex items-center justify-center text-slate-400 font-bold">
                {{ (payment.guest?.name || payment.company?.name || '?').charAt(0) }}
              </div>
              <div>
                <p class="font-bold text-slate-800">{{ payment.guest?.name || payment.company?.name }}</p>
                <p class="text-xs text-slate-500">{{ payment.guest?.phone || payment.company?.email }}</p>
              </div>
            </div>
          </div>

          <!-- Timeline -->
          <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden p-6">
            <h3 class="font-bold text-slate-800 mb-6">{{ $t('Activity Log') }}</h3>
            <div class="space-y-6 relative before:absolute before:left-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-100">
              <div class="relative pl-8">
                <div class="absolute left-0 top-1 w-4 h-4 bg-primary rounded-full border-2 border-white shadow-sm ring-4 ring-primary/10"></div>
                <p class="text-sm font-bold text-slate-800">{{ $t('Payment Created') }}</p>
                <p class="text-[10px] text-slate-500">{{ formatDate(payment.created_at, true) }} by {{ payment.created_by?.name }}</p>
              </div>
              
              <div v-if="payment.confirmed_at" class="relative pl-8">
                <div class="absolute left-0 top-1 w-4 h-4 bg-emerald-500 rounded-full border-2 border-white shadow-sm ring-4 ring-emerald-500/10"></div>
                <p class="text-sm font-bold text-slate-800">{{ $t('Payment Confirmed') }}</p>
                <p class="text-[10px] text-slate-500">{{ formatDate(payment.confirmed_at, true) }} by {{ payment.confirmed_by?.name }}</p>
              </div>

              <div v-if="payment.cancelled_at" class="relative pl-8">
                <div class="absolute left-0 top-1 w-4 h-4 bg-rose-500 rounded-full border-2 border-white shadow-sm ring-4 ring-rose-500/10"></div>
                <p class="text-sm font-bold text-slate-800">{{ payment.status === 'reversed' ? $t('Payment Reversed') : $t('Payment Cancelled') }}</p>
                <p class="text-[10px] text-slate-500">{{ formatDate(payment.cancelled_at, true) }} by {{ payment.cancelled_by?.name }}</p>
                <p v-if="payment.cancellation_reason" class="mt-2 p-2 bg-rose-50 rounded text-xs text-rose-700 italic border border-rose-100">"{{ payment.cancellation_reason }}"</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modals -->
    <Modal :show="showCancelModal" @close="showCancelModal = false">
      <div class="p-6">
        <h2 class="text-xl font-bold text-slate-800 mb-2">{{ $t('Cancel Payment') }}</h2>
        <p class="text-slate-500 mb-4">{{ $t('Please provide a reason for cancelling this payment.') }}</p>
        <textarea v-model="actionReason" rows="3" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl mb-4"></textarea>
        <div class="flex justify-end gap-3">
          <button @click="showCancelModal = false" class="px-4 py-2 text-slate-600 font-bold">{{ $t('Keep Payment') }}</button>
          <button @click="handleAction('cancel')" :disabled="!actionReason" class="px-6 py-2 bg-rose-600 text-white rounded-lg font-bold disabled:opacity-50">
            {{ $t('Confirm Cancellation') }}
          </button>
        </div>
      </div>
    </Modal>

    <Modal :show="showReverseModal" @close="showReverseModal = false">
      <div class="p-6">
        <h2 class="text-xl font-bold text-slate-800 mb-2">{{ $t('Reverse Payment') }}</h2>
        <p class="text-slate-500 mb-4">{{ $t('This will create a reversal transaction and mark the payment as reversed.') }}</p>
        <textarea v-model="actionReason" rows="3" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl mb-4" placeholder="Enter reversal reason..."></textarea>
        <div class="flex justify-end gap-3">
          <button @click="showReverseModal = false" class="px-4 py-2 text-slate-600 font-bold">{{ $t('Close') }}</button>
          <button @click="handleAction('reverse')" :disabled="!actionReason" class="px-6 py-2 bg-slate-800 text-white rounded-lg font-bold disabled:opacity-50">
            {{ $t('Confirm Reversal') }}
          </button>
        </div>
      </div>
    </Modal>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { ArrowLeft, Printer, FileText, CheckCircle } from 'lucide-vue-next';
import Modal from '@/Components/Modal.vue';
import dayjs from 'dayjs';

const props = defineProps({
  payment: Object,
});

const showCancelModal = ref(false);
const showReverseModal = ref(false);
const actionReason = ref('');

function formatDate(date, withTime = false) {
  if (!date) return '-';
  return dayjs(date).format(withTime ? 'DD MMM YYYY, HH:mm' : 'DD MMM YYYY');
}

function formatAmount(amount, currency = 'SAR') {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: currency,
  }).format(amount);
}

function statusClass(status) {
  const classes = {
    pending: 'bg-amber-100 text-amber-700',
    confirmed: 'bg-emerald-100 text-emerald-700',
    cancelled: 'bg-rose-100 text-rose-700',
    reversed: 'bg-slate-800 text-white',
    refunded: 'bg-blue-100 text-blue-700',
  };
  return classes[status] || 'bg-slate-100 text-slate-700';
}

function confirmPayment() {
  if (confirm('Are you sure you want to confirm this payment?')) {
    router.post(route('finance.payments.confirm', props.payment.id));
  }
}

function handleAction(action) {
  router.post(route(`finance.payments.${action}`, props.payment.id), {
    reason: actionReason.value
  }, {
    onSuccess: () => {
      showCancelModal.value = false;
      showReverseModal.value = false;
      actionReason.value = '';
    }
  });
}

function printPayment() {
  window.open(route('finance.payments.print', props.payment.id), '_blank');
}
</script>

<style scoped>
.text-primary { color: #e95a54; }
.bg-primary { background-color: #e95a54; }
</style>
