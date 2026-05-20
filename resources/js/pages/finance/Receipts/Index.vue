<template>
  <div class="p-6 bg-slate-50 min-h-screen">
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-2xl font-bold text-slate-800">{{ $t('Receipts') }}</h1>
        <p class="text-slate-500 text-sm mt-1">{{ $t('Manage all payment receipts') }}</p>
      </div>
      <div class="flex gap-3">
        <button 
          v-if="canExport"
          @click="exportReceipts"
          class="flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 rounded-lg text-slate-600 hover:bg-slate-50 transition-all shadow-sm"
        >
          <Download class="w-4 h-4" />
          {{ $t('Export') }}
        </button>
        <Link 
          v-if="canCreate"
          :href="route('finance.receipts.create')"
          class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-all shadow-md font-medium"
        >
          <Plus class="w-4 h-4" />
          {{ $t('New Receipt') }}
        </Link>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
      <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
        <p class="text-slate-500 text-xs uppercase tracking-wider">{{ $t('Total Receipts') }}</p>
        <p class="text-2xl font-bold text-slate-800 mt-1">{{ stats.total_count || 0 }}</p>
      </div>
      <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
        <p class="text-slate-500 text-xs uppercase tracking-wider">{{ $t('Total Amount') }}</p>
        <p class="text-2xl font-bold text-emerald-600 mt-1">{{ formatAmount(stats.total_amount) }}</p>
      </div>
      <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
        <p class="text-slate-500 text-xs uppercase tracking-wider">{{ $t('Cash Payments') }}</p>
        <p class="text-2xl font-bold text-blue-600 mt-1">{{ formatAmount(stats.by_payment_method?.cash?.total) }}</p>
      </div>
      <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
        <p class="text-slate-500 text-xs uppercase tracking-wider">{{ $t('Card Payments') }}</p>
        <p class="text-2xl font-bold text-purple-600 mt-1">{{ formatAmount(stats.by_payment_method?.card?.total) }}</p>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white/70 backdrop-blur-md border border-slate-200 rounded-xl p-4 mb-6 shadow-sm flex flex-wrap gap-4 items-center">
      <div class="flex-1 min-w-[200px]">
        <div class="relative">
          <Search class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
          <input 
            v-model="filters.search"
            type="text" 
            :placeholder="$t('Search by receipt # or guest...')"
            class="w-full pl-10 pr-4 py-2 bg-slate-50 border-none rounded-lg focus:ring-2 focus:ring-primary/20 transition-all"
            @input="debouncedSearch"
          >
        </div>
      </div>

      <div class="w-40">
        <select 
          v-model="filters.status"
          class="w-full px-4 py-2 bg-slate-50 border-none rounded-lg focus:ring-2 focus:ring-primary/20 transition-all text-slate-600"
          @change="applyFilters"
        >
          <option value="">{{ $t('All Statuses') }}</option>
          <option value="draft">{{ $t('Draft') }}</option>
          <option value="confirmed">{{ $t('Confirmed') }}</option>
          <option value="cancelled">{{ $t('Cancelled') }}</option>
        </select>
      </div>

      <div class="w-40">
        <select 
          v-model="filters.payment_method"
          class="w-full px-4 py-2 bg-slate-50 border-none rounded-lg focus:ring-2 focus:ring-primary/20 transition-all text-slate-600"
          @change="applyFilters"
        >
          <option value="">{{ $t('All Methods') }}</option>
          <option v-for="(label, value) in paymentMethods" :key="value" :value="value">
            {{ label }}
          </option>
        </select>
      </div>

      <div class="w-40">
        <input 
          v-model="filters.date_from"
          type="date" 
          class="w-full px-4 py-2 bg-slate-50 border-none rounded-lg focus:ring-2 focus:ring-primary/20 transition-all text-slate-600"
          @change="applyFilters"
        >
      </div>

      <div class="w-40">
        <input 
          v-model="filters.date_to"
          type="date" 
          class="w-full px-4 py-2 bg-slate-50 border-none rounded-lg focus:ring-2 focus:ring-primary/20 transition-all text-slate-600"
          @change="applyFilters"
        >
      </div>
    </div>

    <!-- Table -->
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-slate-50 border-bottom border-slate-200">
            <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $t('Receipt #') }}</th>
            <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $t('Date') }}</th>
            <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $t('Guest/Company') }}</th>
            <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $t('Amount') }}</th>
            <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $t('Method') }}</th>
            <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $t('Status') }}</th>
            <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">{{ $t('Actions') }}</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <tr v-for="receipt in receipts?.data" :key="receipt.id" class="hover:bg-slate-50/50 transition-colors">
            <td class="px-6 py-4">
              <div class="font-medium text-slate-700">{{ receipt.receipt_number }}</div>
              <div v-if="receipt.reservation" class="text-xs text-slate-400">
                {{ $t('Res:') }} {{ receipt.reservation.code }}
              </div>
            </td>
            <td class="px-6 py-4 text-slate-600">{{ formatDate(receipt.receipt_date) }}</td>
            <td class="px-6 py-4">
              <div v-if="receipt.guest" class="text-slate-700">{{ receipt.guest.name }}</div>
              <div v-else-if="receipt.company" class="text-slate-700">{{ receipt.company.name }}</div>
              <div v-else class="text-slate-400">-</div>
            </td>
            <td class="px-6 py-4">
              <div class="font-medium text-slate-700">{{ formatAmount(receipt.amount, receipt.currency) }}</div>
              <div v-if="receipt.currency !== 'SAR'" class="text-xs text-slate-400">
                {{ formatSarEquivalent(receipt.amount, receipt.exchange_rate) }}
              </div>
            </td>
            <td class="px-6 py-4">
              <span class="px-2 py-1 rounded-full text-xs font-medium" :class="methodClass(receipt.payment_method)">
                {{ paymentMethods[receipt.payment_method] || receipt.payment_method }}
              </span>
            </td>
            <td class="px-6 py-4">
              <span class="px-2 py-1 rounded-full text-xs font-medium" :class="statusClass(receipt.status)">
                {{ receipt.status }}
              </span>
            </td>
            <td class="px-6 py-4 text-right">
              <div class="flex justify-end gap-2">
                <Link 
                  :href="route('finance.receipts.show', receipt.id)"
                  class="p-1.5 text-slate-400 hover:text-primary transition-colors"
                  :title="$t('View')"
                >
                  <Eye class="w-4 h-4" />
                </Link>
                <Link 
                  v-if="canEdit && receipt.status === 'draft'"
                  :href="route('finance.receipts.edit', receipt.id)"
                  class="p-1.5 text-slate-400 hover:text-blue-600 transition-colors"
                  :title="$t('Edit')"
                >
                  <Pencil class="w-4 h-4" />
                </Link>
                <button 
                  v-if="canPrint"
                  @click="printReceipt(receipt.id)"
                  class="p-1.5 text-slate-400 hover:text-emerald-600 transition-colors"
                  :title="$t('Print')"
                >
                  <Printer class="w-4 h-4" />
                </button>
                <button 
                  v-if="canCancel && receipt.status !== 'cancelled'"
                  @click="confirmCancel(receipt)"
                  class="p-1.5 text-slate-400 hover:text-rose-600 transition-colors"
                  :title="$t('Cancel')"
                >
                  <XCircle class="w-4 h-4" />
                </button>
              </div>
            </td>
          </tr>
          <tr v-if="!receipts?.data || receipts?.data.length === 0">
            <td colspan="7" class="px-6 py-12 text-center text-slate-400">
              <div class="flex flex-col items-center gap-2">
                <Receipt class="w-12 h-12 text-slate-200" />
                <p>{{ $t('No receipts found') }}</p>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div v-if="receipts?.meta && receipts?.data?.length > 0" class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex justify-between items-center">
        <p class="text-sm text-slate-500">
          {{ $t('Showing') }} {{ receipts.meta.from }} {{ $t('to') }} {{ receipts.meta.to }} {{ $t('of') }} {{ receipts.meta.total }}
        </p>
        <div class="flex gap-2">
          <Link 
            v-for="link in receipts.meta.links" 
            :key="link.label"
            :href="link.url"
            class="px-3 py-1 border border-slate-200 rounded bg-white text-slate-600 disabled:opacity-50"
            :class="{ 'bg-primary text-white': link.active }"
            v-html="link.label"
          />
        </div>
      </div>
    </div>

    <!-- Cancel Modal -->
    <Modal :show="showCancelModal" @close="showCancelModal = false">
      <div class="p-6">
        <h3 class="text-lg font-semibold text-slate-800 mb-4">{{ $t('Cancel Receipt') }}</h3>
        <p class="text-slate-500 mb-4">
          {{ $t('Are you sure you want to cancel receipt') }} 
          <strong>{{ selectedReceipt?.receipt_number }}</strong>?
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
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { 
  Plus, Search, Eye, Pencil, Printer, XCircle, Download, Receipt 
} from 'lucide-vue-next';
import Modal from '@/Components/Modal.vue';
import dayjs from 'dayjs';

