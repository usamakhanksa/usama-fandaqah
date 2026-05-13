<template>
  <div class="p-6 space-y-6 bg-[#f8f9fa] min-h-full flex flex-col">
    <!-- Header & Filters -->
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-50 flex flex-col md:flex-row md:items-center justify-between gap-6">
      <div>
        <h1 class="text-2xl font-bold text-[#2a273c]">Cancellations & No-Shows</h1>
        <p class="text-xs font-black text-[#e95a54] uppercase tracking-widest mt-1">Cancelled and No-Show Reservations</p>
      </div>

      <div class="flex flex-wrap items-center gap-3">
        <select
          v-model="filters.type"
          class="bg-slate-50 border-none rounded-xl py-3 px-4 text-xs font-bold text-slate-600 focus:ring-2 focus:ring-[#e95a54]"
        >
          <option value="">All Types</option>
          <option value="cancelled">Cancelled</option>
          <option value="no_show">No-Show</option>
        </select>
        <input
          type="date"
          v-model="filters.date"
          class="bg-slate-50 border-none rounded-xl py-3 px-4 text-xs font-bold text-slate-600 focus:ring-2 focus:ring-[#e95a54]"
        >
        <div class="relative">
          <input
            type="text"
            v-model="filters.reason"
            class="bg-slate-50 border-none rounded-xl py-3 px-4 text-xs font-bold text-slate-600 focus:ring-2 focus:ring-[#e95a54] pl-10"
            placeholder="Filter by reason..."
          >
          <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
        </div>
      </div>
    </div>

    <!-- Stats Row -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-50">
        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total</p>
        <p class="text-2xl font-bold text-[#2a273c] mt-1">{{ stats.total }}</p>
      </div>
      <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-50">
        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Cancelled</p>
        <p class="text-2xl font-bold text-[#e95a54] mt-1">{{ stats.cancelled }}</p>
      </div>
      <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-50">
        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">No-Shows</p>
        <p class="text-2xl font-bold text-amber-500 mt-1">{{ stats.no_show }}</p>
      </div>
      <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-50">
        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Penalties</p>
        <p class="text-2xl font-bold text-emerald-600 mt-1">{{ stats.penalties }}</p>
      </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-50 overflow-hidden flex-1 flex flex-col">
      <div class="overflow-x-auto flex-1">
        <table class="w-full border-collapse">
          <thead>
            <tr class="bg-slate-50/50 border-b border-slate-50">
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Reservation #</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Guest</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Dates</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Type</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Reason</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Penalty</span></th>
              <th class="p-4 text-end"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Actions</span></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <tr v-for="res in reservations" :key="res.id" class="hover:bg-slate-50/30 transition-colors group">
              <td class="p-4">
                <span class="text-sm font-bold text-[#e95a54]">#{{ res.code }}</span>
              </td>
              <td class="p-4">
                <div class="flex flex-col">
                  <span class="text-sm font-bold text-[#2a273c]">{{ res.customer?.name || res.guest?.full_name || '—' }}</span>
                  <span class="text-[10px] text-slate-400 font-medium">ID: {{ res.guest_id }}</span>
                </div>
              </td>
              <td class="p-4">
                <div class="flex flex-col">
                  <span class="text-xs font-bold text-[#2a273c]">{{ formatDate(res.check_in) }}</span>
                  <span class="text-[10px] text-slate-400 font-medium">→ {{ formatDate(res.check_out) }}</span>
                </div>
              </td>
              <td class="p-4">
                <span :class="typeClass(res.status)" class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest">
                  {{ typeLabel(res.status) }}
                </span>
              </td>
              <td class="p-4">
                <span class="text-xs text-slate-600 font-medium">{{ res.cancellation_reason || '—' }}</span>
              </td>
              <td class="p-4">
                <span v-if="res.penalty_amount" class="text-xs font-bold text-emerald-600">
                  {{ res.penalty_amount }} SAR
                </span>
                <span v-else class="text-[10px] text-slate-400 font-medium">None</span>
              </td>
              <td class="p-4 text-end">
                <div class="flex items-center justify-end opacity-0 group-hover:opacity-100 transition-opacity">
                  <button @click="viewDetails(res)" class="bg-[#2a273c] text-white px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest hover:bg-opacity-90 transition-all">
                    View Details
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="reservations.length === 0">
              <td colspan="7" class="p-20 text-center">
                <div class="flex flex-col items-center">
                  <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-10 h-10 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                  </div>
                  <h3 class="text-lg font-bold text-[#2a273c]">No Records Found</h3>
                  <p class="text-xs text-slate-400 font-medium">No cancellations or no-shows match your filters.</p>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="p-6 bg-slate-50/50 border-t border-slate-50 flex items-center justify-between">
        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Showing {{ reservations.length }} records</span>
        <div class="flex gap-2">
          <button v-if="pagination.prev" @click="changePage(pagination.current - 1)" class="p-2 hover:bg-white rounded-xl transition-colors border border-transparent hover:border-slate-100"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg></button>
          <button v-if="pagination.next" @click="changePage(pagination.current + 1)" class="p-2 hover:bg-white rounded-xl transition-colors border border-transparent hover:border-slate-100"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg></button>
        </div>
      </div>
    </div>

    <!-- Details Modal -->
    <div v-if="selected" class="fixed inset-0 bg-black/60 flex items-center justify-center z-50" @click.self="selected = null">
      <div class="bg-white rounded-3xl p-8 w-full max-w-lg">
        <div class="flex items-center justify-between mb-6">
          <h3 class="text-xl font-bold text-[#2a273c]">Reservation Details</h3>
          <button @click="selected = null" class="text-slate-400 hover:text-slate-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
          </button>
        </div>
        <div class="space-y-3 text-sm">
          <div class="flex justify-between py-2 border-b border-slate-50">
            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Reservation #</span>
            <span class="font-bold text-[#e95a54]">#{{ selected.code }}</span>
          </div>
          <div class="flex justify-between py-2 border-b border-slate-50">
            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Guest</span>
            <span class="font-bold text-[#2a273c]">{{ selected.customer?.name || selected.guest?.full_name || '—' }}</span>
          </div>
          <div class="flex justify-between py-2 border-b border-slate-50">
            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Check-in</span>
            <span class="font-medium text-slate-600">{{ formatDate(selected.check_in) }}</span>
          </div>
          <div class="flex justify-between py-2 border-b border-slate-50">
            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Check-out</span>
            <span class="font-medium text-slate-600">{{ formatDate(selected.check_out) }}</span>
          </div>
          <div class="flex justify-between py-2 border-b border-slate-50">
            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Type</span>
            <span :class="typeClass(selected.status)" class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest">{{ typeLabel(selected.status) }}</span>
          </div>
          <div class="flex justify-between py-2 border-b border-slate-50">
            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Reason</span>
            <span class="font-medium text-slate-600 text-right max-w-xs">{{ selected.cancellation_reason || '—' }}</span>
          </div>
          <div class="flex justify-between py-2 border-b border-slate-50">
            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Penalty</span>
            <span class="font-bold text-emerald-600">{{ selected.penalty_amount ? selected.penalty_amount + ' SAR' : 'None' }}</span>
          </div>
          <div class="flex justify-between py-2">
            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Updated At</span>
            <span class="font-medium text-slate-600">{{ formatDate(selected.updated_at) }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref, watch } from 'vue';
