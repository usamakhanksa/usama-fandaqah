<template>
  <div class="p-6 bg-slate-50 min-h-screen">
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-2xl font-bold text-slate-800">{{ $t('Group Reservations') }}</h1>
        <p class="text-slate-500 text-sm mt-1">{{ $t('Manage block bookings for companies and groups') }}</p>
      </div>
      <div class="flex gap-3">
        <button 
          @click="router.push('/reservations/groups/create')"
          class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-all shadow-md font-medium"
        >
          <Plus class="w-4 h-4" />
          {{ $t('Create Group') }}
        </button>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white border border-slate-200 rounded-xl p-4 mb-6 shadow-sm flex flex-wrap gap-4 items-center">
      <div class="flex-1 min-w-[200px]">
        <div class="relative">
          <Search class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
          <input 
            v-model="filters.search"
            type="text" 
            :placeholder="$t('Search Group Name...')"
            class="w-full pl-10 pr-4 py-2 bg-slate-50 border-none rounded-lg focus:ring-2 focus:ring-primary/20 transition-all"
            @input="debouncedFetch"
          >
        </div>
      </div>

      <div class="w-48">
        <select 
          v-model="filters.company_id"
          class="w-full px-4 py-2 bg-slate-50 border-none rounded-lg focus:ring-2 focus:ring-primary/20 transition-all text-slate-600"
          @change="fetchGroups"
        >
          <option :value="null">{{ $t('All Companies') }}</option>
          <option v-for="company in companies" :key="company.id" :value="company.id">
            {{ company.name }}
          </option>
        </select>
      </div>

      <div class="w-48">
        <input 
          v-model="filters.date"
          type="date" 
          class="w-full px-4 py-2 bg-slate-50 border-none rounded-lg focus:ring-2 focus:ring-primary/20 transition-all text-slate-600"
          @change="fetchGroups"
        >
      </div>
    </div>

    <!-- Table -->
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-slate-50 border-bottom border-slate-200">
            <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $t('Group Name') }}</th>
            <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $t('Company') }}</th>
            <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-center">{{ $t('Rooms') }}</th>
            <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $t('Dates') }}</th>
            <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $t('Status') }}</th>
            <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">{{ $t('Actions') }}</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <tr v-if="loading" v-for="i in 5" :key="i" class="animate-pulse">
            <td colspan="6" class="px-6 py-4"><div class="h-4 bg-slate-100 rounded w-full"></div></td>
          </tr>
          <tr v-else-if="groups.length === 0">
            <td colspan="6" class="px-6 py-12 text-center text-slate-400">
              <div class="flex flex-col items-center gap-2">
                <Users class="w-12 h-12 text-slate-200" />
                <p>{{ $t('No group reservations found') }}</p>
              </div>
            </td>
          </tr>
          <tr v-for="group in groups" :key="group.id" class="hover:bg-slate-50/50 transition-colors">
            <td class="px-6 py-4">
              <div class="font-medium text-slate-700">{{ group.name }}</div>
              <div class="text-xs text-slate-400">#{{ group.id }}</div>
            </td>
            <td class="px-6 py-4 text-slate-600">
              {{ group.company?.name || $t('Individual Group') }}
            </td>
            <td class="px-6 py-4 text-center">
              <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-primary/10 text-primary text-xs font-bold">
                {{ group.reservations?.length || 0 }}
              </span>
            </td>
            <td class="px-6 py-4 text-sm text-slate-600">
               <div v-if="group.reservations && group.reservations.length > 0">
                  {{ formatDate(group.reservations[0].check_in) }} - {{ formatDate(group.reservations[0].check_out) }}
               </div>
               <div v-else class="text-slate-300">--</div>
            </td>
            <td class="px-6 py-4">
              <span 
                class="px-2.5 py-1 rounded-full text-xs font-medium"
                :class="statusClass(group.status)"
              >
                {{ $t(group.status.charAt(0).toUpperCase() + group.status.slice(1)) }}
              </span>
            </td>
            <td class="px-6 py-4 text-right">
              <div class="flex justify-end gap-2">
                <button 
                  @click="viewGroup(group.id)"
                  class="p-2 text-slate-400 hover:bg-slate-100 rounded-lg transition-colors"
                  :title="$t('View')"
                >
                  <Eye class="w-4 h-4" />
                </button>
                <button 
                  @click="cancelGroup(group.id)"
                  v-if="group.status !== 'cancelled'"
                  class="p-2 text-rose-400 hover:bg-rose-50 rounded-lg transition-colors"
                  :title="$t('Cancel')"
                >
                  <XCircle class="w-4 h-4" />
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
          <button @click="changePage(pagination.current_page - 1)" :disabled="pagination.current_page === 1" class="px-3 py-1 border border-slate-200 rounded bg-white text-slate-600 disabled:opacity-50 transition-all hover:bg-slate-50">
            <ChevronLeft class="w-4 h-4" />
          </button>
          <button @click="changePage(pagination.current_page + 1)" :disabled="pagination.current_page === pagination.last_page" class="px-3 py-1 border border-slate-200 rounded bg-white text-slate-600 disabled:opacity-50 transition-all hover:bg-slate-50">
            <ChevronRight class="w-4 h-4" />
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '@/services/api.js';
import dayjs from 'dayjs';
import { 
  Search, Plus, Users, Eye, XCircle, ChevronLeft, ChevronRight 
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
const groups = ref([]);
const companies = ref([]);
const loading = ref(false);

const filters = reactive({
  search: '',
  company_id: null,
  date: '',
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

const fetchGroups = async () => {
  loading.value = true;
  try {
    const response = await api.get('/reservations/groups', { params: filters });
    groups.value = response.data.data;
    pagination.value = {
      total: response.data.total,
      current_page: response.data.current_page,
      last_page: response.data.last_page,
      per_page: response.data.per_page,
      from: response.data.from,
      to: response.data.to
    };
  } catch (error) {
    console.error('Error fetching group reservations:', error);
  } finally {
    loading.value = false;
  }
};

const fetchCompanies = async () => {
  try {
    const response = await api.get('/companies');
    companies.value = response.data;
  } catch (error) {
    console.error('Error fetching companies:', error);
  }
};

const debouncedFetch = debounce(fetchGroups, 500);

const cancelGroup = async (id) => {
  const result = await Swal.fire({
    title: 'Are you sure?',
    text: "This will cancel all reservations in this group!",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#e95a54',
    cancelButtonColor: '#64748b',
    confirmButtonText: 'Yes, cancel it!'
  });

  if (result.isConfirmed) {
    try {
      await api.post(`/reservations/groups/${id}/cancel`);
      await fetchGroups();
      Swal.fire('Cancelled!', 'Group reservation has been cancelled.', 'success');
    } catch (error) {
      Swal.fire('Error!', 'Failed to cancel group.', 'error');
    }
  }
};

const viewGroup = (id) => {
  // Logic to view group details or edit
  router.push(`/reservations/groups/${id}`);
};

const formatDate = (date) => {
  return dayjs(date).format('MMM DD, YYYY');
};

const statusClass = (status) => {
  switch (status) {
    case 'confirmed': return 'bg-emerald-50 text-emerald-600';
    case 'pending': return 'bg-amber-50 text-amber-600';
    case 'cancelled': return 'bg-rose-50 text-rose-600';
    default: return 'bg-slate-50 text-slate-600';
  }
};

const changePage = (page) => {
  filters.page = page;
  fetchGroups();
};

onMounted(() => {
  fetchGroups();
  fetchCompanies();
});
</script>

<style scoped>
.text-primary { color: #e95a54; }
.bg-primary { background-color: #e95a54; }
</style>
