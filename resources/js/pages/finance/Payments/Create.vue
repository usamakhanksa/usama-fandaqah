<template>
  <div class="p-6 bg-slate-50 min-h-screen">
    <div class="max-w-4xl mx-auto">
      <div class="flex items-center gap-4 mb-6">
        <Link :href="route('finance.payments.index')" class="p-2 bg-white border border-slate-200 rounded-lg text-slate-400 hover:text-slate-600 transition-all shadow-sm">
          <ArrowLeft class="w-5 h-5" />
        </Link>
        <div>
          <h1 class="text-2xl font-bold text-slate-800">{{ $t('Create New Payment') }}</h1>
          <p class="text-slate-500 text-sm mt-1">{{ $t('Step') }} {{ step }}/2: {{ step === 1 ? $t('Select Entity') : $t('Payment Details') }}</p>
        </div>
      </div>

      <!-- Step 1: Select Entity -->
      <div v-if="step === 1" class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 bg-slate-50/50">
          <h2 class="text-lg font-semibold text-slate-800">{{ $t('Who is paying?') }}</h2>
          <p class="text-slate-500 text-sm">{{ $t('Select a reservation, guest, or company to proceed.') }}</p>
        </div>
        
        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Reservation Search -->
          <div class="space-y-4">
            <label class="block text-sm font-bold text-slate-700 uppercase tracking-wider">{{ $t('Search Reservation') }}</label>
            <div class="relative">
              <Search class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
              <input 
                v-model="searchQuery" 
                type="text" 
                :placeholder="$t('Search by guest name, room, or res #')"
                class="w-full pl-10 pr-4 py-3 bg-slate-50 border-none rounded-xl focus:ring-2 focus:ring-primary/20 transition-all"
              >
            </div>
            
            <div class="max-h-[300px] overflow-y-auto space-y-2 pr-2 custom-scrollbar">
              <div 
                v-for="res in filteredReservations" 
                :key="res.id"
                @click="selectReservation(res)"
                class="p-4 rounded-xl border border-slate-100 cursor-pointer transition-all hover:border-primary/30 hover:bg-primary/5"
                :class="form.reservation_id === res.id ? 'border-primary bg-primary/5 ring-1 ring-primary' : 'bg-white'"
              >
                <div class="flex justify-between items-start">
                  <div>
                    <div class="font-bold text-slate-800">{{ res.guest?.name }}</div>
                    <div class="text-xs text-slate-400">{{ res.reservation_number }} • Room: {{ res.room?.number || 'N/A' }}</div>
                  </div>
                  <div class="text-right">
                    <div class="text-xs font-bold text-primary uppercase">{{ res.status }}</div>
                    <div class="text-sm font-bold text-slate-700">{{ formatAmount(res.balance || 0) }}</div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Guest/Company Selection -->
          <div class="space-y-6">
            <div class="space-y-4">
              <label class="block text-sm font-bold text-slate-700 uppercase tracking-wider">{{ $t('Or Select Guest') }}</label>
              <select 
                v-model="form.guest_id" 
                class="w-full px-4 py-3 bg-slate-50 border-none rounded-xl focus:ring-2 focus:ring-primary/20 transition-all text-slate-600"
                @change="onGuestChange"
              >
                <option :value="null">{{ $t('Select Guest') }}</option>
                <option v-for="guest in guests" :key="guest.id" :value="guest.id">{{ guest.name }}</option>
              </select>
            </div>

            <div class="space-y-4">
              <label class="block text-sm font-bold text-slate-700 uppercase tracking-wider">{{ $t('Or Select Company') }}</label>
              <select 
                v-model="form.company_id" 
                class="w-full px-4 py-3 bg-slate-50 border-none rounded-xl focus:ring-2 focus:ring-primary/20 transition-all text-slate-600"
                @change="onCompanyChange"
              >
                <option :value="null">{{ $t('Select Company') }}</option>
                <option v-for="company in companies" :key="company.id" :value="company.id">{{ company.name }}</option>
              </select>
            </div>

            <div v-if="selectedEntityInfo" class="mt-8 p-6 bg-emerald-50 border border-emerald-100 rounded-2xl">
              <div class="flex items-center gap-3 mb-2">
                <CheckCircle class="w-5 h-5 text-emerald-600" />
                <span class="font-bold text-emerald-800">{{ $t('Selected Entity') }}</span>
              </div>
              <div class="text-sm text-emerald-700">{{ selectedEntityInfo }}</div>
            </div>
          </div>
        </div>

        <div class="p-6 border-t border-slate-100 flex justify-end">
          <button 
            @click="nextStep"
            :disabled="!form.reservation_id && !form.guest_id && !form.company_id"
            class="flex items-center gap-2 px-8 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-all shadow-lg shadow-primary/20 font-bold disabled:opacity-50"
          >
            {{ $t('Continue to Payment') }}
            <ArrowRight class="w-5 h-5" />
          </button>
        </div>
      </div>

      <!-- Step 2: Payment Details -->
      <div v-else class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <form @submit.prevent="submit(false)">
          <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Left Column: Basic Info -->
            <div class="space-y-6">
              <div class="space-y-2">
                <label class="block text-sm font-bold text-slate-700 uppercase tracking-wider">{{ $t('Payment Type') }}</label>
                <div class="grid grid-cols-2 gap-2">
                  <button 
                    v-for="type in paymentTypes" :key="type"
                    type="button"
                    @click="form.payment_type = type"
                    class="px-4 py-3 rounded-xl text-sm font-bold transition-all border"
                    :class="form.payment_type === type ? 'bg-primary text-white border-primary shadow-md' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'"
                  >
                    {{ type.charAt(0).toUpperCase() + type.slice(1).replace('_', ' ') }}
                  </button>
                </div>
              </div>

              <div class="space-y-2">
                <div class="flex justify-between items-center">
                  <label class="block text-sm font-bold text-slate-700 uppercase tracking-wider">{{ $t('Amount') }}</label>
                  <button 
                    v-if="selectedReservation?.balance"
                    type="button"
                    @click="form.amount = selectedReservation.balance"
                    class="text-xs font-bold text-primary hover:underline"
                  >
                    {{ $t('Pay Full Balance') }} ({{ formatAmount(selectedReservation.balance) }})
                  </button>
                </div>
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

              <div v-if="form.currency !== 'SAR'" class="p-4 bg-blue-50 border border-blue-100 rounded-xl flex justify-between items-center">
                <span class="text-sm text-blue-700 font-medium">{{ $t('SAR Equivalent') }}</span>
                <span class="text-lg font-bold text-blue-800">{{ formatAmount(form.amount * form.exchange_rate, 'SAR') }}</span>
              </div>
            </div>

            <!-- Right Column: Method & Specifics -->
            <div class="space-y-6">
              <div class="space-y-2">
                <label class="block text-sm font-bold text-slate-700 uppercase tracking-wider">{{ $t('Payment Method') }}</label>
                <div class="grid grid-cols-3 gap-2">
                  <button 
                    v-for="method in paymentMethods" :key="method"
                    type="button"
                    @click="form.payment_method = method"
                    class="flex flex-col items-center gap-1 p-3 rounded-xl border transition-all"
                    :class="form.payment_method === method ? 'bg-primary text-white border-primary shadow-md' : 'bg-white text-slate-500 border-slate-200 hover:bg-slate-50'"
                  >
                    <component :is="getMethodIcon(method)" class="w-5 h-5" />
                    <span class="text-[10px] font-bold uppercase">{{ method.replace('_', ' ') }}</span>
                  </button>
                </div>
              </div>

              <!-- Conditional Fields Based on Method -->
              <div v-if="['visa', 'mastercard', 'mada'].includes(form.payment_method)" class="space-y-4 p-4 bg-slate-50 rounded-2xl animate-in fade-in slide-in-from-top-2">
                <div class="grid grid-cols-2 gap-4">
                  <div class="space-y-2">
                    <label class="block text-[10px] font-bold text-slate-500 uppercase">{{ $t('Card Last 4 Digits') }}</label>
                    <input v-model="form.card_last_four" type="text" maxlength="4" class="w-full px-4 py-2 bg-white border border-slate-200 rounded-lg">
                  </div>
                  <div class="space-y-2">
                    <label class="block text-[10px] font-bold text-slate-500 uppercase">{{ $t('Auth Code') }}</label>
                    <input v-model="form.card_authorization" type="text" class="w-full px-4 py-2 bg-white border border-slate-200 rounded-lg">
                  </div>
                </div>
              </div>

              <div v-if="form.payment_method === 'cheque'" class="space-y-4 p-4 bg-slate-50 rounded-2xl animate-in fade-in slide-in-from-top-2">
                <div class="space-y-2">
                  <label class="block text-[10px] font-bold text-slate-500 uppercase">{{ $t('Bank Name') }}</label>
                  <input v-model="form.bank_name" type="text" class="w-full px-4 py-2 bg-white border border-slate-200 rounded-lg">
                </div>
                <div class="space-y-2">
                  <label class="block text-[10px] font-bold text-slate-500 uppercase">{{ $t('Cheque Number') }}</label>
                  <input v-model="form.cheque_number" type="text" class="w-full px-4 py-2 bg-white border border-slate-200 rounded-lg">
                </div>
              </div>

              <div v-if="form.payment_method === 'bank_transfer'" class="space-y-4 p-4 bg-slate-50 rounded-2xl animate-in fade-in slide-in-from-top-2">
                <div class="space-y-2">
                  <label class="block text-[10px] font-bold text-slate-500 uppercase">{{ $t('Ref Number') }}</label>
                  <input v-model="form.reference_number" type="text" class="w-full px-4 py-2 bg-white border border-slate-200 rounded-lg">
                </div>
                <div class="space-y-2">
                  <label class="block text-[10px] font-bold text-slate-500 uppercase">{{ $t('Bank Name') }}</label>
                  <input v-model="form.bank_name" type="text" class="w-full px-4 py-2 bg-white border border-slate-200 rounded-lg">
                </div>
              </div>

              <div class="space-y-2">
                <label class="block text-sm font-bold text-slate-700 uppercase tracking-wider">{{ $t('Notes') }}</label>
                <textarea v-model="form.notes" rows="3" class="w-full px-4 py-3 bg-slate-50 border-none rounded-xl focus:ring-2 focus:ring-primary/20" :placeholder="$t('Add any internal notes...')"></textarea>
              </div>
            </div>
          </div>

          <div class="p-8 border-t border-slate-100 flex flex-wrap gap-4 justify-between bg-slate-50/50">
            <button 
              type="button"
              @click="step = 1"
              class="px-6 py-3 border border-slate-200 rounded-xl text-slate-600 hover:bg-slate-100 transition-all font-bold"
            >
              {{ $t('Back') }}
            </button>
            <div class="flex gap-4">
              <button 
                type="button"
                @click="submit(false)"
                :disabled="processing"
                class="px-8 py-3 bg-white border border-primary text-primary rounded-xl hover:bg-primary/5 transition-all font-bold disabled:opacity-50"
              >
                {{ $t('Save as Pending') }}
              </button>
              <button 
                type="button"
                @click="submit(true)"
                :disabled="processing"
                class="px-8 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-all shadow-lg shadow-primary/20 font-bold disabled:opacity-50"
              >
                {{ $t('Save & Confirm') }}
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, reactive } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import { 
  ArrowLeft, ArrowRight, Search, CheckCircle, CreditCard, Banknote, Landmark, Smartphone, MoreHorizontal, HelpCircle 
} from 'lucide-vue-next';

