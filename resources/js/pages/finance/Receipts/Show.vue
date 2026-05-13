<template>
  <div class="p-6 bg-slate-50 min-h-screen">
    <div class="max-w-5xl mx-auto">
      <!-- Header -->
      <div class="flex justify-between items-start mb-6">
        <div class="flex items-center gap-4">
          <Link 
            :href="route('finance.receipts.index')"
            class="p-2 bg-white border border-slate-200 rounded-lg text-slate-400 hover:text-slate-600 transition-all shadow-sm"
          >
            <ArrowLeft class="w-5 h-5" />
          </Link>
          <div>
            <div class="flex items-center gap-3">
              <h1 class="text-2xl font-bold text-slate-800">{{ receipt.receipt_number }}</h1>
              <span 
                class="px-3 py-1 rounded-full text-sm font-medium"
                :class="statusClass(receipt.status)"
              >
                {{ receipt.status }}
              </span>
            </div>
            <p class="text-slate-500 text-sm mt-1">
              {{ $t('Created') }} {{ formatDate(receipt.created_at) }} {{ $t('by') }} {{ receipt.created_by?.name }}
            </p>
          </div>
        </div>
        <div class="flex gap-3">
          <button 
            v-if="canPrint"
            @click="printReceipt"
            class="flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 rounded-lg text-slate-600 hover:bg-slate-50 transition-all shadow-sm"
          >
            <Printer class="w-4 h-4" />
            {{ $t('Print') }}
          </button>
          <Link 
            v-if="canEdit && receipt.status === 'draft'"
            :href="route('finance.receipts.edit', receipt.id)"
            class="flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 rounded-lg text-slate-600 hover:bg-slate-50 transition-all shadow-sm"
          >
            <Pencil class="w-4 h-4" />
            {{ $t('Edit') }}
          </Link>
          <button 
            v-if="canCancel && receipt.status !== 'cancelled'"
            @click="showCancelModal = true"
            class="flex items-center gap-2 px-4 py-2 bg-rose-50 border border-rose-200 rounded-lg text-rose-600 hover:bg-rose-100 transition-all shadow-sm"
          >
            <XCircle class="w-4 h-4" />
            {{ $t('Cancel') }}
          </button>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column - Main Info -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Receipt Details -->
          <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-800 mb-4 flex items-center gap-2">
              <Receipt class="w-5 h-5 text-primary" />
              {{ $t('Receipt Details') }}
            </h2>

            <div class="grid grid-cols-2 gap-6">
              <div>
                <label class="text-sm text-slate-500">{{ $t('Receipt Number') }}</label>
                <p class="font-medium text-slate-800">{{ receipt.receipt_number }}</p>
              </div>
              <div>
                <label class="text-sm text-slate-500">{{ $t('Receipt Date') }}</label>
                <p class="font-medium text-slate-800">{{ formatDate(receipt.receipt_date) }}</p>
              </div>
              <div>
                <label class="text-sm text-slate-500">{{ $t('Payment Method') }}</label>
                <p class="font-medium text-slate-800">{{ paymentMethods[receipt.payment_method] }}</p>
              </div>
              <div>
                <label class="text-sm text-slate-500">{{ $t('Status') }}</label>
                <p class="font-medium">
                  <span :class="statusClass(receipt.status)">{{ receipt.status }}</span>
                </p>
              </div>
            </div>

            <hr class="my-4 border-slate-100">

            <div class="grid grid-cols-2 gap-6">
              <div>
                <label class="text-sm text-slate-500">{{ $t('Amount') }}</label>
                <p class="text-2xl font-bold text-emerald-600">
                  {{ formatAmount(receipt.amount, receipt.currency) }}
                </p>
                <p v-if="receipt.currency !== 'SAR'" class="text-sm text-slate-400">
                  {{ $t('SAR Equivalent') }}: {{ formatAmount(receipt.sar_equivalent, 'SAR') }}
                </p>
              </div>
              <div>
                <label class="text-sm text-slate-500">{{ $t('Exchange Rate') }}</label>
                <p class="font-medium text-slate-800">{{ receipt.exchange_rate }}</p>
              </div>
            </div>

            <!-- Conditional Fields -->
            <template v-if="receipt.payment_method === 'card' && receipt.card_last_four">
              <hr class="my-4 border-slate-100">
              <div>
                <label class="text-sm text-slate-500">{{ $t('Card Last Four') }}</label>
                <p class="font-medium text-slate-800">**** {{ receipt.card_last_four }}</p>
              </div>
            </template>

            <template v-if="receipt.payment_method === 'cheque'">
              <hr class="my-4 border-slate-100">
              <div class="grid grid-cols-2 gap-6">
                <div>
                  <label class="text-sm text-slate-500">{{ $t('Cheque Number') }}</label>
                  <p class="font-medium text-slate-800">{{ receipt.cheque_number || '-' }}</p>
                </div>
                <div>
                  <label class="text-sm text-slate-500">{{ $t('Bank Name') }}</label>
                  <p class="font-medium text-slate-800">{{ receipt.bank_name || '-' }}</p>
                </div>
              </div>
            </template>

            <template v-if="receipt.payment_method === 'bank_transfer' && receipt.reference_number">
              <hr class="my-4 border-slate-100">
              <div>
                <label class="text-sm text-slate-500">{{ $t('Reference Number') }}</label>
                <p class="font-medium text-slate-800">{{ receipt.reference_number }}</p>
              </div>
            </template>

            <hr class="my-4 border-slate-100">

            <div v-if="receipt.description">
              <label class="text-sm text-slate-500">{{ $t('Description') }}</label>
              <p class="text-slate-800">{{ receipt.description }}</p>
            </div>

            <div v-if="receipt.notes" class="mt-4">
              <label class="text-sm text-slate-500">{{ $t('Internal Notes') }}</label>
              <p class="text-slate-600 text-sm">{{ receipt.notes }}</p>
            </div>
          </div>

          <!-- Guest/Company Info -->
          <div v-if="receipt.guest || receipt.company" class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-800 mb-4 flex items-center gap-2">
              <User class="w-5 h-5 text-primary" />
              {{ $t('Customer Information') }}
            </h2>

            <div v-if="receipt.guest" class="grid grid-cols-2 gap-6">
              <div>
                <label class="text-sm text-slate-500">{{ $t('Guest Name') }}</label>
                <p class="font-medium text-slate-800">{{ receipt.guest.name }}</p>
              </div>
              <div v-if="receipt.guest.phone">
                <label class="text-sm text-slate-500">{{ $t('Phone') }}</label>
                <p class="font-medium text-slate-800">{{ receipt.guest.phone }}</p>
              </div>
              <div v-if="receipt.guest.email">
                <label class="text-sm text-slate-500">{{ $t('Email') }}</label>
                <p class="font-medium text-slate-800">{{ receipt.guest.email }}</p>
              </div>
            </div>

            <div v-else-if="receipt.company" class="grid grid-cols-2 gap-6">
              <div>
                <label class="text-sm text-slate-500">{{ $t('Company Name') }}</label>
                <p class="font-medium text-slate-800">{{ receipt.company.name }}</p>
              </div>
              <div v-if="receipt.company.tax_number">
                <label class="text-sm text-slate-500">{{ $t('Tax Number') }}</label>
                <p class="font-medium text-slate-800">{{ receipt.company.tax_number }}</p>
              </div>
            </div>
          </div>

          <!-- Reservation Info -->
          <div v-if="receipt.reservation" class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-800 mb-4 flex items-center gap-2">
              <Calendar class="w-5 h-5 text-primary" />
              {{ $t('Reservation Information') }}
            </h2>

            <div class="grid grid-cols-2 gap-6">
              <div>
                <label class="text-sm text-slate-500">{{ $t('Reservation Code') }}</label>
                <Link 
                  :href="route('reservations.show', receipt.reservation.id)"
                  class="font-medium text-primary hover:underline"
                >
                  {{ receipt.reservation.code }}
                </Link>
              </div>
              <div>
                <label class="text-sm text-slate-500">{{ $t('Status') }}</label>
                <p class="font-medium text-slate-800">{{ receipt.reservation.status }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Right Column - Sidebar -->
        <div class="space-y-6">
          <!-- Actions Card -->
          <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-800 mb-4">{{ $t('Actions') }}</h2>
            <div class="space-y-2">
              <button 
                v-if="canPrint"
                @click="printReceipt"
                class="w-full flex items-center gap-2 px-4 py-2 border border-slate-200 rounded-lg text-slate-600 hover:bg-slate-50"
              >
                <Printer class="w-4 h-4" />
                {{ $t('Print Receipt') }}
              </button>
              <button 
                v-if="receipt.status === 'draft'"
                @click="confirmReceipt"
                class="w-full flex items-center gap-2 px-4 py-2 bg-emerald-50 border border-emerald-200 rounded-lg text-emerald-600 hover:bg-emerald-100"
              >
                <CheckCircle class="w-4 h-4" />
                {{ $t('Confirm Receipt') }}
              </button>
              <button 
                v-if="receipt.status === 'confirmed'"
                @click="createCreditNote"
                class="w-full flex items-center gap-2 px-4 py-2 bg-blue-50 border border-blue-200 rounded-lg text-blue-600 hover:bg-blue-100"
              >
                <FileText class="w-4 h-4" />
                {{ $t('Create Credit Note') }}
              </button>
            </div>
          </div>

          <!-- Cancellation Info -->
          <div v-if="receipt.status === 'cancelled'" class="bg-rose-50 border border-rose-200 rounded-xl p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-rose-800 mb-4 flex items-center gap-2">
              <AlertCircle class="w-5 h-5" />
              {{ $t('Cancellation Details') }}
            </h2>
            <div class="space-y-3">
              <div>
                <label class="text-sm text-rose-600">{{ $t('Cancelled At') }}</label>
                <p class="font-medium text-rose-800">{{ formatDate(receipt.cancelled_at) }}</p>
              </div>
              <div>
                <label class="text-sm text-rose-600">{{ $t('Cancelled By') }}</label>
                <p class="font-medium text-rose-800">{{ receipt.cancelled_by?.name }}</p>
              </div>
              <div>
                <label class="text-sm text-rose-600">{{ $t('Reason') }}</label>
                <p class="text-rose-800">{{ receipt.cancellation_reason }}</p>
              </div>
            </div>
          </div>

          <!-- Audit Trail -->
          <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-800 mb-4">{{ $t('Audit Trail') }}</h2>
            <div class="space-y-3 text-sm">
              <div class="flex items-start gap-2">
                <UserPlus class="w-4 h-4 text-slate-400 mt-0.5" />
                <div>
                  <p class="text-slate-800">{{ $t('Created by') }} {{ receipt.created_by?.name }}</p>
                  <p class="text-slate-400">{{ formatDateTime(receipt.created_at) }}</p>
                </div>
              </div>
              <div v-if="receipt.updated_by" class="flex items-start gap-2">
                <RefreshCw class="w-4 h-4 text-slate-400 mt-0.5" />
                <div>
                  <p class="text-slate-800">{{ $t('Updated by') }} {{ receipt.updated_by?.name }}</p>
                  <p class="text-slate-400">{{ formatDateTime(receipt.updated_at) }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Cancel Modal -->
      <Modal :show="showCancelModal" @close="showCancelModal = false">
        <div class="p-6">
          <h3 class="text-lg font-semibold text-slate-800 mb-4">{{ $t('Cancel Receipt') }}</h3>
          <p class="text-slate-500 mb-4">
            {{ $t('Are you sure you want to cancel receipt') }} 
            <strong>{{ receipt.receipt_number }}</strong>?
          </p>
          <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ $t('Cancellation Reason') }} *</label>
            <textarea 
              v-model="cancelReason"
              rows="3"
              class="w-full px-4 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-primary/20"
              :placeholder="$t('Enter reason for cancellation...')"
            ></textarea>
          </div>
          <div class="flex justify-end gap-3">
            <button 
              @click="showCancelModal = false"
              class="px-4 py-2 border border-slate-200 rounded-lg text-slate-600 hover:bg-slate-50"
            >
              {{ $t('Cancel') }}
            </button>
            <button 
              @click="submitCancel"
              :disabled="!cancelReason"
              class="px-4 py-2 bg-rose-600 text-white rounded-lg hover:bg-rose-700 disabled:opacity-50"
            >
              {{ $t('Confirm Cancel') }}
            </button>
          </div>
        </div>
      </Modal>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { 
  ArrowLeft, Receipt, Printer, Pencil, XCircle, User, Calendar,
  CheckCircle, FileText, AlertCircle, UserPlus, RefreshCw
} from 'lucide-vue-next';
import Modal from '@/Components/Modal.vue';
import dayjs from 'dayjs';

