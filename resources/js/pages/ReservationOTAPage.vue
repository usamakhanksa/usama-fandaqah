<template>
  <div class="p-6 bg-slate-50 min-h-screen">
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-2xl font-bold text-slate-800">{{ $t('OTA Reservations') }}</h1>
        <p class="text-slate-500 text-sm mt-1">{{ $t('Manage bookings from external channels like Booking.com, Expedia, etc.') }}</p>
      </div>
      <div class="flex gap-3">
        <button 
          @click="fetchReservations"
          class="flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 rounded-lg text-slate-600 hover:bg-slate-50 transition-all shadow-sm"
        >
          <RotateCw class="w-4 h-4" />
          {{ $t('Refresh') }}
        </button>
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
            :placeholder="$t('Search by Guest or Res #...')"
            class="w-full pl-10 pr-4 py-2 bg-slate-50 border-none rounded-lg focus:ring-2 focus:ring-primary/20 transition-all"
            @input="debouncedFetch"
          >
        </div>
      </div>

      <div class="w-48">
        <select 
          v-model="filters.source_id"
          class="w-full px-4 py-2 bg-slate-50 border-none rounded-lg focus:ring-2 focus:ring-primary/20 transition-all text-slate-600"
          @change="fetchReservations"
        >
          <option :value="null">{{ $t('All Channels') }}</option>
          <option v-for="source in sources" :key="source.id" :value="source.id">
            {{ source.name[currentLocale] || source.name.en }}
          </option>
        </select>
      </div>

      <div class="w-48">
        <input 
          v-model="filters.date"
          type="date" 
          class="w-full px-4 py-2 bg-slate-50 border-none rounded-lg focus:ring-2 focus:ring-primary/20 transition-all text-slate-600"
          @change="fetchReservations"
        >
      </div>

      <div class="w-48">
        <select 
          v-model="filters.status"
          class="w-full px-4 py-2 bg-slate-50 border-none rounded-lg focus:ring-2 focus:ring-primary/20 transition-all text-slate-600"
          @change="fetchReservations"
        >
          <option value="">{{ $t('All Statuses') }}</option>
          <option value="pending">{{ $t('Pending') }}</option>
          <option value="confirmed">{{ $t('Confirmed') }}</option>
          <option value="canceled">{{ $t('Canceled') }}</option>
        </select>
      </div>
    </div>

    <!-- Table -->
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-slate-50 border-bottom border-slate-200">
            <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $t('Reservation #') }}</th>
            <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $t('Channel') }}</th>
            <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $t('Guest Name') }}</th>
            <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $t('Dates') }}</th>
            <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $t('Room') }}</th>
            <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $t('Status') }}</th>
            <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">{{ $t('Actions') }}</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <tr v-if="loading" v-for="i in 5" :key="i" class="animate-pulse">
            <td colspan="7" class="px-6 py-4"><div class="h-4 bg-slate-100 rounded w-full"></div></td>
          </tr>
          <tr v-else-if="reservations.length === 0">
            <td colspan="7" class="px-6 py-12 text-center text-slate-400">
              <div class="flex flex-col items-center gap-2">
                <Inbox class="w-12 h-12 text-slate-200" />
                <p>{{ $t('No OTA reservations found') }}</p>
              </div>
            </td>
          </tr>
          <tr v-for="res in reservations" :key="res.id" class="hover:bg-slate-50/50 transition-colors">
            <td class="px-6 py-4 font-medium text-slate-700">{{ res.code }}</td>
            <td class="px-6 py-4">
              <div class="flex items-center gap-2">
                <Globe class="w-4 h-4 text-primary" />
                <span class="text-slate-600">{{ res.source?.name[currentLocale] || res.source?.name.en || 'N/A' }}</span>
              </div>
            </td>
            <td class="px-6 py-4">
              <div class="flex flex-col">
                <span class="text-slate-700 font-medium">{{ res.guest?.name }}</span>
                <span class="text-slate-400 text-xs">{{ res.guest?.phone }}</span>
              </div>
            </td>
            <td class="px-6 py-4">
              <div class="flex flex-col text-sm">
                <span class="text-slate-600">{{ formatDate(res.check_in) }} - {{ formatDate(res.check_out) }}</span>
                <span class="text-slate-400 text-xs">{{ res.nights }} {{ $t('nights') }}</span>
              </div>
            </td>
            <td class="px-6 py-4 text-slate-600">{{ res.room?.number || $t('Assign on Arrival') }}</td>
            <td class="px-6 py-4">
              <span 
                class="px-2.5 py-1 rounded-full text-xs font-medium"
                :class="statusClass(res.status)"
              >
                {{ $t(res.status.charAt(0).toUpperCase() + res.status.slice(1)) }}
              </span>
            </td>
            <td class="px-6 py-4 text-right">
              <div class="flex justify-end gap-2">
                <button 
                  @click="syncStatus(res.id)"
                  class="p-2 text-primary hover:bg-primary/5 rounded-lg transition-colors"
                  :title="$t('Sync Status')"
                >
                  <RefreshCw class="w-4 h-4" />
                </button>
                <button 
                  @click="viewDetails(res.id)"
                  class="p-2 text-slate-400 hover:bg-slate-100 rounded-lg transition-colors"
                  :title="$t('View Details')"
                >
                  <Eye class="w-4 h-4" />
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div v-if="pagination.total > pagination.per_page" class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex justify-between items-center">
        <p class="text-sm text-slate-500">
          {{ $t('Showing') }} {{ pagination.from }} {{ $t('to') }} {{ pagination.to }} {{ $t('of') }} {{ pagination.total }}
        </p>
        <div class="flex gap-2">
          <button 
            @click="changePage(pagination.current_page - 1)"
            :disabled="pagination.current_page === 1"
            class="px-3 py-1 border border-slate-200 rounded bg-white text-slate-600 disabled:opacity-50"
          >
            {{ $t('Previous') }}
          </button>
          <button 
            @click="changePage(pagination.current_page + 1)"
            :disabled="pagination.current_page === pagination.last_page"
            class="px-3 py-1 border border-slate-200 rounded bg-white text-slate-600 disabled:opacity-50"
          >
            {{ $t('Next') }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import api from '@/services/api.js';
import dayjs from 'dayjs';
import { 
  Search, RotateCw, Globe, Eye, RefreshCw, Inbox, 
  CheckCircle2, XCircle, AlertCircle 
} from 'lucide-vue-next';
import Swal from 'sweetalert2';
// Native debounce implementation
const debounce = (fn, delay) => {
  let timeout;
  return (...args) => {
    clearTimeout(timeout);
    timeout = setTimeout(() => fn(...args), delay);
  };
};

const router = useRouter();
const reservations = ref([]);
const sources = ref([]);
const loading = ref(false);
const currentLocale = ref('en'); // Should be dynamic based on i18n

const filters = reactive({
  search: '',
  source_id: null,
  date: '',
  status: '',
  per_page: 25,
  page: 1
});

const pagination = ref({
  total: 0,
  current_page: 1,
  last_page: 1,
  per_page: 25,
  from: 0,
  to: 0
});

const fetchReservations = async () => {
  loading.value = true;
  try {
    const response = await api.get('/reservations/ota', { params: filters });
    reservations.value = response.data.data;
    pagination.value = response.data.meta;
  } catch (error) {
    console.error('Error fetching OTA reservations:', error);
    Swal.fire({
      icon: 'error',
      title: 'Oops...',
      text: 'Failed to load OTA reservations',
    });
  } finally {
    loading.value = false;
  }
};

const fetchSources = async () => {
  try {
    const response = await api.get('/sources');
    // Filter only OTA/Travel agent sources from the paginated response
    sources.value = (response.data.data || []).filter(s => s.is_travel_agent);
  } catch (error) {
    console.error('Error fetching sources:', error);
  }
};

const debouncedFetch = debounce(fetchReservations, 500);

const syncStatus = async (id) => {
  try {
    loading.value = true;
    await api.post(`/reservations/${id}/sync-status`);
    await fetchReservations();
    Swal.fire({
      icon: 'success',
      title: 'Synced!',
      text: 'Reservation status synchronized with channel.',
      timer: 2000,
      showConfirmButton: false
    });
  } catch (error) {
    console.error('Error syncing status:', error);
    Swal.fire({
      icon: 'error',
      title: 'Sync Failed',
      text: error.response?.data?.message || 'Could not sync with OTA channel.',
    });
  } finally {
    loading.value = false;
  }
};

const viewDetails = (id) => {
  router.push(`/reservations/management/${id}`);
};

const formatDate = (date) => {
  return dayjs(date).format('MMM DD, YYYY');
};

const statusClass = (status) => {
  switch (status) {
    case 'confirmed': return 'bg-emerald-50 text-emerald-600';
    case 'pending': return 'bg-amber-50 text-amber-600';
    case 'canceled': return 'bg-rose-50 text-rose-600';
    default: return 'bg-slate-50 text-slate-600';
  }
};

const changePage = (page) => {
  filters.page = page;
  fetchReservations();
};

onMounted(() => {
  fetchReservations();
  fetchSources();
});
</script>

<style scoped>
.text-primary { color: #e95a54; }
.bg-primary { background-color: #e95a54; }
</style>