const props = defineProps({
  guests: Array,
  reservations: Array,
  shifts: Array,
  companies: Array,
  invoices: Array,
});

const step = ref(1);
const searchQuery = ref('');
const selectedReservation = ref(null);

const form = useForm({
  reservation_id: null,
  guest_id: null,
  company_id: null,
  invoice_id: null,
  amount: 0,
  payment_date: new Date().toISOString().split('T')[0],
  payment_method: 'cash',
  payment_type: 'payment',
  reference_number: '',
  bank_name: '',
  cheque_number: '',
  card_last_four: '',
  card_authorization: '',
  description: '',
  notes: '',
  cashier_shift_id: props.shifts.length > 0 ? props.shifts[0].id : null,
  is_advance: false,
  is_deposit: false,
  currency: 'SAR',
  exchange_rate: 1.0000,
  confirm_now: false,
});

const paymentMethods = ['cash', 'visa', 'mastercard', 'mada', 'apple_pay', 'bank_transfer', 'cheque', 'online', 'other'];
const paymentTypes = ['payment', 'deposit', 'partial_payment', 'advance', 'refund', 'adjustment'];

const filteredReservations = computed(() => {
  if (!searchQuery.value) return props.reservations;
  const q = searchQuery.value.toLowerCase();
  return props.reservations.filter(r => 
    r.reservation_number.toLowerCase().includes(q) || 
    r.guest?.name.toLowerCase().includes(q)
  );
});

