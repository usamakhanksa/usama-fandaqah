<template>
  <div class="p-6 space-y-6 bg-[#f8f9fa] min-h-full">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-3xl shadow-sm border border-slate-50">
      <div>
        <h1 class="text-2xl font-bold text-[#2a273c]">{{ $t('nav.reservations') || 'Reservations' }}</h1>
        <nav class="text-xs text-slate-400 mt-1 flex gap-2">
          <span>fandaqah</span>
          <span>/</span>
          <span>{{ $t('nav.management') || 'Management' }}</span>
        </nav>
      </div>
      <div class="flex items-center gap-3">
        <button 
          @click="exportAll"
          class="bg-white border border-slate-200 text-[#2a273c] px-4 py-2 rounded-xl text-sm font-semibold hover:bg-slate-50 transition-colors flex items-center gap-2"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
          {{ $t('table.export_all') || 'Export All' }}
        </button>
        <router-link to="/reservations/create" class="bg-[#e95a54] text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-opacity-90 shadow-lg shadow-rose-100 transition-all flex items-center gap-2">
          <span>+</span>
          {{ $t('table.add_reservation') || 'Create Reservation' }}
        </router-link>
      </div>
    </div>

    <!-- Filters Section -->
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-50 space-y-4">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Search -->
        <div class="relative">
          <input 
            v-model="filters.search" 
            class="w-full bg-slate-50 border-none rounded-xl py-3 px-10 text-sm focus:ring-2 focus:ring-[#e95a54] transition-all" 
            :placeholder="$t('table.search_placeholder') || 'Search #, guest, phone...'"
          >
          <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
        </div>

        <!-- Status Filter -->
        <select v-model="filters.status" class="bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-medium text-slate-600 focus:ring-2 focus:ring-[#e95a54]">
          <option value="">{{ $t('table.all_status') || 'All Statuses' }}</option>
          <option value="confirmed">Confirmed</option>
          <option value="checked-in">Checked In</option>
          <option value="checked-out">Checked Out</option>
          <option value="cancelled">Cancelled</option>
          <option value="no-show">No Show</option>
        </select>

        <!-- Date From -->
        <div class="relative">
          <input type="date" v-model="filters.date_in" class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-[#e95a54]">
          <span class="absolute right-3 top-1 text-[10px] text-slate-400 uppercase font-bold">In</span>
        </div>

        <!-- Date To -->
        <div class="relative">
          <input type="date" v-model="filters.date_out" class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-[#e95a54]">
          <span class="absolute right-3 top-1 text-[10px] text-slate-400 uppercase font-bold">Out</span>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 pt-2">
        <!-- Source Filter -->
        <select v-model="filters.source_id" class="bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-medium text-slate-600 focus:ring-2 focus:ring-[#e95a54]">
          <option value="">All Sources</option>
          <option v-for="source in sources" :key="source.id" :value="source.id">{{ source.name }}</option>
        </select>

        <!-- Room Type Filter -->
        <select v-model="filters.room_type_id" class="bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-medium text-slate-600 focus:ring-2 focus:ring-[#e95a54]">
          <option value="">All Room Types</option>
          <option v-for="type in roomTypes" :key="type.id" :value="type.id">{{ type.name }}</option>
        </select>

        <!-- Company Filter -->
        <select v-model="filters.company_id" class="bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-medium text-slate-600 focus:ring-2 focus:ring-[#e95a54]">
          <option value="">All Companies</option>
          <option v-for="company in companies" :key="company.id" :value="company.id">{{ company.name }}</option>
        </select>

        <!-- Per Page -->
        <select v-model="perPage" class="bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-medium text-slate-600 focus:ring-2 focus:ring-[#e95a54]">
          <option :value="25">25 per page</option>
          <option :value="50">50 per page</option>
          <option :value="100">100 per page</option>
        </select>
      </div>
    </div>

    <!-- Bulk Actions & Sorting -->
    <div class="flex items-center justify-between px-2">
      <div class="flex items-center gap-4">
        <div v-if="selectedIds.length > 0" class="flex items-center gap-2 bg-[#e95a54]/10 text-[#e95a54] px-4 py-2 rounded-xl animate-fade-in">
          <span class="text-xs font-bold">{{ selectedIds.length }} selected</span>
          <button @click="bulkExport" class="text-xs font-black uppercase hover:underline">Export</button>
          <button @click="bulkPrint" class="text-xs font-black uppercase hover:underline ml-2">Print</button>
          <button @click="selectedIds = []" class="text-xs font-black uppercase hover:underline ml-2">Clear</button>
        </div>
      </div>

      <div class="flex items-center gap-2">
        <label class="text-xs text-slate-400 font-bold uppercase tracking-wider">Sort By:</label>
        <select v-model="sortBy" class="bg-transparent border-none text-xs font-bold text-[#2a273c] focus:ring-0 cursor-pointer">
          <option value="created_at">Date Created</option>
          <option value="check_in">Date In</option>
          <option value="check_out">Date Out</option>
          <option value="guest_name">Guest Name</option>
          <option value="total_amount">Total Amount</option>
        </select>
      </div>
    </div>

    <!-- Reservations Table -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-50 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="bg-slate-50 text-slate-400 font-bold uppercase text-[10px] tracking-widest border-b border-slate-100">
              <th class="px-6 py-4 text-start">
                <input type="checkbox" @change="toggleAll" :checked="isAllSelected" class="rounded border-slate-300 text-[#e95a54] focus:ring-[#e95a54]">
              </th>
              <th class="px-6 py-4 text-start">Reservation #</th>
              <th class="px-6 py-4 text-start">Guest Name</th>
              <th class="px-6 py-4 text-start">Room(s)</th>
              <th class="px-6 py-4 text-start">Date In</th>
              <th class="px-6 py-4 text-start">Date Out</th>
              <th class="px-6 py-4 text-start">Nights</th>
              <th class="px-6 py-4 text-start">Source</th>
              <th class="px-6 py-4 text-start">Status</th>
              <th class="px-6 py-4 text-start">Balance</th>
              <th class="px-6 py-4 text-end">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <tr v-for="row in rows" :key="row.id" class="hover:bg-slate-50/50 transition-colors group">
              <td class="px-6 py-5">
                <input type="checkbox" v-model="selectedIds" :value="row.id" class="rounded border-slate-300 text-[#e95a54] focus:ring-[#e95a54]">
              </td>
              <td class="px-6 py-5 font-bold text-[#2a273c]">
                <div class="flex flex-col">
                  <span>{{ row.code }}</span>
                  <span class="text-[10px] text-slate-400 font-normal">Created {{ formatDate(row.created_at) }}</span>
                </div>
              </td>
              <td class="px-6 py-5">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 rounded-full bg-[#f2f0eb] flex items-center justify-center text-[#2a273c] font-bold text-xs uppercase overflow-hidden">
                    <img v-if="row.guest?.avatar" :src="row.guest.avatar" class="w-full h-full object-cover">
                    <span v-else>{{ row.guest?.name?.charAt(0) }}</span>
                  </div>
                  <div class="flex flex-col">
                    <span class="font-bold text-[#2a273c]">{{ row.guest?.name }}</span>
                    <span class="text-[11px] text-slate-400">{{ row.guest?.phone }}</span>
                  </div>
                </div>
              </td>
              <td class="px-6 py-5">
                <span class="bg-indigo-50 text-indigo-600 px-3 py-1 rounded-lg font-bold text-[11px]">Room {{ row.room?.number }}</span>
              </td>
              <td class="px-6 py-5 text-slate-600 font-medium">{{ row.check_in }}</td>
              <td class="px-6 py-5 text-slate-600 font-medium">{{ row.check_out }}</td>
              <td class="px-6 py-5 font-bold text-[#2a273c]">{{ calculateNights(row.check_in, row.check_out) }}</td>
              <td class="px-6 py-5">
                <span class="text-xs text-slate-500">{{ row.source?.name || 'Walk-in' }}</span>
              </td>
              <td class="px-6 py-5">
                <span 
                  :class="[
                    'px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest',
                    getStatusClass(row.status)
                  ]"
                >
                  {{ row.status }}
                </span>
              </td>
              <td class="px-6 py-5">
                <div class="flex flex-col">
                  <span class="font-bold text-[#2a273c]">{{ row.booking?.total_amount || 0 }} SAR</span>
                  <span class="text-[10px] text-emerald-500 font-bold" v-if="isPaid(row)">PAID</span>
                  <span class="text-[10px] text-rose-500 font-bold" v-else>UNPAID</span>
                </div>
              </td>
              <td class="px-6 py-5 text-end">
                <div class="flex items-center justify-end gap-2">
                  <router-link :to="`/reservations/${row.id}`" class="p-2 hover:bg-slate-100 rounded-lg transition-colors text-slate-400 hover:text-[#e95a54]" title="View">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                  </router-link>
                  <router-link :to="`/reservations/${row.id}/edit`" class="p-2 hover:bg-slate-100 rounded-lg transition-colors text-slate-400 hover:text-indigo-500" title="Edit">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                  </router-link>
                  <div class="relative group/menu">
                    <button class="p-2 hover:bg-slate-100 rounded-lg transition-colors text-slate-400">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                    </button>
                    <div class="absolute right-0 top-full mt-1 bg-white border border-slate-100 rounded-xl shadow-xl py-2 w-48 z-10 hidden group-hover/menu:block">
                      <button @click="checkIn(row.id)" v-if="row.status === 'confirmed'" class="w-full text-left px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-50 hover:text-emerald-600">Check-In</button>
                      <button @click="checkOut(row.id)" v-if="row.status === 'checked-in'" class="w-full text-left px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-50 hover:text-rose-600">Check-Out</button>
                      <button @click="cancelReservation(row.id)" v-if="row.status !== 'cancelled'" class="w-full text-left px-4 py-2 text-xs font-bold text-rose-500 hover:bg-rose-50">Cancel Reservation</button>
                      <div class="h-px bg-slate-50 my-1"></div>
                      <button @click="printReservation(row.id)" class="w-full text-left px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-50">Print Summary</button>
                    </div>
                  </div>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="bg-slate-50 px-6 py-4 flex items-center justify-between border-t border-slate-100">
        <span class="text-xs text-slate-400 font-medium">Showing {{ rows.length }} of {{ totalRecords }} results</span>
        <div class="flex items-center gap-1">
          <button @click="page--" :disabled="page === 1" class="p-2 rounded-lg hover:bg-white transition-all disabled:opacity-50">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
          </button>
          <div class="flex items-center gap-1">
            <button v-for="p in totalPages" :key="p" @click="page = p" :class="['w-8 h-8 rounded-lg text-xs font-bold transition-all', page === p ? 'bg-[#e95a54] text-white shadow-md shadow-rose-100' : 'hover:bg-white text-slate-400']">
              {{ p }}
            </button>
          </div>
          <button @click="page++" :disabled="page === totalPages" class="p-2 rounded-lg hover:bg-white transition-all disabled:opacity-50">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref, watch, computed } from 'vue';
import api from '../services/api';
import { useI18n } from 'vue-i18n';
import dayjs from 'dayjs';

const { t } = useI18n();

const filters = reactive({ 
  search: '', 
  status: '', 
  date_in: '', 
  date_out: '',
  source_id: '',
  room_type_id: '',
  company_id: ''
});

const perPage = ref(25);
const page = ref(1);
const sortBy = ref('created_at');
const sortOrder = ref('desc');
const rows = ref([]);
const totalRecords = ref(0);
const totalPages = ref(1);

const sources = ref([]);
const roomTypes = ref([]);
const companies = ref([]);
const selectedIds = ref([]);

const isAllSelected = computed(() => {
  return rows.value.length > 0 && selectedIds.value.length === rows.value.length;
});

const load = async () => {
  try {
    const { data } = await api.get('/reservations', { 
      params: { 
        ...filters,
        per_page: perPage.value,
        page: page.value,
        sort_by: sortBy.value,
        sort_order: sortOrder.value
      } 
    });
    rows.value = data.data || [];
    totalRecords.value = data.meta?.total || rows.value.length;
    totalPages.value = data.meta?.last_page || 1;
  } catch (err) {
    console.error('Failed to load reservations', err);
  }
};

const loadLookups = async () => {
  try {
    const [src, types, comp] = await Promise.all([
      api.get('/sources'),
      api.get('/master-data/room_types'),
      api.get('/company-profiles')
    ]);
    sources.value = src.data.data || [];
    roomTypes.value = types.data.data || [];
    companies.value = comp.data.data || [];
  } catch (err) {
    console.error('Failed to load lookups', err);
  }
};

const getStatusClass = (status) => {
  switch (status?.toLowerCase()) {
    case 'confirmed': return 'bg-blue-50 text-blue-600';
    case 'checked-in': return 'bg-emerald-50 text-emerald-600';
    case 'checked-out': return 'bg-slate-100 text-slate-500';
    case 'cancelled': return 'bg-rose-50 text-rose-600';
    case 'no-show': return 'bg-amber-50 text-amber-600';
    default: return 'bg-slate-50 text-slate-400';
  }
};

const calculateNights = (start, end) => {
  if (!start || !end) return 0;
  return dayjs(end).diff(dayjs(start), 'day');
};

const formatDate = (date) => dayjs(date).format('MMM D, YYYY');

const isPaid = (row) => row.booking?.invoices?.some(inv => inv.status === 'paid');

const toggleAll = () => {
  if (isAllSelected.value) {
    selectedIds.value = [];
  } else {
    selectedIds.value = rows.value.map(r => r.id);
  }
};

const checkIn = async (id) => {
  if (!confirm('Check-in this guest?')) return;
  await api.post(`/reservations/${id}/check-in`);
  load();
};

const checkOut = async (id) => {
  if (!confirm('Check-out this guest?')) return;
  await api.post(`/reservations/${id}/check-out`);
  load();
};

const cancelReservation = async (id) => {
  const reason = prompt('Cancellation reason?');
  if (reason === null) return;
  await api.delete(`/reservations/${id}`, { data: { cancellation_reason: reason } });
  load();
};

const exportAll = () => {
  window.location.href = `/api/reservations/export?${new URLSearchParams(filters).toString()}`;
};

const bulkExport = () => {
  window.location.href = `/api/reservations/export?ids=${selectedIds.value.join(',')}`;
};

const bulkPrint = () => {
  alert('Printing selected reservations: ' + selectedIds.value.join(', '));
};

const printReservation = (id) => {
  alert('Printing reservation #' + id);
};

watch(() => [filters, perPage, page, sortBy, sortOrder], load, { deep: true });

onMounted(() => {
  load();
  loadLookups();
});
</script>

<style scoped>
.animate-fade-in { animation: fadeIn 0.3s ease-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
</style>