const props = defineProps({
  receipts: {
    type: Object,
    default: () => ({ data: [] })
  },
  stats: {
    type: Object,
    default: () => ({})
  },
  filters: {
    type: Object,
    default: () => ({})
  },
  paymentMethods: {
    type: Object,
    default: () => ({})
  },
  canCreate: Boolean,
  canEdit: Boolean,
  canDelete: Boolean,
  canCancel: Boolean,
  canPrint: Boolean,
  canExport: Boolean,
});

const filters = reactive({
  search: props.filters?.search || '',
  status: props.filters?.status || '',
  payment_method: props.filters?.payment_method || '',
  date_from: props.filters?.date_from || '',
  date_to: props.filters?.date_to || '',
});

const showCancelModal = ref(false);
const selectedReceipt = ref(null);
const cancelReason = ref('');

const debouncedSearch = debounce(() => {
  applyFilters();
}, 500);

function applyFilters() {
  router.get(route('finance.receipts.index'), filters, {
    preserveState: true,
    replace: true,
  });
}

function formatDate(date) {
  return dayjs(date).format('MMM DD, YYYY');
}

function formatAmount(amount, currency = 'SAR') {
  if (!amount) return '-';
  const symbol = currency === 'SAR' ? '﷼' : '$';
  return symbol + ' ' + Number(amount).toLocaleString('en-US', { minimumFractionDigits: 2 });
}