const selectedEntityInfo = computed(() => {
  if (selectedReservation.value) {
    return `${selectedReservation.value.guest?.name} (Res: ${selectedReservation.value.reservation_number})`;
  }
  if (form.guest_id) {
    const guest = props.guests.find(g => g.id === form.guest_id);
    return guest ? guest.name : '';
  }
  if (form.company_id) {
    const company = props.companies.find(c => c.id === form.company_id);
    return company ? company.name : '';
  }
  return '';
});

function selectReservation(res) {
  selectedReservation.value = res;
  form.reservation_id = res.id;
  form.guest_id = res.guest_id;
  form.company_id = res.company_id;
  
  // Auto set payment type based on status
  if (['checked_in', 'staying'].includes(res.status)) {
    form.payment_type = 'payment';
    form.is_deposit = false;
  } else if (['confirmed', 'reserved'].includes(res.status)) {
    form.payment_type = 'deposit';
    form.is_deposit = true;
  }
  
  // Auto set amount to balance
  form.amount = res.balance || 0;
}

function onGuestChange() {
  if (form.guest_id) {
    form.reservation_id = null;
    form.company_id = null;
    selectedReservation.value = null;
  }
}

function onCompanyChange() {
  if (form.company_id) {
    form.reservation_id = null;
    form.guest_id = null;
    selectedReservation.value = null;
  }
}

function nextStep() {
  step.value = 2;
}

function getMethodIcon(method) {
  switch(method) {
    case 'cash': return Banknote;
    case 'visa':
    case 'mastercard':
    case 'mada': return CreditCard;
    case 'bank_transfer':
    case 'cheque': return Landmark;
    case 'apple_pay': return Smartphone;
    case 'online': return Landmark;
    case 'other': return MoreHorizontal;
    default: return HelpCircle;
  }
}

function formatAmount(amount, currency = 'SAR') {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: currency,
  }).format(amount);
}

function submit(confirmNow) {
  form.confirm_now = confirmNow;
  form.post(route('finance.payments.store'), {
    onSuccess: () => {
      // Handled by controller redirect
    }
  });
}
</script>

<style scoped>
.text-primary { color: #e95a54; }
.bg-primary { background-color: #e95a54; }
.border-primary { border-color: #e95a54; }
.ring-primary { --tw-ring-color: #e95a54; }

.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 10px;
}
</style>
