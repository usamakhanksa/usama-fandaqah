<template>
  <div class="p-6 space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-black text-slate-900 tracking-tight">Invoice Transfers (Folio → AR)</h1>
        <p class="text-slate-500 text-sm font-medium">Formally transfer guest folios to corporate city ledger</p>
      </div>
      <button @click="openTransferModal" class="bg-slate-900 text-white px-6 py-2.5 rounded-2xl flex items-center gap-2 hover:bg-rose-600 transition-all font-bold text-sm shadow-lg shadow-slate-200">
        <ArrowUpRight class="w-4 h-4" />
        New Transfer
      </button>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="bg-white p-6 rounded-[32px] border border-slate-100 shadow-sm">
        <div class="flex items-center gap-4">
          <div class="p-3 bg-indigo-50 rounded-2xl text-indigo-600">
            <FileText class="w-6 h-6" />
          </div>
          <div>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Transferred</p>
            <h3 class="text-2xl font-black text-slate-900">{{ formatCurrency(totalAmount) }}</h3>
          </div>
        </div>
      </div>
      <div class="bg-white p-6 rounded-[32px] border border-slate-100 shadow-sm">
        <div class="flex items-center gap-4">
          <div class="p-3 bg-emerald-50 rounded-2xl text-emerald-600">
            <CheckCircle2 class="w-6 h-6" />
          </div>
          <div>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Transfer Count</p>
            <h3 class="text-2xl font-black text-slate-900">{{ transfers.length }}</h3>
          </div>
        </div>
      </div>
      <div class="bg-white p-6 rounded-[32px] border border-slate-100 shadow-sm">
        <div class="flex items-center gap-4">
          <div class="p-3 bg-amber-50 rounded-2xl text-amber-600">
            <Clock class="w-6 h-6" />
          </div>
          <div>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Avg. Transfer Size</p>
            <h3 class="text-2xl font-black text-slate-900">{{ formatCurrency(avgAmount) }}</h3>
          </div>
        </div>
      </div>
    </div>

    <!-- Filter -->
    <AdvancedFilter 
      :can-export="true"
      @filter="onFilter"
      @export="onExport"
    >
      <template #extra="{ filters }">
        <select v-model="filters.company_id" class="bg-slate-50 border-none rounded-2xl px-4 py-3 text-sm font-bold text-slate-700 outline-none focus:ring-2 ring-rose-200">
          <option value="">All Companies</option>
          <option v-for="c in companies" :key="c.id" :value="c.id">{{ c.name }}</option>
        </select>
      </template>
    </AdvancedFilter>

    <!-- Table -->
    <div class="bg-white rounded-[32px] border border-slate-100 shadow-sm overflow-hidden relative">
      <div v-if="loading" class="absolute inset-0 bg-white/50 backdrop-blur-sm z-10 flex items-center justify-center">
        <div class="w-8 h-8 border-4 border-rose-500 border-t-transparent rounded-full animate-spin"></div>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left">
          <!-- ... table header ... -->
          <thead>
            <tr class="bg-slate-50/50">
              <th @click="toggleSort('created_at')" class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest cursor-pointer hover:text-slate-900 transition-colors">
                Date / By <ArrowUpDown v-if="filters.sort_by === 'created_at'" class="w-3 h-3 inline ml-1" />
              </th>
              <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Reservation</th>
              <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Company</th>
              <th @click="toggleSort('amount')" class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest cursor-pointer hover:text-slate-900 transition-colors">
                Amount <ArrowUpDown v-if="filters.sort_by === 'amount'" class="w-3 h-3 inline ml-1" />
              </th>
              <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Promissory</th>
              <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <tr v-for="t in transfers" :key="t.id" class="hover:bg-slate-50/50 transition-colors group">
              <!-- ... table body rows ... -->
              <td class="px-6 py-4">
                <div class="text-sm font-bold text-slate-900">{{ formatDate(t.transferred_at || t.created_at) }}</div>
                <div class="text-[10px] text-slate-400 font-bold uppercase">{{ t.transferred_by?.name || t.user?.name }}</div>
              </td>
              <td class="px-6 py-4">
                <div class="text-sm font-black text-slate-700">#{{ t.reservation?.code }}</div>
                <div class="text-[10px] text-slate-400 font-medium">{{ t.reservation?.guest?.name || 'Walk-in' }}</div>
              </td>
              <td class="px-6 py-4">
                <div class="text-sm font-bold text-slate-700">{{ t.company?.name }}</div>
                <div class="text-[10px] text-rose-500 font-bold uppercase tracking-tighter">City Ledger</div>
              </td>
              <td class="px-6 py-4">
                <div class="text-sm font-black text-slate-900">{{ formatCurrency(t.amount) }}</div>
              </td>
              <td class="px-6 py-4">
                <div v-if="t.promissory" class="text-xs font-bold text-slate-600">
                  <span class="bg-slate-100 px-2 py-1 rounded text-[10px]">#{{ t.promissory.serial || t.promissory.id }}</span>
                </div>
                <span v-else class="text-slate-300 text-[10px]">N/A</span>
              </td>
              <td class="px-6 py-4 text-right">
                <button class="p-2 text-slate-400 hover:text-slate-900 hover:bg-slate-100 rounded-xl transition-all opacity-0 group-hover:opacity-100">
                  <Printer class="w-4 h-4" />
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="pagination.total > 0" class="p-6 border-t border-slate-50 flex items-center justify-between">
        <span class="text-xs font-bold text-slate-400">Showing {{ pagination.from }}-{{ pagination.to }} of {{ pagination.total }}</span>
        <div class="flex gap-2">
          <button 
            @click="changePage(pagination.current_page - 1)" 
            :disabled="pagination.current_page === 1"
            class="p-2 border border-slate-100 rounded-xl hover:bg-slate-50 disabled:opacity-50 transition-all"
          >
            <ChevronLeft class="w-4 h-4" />
          </button>
          <button 
            @click="changePage(pagination.current_page + 1)" 
            :disabled="pagination.current_page === pagination.last_page"
            class="p-2 border border-slate-100 rounded-xl hover:bg-slate-50 disabled:opacity-50 transition-all"
          >
            <ChevronRight class="w-4 h-4" />
          </button>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="!loading && transfers.length === 0" class="p-20 text-center">
        <div class="w-20 h-20 bg-slate-50 rounded-[32px] flex items-center justify-center mx-auto mb-6 text-slate-300">
          <Inbox class="w-10 h-10" />
        </div>
        <h3 class="text-lg font-black text-slate-900">No transfers found</h3>
        <p class="text-sm text-slate-500 mt-2 font-medium">Try adjusting your filters or search term</p>
      </div>
    </div>

    <!-- Transfer Modal -->
    <div v-if="showModal" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm animate-in fade-in duration-300">
      <div class="bg-white rounded-[40px] w-full max-w-xl shadow-2xl overflow-hidden animate-in zoom-in-95 duration-300">
        <div class="p-8 border-b border-slate-50 flex items-center justify-between">
          <div>
            <h2 class="text-xl font-black text-slate-900">Folio Transfer to AR</h2>
            <p class="text-xs text-slate-500 font-medium mt-1">Select reservation and corporate account to proceed</p>
          </div>
          <button @click="showModal = false" class="p-2 text-slate-400 hover:bg-slate-50 rounded-full"><X class="w-5 h-5" /></button>
        </div>

        <form @submit.prevent="submitTransfer" class="p-8 space-y-6">
          <!-- Reservation Search -->
          <div>
            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Select Reservation</label>
            <div class="relative">
              <select v-model="form.reservation_id" @change="onReservationChange" required class="w-full bg-slate-50 border-none rounded-2xl px-4 py-3 text-sm focus:ring-2 ring-rose-300 outline-none transition-all appearance-none">
                <option :value="null">Choose reservation...</option>
                <option v-for="r in pendingReservations" :key="r.id" :value="r.id">
                  #{{ r.code }} - {{ r.guest?.name || r.guest_id }} ({{ formatCurrency(r.total_price) }})
                </option>
              </select>
              <ChevronDown class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <!-- Company Selection -->
            <div>
              <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Target Corporate Account</label>
              <div class="relative">
                <select v-model="form.company_id" required class="w-full bg-slate-50 border-none rounded-2xl px-4 py-3 text-sm focus:ring-2 ring-rose-300 outline-none transition-all appearance-none">
                  <option :value="null">Select company...</option>
                  <option v-for="c in companies" :key="c.id" :value="c.id">{{ c.name }}</option>
                </select>
                <ChevronDown class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" />
              </div>
            </div>

            <!-- Amount -->
            <div>
              <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Amount to Transfer</label>
              <div class="relative">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-sm">SAR</span>
                <input v-model="form.amount" type="number" step="0.01" required class="w-full bg-slate-50 border-none rounded-2xl pl-12 pr-4 py-3 text-sm focus:ring-2 ring-rose-300 outline-none transition-all font-black text-slate-900" />
              </div>
            </div>
          </div>

          <div>
            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Internal Notes</label>
            <textarea v-model="form.notes" rows="3" class="w-full bg-slate-50 border-none rounded-2xl px-4 py-3 text-sm focus:ring-2 ring-rose-300 outline-none transition-all" placeholder="Reason for transfer, PO reference, etc."></textarea>
          </div>

          <div class="bg-rose-50 p-6 rounded-[24px] border border-rose-100 flex gap-4 items-start">
            <div class="p-2 bg-white rounded-xl shadow-sm text-rose-500">
              <ShieldAlert class="w-5 h-5" />
            </div>
            <div>
              <p class="text-xs font-bold text-rose-900">Financial Impact</p>
              <p class="text-[10px] text-rose-700 font-medium leading-relaxed mt-1">
                This action will move the balance from the Guest Ledger to the City Ledger. 
                A digital promissory note will be automatically generated for the target corporate account.
              </p>
            </div>
          </div>

          <div class="flex gap-3 pt-2">
            <button type="button" @click="showModal = false" class="flex-1 px-6 py-4 border border-slate-100 text-slate-500 rounded-2xl font-bold hover:bg-slate-50 transition-all">Cancel</button>
            <button type="submit" :disabled="loading || !canSubmit" class="flex-[2] bg-slate-900 text-white px-6 py-4 rounded-2xl font-bold hover:bg-rose-600 transition-all shadow-lg shadow-slate-200 disabled:opacity-50">
              {{ loading ? 'Processing...' : 'Confirm AR Transfer' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { 
  ArrowUpRight, FileText, CheckCircle2, Clock, 
  Search, Printer, X, ChevronDown, ShieldAlert,
  ArrowUpDown, ChevronLeft, ChevronRight, Inbox
} from 'lucide-vue-next';
import api from '../../services/api';
import AdvancedFilter from '../../components/common/AdvancedFilter.vue';

const transfers = ref([]);
const pendingReservations = ref([]);
const companies = ref([]);
const showModal = ref(false);
const loading = ref(false);

const filters = ref({
  search: '',
  date_from: '',
  date_to: '',
  company_id: '',
  sort_by: 'created_at',
  sort_order: 'desc',
  page: 1,
  per_page: 15
});

const pagination = ref({
  current_page: 1,
  last_page: 1,
  total: 0,
  from: 0,
  to: 0
});

const form = ref({
  reservation_id: null,
  company_id: null,
  amount: 0,
  notes: ''
});

const totalAmount = computed(() => transfers.value.reduce((acc, t) => acc + parseFloat(t.amount || 0), 0));
const avgAmount = computed(() => transfers.value.length ? totalAmount.value / transfers.value.length : 0);

const canSubmit = computed(() => form.value.reservation_id && form.value.company_id && form.value.amount > 0);

const fetchData = async () => {
  loading.value = true;
  try {
    const [transRes, resRes, compRes] = await Promise.all([
      api.get('/ar/invoice-transfers', { params: filters.value }),
      api.get('/reservations', { params: { stay_type: 'checkin,checkout', has_balance: 1 } }),
      api.get('/companies')
    ]);
    
    transfers.value = transRes.data.data;
    pagination.value = {
      current_page: transRes.data.current_page,
      last_page: transRes.data.last_page,
      total: transRes.data.total,
      from: transRes.data.from,
      to: transRes.data.to
    };

    pendingReservations.value = resRes.data.data || resRes.data;
    companies.value = compRes.data.data || compRes.data;
  } catch (err) {
    console.error('Failed to fetch data', err);
  } finally {
    loading.value = false;
  }
};

const onFilter = (newFilters) => {
  filters.value = { ...filters.value, ...newFilters, page: 1 };
  fetchData();
};

const changePage = (page) => {
  filters.value.page = page;
  fetchData();
};

const toggleSort = (field) => {
  if (filters.value.sort_by === field) {
    filters.value.sort_order = filters.value.sort_order === 'asc' ? 'desc' : 'asc';
  } else {
    filters.value.sort_by = field;
    filters.value.sort_order = 'desc';
  }
  fetchData();
};

const onExport = async () => {
  try {
    const response = await api.get('/ar/invoice-transfers/export', { 
      params: filters.value,
      responseType: 'blob' 
    });
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `invoice_transfers_${new Date().toISOString().slice(0,10)}.csv`);
    document.body.appendChild(link);
    link.click();
  } catch (err) {
    alert('Export failed');
  }
};

const openTransferModal = () => {
  form.value = { reservation_id: null, company_id: null, amount: 0, notes: '' };
  showModal.value = true;
};

const onReservationChange = () => {
  const selected = pendingReservations.value.find(r => r.id === form.value.reservation_id);
  if (selected) {
    form.value.amount = selected.total_price || 0;
    if (selected.company_id) {
       form.value.company_id = selected.company_id;
    }
  }
};

const submitTransfer = async () => {
  loading.value = true;
  try {
    await api.post('/ar/invoice-transfer', form.value);
    await fetchData();
    showModal.value = false;
  } catch (err) {
    alert(err.response?.data?.message || 'Transfer failed');
  } finally {
    loading.value = false;
  }
};

const formatCurrency = (val) => {
  return new Intl.NumberFormat('en-SA', { style: 'currency', currency: 'SAR' }).format(val || 0);
};

const formatDate = (date) => {
  if (!date) return '';
  return new Date(date).toLocaleDateString('en-US', { day: 'numeric', month: 'short', year: 'numeric' });
};

onMounted(fetchData);
</script>
