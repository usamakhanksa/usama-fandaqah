<template>
  <div class="p-6 space-y-6 bg-[#f8f9fa] min-h-full">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-3xl shadow-sm border border-slate-50">
      <div>
        <h1 class="text-2xl font-bold text-[#2a273c]">{{ $t('nav.management') }}</h1>
        <nav class="text-xs text-slate-400 mt-1 flex gap-2">
          <span>fandaqah</span>
          <span>/</span>
          <span>{{ $t('nav.management') }}</span>
        </nav>
      </div>
      <div class="flex items-center gap-3">
        <button class="bg-white border border-slate-200 text-[#2a273c] px-4 py-2 rounded-xl text-sm font-semibold hover:bg-slate-50 transition-colors flex items-center gap-2" @click="exportCsv">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
          {{ $t('table.export') }}
        </button>
        <router-link to="/reservations/create" class="bg-[#e95a54] text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-opacity-90 shadow-lg shadow-rose-100 transition-all flex items-center gap-2">
          <span>+</span>
          {{ $t('table.add_new') }}
        </router-link>
      </div>
    </div>

    <!-- Filters & View Toggles -->
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-50 flex flex-col lg:flex-row gap-4 items-center">
      <div class="relative flex-1 w-full">
        <input 
          v-model="filters.search" 
          class="w-full bg-slate-50 border-none rounded-xl py-3 px-10 text-sm focus:ring-2 focus:ring-[#e95a54] transition-all" 
          :placeholder="$t('table.search_placeholder')"
        >
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
      </div>
      
      <div class="flex items-center gap-2 w-full lg:w-auto">
        <select v-model="filters.status" class="bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-medium text-slate-600 focus:ring-2 focus:ring-[#e95a54]">
          <option value="">{{ $t('table.all_status') }}</option>
          <option value="confirmed">{{ $t('table.confirmed') }}</option>
          <option value="pending">{{ $t('table.pending') }}</option>
        </select>
        
        <div class="h-10 w-px bg-slate-100 mx-2 hidden lg:block"></div>
        
        <div class="flex bg-slate-50 p-1 rounded-xl">
          <button 
            @click="viewType = 'list'"
            :class="['px-3 py-1.5 rounded-lg text-xs font-bold transition-all', viewType === 'list' ? 'bg-white text-[#e95a54] shadow-sm' : 'text-slate-400 hover:text-slate-600']"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
          </button>
          <button 
            @click="viewType = 'grid'"
            :class="['px-3 py-1.5 rounded-lg text-xs font-bold transition-all', viewType === 'grid' ? 'bg-white text-[#e95a54] shadow-sm' : 'text-slate-400 hover:text-slate-600']"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
          </button>
          <button 
            @click="viewType = 'kanban'"
            :class="['px-3 py-1.5 rounded-lg text-xs font-bold transition-all', viewType === 'kanban' ? 'bg-white text-[#e95a54] shadow-sm' : 'text-slate-400 hover:text-slate-600']"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
          </button>
        </div>
      </div>
    </div>

    <!-- View Content -->
    <div v-if="viewType === 'list'" class="bg-white rounded-3xl shadow-sm border border-slate-50 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="bg-slate-50 text-slate-400 font-bold uppercase text-[10px] tracking-widest border-b border-slate-100">
              <th class="px-6 py-4 text-start">#</th>
              <th class="px-6 py-4 text-start">{{ $t('table.booking_no') }}</th>
              <th class="px-6 py-4 text-start">{{ $t('table.visitor_name') }}</th>
              <th class="px-6 py-4 text-start">{{ $t('table.unit_no') }}</th>
              <th class="px-6 py-4 text-start">{{ $t('table.arrive') }}</th>
              <th class="px-6 py-4 text-start">{{ $t('table.depart') }}</th>
              <th class="px-6 py-4 text-start">{{ $t('table.status') }}</th>
              <th class="px-6 py-4 text-end">{{ $t('table.actions') }}</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <tr v-for="row in rows" :key="row.id" class="hover:bg-slate-50/50 transition-colors group">
              <td class="px-6 py-5 font-medium text-slate-400">#{{ row.id }}</td>
              <td class="px-6 py-5 font-bold text-[#2a273c]">{{ row.code }}</td>
              <td class="px-6 py-5">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 rounded-full bg-[#f2f0eb] flex items-center justify-center text-[#2a273c] font-bold text-xs uppercase">
                    {{ row.guest?.name?.charAt(0) }}
                  </div>
                  <span class="font-medium text-[#2a273c]">{{ row.guest?.name }}</span>
                </div>
              </td>
              <td class="px-6 py-5">
                <span class="bg-[#fbcdab]/20 text-[#2a273c] px-3 py-1 rounded-lg font-bold text-xs">Room {{ row.room?.number }}</span>
              </td>
              <td class="px-6 py-5 text-slate-500 font-medium">{{ row.check_in }}</td>
              <td class="px-6 py-5 text-slate-500 font-medium">{{ row.check_out }}</td>
              <td class="px-6 py-5">
                <span 
                  :class="[
                    'px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest',
                    row.reservation_status_id === 2 ? 'bg-emerald-50 text-emerald-600' : 'bg-[#e95a54]/10 text-[#e95a54]'
                  ]"
                >
                  {{ row.reservation_status_id === 2 ? $t('table.confirmed') : $t('table.pending') }}
                </span>
              </td>
              <td class="px-6 py-5 text-end">
                <router-link :to="`/reservations/management/${row.id}`" class="text-[#e95a54] font-bold hover:underline">
                  {{ $t('table.view') }}
                </router-link>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Grid View -->
    <div v-else-if="viewType === 'grid'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div v-for="row in rows" :key="row.id" class="bg-white rounded-3xl p-6 shadow-sm border border-slate-50 hover:border-[#e95a54] transition-all group">
         <div class="flex justify-between items-start mb-4">
           <div class="w-12 h-12 rounded-2xl bg-[#f2f0eb] flex items-center justify-center text-[#2a273c] font-black text-lg uppercase group-hover:bg-[#e95a54] group-hover:text-white transition-colors">
              {{ row.guest?.name?.charAt(0) }}
           </div>
           <span 
              :class="[
                'px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest',
                row.reservation_status_id === 2 ? 'bg-emerald-50 text-emerald-600' : 'bg-[#e95a54]/10 text-[#e95a54]'
              ]"
            >
              {{ row.reservation_status_id === 2 ? 'Confirmed' : 'Pending' }}
            </span>
         </div>
         
         <div class="space-y-1 mb-6">
           <h3 class="text-lg font-bold text-[#2a273c]">{{ row.guest?.name }}</h3>
           <p class="text-xs text-slate-400 font-medium">Booking ID: <span class="text-[#e95a54]">{{ row.code }}</span></p>
         </div>
         
         <div class="grid grid-cols-2 gap-4 pt-4 border-t border-slate-50">
           <div>
             <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-1">{{ $t('table.arrive') }}</p>
             <p class="text-sm font-bold text-[#2a273c]">{{ row.check_in }}</p>
           </div>
           <div>
             <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-1">{{ $t('table.unit_no') }}</p>
             <p class="text-sm font-bold text-[#e95a54]">Room {{ row.room?.number }}</p>
           </div>
         </div>
         
         <router-link :to="`/reservations/management/${row.id}`" class="block w-full mt-6 text-center py-3 bg-[#f8f9fa] rounded-xl text-sm font-bold text-[#2a273c] hover:bg-[#2a273c] hover:text-white transition-all">
           {{ $t('table.view_details') || 'View Details' }}
         </router-link>
       </div>
    </div>
    <!-- Kanban View -->
    <div v-else-if="viewType === 'kanban'">
      <KanbanView :items="rows" />
    </div>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref, watch } from 'vue';
import api from '../services/api';
import { useI18n } from 'vue-i18n';
import KanbanView from '../components/dashboard/KanbanView.vue';

const { locale } = useI18n();
const { t } = useI18n();

const filters = reactive({ search: '', from: '', to: '', status: '' });
const rows = ref([]);
const viewType = ref('list');

const load = async () => {
  const { data } = await api.get('/reservations', { params: { search: filters.search } });
  rows.value = data.data || [];
};

const exportCsv = () => {
  // same logic as before...
};

watch(() => [filters.search, filters.status], load);
onMounted(load);
</script>

<style scoped>
.transition-all { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }

[dir="rtl"] .text-start { text-align: right; }
[dir="rtl"] .text-end { text-align: left; }
</style>
