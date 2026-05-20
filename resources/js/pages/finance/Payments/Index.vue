<template>
  <div class="p-6 bg-slate-50 min-h-screen">
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-2xl font-bold text-slate-800">{{ $t('Payments') }}</h1>
        <p class="text-slate-500 text-sm mt-1">{{ $t('Manage all financial payments and refunds') }}</p>
      </div>
      <div class="flex gap-3">
        <button 
          @click="exportPayments"
          class="flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 rounded-lg text-slate-600 hover:bg-slate-50 transition-all shadow-sm"
        >
          <Download class="w-4 h-4" />
          {{ $t('Export') }}
        </button>
        <Link 
          :href="route('finance.payments.create')"
          class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-all shadow-md font-medium"
        >
          <Plus class="w-4 h-4" />
          {{ $t('New Payment') }}
        </Link>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
      <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
        <p class="text-slate-500 text-xs uppercase tracking-wider">{{ $t('Today Total') }}</p>
        <p class="text-2xl font-bold text-emerald-600 mt-1">{{ formatAmount(stats.today_total) }}</p>
      </div>
      <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
        <p class="text-slate-500 text-xs uppercase tracking-wider">{{ $t('This Week') }}</p>
        <p class="text-2xl font-bold text-blue-600 mt-1">{{ formatAmount(stats.week_total) }}</p>
      </div>
      <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
        <p class="text-slate-500 text-xs uppercase tracking-wider">{{ $t('This Month') }}</p>
        <p class="text-2xl font-bold text-purple-600 mt-1">{{ formatAmount(stats.month_total) }}</p>
      </div>
      <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
        <p class="text-slate-500 text-xs uppercase tracking-wider">{{ $t('Pending Count') }}</p>
        <p class="text-2xl font-bold text-amber-600 mt-1">{{ stats.pending_count || 0 }}</p>
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
            :placeholder="$t('Search by payment #, guest, or reservation...')"
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
          <option value="pending">{{ $t('Pending') }}</option>
          <option value="confirmed">{{ $t('Confirmed') }}</option>
          <option value="cancelled">{{ $t('Cancelled') }}</option>
          <option value="reversed">{{ $t('Reversed') }}</option>
          <option value="refunded">{{ $t('Refunded') }}</option>
        </select>
      </div>

      <div class="w-40">
        <select 
          v-model="filters.method"
          class="w-full px-4 py-2 bg-slate-50 border-none rounded-lg focus:ring-2 focus:ring-primary/20 transition-all text-slate-600"
          @change="applyFilters"
        >
          <option value="">{{ $t('All Methods') }}</option>
          <option value="cash">{{ $t('Cash') }}</option>
          <option value="visa">{{ $t('Visa') }}</option>
          <option value="mastercard">{{ $t('Mastercard') }}</option>
          <option value="mada">{{ $t('Mada') }}</option>
          <option value="apple_pay">{{ $t('Apple Pay') }}</option>
          <option value="bank_transfer">{{ $t('Bank Transfer') }}</option>
          <option value="cheque">{{ $t('Cheque') }}</option>
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
          <tr class="bg-slate-50 border-b border-slate-200">
            <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $t('Payment #') }}</th>
            <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $t('Date') }}</th>
            <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $t('Guest / Reservation') }}</th>
            <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $t('Amount') }}</th>
            <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $t('Method / Type') }}</th>
            <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $t('Status') }}</th>
            <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">{{ $t('Actions') }}</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <tr v-for="payment in payments?.data" :key="payment.id" class="hover:bg-slate-50/50 transition-colors">
            <td class="px-6 py-4 font-medium text-slate-700">
              {{ payment.payment_number }}
            </td>
            <td class="px-6 py-4 text-slate-600">
              {{ formatDate(payment.payment_date) }}
            </td>
            <td class="px-6 py-4">
              <div class="text-slate-700">{{ payment.guest?.name || '-' }}</div>
              <div v-if="payment.reservation" class="text-xs text-slate-400">
                {{ $t('Res:') }} {{ payment.reservation.reservation_number }}
              </div>
            </td>
            <td class="px-6 py-4">
              <div class="font-bold" :class="payment.payment_type === 'refund' ? 'text-rose-600' : 'text-slate-800'">
                {{ payment.payment_type === 'refund' ? '-' : '' }}{{ formatAmount(payment.amount, payment.currency) }}
              </div>
              <div v-if="payment.currency !== 'SAR'" class="text-xs text-slate-400">
                {{ formatAmount(payment.amount * payment.exchange_rate, 'SAR') }}
              </div>
            </td>
            <td class="px-6 py-4">
              <div class="flex flex-col gap-1">
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase w-fit" :class="methodClass(payment.payment_method)">
                  {{ payment.payment_method }}
                </span>
                <span class="text-xs text-slate-400 italic">
                  {{ payment.payment_type }}
                </span>
              </div>
            </td>
            <td class="px-6 py-4">
              <span class="px-2 py-1 rounded-full text-xs font-medium" :class="statusClass(payment.status)">
                {{ payment.status }}
              </span>
            </td>
            <td class="px-6 py-4 text-right">
              <div class="flex justify-end gap-2">
                <Link 
                  :href="route('finance.payments.show', payment.id)"
                  class="p-1.5 text-slate-400 hover:text-primary transition-colors"
                >
                  <Eye class="w-4 h-4" />
                </Link>
                <Link 
                  v-if="payment.status === 'pending'"
                  :href="route('finance.payments.edit', payment.id)"
                  class="p-1.5 text-slate-400 hover:text-blue-600 transition-colors"
                >
                  <Pencil class="w-4 h-4" />
                </Link>
                <button 
                  v-if="payment.status === 'pending'"
                  @click="confirmPayment(payment.id)"
                  class="p-1.5 text-slate-400 hover:text-emerald-600 transition-colors"
                >
                  <CheckCircle class="w-4 h-4" />
                </button>
                <button 
                  @click="printPayment(payment.id)"
                  class="p-1.5 text-slate-400 hover:text-slate-600 transition-colors"
                >
                  <Printer class="w-4 h-4" />
                </button>
              </div>
            </td>
          </tr>
          <tr v-if="!payments?.data || payments?.data.length === 0">
            <td colspan="7" class="px-6 py-12 text-center text-slate-400">
              {{ $t('No payments found') }}
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div v-if="payments?.data?.length > 0" class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex justify-between items-center">
        <div class="text-sm text-slate-500">
          Showing {{ payments.from }} to {{ payments.to }} of {{ payments.total }} entries
        </div>
        <div class="flex gap-2">
          <Link 
            v-for="link in payments.links" 
            :key="link.label"
            :href="link.url"
            class="px-3 py-1 border border-slate-200 rounded bg-white text-slate-600"
            :class="{ 'bg-primary text-white': link.active }"
            v-html="link.label"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { 
  Plus, Search, Eye, Pencil, Printer, Download, CheckCircle, XCircle 
} from 'lucide-vue-next';
import dayjs from 'dayjs';