const props = defineProps({
  receipt: Object,
  canEdit: Boolean,
  canDelete: Boolean,
  canCancel: Boolean,
  canPrint: Boolean,
});

const showCancelModal = ref(false);
const cancelReason = ref('');

const paymentMethods = {
  cash: 'Cash',
  card: 'Card',
  bank_transfer: 'Bank Transfer',
  cheque: 'Cheque',
  online: 'Online',
  other: 'Other',
};

function formatDate(date) {
  return dayjs(date).format('MMM DD, YYYY');
}

function formatDateTime(date) {
  return dayjs(date).format('MMM DD, YYYY HH:mm');
}

function formatAmount(amount, currency = 'SAR') {
  if (!amount) return '-';
  const symbol = currency === 'SAR' ? '﷼' : '$';
  return symbol + ' ' + Number(amount).toLocaleString('en-US', { minimumFractionDigits: 2 });
}

function statusClass(status) {
  const classes = {
    draft: 'bg-slate-100 text-slate-600',
    confirmed: 'bg-emerald-100 text-emerald-600',
    cancelled: 'bg-rose-100 text-rose-600',
    refunded: 'bg-amber-100 text-amber-600',
  };
  return classes[status] || 'bg-slate-100 text-slate-600';
}

function printReceipt() {
  window.open(route('finance.receipts.print', props.receipt.id), '_blank');
}

function confirmReceipt() {
  router.post(route('finance.receipts.confirm', props.receipt.id), {}, {
    onSuccess: () => {
      // Success message handled by controller
    },
  });
}

function createCreditNote() {
  router.get(route('finance.credit-notes.create', { receipt_id: props.receipt.id }));
}

function submitCancel() {
  if (!cancelReason.value) return;
  
  router.post(route('finance.receipts.cancel', props.receipt.id), {
    cancellation_reason: cancelReason.value,
  }, {
    onSuccess: () => {
      showCancelModal.value = false;
      cancelReason.value = '';
    },
  });
}
</script>

<style scoped>
.text-primary { color: #e95a54; }
.bg-primary { background-color: #e95a54; }
</style>