import api from '../services/api';
import dayjs from 'dayjs';

const reservations = ref([]);
const selected = ref(null);
const filters = reactive({ date: '', type: '', reason: '', per_page: 15, page: 1 });
const pagination = reactive({ current: 1, next: false, prev: false });
const stats = reactive({ total: 0, cancelled: 0, no_show: 0, penalties: 0 });

const typeLabel = (status) => status === 'no_show' ? 'No-Show' : 'Cancelled';
const typeClass = (status) => status === 'no_show'
  ? 'bg-amber-100 text-amber-600'
  : 'bg-red-100 text-red-600';

const formatDate = (d) => d ? dayjs(d).format('DD MMM YYYY') : '—';

const load = async () => {
  try {
    const { data } = await api.get('/reservations/cancellations', { params: filters });
    reservations.value = data.data || [];
    pagination.next = !!data.next_page_url;
    pagination.prev = !!data.prev_page_url;
    pagination.current = data.current_page || 1;
    if (data.stats) Object.assign(stats, data.stats);
  } catch (err) {
    console.error('Failed to load cancellations', err);
  }
};

const viewDetails = (res) => { selected.value = res; };
const changePage = (page) => { filters.page = page; load(); };

watch(() => [filters.date, filters.type, filters.reason], () => { filters.page = 1; load(); });
onMounted(() => load());
</script>
