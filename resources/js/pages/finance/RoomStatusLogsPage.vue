<template>
  <div class="p-6 space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-black text-slate-900 tracking-tight">Room Status History</h1>
        <p class="text-slate-500 text-sm font-medium">Audit trail for room status transitions and housekeeping</p>
      </div>
      <div class="flex gap-3">
        <button @click="exportLogs" class="bg-white border border-slate-200 text-slate-700 px-4 py-2 rounded-xl flex items-center gap-2 hover:bg-slate-50 transition-all font-bold text-sm">
          <Download class="w-4 h-4" />
          Export Audit
        </button>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white p-6 rounded-[32px] border border-slate-100 shadow-sm grid grid-cols-1 md:grid-cols-5 gap-4">
      <div>
        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Room Number</label>
        <input v-model="filters.unit_id" type="text" placeholder="Filter by room..." class="w-full bg-slate-50 border-none rounded-2xl px-4 py-2.5 text-sm focus:ring-2 ring-rose-300 outline-none transition-all" />
      </div>
      <div>
        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Status</label>
        <select v-model="filters.status" class="w-full bg-slate-50 border-none rounded-2xl px-4 py-2.5 text-sm focus:ring-2 ring-rose-300 outline-none transition-all">
          <option :value="null">All Statuses</option>
          <option value="available">Available</option>
          <option value="dirty">Cleaning</option>
          <option value="maintenance">Maintenance</option>
          <option value="occupied">Occupied</option>
        </select>
      </div>
      <div>
        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Date From</label>
        <input v-model="filters.date_from" type="date" class="w-full bg-slate-50 border-none rounded-2xl px-4 py-2.5 text-sm focus:ring-2 ring-rose-300 outline-none transition-all" />
      </div>
      <div>
        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Date To</label>
        <input v-model="filters.date_to" type="date" class="w-full bg-slate-50 border-none rounded-2xl px-4 py-2.5 text-sm focus:ring-2 ring-rose-300 outline-none transition-all" />
      </div>
      <div class="flex items-end">
        <button @click="fetchLogs" class="w-full bg-slate-900 text-white py-2.5 rounded-2xl font-bold text-sm hover:bg-rose-600 transition-all shadow-lg shadow-slate-200">
          Apply Filters
        </button>
      </div>
    </div>

    <!-- View Toggle -->
    <div class="flex justify-center">
      <div class="bg-slate-100 p-1 rounded-2xl flex gap-1">
        <button 
          @click="viewType = 'table'" 
          :class="viewType === 'table' ? 'bg-white shadow-sm text-slate-900' : 'text-slate-500'"
          class="px-6 py-2 rounded-xl text-sm font-bold transition-all flex items-center gap-2"
        >
          <Table2 class="w-4 h-4" />
          Log Table
        </button>
        <button 
          @click="viewType = 'timeline'" 
          :class="viewType === 'timeline' ? 'bg-white shadow-sm text-slate-900' : 'text-slate-500'"
          class="px-6 py-2 rounded-xl text-sm font-bold transition-all flex items-center gap-2"
        >
          <History class="w-4 h-4" />
          Timeline View
        </button>
      </div>
    </div>

    <!-- Content -->
    <div v-if="loading" class="flex justify-center py-20">
      <div class="animate-spin rounded-full h-12 w-12 border-4 border-rose-500 border-t-transparent"></div>
    </div>

    <template v-else>
      <!-- Table View -->
      <div v-if="viewType === 'table'" class="bg-white rounded-[32px] border border-slate-100 shadow-sm overflow-hidden animate-in fade-in slide-in-from-bottom-4 duration-500">
        <div class="overflow-x-auto">
          <table class="w-full text-left">
            <thead>
              <tr class="bg-slate-50/50">
                <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Time</th>
                <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Room</th>
                <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Transition</th>
                <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">User</th>
                <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Reason / Trigger</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
              <tr v-for="log in logs" :key="log.id" class="hover:bg-slate-50/50 transition-colors group">
                <td class="px-6 py-4">
                  <div class="text-sm font-bold text-slate-900">{{ formatDate(log.changed_at) }}</div>
                  <div class="text-[10px] text-slate-400 font-medium">{{ formatTime(log.changed_at) }}</div>
                </td>
                <td class="px-6 py-4">
                  <div class="inline-flex items-center justify-center w-10 h-10 bg-slate-100 rounded-xl font-black text-slate-700 text-sm group-hover:bg-rose-50 group-hover:text-rose-600 transition-colors">
                    {{ log.unit?.unit_number || log.unit_id }}
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <span :class="getStatusClass(log.from_status)" class="text-[10px] font-black uppercase tracking-widest px-2 py-1 rounded-lg">
                      {{ log.from_status || 'INIT' }}
                    </span>
                    <ArrowRight class="w-3 h-3 text-slate-300" />
                    <span :class="getStatusClass(log.to_status)" class="text-[10px] font-black uppercase tracking-widest px-2 py-1 rounded-lg">
                      {{ log.to_status }}
                    </span>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600 text-xs font-bold">
                      {{ log.user?.name?.charAt(0) || 'S' }}
                    </div>
                    <span class="text-sm font-bold text-slate-700">{{ log.user?.name || 'System' }}</span>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <p class="text-sm text-slate-600 font-medium max-w-xs truncate">{{ log.change_reason }}</p>
                  <p v-if="log.reference_type" class="text-[10px] text-slate-400 uppercase font-bold tracking-tight mt-0.5">
                    Ref: {{ log.reference_type.split('\\').pop() }} #{{ log.reference_id }}
                  </p>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Timeline View -->
      <div v-else class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
        <div v-for="(group, date) in groupedLogs" :key="date" class="space-y-4">
          <div class="flex items-center gap-4">
            <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest whitespace-nowrap">{{ date }}</h3>
            <div class="h-px bg-slate-100 w-full"></div>
          </div>
          
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div v-for="log in group" :key="log.id" class="relative bg-white p-6 rounded-[32px] border border-slate-100 shadow-sm flex items-start gap-6 group hover:border-rose-100 transition-all">
              <div class="text-right min-w-[60px]">
                <div class="text-lg font-black text-slate-900 leading-none">{{ formatTime(log.changed_at) }}</div>
                <div class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-1">PM</div>
              </div>

              <div class="flex-1 space-y-4">
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-3">
                    <div class="px-3 py-1 bg-slate-900 text-white rounded-xl text-xs font-black">Room {{ log.unit?.unit_number }}</div>
                    <div class="flex items-center gap-2">
                      <span :class="getStatusClass(log.from_status)" class="text-[8px] font-black uppercase px-1.5 py-0.5 rounded-md">{{ log.from_status || 'INIT' }}</span>
                      <ArrowRight class="w-2 h-2 text-slate-300" />
                      <span :class="getStatusClass(log.to_status)" class="text-[8px] font-black uppercase px-1.5 py-0.5 rounded-md">{{ log.to_status }}</span>
                    </div>
                  </div>
                  <div class="text-[10px] font-bold text-slate-400">{{ log.user?.name || 'System' }}</div>
                </div>

                <p class="text-sm font-bold text-slate-700">{{ log.change_reason }}</p>
                
                <div v-if="log.reference_type" class="bg-slate-50 rounded-2xl p-4 flex items-center justify-between">
                  <div class="flex items-center gap-3">
                    <div class="p-2 bg-white rounded-xl shadow-sm">
                      <FileText v-if="log.reference_type.includes('CheckOut')" class="w-4 h-4 text-rose-500" />
                      <Wrench v-else-if="log.reference_type.includes('Maintenance')" class="w-4 h-4 text-slate-500" />
                      <Sparkles v-else class="w-4 h-4 text-amber-500" />
                    </div>
                    <div>
                      <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ log.reference_type.split('\\').pop() }}</div>
                      <div class="text-xs font-black text-slate-700">ID: #{{ log.reference_id }}</div>
                    </div>
                  </div>
                  <button class="text-xs font-bold text-rose-500 hover:text-rose-600">View Details</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- Pagination -->
    <div v-if="meta.last_page > 1" class="flex justify-between items-center bg-white p-4 rounded-[24px] border border-slate-100 shadow-sm">
      <span class="text-xs font-bold text-slate-400 uppercase tracking-widest ml-4">Page {{ meta.current_page }} of {{ meta.last_page }}</span>
      <div class="flex gap-2">
        <button 
          @click="changePage(meta.current_page - 1)" 
          :disabled="meta.current_page === 1"
          class="p-2 rounded-xl border border-slate-100 disabled:opacity-50 hover:bg-slate-50 transition-all"
        >
          <ChevronLeft class="w-5 h-5" />
        </button>
        <button 
          @click="changePage(meta.current_page + 1)" 
          :disabled="meta.current_page === meta.last_page"
          class="p-2 rounded-xl border border-slate-100 disabled:opacity-50 hover:bg-slate-50 transition-all"
        >
          <ChevronRight class="w-5 h-5" />
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { 
  Download, History, Table2, ArrowRight, ArrowLeft, 
  ChevronLeft, ChevronRight, FileText, Wrench, Sparkles 
} from 'lucide-vue-next';
import api from '../../services/api';