function formatSarEquivalent(amount, rate) {
  const sarAmount = amount * rate;
  return '﷼ ' + Number(sarAmount).toLocaleString('en-US', { minimumFractionDigits: 2 });
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

function methodClass(method) {
  const classes = {
    cash: 'bg-emerald-50 text-emerald-600',
    card: 'bg-blue-50 text-blue-600',
    bank_transfer: 'bg-purple-50 text-purple-600',
    cheque: 'bg-amber-50 text-amber-600',
    online: 'bg-cyan-50 text-cyan-600',
    other: 'bg-slate-50 text-slate-600',
  };
  return classes[method] || 'bg-slate-50 text-slate-600';
}

function printReceipt(id) {
  window.open(route('finance.receipts.print', id), '_blank');
}

function exportReceipts() {
  window.location.href = route('finance.receipts.export', filters);
}

function confirmCancel(receipt) {
  selectedReceipt.value = receipt;
  cancelReason.value = '';
  showCancelModal.value = true;
}

function submitCancel() {
  if (!cancelReason.value) return;
  
  router.post(route('finance.receipts.cancel', selectedReceipt.value.id), {
    cancellation_reason: cancelReason.value,
  }, {
    onSuccess: () => {
      showCancelModal.value = false;
      selectedReceipt.value = null;
      cancelReason.value = '';
    },
  });
}

function debounce(fn, delay) {
  let timeout;
  return (...args) => {
    clearTimeout(timeout);
    timeout = setTimeout(() => fn(...args), delay);
  };
}
</script>

<style scoped>
.text-primary { color: #e95a54; }
.bg-primary { background-color: #e95a54; }
</style>