const props = defineProps({
  payments: {
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
});

const filters = reactive({
  search: props.filters.search || '',
  status: props.filters.status || '',
  method: props.filters.method || '',
  date_from: props.filters.date_from || '',
  date_to: props.filters.date_to || '',
});

function applyFilters() {
  router.get(route('finance.payments.index'), filters, {
    preserveState: true,
    replace: true,
  });
}

const debouncedSearch = debounce(() => {
  applyFilters();
}, 500);

function formatDate(date) {
  return dayjs(date).format('DD MMM YYYY');
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
    reversed: 'bg-slate-100 text-slate-700',
    refunded: 'bg-blue-100 text-blue-700',
  };
  return classes[status] || 'bg-slate-100 text-slate-700';
}

function methodClass(method) {
  const classes = {
    cash: 'bg-emerald-50 text-emerald-600 border border-emerald-200',
    visa: 'bg-blue-50 text-blue-600 border border-blue-200',
    mastercard: 'bg-orange-50 text-orange-600 border border-orange-200',
    mada: 'bg-cyan-50 text-cyan-600 border border-cyan-200',
    apple_pay: 'bg-slate-50 text-slate-600 border border-slate-200',
    bank_transfer: 'bg-purple-50 text-purple-600 border border-purple-200',
    cheque: 'bg-amber-50 text-amber-600 border border-amber-200',
    online: 'bg-indigo-50 text-indigo-600 border border-indigo-200',
  };
  return classes[method] || 'bg-slate-50 text-slate-600 border border-slate-200';
}

function confirmPayment(id) {
  if (confirm('Are you sure you want to confirm this payment?')) {
    router.post(route('finance.payments.confirm', id));
  }
}

function printPayment(id) {
  window.open(route('finance.payments.print', id), '_blank');
}

function exportPayments() {
  window.location.href = route('finance.payments.export', filters);
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