const logs = ref([]);
const loading = ref(false);
const viewType = ref('table');
const meta = ref({ current_page: 1, last_page: 1 });

const filters = ref({
  unit_id: '',
  status: null,
  date_from: '',
  date_to: '',
  per_page: 15,
});

const fetchLogs = async () => {
  loading.value = true;
  try {
    const { data } = await api.get('/room-status-logs', { 
      params: { ...filters.value, page: meta.value.current_page } 
    });
    logs.value = data.data;
    meta.value = {
      current_page: data.meta.current_page,
      last_page: data.meta.last_page,
    };
  } catch (err) {
    console.error('Failed to fetch room status logs', err);
  } finally {
    loading.value = false;
  }
};

const groupedLogs = computed(() => {
  const groups = {};
  logs.value.forEach(log => {
    const date = formatDate(log.changed_at);
    if (!groups[date]) groups[date] = [];
    groups[date].push(log);
  });
  return groups;
});

const changePage = (page) => {
  meta.value.current_page = page;
  fetchLogs();
};

const getStatusClass = (status) => {
  switch (status) {
    case 'available': return 'bg-emerald-50 text-emerald-600 border border-emerald-100';
    case 'dirty': return 'bg-amber-50 text-amber-600 border border-amber-100';
    case 'maintenance': return 'bg-slate-100 text-slate-600 border border-slate-200';
    case 'occupied': return 'bg-rose-50 text-rose-600 border border-rose-100';
    default: return 'bg-slate-50 text-slate-400 border border-slate-100';
  }
};

const formatDate = (date) => {
  if (!date) return '';
  return new Date(date).toLocaleDateString('en-US', { day: 'numeric', month: 'short', year: 'numeric' });
};

const formatTime = (date) => {
  if (!date) return '';
  return new Date(date).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
};

const exportLogs = () => {
  const csv = [
    ['Time', 'Room', 'From', 'To', 'User', 'Reason'],
    ...logs.value.map(l => [
      l.changed_at,
      l.unit?.unit_number,
      l.from_status,
      l.to_status,
      l.user?.name,
      l.change_reason
    ])
  ].map(row => row.join(',')).join('\n');

  const blob = new Blob([csv], { type: 'text/csv' });
  const url = window.URL.createObjectURL(blob);
  const link = document.createElement('a');
  link.href = url;
  link.setAttribute('download', 'room_status_audit.csv');
  document.body.appendChild(link);
  link.click();
  link.remove();
};

onMounted(fetchLogs);
</script>
