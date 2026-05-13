<template>
  <div class="p-6 space-y-6 bg-[#f8f9fa] min-h-full flex flex-col">
    <!-- Header & Quick Filters -->
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-50 flex flex-col md:flex-row md:items-center justify-between gap-6">
      <div>
        <h1 class="text-2xl font-bold text-[#2a273c]">Daily Arrivals</h1>
        <p class="text-xs font-black text-[#e95a54] uppercase tracking-widest mt-1">Expected Guests for {{ formattedDate }}</p>
      </div>
      
      <div class="flex flex-wrap items-center gap-3">
        <input 
          type="date" 
          v-model="filters.date" 
          class="bg-slate-50 border-none rounded-xl py-3 px-4 text-xs font-bold text-slate-600 focus:ring-2 focus:ring-[#e95a54]"
        >
        <select v-model="filters.room_type_id" class="bg-slate-50 border-none rounded-xl py-3 px-4 text-xs font-bold text-slate-600 focus:ring-2 focus:ring-[#e95a54]">
          <option value="">All Room Types</option>
          <option v-for="t in roomTypes" :key="t.id" :value="t.id">{{ t.name }}</option>
        </select>
        <div class="flex items-center gap-2 bg-slate-50 px-4 py-3 rounded-xl">
           <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">VIP Only</span>
           <input type="checkbox" v-model="filters.vip" class="rounded text-[#e95a54] focus:ring-[#e95a54]">
        </div>
      </div>
    </div>

    <!-- Arrivals Table -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-50 overflow-hidden flex-1 flex flex-col">
      <div class="overflow-x-auto flex-1">
        <table class="w-full border-collapse">
          <thead>
            <tr class="bg-slate-50/50 border-b border-slate-50">
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">ETA / Res #</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Guest Information</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Room / Type</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Source</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Special Requests</span></th>
              <th class="p-4 text-end"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Actions</span></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <tr v-for="arrival in arrivals" :key="arrival.id" class="hover:bg-slate-50/30 transition-colors group">
              <td class="p-4">
                <div class="flex flex-col">
                  <span class="text-sm font-bold text-[#e95a54]">{{ formatTime(arrival.check_in_time) }}</span>
                  <span class="text-[10px] font-black text-slate-400 uppercase tracking-tight">#{{ arrival.reservation_number }}</span>
                </div>
              </td>
              <td class="p-4">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-[#2a273c] font-bold">
                    {{ arrival.guest?.name?.charAt(0) }}
                  </div>
                  <div class="flex flex-col">
                    <div class="flex items-center gap-2">
                      <span class="text-sm font-bold text-[#2a273c]">{{ arrival.guest?.name }}</span>
                      <span v-if="arrival.guest?.is_vip" class="bg-amber-100 text-amber-600 text-[8px] font-black px-1.5 py-0.5 rounded uppercase">VIP</span>
                    </div>
                    <span class="text-[10px] text-slate-400 font-medium">{{ arrival.guest?.phone }}</span>
                  </div>
                </div>
              </td>
              <td class="p-4">
                <div class="flex flex-col">
                  <span class="text-sm font-bold text-[#2a273c]">Room {{ arrival.room?.number || 'TBD' }}</span>
                  <span class="text-[10px] text-slate-400 font-medium">{{ arrival.room?.room_type?.name }}</span>
                </div>
              </td>
              <td class="p-4">
                <span class="bg-slate-100 text-slate-600 text-[10px] font-bold px-2 py-1 rounded-lg">{{ arrival.source?.name }}</span>
              </td>
              <td class="p-4 max-w-[200px]">
                <p class="text-[10px] text-slate-500 italic truncate" :title="arrival.special_requests">
                  {{ arrival.special_requests || 'No special requests' }}
                </p>
              </td>
              <td class="p-4 text-end">
                <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                  <button @click="printRegCard(arrival.id)" class="p-2 hover:bg-slate-100 rounded-lg transition-colors text-slate-400 hover:text-[#2a273c]" title="Print Reg Card">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                  </button>
                  <button @click="view(arrival.id)" class="bg-slate-100 text-[#2a273c] px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest hover:bg-slate-200 transition-colors">
                    View
                  </button>
                  <button @click="checkIn(arrival)" class="bg-[#e95a54] text-white px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest shadow-lg shadow-rose-100 hover:bg-opacity-90 transition-all">
                    Check-In
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="arrivals.length === 0">
              <td colspan="6" class="p-20 text-center">
                 <div class="flex flex-col items-center">
                    <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mb-4">
                       <svg class="w-10 h-10 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-[#2a273c]">No Arrivals Found</h3>
                    <p class="text-xs text-slate-400 font-medium">There are no reservations scheduled to arrive for this criteria.</p>
                 </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="p-6 bg-slate-50/50 border-t border-slate-50 flex items-center justify-between">
         <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Showing {{ arrivals.length }} expected guests</span>
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
const arrivals = ref([]);
const roomTypes = ref([]);
const filters = reactive({ 
  date: dayjs().format('YYYY-MM-DD'), 
  room_type_id: '',
  vip: false,
  per_page: 25,
  page: 1
});
const pagination = reactive({ current: 1, next: false, prev: false });

const formattedDate = computed(() => dayjs(filters.date).format('DD MMM YYYY'));

const load = async () => {
  try {
    const { data } = await api.get('/reservations/arrivals', { params: filters });
    arrivals.value = data.data;
    pagination.next = !!data.links?.next;
    pagination.prev = !!data.links?.prev;
    pagination.current = data.meta?.current_page || 1;
  } catch (err) {
    console.error('Failed to load arrivals', err);
  }
};

const loadLookups = async () => {
  try {
    const { data } = await api.get('/master-data/room_types');
    roomTypes.value = data.data;
  } catch (err) {
    console.error('Failed to load lookups', err);
  }
};

const formatTime = (time) => time ? dayjs(`2000-01-01 ${time}`).format('hh:mm A') : 'N/A';

const view = (id) => router.push(`/reservations/${id}`);

const checkIn = async (arrival) => {
  const result = await Swal.fire({
    title: 'Confirm Check-In',
    text: `Are you sure you want to check-in ${arrival.guest?.name}?`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#e95a54',
    confirmButtonText: 'Yes, Check-In'
  });

  if (result.isConfirmed) {
    try {
      await api.post(`/reservations/${arrival.id}/check-in`);
      Swal.fire('Checked-In!', 'The guest has been successfully checked-in.', 'success');
      load();
    } catch (err) {
      Swal.fire('Error', err.response?.data?.message || 'Failed to check-in', 'error');
    }
  }
};

const printRegCard = (id) => {
  window.open(`/reservations/${id}/print-reg-card`, '_blank');
};

const changePage = (page) => {
  filters.page = page;
  load();
};

watch(() => [filters.date, filters.room_type_id, filters.vip], () => {
  filters.page = 1;
  load();
});

onMounted(() => {
  load();
  loadLookups();
});
</script>
