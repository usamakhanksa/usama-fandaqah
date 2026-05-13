<template>
  <div class="p-6 space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-black text-slate-900 tracking-tight">Commission Management</h1>
        <p class="text-slate-500 text-sm font-medium">Approve and track payments for travel agent partners</p>
      </div>
      <div class="flex gap-3">
        <button @click="exportCommissions" class="bg-white border border-slate-200 text-slate-700 px-4 py-2.5 rounded-2xl flex items-center gap-2 hover:bg-slate-50 transition-all font-bold text-sm">
          <Download class="w-4 h-4" />
          Export Report
        </button>
      </div>
    </div>

    <!-- Monthly Summary -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
      <div v-for="stat in summary" :key="stat.status" class="bg-white p-6 rounded-[32px] border border-slate-100 shadow-sm">
        <div class="flex items-center justify-between mb-4">
          <div :class="getStatusIconClass(stat.status)" class="p-2.5 rounded-xl">
            <component :is="getStatusIcon(stat.status)" class="w-5 h-5" />
          </div>
          <span class="text-xs font-black text-slate-400 uppercase tracking-widest">{{ stat.status }}</span>
        </div>
        <h3 class="text-2xl font-black text-slate-900">{{ formatCurrency(stat.total) }}</h3>
        <p class="text-[10px] text-slate-400 font-bold uppercase mt-1">{{ stat.count }} Reservations</p>
      </div>
    </div>

    <!-- Filters & List -->
    <div class="bg-white rounded-[32px] border border-slate-100 shadow-sm overflow-hidden">
      <div class="p-6 border-b border-slate-50 flex flex-wrap gap-4 items-center">
        <div class="flex-1 min-w-[200px]">
          <select v-model="filters.status" class="w-full bg-slate-50 border-none rounded-2xl px-4 py-2.5 text-sm focus:ring-2 ring-rose-300 outline-none transition-all">
            <option :value="null">All Statuses</option>
            <option value="pending">Pending Approval</option>
            <option value="approved">Approved</option>
            <option value="paid">Paid</option>
            <option value="cancelled">Cancelled</option>
          </select>
        </div>
        <div class="flex-1 min-w-[200px]">
          <select v-model="filters.source_id" class="w-full bg-slate-50 border-none rounded-2xl px-4 py-2.5 text-sm focus:ring-2 ring-rose-300 outline-none transition-all">
            <option :value="null">All Agents</option>
            <option v-for="agent in agents" :key="agent.id" :value="agent.id">{{ agent.name.en }}</option>
          </select>
        </div>
        <button @click="fetchCommissions" class="bg-slate-900 text-white px-6 py-2.5 rounded-2xl font-bold text-sm hover:bg-rose-600 transition-all">
          Apply Filters
        </button>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left">
          <thead>
            <tr class="bg-slate-50/50">
              <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Reservation</th>
              <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Agent</th>
              <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Base Revenue</th>
              <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Commission</th>
              <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Status</th>
              <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <tr v-for="item in commissions" :key="item.id" class="hover:bg-slate-50/50 transition-colors">
              <td class="px-6 py-4">
                <div class="text-sm font-bold text-slate-900">#{{ item.reservation?.code || 'RSV-' + item.reservation_id }}</div>
                <div class="text-[10px] text-slate-400 font-medium">{{ formatDate(item.period_from) }} - {{ formatDate(item.period_to) }}</div>
              </td>
              <td class="px-6 py-4 text-sm font-bold text-slate-700">{{ item.source?.name?.en }}</td>
              <td class="px-6 py-4 text-sm font-medium text-slate-600">{{ formatCurrency(item.room_revenue_base) }}</td>
              <td class="px-6 py-4">
                <div class="text-sm font-black text-rose-600">{{ formatCurrency(item.commission_amount) }}</div>
                <div class="text-[10px] text-slate-400 font-bold">{{ item.commission_rate }}{{ item.commission_type === 'percentage' ? '%' : ' SAR' }}</div>
              </td>
              <td class="px-6 py-4">
                <span :class="getStatusBadgeClass(item.status)" class="text-[10px] font-black uppercase tracking-widest px-2.5 py-1 rounded-lg">
                  {{ item.status }}
                </span>
              </td>
              <td class="px-6 py-4 text-right">
                <button v-if="item.status === 'pending'" @click="approveCommission(item.id)" class="text-[10px] font-black uppercase text-emerald-600 hover:bg-emerald-50 px-3 py-1.5 rounded-lg transition-all">Approve</button>
                <button v-if="item.status === 'approved'" @click="openPayModal(item)" class="text-[10px] font-black uppercase text-indigo-600 hover:bg-indigo-50 px-3 py-1.5 rounded-lg transition-all">Mark Paid</button>
                <span v-if="item.status === 'paid'" class="text-[10px] font-bold text-slate-400">Paid on {{ formatDate(item.paid_at) }}</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Pay Modal -->
    <div v-if="showPayModal" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm">
      <div class="bg-white rounded-[40px] w-full max-w-md shadow-2xl overflow-hidden">
        <div class="p-8 border-b border-slate-50 flex items-center justify-between">
          <h2 class="text-xl font-black text-slate-900">Process Payment</h2>
          <button @click="showPayModal = false" class="p-2 text-slate-400 hover:bg-slate-50 rounded-full"><X class="w-5 h-5" /></button>
        </div>
        <div class="p-8 space-y-6">
          <div class="bg-slate-50 p-6 rounded-[24px]">
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Paying Commission To</p>
            <p class="text-lg font-black text-slate-900">{{ activePayment?.source?.name?.en }}</p>
            <div class="flex justify-between mt-4 pt-4 border-t border-slate-200">
              <span class="text-sm font-bold text-slate-500">Amount</span>
              <span class="text-lg font-black text-rose-600">{{ formatCurrency(activePayment?.commission_amount) }}</span>
            </div>
          </div>

          <div>
            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Payment Reference</label>
            <input v-model="payForm.payment_reference" type="text" class="w-full bg-slate-50 border-none rounded-2xl px-4 py-3 text-sm focus:ring-2 ring-rose-300 outline-none transition-all" placeholder="Bank transfer ID, Check #, etc." />
          </div>

          <div class="flex gap-3">
            <button @click="showPayModal = false" class="flex-1 px-6 py-3 border border-slate-100 text-slate-500 rounded-2xl font-bold hover:bg-slate-50">Cancel</button>
            <button @click="confirmPayment" class="flex-[2] bg-slate-900 text-white px-6 py-3 rounded-2xl font-bold hover:bg-rose-600 transition-all shadow-lg shadow-slate-200">Confirm Payment</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { 
  Download, Clock, CheckCircle2, CreditCard, AlertCircle,
  X, Search, Filter, ArrowUpRight
} from 'lucide-vue-next';
import api from '../../services/api';

