<template>
  <div class="p-6 space-y-6 bg-[#f8f9fa] min-h-full flex flex-col">
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-50 flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-[#2a273c]">Guest Directory</h1>
        <p class="text-xs font-black text-[#e95a54] uppercase tracking-widest mt-1">All Registered Guests</p>
      </div>
      <div class="flex gap-3">
        <div class="relative"><input v-model="filters.search" type="text" placeholder="Search name, phone, email..." class="bg-slate-50 border-none rounded-xl py-3 px-4 pl-10 text-xs font-bold text-slate-600 focus:ring-2 focus:ring-[#e95a54] w-64"><svg class="w-4 h-4 text-slate-400 absolute left-3 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg></div>
        <input v-model="filters.nationality" type="text" placeholder="Nationality" class="bg-slate-50 border-none rounded-xl py-3 px-4 text-xs font-bold text-slate-600 focus:ring-2 focus:ring-[#e95a54] w-32">
      </div>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-slate-50 overflow-hidden flex-1 flex flex-col">
      <div class="overflow-x-auto flex-1">
        <table class="w-full border-collapse">
          <thead>
            <tr class="bg-slate-50/50 border-b border-slate-50">
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Name</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Nationality</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Phone</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Email</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">ID Number</span></th>
              <th class="p-4 text-end"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Actions</span></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <tr v-for="g in guests" :key="g.id" class="hover:bg-slate-50/30 transition-colors group">
              <td class="p-4"><span class="text-sm font-bold text-[#2a273c]">{{ g.name }}</span></td>
              <td class="p-4"><span class="text-xs text-slate-600">{{ g.nationality || '—' }}</span></td>
              <td class="p-4"><span class="text-xs text-slate-600">{{ g.phone || '—' }}</span></td>
              <td class="p-4"><span class="text-xs text-slate-600">{{ g.email || '—' }}</span></td>
              <td class="p-4"><span class="text-xs text-slate-500 font-mono">{{ g.id_number || '—' }}</span></td>
              <td class="p-4 text-end">
                <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                  <button @click="viewGuest(g)" class="bg-[#2a273c] text-white px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest">View Profile</button>
                </div>
              </td>
            </tr>
            <tr v-if="!guests.length"><td colspan="6" class="p-16 text-center text-slate-400 text-sm">No guests found.</td></tr>
          </tbody>
        </table>
      </div>
      <div class="p-6 bg-slate-50/50 border-t border-slate-50 flex items-center justify-between">
        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ pagination.total }} guests</span>
        <div class="flex gap-2">
          <button v-if="pagination.prev" @click="changePage(pagination.current - 1)" class="p-2 hover:bg-white rounded-xl border border-transparent hover:border-slate-100"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg></button>
          <button v-if="pagination.next" @click="changePage(pagination.current + 1)" class="p-2 hover:bg-white rounded-xl border border-transparent hover:border-slate-100"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg></button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, watch, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '../services/api';

const router = useRouter();
const guests = ref([]);
const filters = reactive({ search: '', nationality: '', page: 1 });
const pagination = reactive({ current: 1, next: false, prev: false, total: 0 });

const load = async () => {
  const { data } = await api.get('/guests-module/guests', { params: filters });
  guests.value = data.data || [];
  pagination.next = !!data.next_page_url;
  pagination.prev = !!data.prev_page_url;
  pagination.current = data.current_page || 1;
  pagination.total = data.total || 0;
};

const viewGuest = (g) => router.push(`/guests/${g.id}`);
const changePage = (page) => { filters.page = page; load(); };
watch(filters, () => { filters.page = 1; load(); });
onMounted(load);
</script>
