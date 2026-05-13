<template>
  <div class="p-6 space-y-6 bg-[#f8f9fa] min-h-full flex flex-col">
    <!-- Header & Quick Filters -->
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-50 flex flex-col md:flex-row md:items-center justify-between gap-6">
      <div>
        <h1 class="text-2xl font-bold text-[#2a273c]">Daily Departures</h1>
        <p class="text-xs font-black text-[#e95a54] uppercase tracking-widest mt-1">Expected Check-outs for {{ formattedDate }}</p>
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
        <select v-model="filters.source_id" class="bg-slate-50 border-none rounded-xl py-3 px-4 text-xs font-bold text-slate-600 focus:ring-2 focus:ring-[#e95a54]">
          <option value="">All Sources</option>
          <option v-for="s in sources" :key="s.id" :value="s.id">{{ s.name }}</option>
        </select>
      </div>
    </div>

    <!-- Departures Table -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-50 overflow-hidden flex-1 flex flex-col">
      <div class="overflow-x-auto flex-1">
        <table class="w-full border-collapse">
          <thead>
            <tr class="bg-slate-50/50 border-b border-slate-50">
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Time / Res #</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Guest Information</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Room / Type</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Balance</span></th>
              <th class="p-4 text-end"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Actions</span></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <tr v-for="departure in departures" :key="departure.id" class="hover:bg-slate-50/30 transition-colors group">
              <td class="p-4">
                <div class="flex flex-col">
                  <span class="text-sm font-bold text-slate-600">{{ formatTime(departure.check_out_time) }}</span>
                  <span class="text-[10px] font-black text-slate-400 uppercase tracking-tight">#{{ departure.reservation_number }}</span>
                </div>
              </td>
              <td class="p-4">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-[#2a273c] font-bold">
                    {{ departure.guest?.name?.charAt(0) }}
                  </div>
                  <div class="flex flex-col">
                    <span class="text-sm font-bold text-[#2a273c]">{{ departure.guest?.name }}</span>
                    <span class="text-[10px] text-slate-400 font-medium">{{ departure.guest?.phone }}</span>
                  </div>
                </div>
              </td>
              <td class="p-4">
                <div class="flex flex-col">
                  <span class="text-sm font-bold text-[#2a273c]">Room {{ departure.room?.number }}</span>
                  <span class="text-[10px] text-slate-400 font-medium">{{ departure.room?.room_type?.name }}</span>
                </div>
              </td>
              <td class="p-4">
                <div class="flex flex-col">
                  <span :class="['text-sm font-bold', departure.balance > 0 ? 'text-rose-500' : 'text-emerald-500']">
                    {{ formatCurrency(departure.balance) }}
                  </span>
                  <span class="text-[10px] font-black text-slate-300 uppercase">Current Balance</span>
                </div>
              </td>
              <td class="p-4 text-end">
                <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                  <button @click="printInvoice(departure.id)" class="p-2 hover:bg-slate-100 rounded-lg transition-colors text-slate-400 hover:text-[#2a273c]" title="Print Invoice">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                  </button>
                  <button @click="view(departure.id)" class="bg-slate-100 text-[#2a273c] px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest hover:bg-slate-200 transition-colors">
                    View
                  </button>
                  <button @click="checkOut(departure)" class="bg-[#2a273c] text-white px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest shadow-lg shadow-slate-100 hover:bg-opacity-90 transition-all">
                    Check-Out
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="departures.length === 0">
              <td colspan="5" class="p-20 text-center">
                 <div class="flex flex-col items-center">
                    <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mb-4">
                       <svg class="w-10 h-10 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-[#2a273c]">No Departures Found</h3>
                    <p class="text-xs text-slate-400 font-medium">There are no guests scheduled to check-out for this criteria.</p>
                 </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="p-6 bg-slate-50/50 border-t border-slate-50 flex items-center justify-between">
         <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Showing {{ departures.length }} expected check-outs</span>
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
const departures = ref([]);
const roomTypes = ref([]);
const sources = ref([]);
const filters = reactive({ 
  date: dayjs().format('YYYY-MM-DD'), 
  room_type_id: '',
  source_id: '',
  per_page: 25,
  page: 1
});
const pagination = reactive({ current: 1, next: false, prev: false });

const formattedDate = computed(() => dayjs(filters.date).format('DD MMM YYYY'));

const load = async () => {
  try {
    const { data } = await api.get('/reservations/departures', { params: filters });
    departures.value = data.data;
    pagination.next = !!data.links?.next;
    pagination.prev = !!data.links?.prev;
    pagination.current = data.meta?.current_page || 1;
  } catch (err) {
    console.error('Failed to load departures', err);
  }
};

const loadLookups = async () => {
  try {
    const [types, srcs] = await Promise.all([
      api.get('/master-data/room_types'),
      api.get('/master-data/sources')
    ]);
    roomTypes.value = types.data.data;
    sources.value = srcs.data.data;
  } catch (err) {
    console.error('Failed to load lookups', err);
  }
};

const formatTime = (time) => time ? dayjs(`2000-01-01 ${time}`).format('hh:mm A') : 'N/A';
const formatCurrency = (val) => new Intl.NumberFormat('en-SA', { style: 'currency', currency: 'SAR' }).format(val);

const view = (id) => router.push(`/reservations/${id}`);

const checkOut = async (departure) => {
  if (departure.balance > 0) {
    const settle = await Swal.fire({
      title: 'Outstanding Balance',
      text: `Guest has a balance of ${formatCurrency(departure.balance)}. Settle now?`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Settle & Check-Out',
      cancelButtonText: 'Check-Out Anyway'
    });
    
    if (settle.isDismissed && settle.dismiss === Swal.DismissReason.cancel) {
      // Proceed with check-out anyway if user clicks "Check-Out Anyway"
    } else if (!settle.isConfirmed) {
       return;
    }
  }

  const result = await Swal.fire({
    title: 'Confirm Check-Out',
    text: `Are you sure you want to check-out ${departure.guest?.name}?`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#2a273c',
    confirmButtonText: 'Yes, Check-Out'
  });

  if (result.isConfirmed) {
    try {
      await api.post(`/reservations/${departure.id}/check-out`);
      Swal.fire('Checked-Out!', 'The guest has been successfully checked-out.', 'success');
      load();
    } catch (err) {
      Swal.fire('Error', err.response?.data?.message || 'Failed to check-out', 'error');
    }
  }
};

const printInvoice = (id) => {
  window.open(`/reservations/${id}/print-invoice`, '_blank');
};

const changePage = (page) => {
  filters.page = page;
  load();
};

watch(() => [filters.date, filters.room_type_id, filters.source_id], () => {
  filters.page = 1;
  load();
});

onMounted(() => {
  load();
  loadLookups();
});
</script>
