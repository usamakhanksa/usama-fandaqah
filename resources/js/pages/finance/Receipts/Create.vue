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
          <h1 class="text-2xl font-bold text-slate-800">{{ $t('Create Receipt') }}</h1>
          <p class="text-slate-500 text-sm mt-1">{{ $t('Record a new payment receipt') }}</p>
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

            <!-- Status -->
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">
                {{ $t('Status') }} *
              </label>
              <select 
                v-model="form.status"
                class="w-full px-4 py-2.5 bg-slate-50 border-slate-200 border rounded-lg focus:ring-2 focus:ring-primary/20 transition-all outline-none"
              >
                <option value="draft">{{ $t('Draft') }}</option>
                <option value="confirmed">{{ $t('Confirmed') }}</option>
              </select>
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
              <p class="text-xs text-slate-400 mt-1">
                {{ $t('SAR Equivalent') }}: {{ sarEquivalent }}
              </p>
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
              <p v-if="form.errors.payment_method" class="text-red-500 text-sm mt-1">{{ form.errors.payment_method }}</p>
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
                  :class="{ 'border-red-500': form.errors.card_last_four }"
                  placeholder="1234"
                >
                <p v-if="form.errors.card_last_four" class="text-red-500 text-sm mt-1">{{ form.errors.card_last_four }}</p>
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
                  :class="{ 'border-red-500': form.errors.cheque_number }"
                >
                <p v-if="form.errors.cheque_number" class="text-red-500 text-sm mt-1">{{ form.errors.cheque_number }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">
                  {{ $t('Bank Name') }} *
                </label>
                <input 
                  v-model="form.bank_name"
                  type="text" 
                  class="w-full px-4 py-2.5 bg-slate-50 border-slate-200 border rounded-lg focus:ring-2 focus:ring-primary/20 transition-all outline-none"
                  :class="{ 'border-red-500': form.errors.bank_name }"
                >
                <p v-if="form.errors.bank_name" class="text-red-500 text-sm mt-1">{{ form.errors.bank_name }}</p>
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
                  :class="{ 'border-red-500': form.errors.reference_number }"
                >
                <p v-if="form.errors.reference_number" class="text-red-500 text-sm mt-1">{{ form.errors.reference_number }}</p>
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
                :placeholder="$t('Brief description of the receipt...')"
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
                :placeholder="$t('Internal notes (not visible to guest)...')"
              ></textarea>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-end gap-3">
          <Link 
            :href="route('finance.receipts.index')"
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
            {{ $t('Save Receipt') }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { computed, watch } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import { 
  ArrowLeft, Receipt, CreditCard, FileText, Save, Loader2 
} from 'lucide-vue-next';
import dayjs from 'dayjs';

const props = defineProps({
  reservations: Array,
  guests: Array,
  companies: Array,
  paymentMethods: Object,
  defaultCurrency: String,
});

const form = useForm({
  reservation_id: null,
  guest_id: null,
  company_id: null,
  receipt_date: dayjs().format('YYYY-MM-DD'),
  amount: '',
  currency: props.defaultCurrency || 'SAR',
  exchange_rate: '1.0000',
  payment_method: 'cash',
  reference_number: '',
  bank_name: '',
  cheque_number: '',
  card_last_four: '',
  description: '',
  notes: '',
  status: 'draft',
});

// Auto-set exchange rate when currency changes
watch(() => form.currency, (newCurrency) => {
  if (newCurrency === 'SAR') {
    form.exchange_rate = '1.0000';
  }
});

const sarEquivalent = computed(() => {
  const amount = parseFloat(form.amount) || 0;
  const rate = parseFloat(form.exchange_rate) || 0;
  const sar = amount * rate;
  return '﷼ ' + sar.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
});

function submit() {
  form.post(route('finance.receipts.store'), {
    onSuccess: () => {
      // Reset form or redirect handled by controller
    },
  });
}
</script>

<style scoped>
.text-primary { color: #e95a54; }
.bg-primary { background-color: #e95a54; }
</style>
