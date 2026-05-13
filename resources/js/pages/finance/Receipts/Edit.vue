<template>
  <div class="p-6 bg-slate-50 min-h-screen">
    <div class="max-w-4xl mx-auto">
      <div class="flex items-center gap-4 mb-6">
        <Link 
          :href="route('finance.receipts.index')"
          class="p-2 bg-white border border-slate-200 rounded-lg text-slate-400 hover:text-slate-600 transition-all shadow-sm"
        >
          <ArrowLeft class="w-5 h-5" />
        </Link>
        <div>
          <h1 class="text-2xl font-bold text-slate-800">{{ $t('Edit Receipt') }}</h1>
          <p class="text-slate-500 text-sm mt-1">{{ $t('Receipt') }}: {{ receipt.receipt_number }}</p>
        </div>
        <span 
          class="ml-auto px-3 py-1 rounded-full text-sm font-medium"
          :class="statusClass(receipt.status)"
        >
          {{ receipt.status }}
        </span>
      </div>

      <!-- Audit Trail -->
      <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6">
        <div class="flex items-center gap-2 text-sm text-blue-800">
          <Info class="w-4 h-4" />
          <span>{{ $t('Created by') }} {{ receipt.created_by?.name }} {{ $t('on') }} {{ formatDate(receipt.created_at) }}</span>
        </div>
        <div v-if="receipt.updated_by" class="flex items-center gap-2 text-sm text-blue-800 mt-1">
          <RefreshCw class="w-4 h-4" />
          <span>{{ $t('Last updated by') }} {{ receipt.updated_by?.name }} {{ $t('on') }} {{ formatDate(receipt.updated_at) }}</span>
        </div>
      </div>

      <form @submit.prevent="submit" class="space-y-6">
        <!-- Main Form Card -->
        <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm">
          <h2 class="text-lg font-semibold text-slate-800 mb-4 flex items-center gap-2">
            <Receipt class="w-5 h-5 text-primary" />
            {{ $t('Receipt Information') }}
          </h2>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Receipt Date -->
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">
                {{ $t('Receipt Date') }} *
              </label>
              <input 
                v-model="form.receipt_date"
                type="date" 
                class="w-full px-4 py-2.5 bg-slate-50 border-slate-200 border rounded-lg focus:ring-2 focus:ring-primary/20 transition-all outline-none"
                :class="{ 'border-red-500': form.errors.receipt_date }"
              >
              <p v-if="form.errors.receipt_date" class="text-red-500 text-sm mt-1">{{ form.errors.receipt_date }}</p>
            </div>

            <!-- Status (display only for edit) -->
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">
                {{ $t('Status') }}
              </label>
              <div class="px-4 py-2.5 bg-slate-100 border border-slate-200 rounded-lg text-slate-600">
                {{ receipt.status }}
                <span class="text-xs text-slate-400">({{ $t('Cannot change status from edit') }})</span>
              </div>
            </div>

            <!-- Reservation -->
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-slate-700 mb-1">
                {{ $t('Reservation') }} ({{ $t('Optional') }})
              </label>
              <select 
                v-model="form.reservation_id"
                class="w-full px-4 py-2.5 bg-slate-50 border-slate-200 border rounded-lg focus:ring-2 focus:ring-primary/20 transition-all outline-none"
              >
                <option :value="null">{{ $t('Select Reservation') }}</option>
                <option v-for="res in reservations" :key="res.id" :value="res.id">
                  {{ res.code }} - {{ res.guest?.name || $t('No guest') }}
                </option>
              </select>
            </div>

            <!-- Guest -->
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">
                {{ $t('Guest') }} ({{ $t('Optional') }})
              </label>
              <select 
                v-model="form.guest_id"
                class="w-full px-4 py-2.5 bg-slate-50 border-slate-200 border rounded-lg focus:ring-2 focus:ring-primary/20 transition-all outline-none"
              >
                <option :value="null">{{ $t('Select Guest') }}</option>
                <option v-for="guest in guests" :key="guest.id" :value="guest.id">
                  {{ guest.name }}
                </option>
              </select>
            </div>

            <!-- Company -->
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">
                {{ $t('Company') }} ({{ $t('Optional') }})
              </label>
              <select 
                v-model="form.company_id"
                class="w-full px-4 py-2.5 bg-slate-50 border-slate-200 border rounded-lg focus:ring-2 focus:ring-primary/20 transition-all outline-none"
              >
                <option :value="null">{{ $t('Select Company') }}</option>
                <option v-for="company in companies" :key="company.id" :value="company.id">
                  {{ company.name }}
                </option>
              </select>
            </div>
          </div>
        </div>

        <!-- Payment Details -->
        <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm">
          <h2 class="text-lg font-semibold text-slate-800 mb-4 flex items-center gap-2">
            <CreditCard class="w-5 h-5 text-primary" />
            {{ $t('Payment Details') }}
          </h2>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Amount -->
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">
                {{ $t('Amount') }} *
              </label>
              <input 
                v-model="form.amount"
                type="number" 
                step="0.01"
                min="0.01"
                class="w-full px-4 py-2.5 bg-slate-50 border-slate-200 border rounded-lg focus:ring-2 focus:ring-primary/20 transition-all outline-none"
                :class="{ 'border-red-500': form.errors.amount }"
              >
              <p v-if="form.errors.amount" class="text-red-500 text-sm mt-1">{{ form.errors.amount }}</p>
            </div>

            <!-- Currency -->
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">
                {{ $t('Currency') }} *
              </label>
              <select 
                v-model="form.currency"
                class="w-full px-4 py-2.5 bg-slate-50 border-slate-200 border rounded-lg focus:ring-2 focus:ring-primary/20 transition-all outline-none"
              >
                <option value="SAR">SAR (Saudi Riyal)</option>
                <option value="USD">USD (US Dollar)</option>
                <option value="EUR">EUR (Euro)</option>
                <option value="GBP">GBP (British Pound)</option>
                <option value="AED">AED (UAE Dirham)</option>
              </select>
            </div>

            <!-- Exchange Rate -->
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">
                {{ $t('Exchange Rate') }} *
              </label>
              <input 
                v-model="form.exchange_rate"
                type="number" 
                step="0.0001"
                min="0.0001"
                class="w-full px-4 py-2.5 bg-slate-50 border-slate-200 border rounded-lg focus:ring-2 focus:ring-primary/20 transition-all outline-none"
                :disabled="form.currency === 'SAR'"
              >
            </div>

            <!-- Payment Method -->
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">
                {{ $t('Payment Method') }} *
              </label>
              <select 
                v-model="form.payment_method"
                class="w-full px-4 py-2.5 bg-slate-50 border-slate-200 border rounded-lg focus:ring-2 focus:ring-primary/20 transition-all outline-none"
                :class="{ 'border-red-500': form.errors.payment_method }"
              >
                <option v-for="(label, value) in paymentMethods" :key="value" :value="value">
                  {{ label }}
                </option>
              </select>
            </div>

            <!-- Conditional Fields -->
            <template v-if="form.payment_method === 'card'">
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">
                  {{ $t('Card Last Four') }} *
                </label>
                <input 
                  v-model="form.card_last_four"
                  type="text" 
                  maxlength="4"
                  class="w-full px-4 py-2.5 bg-slate-50 border-slate-200 border rounded-lg focus:ring-2 focus:ring-primary/20 transition-all outline-none"
                  placeholder="1234"
                >
              </div>
            </template>

            <template v-if="form.payment_method === 'cheque'">
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">
                  {{ $t('Cheque Number') }} *
                </label>
                <input 
                  v-model="form.cheque_number"
                  type="text" 
                  class="w-full px-4 py-2.5 bg-slate-50 border-slate-200 border rounded-lg focus:ring-2 focus:ring-primary/20 transition-all outline-none"
                >
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">
                  {{ $t('Bank Name') }} *
                </label>
                <input 
                  v-model="form.bank_name"
                  type="text" 
                  class="w-full px-4 py-2.5 bg-slate-50 border-slate-200 border rounded-lg focus:ring-2 focus:ring-primary/20 transition-all outline-none"
                >
              </div>
            </template>

            <template v-if="form.payment_method === 'bank_transfer'">
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-700 mb-1">
                  {{ $t('Reference Number') }} *
                </label>
                <input 
                  v-model="form.reference_number"
                  type="text" 
                  class="w-full px-4 py-2.5 bg-slate-50 border-slate-200 border rounded-lg focus:ring-2 focus:ring-primary/20 transition-all outline-none"
                >
              </div>
            </template>
          </div>
        </div>

        <!-- Additional Information -->
        <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm">
          <h2 class="text-lg font-semibold text-slate-800 mb-4 flex items-center gap-2">
            <FileText class="w-5 h-5 text-primary" />
            {{ $t('Additional Information') }}
          </h2>

          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">
                {{ $t('Description') }}
              </label>
              <textarea 
                v-model="form.description"
                rows="2"
                class="w-full px-4 py-2.5 bg-slate-50 border-slate-200 border rounded-lg focus:ring-2 focus:ring-primary/20 transition-all outline-none"
              ></textarea>
            </div>

            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">
                {{ $t('Internal Notes') }}
              </label>
              <textarea 
                v-model="form.notes"
                rows="2"
                class="w-full px-4 py-2.5 bg-slate-50 border-slate-200 border rounded-lg focus:ring-2 focus:ring-primary/20 transition-all outline-none"
              ></textarea>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-end gap-3">
          <Link 
            :href="route('finance.receipts.show', receipt.id)"
            class="px-6 py-2.5 border border-slate-200 rounded-lg text-slate-600 hover:bg-slate-50 font-medium"
          >
            {{ $t('Cancel') }}
          </Link>
          <button 
            type="submit"
            :disabled="form.processing"
            class="px-6 py-2.5 bg-primary text-white rounded-lg hover:bg-primary/90 font-medium disabled:opacity-50 flex items-center gap-2"
          >
            <Save v-if="!form.processing" class="w-4 h-4" />
            <Loader2 v-else class="w-4 h-4 animate-spin" />
            {{ $t('Update Receipt') }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import { 
  ArrowLeft, Receipt, CreditCard, FileText, Save, Loader2, Info, RefreshCw 
} from 'lucide-vue-next';
import dayjs from 'dayjs';

const props = defineProps({
  receipt: Object,
  reservations: Array,
  guests: Array,
  companies: Array,
  paymentMethods: Object,
});

const form = useForm({
  reservation_id: props.receipt.reservation_id,
  guest_id: props.receipt.guest_id,
  company_id: props.receipt.company_id,
  receipt_date: dayjs(props.receipt.receipt_date).format('YYYY-MM-DD'),
  amount: props.receipt.amount,
  currency: props.receipt.currency,
  exchange_rate: props.receipt.exchange_rate,
  payment_method: props.receipt.payment_method,
  reference_number: props.receipt.reference_number || '',
  bank_name: props.receipt.bank_name || '',
  cheque_number: props.receipt.cheque_number || '',
  card_last_four: props.receipt.card_last_four || '',
  description: props.receipt.description || '',
  notes: props.receipt.notes || '',
});

function submit() {
  form.put(route('finance.receipts.update', props.receipt.id), {
    onSuccess: () => {
      // Redirect handled by controller
    },
  });
}

function formatDate(date) {
  return dayjs(date).format('MMM DD, YYYY HH:mm');
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
</script>

<style scoped>
.text-primary { color: #e95a54; }
.bg-primary { background-color: #e95a54; }
</style>
