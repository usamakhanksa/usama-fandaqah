<template>
  <div class="p-6 space-y-6 bg-[#f8f9fa] min-h-full flex flex-col">
    <!-- Header & Quick Filters -->
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-50 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
      <div>
        <h1 class="text-2xl font-bold text-[#2a273c]">Online Reservations</h1>
        <p class="text-xs font-black text-[#e95a54] uppercase tracking-widest mt-1">Pending bookings from website/booking engine</p>
      </div>
      
      <div class="flex flex-wrap items-center gap-3">
        <div class="relative">
          <input 
            type="text" 
            v-model="filters.search" 
            placeholder="Search reservation or guest..."
            class="bg-slate-50 border-none rounded-xl py-3 pl-10 pr-4 text-xs font-bold text-slate-600 focus:ring-2 focus:ring-[#e95a54] w-64"
          >
          <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
        </div>
        
        <input 
          type="date" 
          v-model="filters.date"
          class="bg-slate-50 border-none rounded-xl py-3 px-4 text-xs font-bold text-slate-600 focus:ring-2 focus:ring-[#e95a54]"
        >

        <select v-model="filters.status" class="bg-slate-50 border-none rounded-xl py-3 px-4 text-xs font-bold text-slate-600 focus:ring-2 focus:ring-[#e95a54]">
          <option value="">All Statuses</option>
          <option value="pending">Pending</option>
          <option value="confirmed">Confirmed</option>
          <option value="cancelled">Cancelled</option>
        </select>
      </div>
    </div>

    <!-- Reservations Table -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-50 overflow-hidden flex-1 flex flex-col">
      <div class="overflow-x-auto flex-1">
        <table class="w-full border-collapse">
          <thead>
            <tr class="bg-slate-50/50 border-b border-slate-50">
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Res #</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Guest</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Dates</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Room / Source</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Status</span></th>
              <th class="p-4 text-end"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Actions</span></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <tr v-for="res in reservations" :key="res.id" class="hover:bg-slate-50/30 transition-colors group">
              <td class="p-4">
                <span class="text-xs font-black text-[#e95a54]">{{ res.code }}</span>
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
              <td class="p-4">
                <div class="flex flex-col">
                  <span class="text-sm font-bold text-slate-600">{{ formatDate(res.check_in) }} - {{ formatDate(res.check_out) }}</span>
                  <span class="text-[10px] text-slate-400 font-medium">{{ res.nights }} nights</span>
                </div>
              </td>
              <td class="p-4">
                <div class="flex flex-col">
                  <span class="text-sm font-bold text-[#2a273c]">{{ res.room?.number || 'Assign on arrival' }}</span>
                  <span class="text-[10px] text-emerald-600 font-black uppercase tracking-widest">{{ res.source?.name }}</span>
                </div>
              </td>
              <td class="p-4">
                <span :class="[
                  'px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest',
                  statusClass(res.status)
                ]">
                  {{ res.status }}
                </span>
              </td>
              <td class="p-4 text-end">
                <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                  <button @click="view(res.id)" class="p-2 hover:bg-slate-100 rounded-lg transition-colors text-slate-400 hover:text-[#2a273c]" title="View">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                  </button>
                  <button v-if="res.status === 'pending'" @click="confirm(res)" class="p-2 hover:bg-emerald-50 rounded-lg transition-colors text-slate-400 hover:text-emerald-600" title="Confirm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                  </button>
                  <button v-if="res.status === 'pending'" @click="reject(res)" class="p-2 hover:bg-rose-50 rounded-lg transition-colors text-slate-400 hover:text-rose-600" title="Reject">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                  </button>
                  <button @click="edit(res.id)" class="p-2 hover:bg-slate-100 rounded-lg transition-colors text-slate-400 hover:text-blue-600" title="Edit">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                  </button>
                  <button @click="remove(res)" class="p-2 hover:bg-rose-50 rounded-lg transition-colors text-slate-400 hover:text-rose-600" title="Delete">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="reservations.length === 0">
              <td colspan="6" class="p-20 text-center">
                 <div class="flex flex-col items-center">
                    <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mb-4">
                       <svg class="w-10 h-10 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-[#2a273c]">No Online Reservations</h3>
                    <p class="text-xs text-slate-400 font-medium">Currently there are no online bookings matching your filters.</p>
                 </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="p-6 bg-slate-50/50 border-t border-slate-50 flex items-center justify-between">
         <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Showing {{ reservations.length }} online reservations</span>
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
const reservations = ref([]);

const filters = reactive({ 
  search: '',
  date: '',
  status: '',
  per_page: 25,
  page: 1
});
const pagination = reactive({ current: 1, next: false, prev: false });

const load = async () => {
  try {
    const { data } = await api.get('/reservations/online', { params: filters });
    reservations.value = data.data;
    pagination.next = !!data.links?.next;
    pagination.prev = !!data.links?.prev;
    pagination.current = data.meta?.current_page || 1;
  } catch (err) {
    console.error('Failed to load online reservations', err);
  }
};

const formatDate = (date) => date ? dayjs(date).format('DD MMM YYYY') : 'N/A';

const statusClass = (status) => {
  switch (status) {
    case 'pending': return 'bg-amber-100 text-amber-600';
    case 'confirmed': return 'bg-emerald-100 text-emerald-600';
    case 'cancelled': return 'bg-rose-100 text-rose-600';
    default: return 'bg-slate-100 text-slate-600';
  }
};

const view = (id) => router.push(`/reservations/${id}`);
const edit = (id) => router.push(`/reservations/${id}/edit`);

const confirm = async (res) => {
  const result = await Swal.fire({
    title: 'Confirm Reservation',
    text: `Are you sure you want to confirm the booking for ${res.guest?.name}?`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#10b981',
    confirmButtonText: 'Yes, Confirm'
  });

  if (result.isConfirmed) {
    try {
      await api.post(`/reservations/${res.id}/confirm`);
      Swal.fire('Confirmed!', 'Reservation has been confirmed.', 'success');
      load();
    } catch (err) {
      Swal.fire('Error', err.response?.data?.message || 'Failed to confirm', 'error');
    }
  }
};

const reject = async (res) => {
  const { value: reason } = await Swal.fire({
    title: 'Reject Reservation',
    input: 'text',
    inputLabel: 'Reason for rejection',
    inputPlaceholder: 'Enter reason...',
    showCancelButton: true,
    confirmButtonColor: '#ef4444',
  });

  if (reason !== undefined) {
    try {
      await api.post(`/reservations/${res.id}/reject`, { reason });
      Swal.fire('Rejected!', 'Reservation has been rejected.', 'success');
      load();
    } catch (err) {
      Swal.fire('Error', err.response?.data?.message || 'Failed to reject', 'error');
    }
  }
};

const remove = async (res) => {
  const result = await Swal.fire({
    title: 'Delete Reservation',
    text: 'This action cannot be undone!',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#ef4444',
    confirmButtonText: 'Yes, Delete'
  });

  if (result.isConfirmed) {
    try {
      await api.delete(`/reservations/${res.id}`);
      Swal.fire('Deleted!', 'Reservation has been deleted.', 'success');
      load();
    } catch (err) {
      Swal.fire('Error', err.response?.data?.message || 'Failed to delete', 'error');
    }
  }
};

const changePage = (page) => {
  filters.page = page;
  load();
};

watch(() => [filters.status, filters.date, filters.search], () => {
  filters.page = 1;
  load();
});

onMounted(() => {
  load();
});
</script>
