<template>
  <div class="p-6 space-y-6 bg-[#f8f9fa] min-h-full flex flex-col">
    <!-- Header & Quick Filters -->
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-50 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
      <div>
        <h1 class="text-2xl font-bold text-[#2a273c]">In-House Guests</h1>
        <p class="text-xs font-black text-[#e95a54] uppercase tracking-widest mt-1">Current residents as of {{ formattedDate }}</p>
      </div>
      
      <div class="flex flex-wrap items-center gap-3">
        <div class="relative">
          <input 
            type="text" 
            v-model="filters.search" 
            placeholder="Search name or room..."
            class="bg-slate-50 border-none rounded-xl py-3 pl-10 pr-4 text-xs font-bold text-slate-600 focus:ring-2 focus:ring-[#e95a54] w-64"
          >
          <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
        </div>
        
        <select v-model="filters.room_type_id" class="bg-slate-50 border-none rounded-xl py-3 px-4 text-xs font-bold text-slate-600 focus:ring-2 focus:ring-[#e95a54]">
          <option value="">All Room Types</option>
          <option v-for="t in roomTypes" :key="t.id" :value="t.id">{{ t.name }}</option>
        </select>

        <select v-model="filters.room_floor_id" class="bg-slate-50 border-none rounded-xl py-3 px-4 text-xs font-bold text-slate-600 focus:ring-2 focus:ring-[#e95a54]">
          <option value="">All Floors</option>
          <option v-for="f in floors" :key="f.id" :value="f.id">{{ f.name }}</option>
        </select>

        <select v-model="filters.source_id" class="bg-slate-50 border-none rounded-xl py-3 px-4 text-xs font-bold text-slate-600 focus:ring-2 focus:ring-[#e95a54]">
          <option value="">All Sources</option>
          <option v-for="s in sources" :key="s.id" :value="s.id">{{ s.name }}</option>
        </select>
      </div>
    </div>

    <!-- In-House Table -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-50 overflow-hidden flex-1 flex flex-col">
      <div class="overflow-x-auto flex-1">
        <table class="w-full border-collapse">
          <thead>
            <tr class="bg-slate-50/50 border-b border-slate-50">
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Room</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Guest</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Check-In</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Exp. Checkout</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Balance</span></th>
              <th class="p-4 text-end"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Actions</span></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <tr v-for="res in inHouse" :key="res.id" class="hover:bg-slate-50/30 transition-colors group">
              <td class="p-4">
                <div class="flex flex-col">
                  <span class="text-sm font-bold text-[#2a273c]">{{ res.room?.number || 'TBD' }}</span>
                  <span class="text-[10px] text-slate-400 font-medium">{{ res.room?.floor }}</span>
                </div>
              </td>
              <td class="p-4">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-[#2a273c] font-bold">
                    {{ res.guest?.name?.charAt(0) }}
                  </div>
                  <div class="flex flex-col">
                    <span class="text-sm font-bold text-[#2a273c]">{{ res.guest?.name }}</span>
                    <span class="text-[10px] text-slate-400 font-medium">{{ res.guest?.phone }}</span>
                  </div>
                </div>
              </td>
              <td class="p-4 text-sm font-medium text-slate-600">
                {{ formatDate(res.check_in) }}
              </td>
              <td class="p-4 text-sm font-medium text-slate-600">
                {{ formatDate(res.check_out) }}
              </td>
              <td class="p-4">
                <span :class="[
                  'text-sm font-bold',
                  res.balance > 0 ? 'text-emerald-600' : (res.balance < 0 ? 'text-rose-600' : 'text-slate-600')
                ]">
                  {{ formatCurrency(res.balance) }}
                </span>
              </td>
              <td class="p-4 text-end">
                <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                  <button @click="view(res.id)" class="p-2 hover:bg-slate-100 rounded-lg transition-colors text-slate-400 hover:text-[#2a273c]" title="View Details">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                  </button>
                  <button @click="collectPayment(res)" class="p-2 hover:bg-slate-100 rounded-lg transition-colors text-slate-400 hover:text-emerald-600" title="Collect Payment">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                  </button>
                  <button @click="addService(res)" class="p-2 hover:bg-slate-100 rounded-lg transition-colors text-slate-400 hover:text-blue-600" title="Add Service">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 6v6m0 0v6m0-6h6m-6 0H6" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                  </button>
                  <button @click="checkOut(res)" class="bg-rose-100 text-rose-600 px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest hover:bg-rose-200 transition-colors">
                    Check-Out
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="inHouse.length === 0">
              <td colspan="6" class="p-20 text-center">
                 <div class="flex flex-col items-center">
                    <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mb-4">
                       <svg class="w-10 h-10 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-[#2a273c]">No In-House Guests</h3>
                    <p class="text-xs text-slate-400 font-medium">Currently there are no checked-in guests matching your filters.</p>
                 </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="p-6 bg-slate-50/50 border-t border-slate-50 flex items-center justify-between">
         <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Showing {{ inHouse.length }} guests in-house</span>
         <div class="flex gap-2">
            <button v-if="pagination.prev" @click="changePage(pagination.current - 1)" class="p-2 hover:bg-white rounded-xl transition-colors border border-transparent hover:border-slate-100"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg></button>
            <button v-if="pagination.next" @click="changePage(pagination.current + 1)" class="p-2 hover:bg-white rounded-xl transition-colors border border-transparent hover:border-slate-100"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg></button>
         </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref, computed, watch } from 'vue';