const commissions = ref([]);
const summary = ref([]);
const agents = ref([]);
const loading = ref(false);
const showPayModal = ref(false);
const activePayment = ref(null);

const filters = ref({
  status: null,
  source_id: null,
  per_page: 20
});

const payForm = ref({
  payment_reference: '',
  paid_at: new Date().toISOString().split('T')[0]
});

const fetchCommissions = async () => {
  try {
    const { data } = await api.get('/commissions', { params: filters.value });
    commissions.value = data.data;
  } catch (err) {
    console.error('Failed to fetch commissions', err);
  }
};

const fetchSummary = async () => {
  try {
    const { data } = await api.get('/commissions/summary');
    summary.value = data.data;
  } catch (err) {
    console.error('Failed to fetch summary', err);
  }
};

const fetchAgents = async () => {
  try {
    const { data } = await api.get('/sources', { params: { is_travel_agent: 1 } });
    agents.value = data.data;
  } catch (err) {
    console.error('Failed to fetch agents', err);
  }
};

const approveCommission = async (id) => {
  try {
    await api.post(`/commissions/${id}/approve`);
    await fetchCommissions();
    await fetchSummary();
  } catch (err) {
    alert(err.response?.data?.message || 'Approval failed');
  }
};

const openPayModal = (item) => {
  activePayment.value = item;
  payForm.value.payment_reference = '';
  showPayModal.value = true;
};

const confirmPayment = async () => {
  if (!payForm.value.payment_reference) return alert('Reference is required');
  try {
    await api.post(`/commissions/${activePayment.value.id}/pay`, payForm.value);
    showPayModal.value = false;
    await fetchCommissions();
    await fetchSummary();
  } catch (err) {
    alert(err.response?.data?.message || 'Payment failed');
  }
};

const formatCurrency = (val) => {
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'SAR' }).format(val || 0);
};

const formatDate = (date) => {
  if (!date) return '';
  return new Date(date).toLocaleDateString('en-US', { day: 'numeric', month: 'short' });
};

const getStatusIcon = (status) => {
  switch (status) {
    case 'pending': return Clock;
    case 'approved': return CheckCircle2;
    case 'paid': return CreditCard;
    default: return AlertCircle;
  }
};

const getStatusIconClass = (status) => {
  switch (status) {
    case 'pending': return 'bg-amber-50 text-amber-500';
    case 'approved': return 'bg-emerald-50 text-emerald-500';
    case 'paid': return 'bg-indigo-50 text-indigo-500';
    default: return 'bg-slate-50 text-slate-500';
  }
};

const getStatusBadgeClass = (status) => {
  switch (status) {
    case 'pending': return 'bg-amber-50 text-amber-600';
    case 'approved': return 'bg-emerald-50 text-emerald-600';
    case 'paid': return 'bg-indigo-50 text-indigo-600';
    case 'cancelled': return 'bg-rose-50 text-rose-600';
    default: return 'bg-slate-100 text-slate-600';
  }
};

onMounted(() => {
  fetchCommissions();
  fetchSummary();
  fetchAgents();
});
</script>
