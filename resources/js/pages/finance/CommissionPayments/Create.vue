<template>
  <div class="p-6 bg-slate-50 min-h-screen">
    <div class="max-w-5xl mx-auto">
      <div class="flex items-center gap-4 mb-6">
        <Link :href="route('finance.commission-payments.index')" class="p-2 bg-white border border-slate-200 rounded-lg text-slate-400 hover:text-slate-600 transition-all">
          <ArrowLeft class="w-5 h-5" />
        </Link>
        <div>
          <h1 class="text-2xl font-bold text-slate-800">{{ $t('New Commission Payment') }}</h1>
          <p class="text-slate-500 text-sm">{{ $t('Calculate and record agent commission payment') }}</p>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Selection & Calculation -->
        <div class="lg:col-span-1 flex flex-col gap-6">
          <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-bold text-slate-800 mb-4">{{ $t('Calculation Criteria') }}</h3>
            
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">{{ $t('Select Agent') }} *</label>
                <select v-model="selection.agent_id" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg outline-none focus:ring-2 focus:ring-primary/20 transition-all">
                  <option v-for="agent in agents" :key="agent.id" :value="agent.id">{{ agent.name }}</option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">{{ $t('Period From') }} *</label>
                <input v-model="selection.from" type="date" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg outline-none focus:ring-2 focus:ring-primary/20 transition-all">
              </div>

              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">{{ $t('Period To') }} *</label>
                <input v-model="selection.to" type="date" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg outline-none focus:ring-2 focus:ring-primary/20 transition-all">
              </div>

              <button 
                @click="calculateCommission"
                :disabled="!isSelectionValid || calculating"
                class="w-full py-2 bg-slate-800 text-white rounded-lg hover:bg-slate-700 transition-all font-medium flex items-center justify-center gap-2 disabled:opacity-50"
              >
                <RefreshCw class="w-4 h-4" :class="{ 'animate-spin': calculating }" />
                {{ $t('Calculate Commission') }}
              </button>
            </div>
          </div>

          <!-- Totals Summary -->
          <div v-if="calculationResult" class="bg-primary text-white border border-primary/20 rounded-xl shadow-md p-6">
            <h3 class="text-lg font-bold mb-4">{{ $t('Commission Summary') }}</h3>
            <div class="space-y-2">
              <div class="flex justify-between items-center opacity-80">
                <span>{{ $t('Reservations') }}</span>
                <span>{{ calculationResult.details.length }}</span>
              </div>
              <div class="flex justify-between items-center text-xl font-bold pt-2 border-t border-white/20">
                <span>{{ $t('Total Amount') }}</span>
                <span>{{ formatAmount(calculationResult.total_commission) }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Details & Payment -->
        <div class="lg:col-span-2">
          <div v-if="calculationResult" class="flex flex-col gap-6">
            <!-- Details Table -->
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
              <div class="p-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                <h3 class="font-bold text-slate-800">{{ $t('Reservation Details') }}</h3>
              </div>
              <div class="overflow-x-auto max-h-[400px]">
                <table class="w-full text-left text-sm">
                  <thead class="sticky top-0 bg-white shadow-sm">
                    <tr class="text-slate-500 font-semibold border-b border-slate-100">
                      <th class="px-4 py-3">{{ $t('Res Code') }}</th>
                      <th class="px-4 py-3">{{ $t('Guest') }}</th>
                      <th class="px-4 py-3 text-right">{{ $t('Revenue') }}</th>
                      <th class="px-4 py-3 text-right">{{ $t('Rate') }}</th>
                      <th class="px-4 py-3 text-right">{{ $t('Commission') }}</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-slate-50">
                    <tr v-for="detail in calculationResult.details" :key="detail.reservation_id" class="hover:bg-slate-50">
                      <td class="px-4 py-3 text-slate-700 font-medium">{{ detail.reservation_code }}</td>
                      <td class="px-4 py-3 text-slate-600">{{ detail.guest_name }}</td>
                      <td class="px-4 py-3 text-right font-mono">{{ formatAmount(detail.room_revenue) }}</td>
                      <td class="px-4 py-3 text-right text-slate-500">{{ detail.commission_rate }}%</td>
                      <td class="px-4 py-3 text-right font-bold text-emerald-600">{{ formatAmount(detail.commission_amount) }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- Payment Form -->
            <form @submit.prevent="submitPayment" class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
              <h3 class="text-lg font-bold text-slate-800 mb-4">{{ $t('Payment Information') }}</h3>
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-slate-700 mb-1">{{ $t('Payment Method') }} *</label>
                  <select v-model="form.payment_method" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg outline-none focus:ring-2 focus:ring-primary/20 transition-all">
                    <option value="cash">{{ $t('Cash') }}</option>
                    <option value="card">{{ $t('Card') }}</option>
                    <option value="bank_transfer">{{ $t('Bank Transfer') }}</option>
                    <option value="cheque">{{ $t('Cheque') }}</option>
                  </select>
                </div>

                <div v-if="form.payment_method === 'bank_transfer'">
                  <label class="block text-sm font-medium text-slate-700 mb-1">{{ $t('Bank') }}</label>
                  <select v-model="form.bank_id" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg outline-none focus:ring-2 focus:ring-primary/20 transition-all">
                    <option v-for="bank in banks" :key="bank.id" :value="bank.id">{{ bank.name }}</option>
                  </select>
                </div>

                <div>
                  <label class="block text-sm font-medium text-slate-700 mb-1">{{ $t('Payment Date') }} *</label>
                  <input v-model="form.payment_date" type="date" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg outline-none focus:ring-2 focus:ring-primary/20 transition-all">
                </div>

                <div>
                  <label class="block text-sm font-medium text-slate-700 mb-1">{{ $t('Reference #') }}</label>
                  <input v-model="form.reference_number" type="text" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg outline-none focus:ring-2 focus:ring-primary/20 transition-all">
                </div>

                <div class="col-span-2">
                  <label class="block text-sm font-medium text-slate-700 mb-1">{{ $t('Amount to Pay') }} *</label>
                  <input v-model="form.total_paid" type="number" step="0.01" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg outline-none focus:ring-2 focus:ring-primary/20 transition-all font-bold text-lg text-emerald-600">
                </div>

                <div class="col-span-2">
                  <label class="block text-sm font-medium text-slate-700 mb-1">{{ $t('Notes') }}</label>
                  <textarea v-model="form.notes" rows="2" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg outline-none focus:ring-2 focus:ring-primary/20 transition-all"></textarea>
                </div>
              </div>

              <div class="mt-6">
                <button type="submit" :disabled="form.processing" class="w-full py-3 bg-primary text-white rounded-lg hover:bg-primary/90 transition-all shadow-md font-bold text-lg disabled:opacity-50">
                  {{ $t('Confirm Payment') }}
                </button>
              </div>
            </form>
          </div>

          <!-- Empty State -->
          <div v-else class="h-full flex flex-col items-center justify-center bg-white border border-slate-200 border-dashed rounded-xl p-12 text-slate-400">
            <Calculator class="w-16 h-16 text-slate-100 mb-4" />
            <p class="text-lg font-medium">{{ $t('Select an agent and period to start calculation') }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, RefreshCw, Calculator } from 'lucide-vue-next';
import axios from 'axios';

const props = defineProps({
  agents: Array,
  banks: Array,
});

const selection = reactive({
  agent_id: '',
  from: '',
  to: '',
});

const isSelectionValid = computed(() => {
  return selection.agent_id && selection.from && selection.to;
});

const calculating = ref(false);
const calculationResult = ref(null);

const form = useForm({
  agent_id: '',
  from: '',
  to: '',
  payment_method: 'bank_transfer',
  bank_id: null,
  payment_date: new Date().toISOString().substr(0, 10),
  reference_number: '',
  total_paid: 0,
  notes: '',
});

async function calculateCommission() {
  calculating.value = true;
  try {
    const response = await axios.get(route('finance.commission-payments.calculate'), {
      params: selection
    });
    calculationResult.value = response.data;
    form.agent_id = selection.agent_id;
    form.from = selection.from;
    form.to = selection.to;
    form.total_paid = response.data.total_commission;
  } catch (error) {
    alert('Failed to calculate commission.');
  } finally {
    calculating.value = false;
  }
}

function submitPayment() {
  form.post(route('finance.commission-payments.store'));
}

function formatAmount(amount) {
  return '﷼ ' + Number(amount).toLocaleString('en-US', { minimumFractionDigits: 2 });
}
</script>

<style scoped>
.text-primary { color: #e95a54; }
.bg-primary { background-color: #e95a54; }
</style>