import { useRouter } from 'vue-router';
import api from '../services/api';
import dayjs from 'dayjs';
import Swal from 'sweetalert2';

const router = useRouter();
const inHouse = ref([]);
const roomTypes = ref([]);
const floors = ref([]);
const sources = ref([]);

const filters = reactive({ 
  search: '',
  room_type_id: '',
  room_floor_id: '',
  source_id: '',
  per_page: 25,
  page: 1
});
const pagination = reactive({ current: 1, next: false, prev: false });

const formattedDate = computed(() => dayjs().format('DD MMM YYYY'));

const load = async () => {
  try {
    const { data } = await api.get('/reservations/in-house', { params: filters });
    inHouse.value = data.data;
    pagination.next = !!data.links?.next;
    pagination.prev = !!data.links?.prev;
    pagination.current = data.meta?.current_page || 1;
  } catch (err) {
    console.error('Failed to load in-house guests', err);
  }
};

const loadLookups = async () => {
  try {
    const [rt, fl, sr] = await Promise.all([
      api.get('/master-data/room_types'),
      api.get('/master-data/room_floors'),
      api.get('/master-data/sources')
    ]);
    roomTypes.value = rt.data.data;
    floors.value = fl.data.data;
    sources.value = sr.data.data;
  } catch (err) {
    console.error('Failed to load lookups', err);
  }
};

const formatDate = (date) => date ? dayjs(date).format('DD MMM YYYY') : 'N/A';
const formatCurrency = (val) => new Intl.NumberFormat('en-SA', { style: 'currency', currency: 'SAR' }).format(val || 0);

const view = (id) => router.push(`/reservations/${id}`);

const checkOut = async (res) => {
  const result = await Swal.fire({
    title: 'Confirm Check-Out',
    text: `Are you sure you want to check-out ${res.guest?.name} from room ${res.room?.number}?`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#e95a54',
    confirmButtonText: 'Yes, Check-Out'
  });

  if (result.isConfirmed) {
    try {
      await api.post(`/reservations/${res.id}/check-out`);
      Swal.fire('Checked-Out!', 'Guest has been checked out successfully.', 'success');
      load();
    } catch (err) {
      Swal.fire('Error', err.response?.data?.message || 'Failed to check-out', 'error');
    }
  }
};

const collectPayment = (res) => {
  // Redirect to financial entry for this reservation
  router.push(`/financial/receipts/create?reservation_id=${res.id}`);
};

const addService = (res) => {
  // Redirect to add service (POS or similar)
  router.push(`/pos/services?reservation_id=${res.id}`);
};

const changePage = (page) => {
  filters.page = page;
  load();
};

watch(() => [filters.room_type_id, filters.room_floor_id, filters.source_id, filters.search], () => {
  filters.page = 1;
  load();
});

onMounted(() => {
  load();
  loadLookups();
});
</script>
