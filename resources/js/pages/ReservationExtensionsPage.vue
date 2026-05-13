<template>
  <div class="p-6 space-y-6 bg-[#f8f9fa] min-h-full flex flex-col">
    <!-- Header & Quick Filters -->
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-50 flex flex-col md:flex-row md:items-center justify-between gap-6">
      <div>
        <h1 class="text-2xl font-bold text-[#2a273c]">Stay Extensions History</h1>
        <p class="text-xs font-black text-[#e95a54] uppercase tracking-widest mt-1">Audit Trail of Reservation Extensions</p>
      </div>
      
      <div class="flex flex-wrap items-center gap-3">
        <input 
          type="date" 
          v-model="filters.date" 
          class="bg-slate-50 border-none rounded-xl py-3 px-4 text-xs font-bold text-slate-600 focus:ring-2 focus:ring-[#e95a54]"
          placeholder="Filter by Date"
        >
        <div class="relative">
          <input 
            type="text" 
            v-model="filters.search" 
            class="bg-slate-50 border-none rounded-xl py-3 px-4 text-xs font-bold text-slate-600 focus:ring-2 focus:ring-[#e95a54] pl-10"
            placeholder="Search Reservation..."
          >
          <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
        </div>
      </div>
    </div>

    <!-- Extensions Table -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-50 overflow-hidden flex-1 flex flex-col">
      <div class="overflow-x-auto flex-1">
        <table class="w-full border-collapse">
          <thead>
            <tr class="bg-slate-50/50 border-b border-slate-50">
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Date / Time</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Reservation</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Guest</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Original Checkout</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">New Checkout</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Reason / By</span></th>
              <th class="p-4 text-end"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Actions</span></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <tr v-for="extension in extensions" :key="extension.id" class="hover:bg-slate-50/30 transition-colors group">
              <td class="p-4">
                <div class="flex flex-col">
                  <span class="text-sm font-bold text-[#2a273c]">{{ formatDate(extension.created_at) }}</span>
                  <span class="text-[10px] font-black text-slate-400 uppercase tracking-tight">{{ formatTime(extension.created_at) }}</span>
                </div>
              </td>
              <td class="p-4">
                <span class="text-sm font-bold text-[#e95a54]">#{{ extension.reservation?.number }}</span>
              </td>
              <td class="p-4">
                <div class="flex flex-col">
                  <span class="text-sm font-bold text-[#2a273c]">{{ extension.reservation?.customer?.name }}</span>
                  <span class="text-[10px] text-slate-400 font-medium">Guest ID: {{ extension.reservation?.customer_id }}</span>
                </div>
              </td>
              <td class="p-4">
                <span class="text-xs font-medium text-slate-400 line-through">{{ formatDate(extension.old_check_out) }}</span>
              </td>
              <td class="p-4">
                <div class="flex items-center gap-2">
                   <span class="bg-emerald-50 text-emerald-600 px-2 py-1 rounded-lg text-xs font-bold border border-emerald-100">{{ formatDate(extension.new_check_out) }}</span>
                   <span class="text-[10px] font-black text-emerald-400 uppercase tracking-widest">+{{ extension.extension_days }} Days</span>
                </div>
              </td>
              <td class="p-4">
                <div class="flex flex-col">
                  <span class="text-xs text-slate-600 font-medium">{{ extension.reason || 'No reason provided' }}</span>
                  <span class="text-[10px] font-black text-[#e95a54] uppercase tracking-widest mt-0.5">By: {{ extension.creator?.name }}</span>
                </div>
              </td>
              <td class="p-4 text-end">
                <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                  <button @click="viewReservation(extension.reservation_id)" class="bg-slate-100 text-[#2a273c] px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest hover:bg-slate-200 transition-colors">
                    View Reservation
                  </button>
                  <button @click="viewDetails(extension)" class="bg-[#2a273c] text-white px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest hover:bg-opacity-90 transition-all">
                    Details
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="extensions.length === 0">
              <td colspan="7" class="p-20 text-center">
                 <div class="flex flex-col items-center">
                    <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mb-4">
                       <svg class="w-10 h-10 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-[#2a273c]">No Extensions Found</h3>
                    <p class="text-xs text-slate-400 font-medium">There are no stay extension records matching your filters.</p>
                 </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="p-6 bg-slate-50/50 border-t border-slate-50 flex items-center justify-between">
         <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Showing {{ extensions.length }} extensions</span>
         <div class="flex gap-2">
            <button v-if="pagination.prev" @click="changePage(pagination.current - 1)" class="p-2 hover:bg-white rounded-xl transition-colors border border-transparent hover:border-slate-100"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg></button>
            <button v-if="pagination.next" @click="changePage(pagination.current + 1)" class="p-2 hover:bg-white rounded-xl transition-colors border border-transparent hover:border-slate-100"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg></button>
         </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import api from '../services/api';
import dayjs from 'dayjs';
import Swal from 'sweetalert2';

const router = useRouter();
const extensions = ref([]);
const filters = reactive({ 
  date: '', 
  search: '',
  per_page: 20,
  page: 1
});
const pagination = reactive({ current: 1, next: false, prev: false });

const load = async () => {
  try {
    const { data } = await api.get('/reservations/extensions', { params: filters });
    extensions.value = data.data;
    pagination.next = !!data.next_page_url;
    pagination.prev = !!data.prev_page_url;
    pagination.current = data.current_page || 1;
  } catch (err) {
    console.error('Failed to load extensions', err);
  }
};

const formatDate = (date) => dayjs(date).format('DD MMM YYYY');
const formatTime = (date) => dayjs(date).format('hh:mm A');

const viewReservation = (id) => router.push(`/reservations/${id}`);

const viewDetails = (extension) => {
  Swal.fire({
    title: 'Extension Details',
    html: `
      <div class="text-start space-y-2 text-sm">
        <p><strong>Reservation:</strong> #${extension.reservation?.number}</p>
        <p><strong>Guest:</strong> ${extension.reservation?.customer?.name}</p>
        <p><strong>Original Checkout:</strong> ${formatDate(extension.old_check_out)}</p>
        <p><strong>New Checkout:</strong> ${formatDate(extension.new_check_out)}</p>
        <p><strong>Extension Days:</strong> ${extension.extension_days} Days</p>
        <p><strong>Reason:</strong> ${extension.reason || 'N/A'}</p>
        <p><strong>Performed By:</strong> ${extension.creator?.name}</p>
        <p><strong>Date:</strong> ${dayjs(extension.created_at).format('YYYY-MM-DD HH:mm:ss')}</p>
      </div>
    `,
    icon: 'info',
    confirmButtonColor: '#2a273c'
  });
};

const changePage = (page) => {
  filters.page = page;
  load();
};

watch(() => [filters.date, filters.search], () => {
  filters.page = 1;
  load();
});

onMounted(() => {
  load();
});
</script>
