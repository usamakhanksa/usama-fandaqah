<template>
  <div class="p-6 space-y-6 bg-[#f8f9fa] min-h-full flex flex-col">
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-50 flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-[#2a273c]">Registration Cards</h1>
        <p class="text-xs font-black text-[#e95a54] uppercase tracking-widest mt-1">Guest Registration Cards</p>
      </div>
      <div class="flex gap-3">
        <input type="date" v-model="filters.date" class="bg-slate-50 border-none rounded-xl py-3 px-4 text-xs font-bold text-slate-600 focus:ring-2 focus:ring-[#e95a54]">
        <div class="relative">
          <input type="text" v-model="filters.guest" class="bg-slate-50 border-none rounded-xl py-3 px-4 text-xs font-bold text-slate-600 focus:ring-2 focus:ring-[#e95a54] pl-10" placeholder="Search...">
          <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
        </div>
      </div>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-slate-50 overflow-hidden flex-1 flex flex-col">
      <div class="overflow-x-auto flex-1">
        <table class="w-full border-collapse">
          <thead>
            <tr class="bg-slate-50/50 border-b border-slate-50">
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Reservation #</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Check-in</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Check-out</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Status</span></th>
              <th class="p-4 text-end"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Actions</span></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <tr v-for="res in cards" :key="res.id" class="hover:bg-slate-50/30 transition-colors group">
              <td class="p-4"><span class="text-sm font-bold text-[#e95a54]">#{{ res.code }}</span></td>
              <td class="p-4"><span class="text-sm font-bold text-[#2a273c]">{{ formatDate(res.check_in) }}</span></td>
              <td class="p-4"><span class="text-sm text-slate-600">{{ formatDate(res.check_out) }}</span></td>
              <td class="p-4"><span class="text-[10px] font-black text-slate-400 uppercase">{{ res.status }}</span></td>
              <td class="p-4 text-end">
                <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                  <button @click="view(res)" class="bg-slate-100 text-[#2a273c] px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest hover:bg-slate-200">View</button>
                  <button @click="print(res)" class="bg-[#2a273c] text-white px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest hover:bg-opacity-90">Print</button>
                </div>
              </td>
            </tr>
            <tr v-if="!cards.length"><td colspan="5" class="p-16 text-center text-slate-400 text-sm">No registration cards found.</td></tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Print Preview Modal -->
    <div v-if="selected" class="fixed inset-0 bg-black/60 flex items-center justify-center z-50" @click.self="selected = null">
      <div class="bg-white rounded-3xl p-8 w-full max-w-lg print:shadow-none">
        <div class="flex justify-between items-start mb-6">
          <div>
            <h2 class="text-xl font-bold text-[#2a273c]">Registration Card</h2>
            <p class="text-xs text-slate-400">{{ cardData?.hotel?.name }}</p>
          </div>
          <button @click="selected = null" class="text-slate-400 hover:text-slate-600 print:hidden">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
          </button>
        </div>
        <div class="space-y-2 text-sm border-t border-slate-100 pt-4">
          <div class="flex justify-between py-1"><span class="text-slate-400 font-medium">Reservation #</span><span class="font-bold text-[#e95a54]">#{{ selected.code }}</span></div>
          <div class="flex justify-between py-1"><span class="text-slate-400 font-medium">Check-in</span><span class="font-bold">{{ formatDate(selected.check_in) }}</span></div>
          <div class="flex justify-between py-1"><span class="text-slate-400 font-medium">Check-out</span><span class="font-bold">{{ formatDate(selected.check_out) }}</span></div>
          <div class="flex justify-between py-1"><span class="text-slate-400 font-medium">Room</span><span class="font-bold">{{ selected.unit_id || '—' }}</span></div>
        </div>
        <div class="flex gap-3 mt-6 print:hidden">
          <button @click="window.print()" class="flex-1 bg-[#2a273c] text-white py-3 rounded-xl text-xs font-black uppercase tracking-widest">Print</button>
          <button @click="selected = null" class="flex-1 bg-slate-100 text-[#2a273c] py-3 rounded-xl text-xs font-black uppercase tracking-widest">Close</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, watch, onMounted } from 'vue';
import api from '../services/api';
import dayjs from 'dayjs';

const cards = ref([]);
const selected = ref(null);
const cardData = ref(null);
const filters = reactive({ date: '', guest: '' });

const formatDate = (d) => d ? dayjs(d).format('DD MMM YYYY') : '—';

const load = async () => {
  const { data } = await api.get('/front-desk/registration-cards', { params: filters });
  cards.value = data.data || [];
};

const view = async (res) => {
  selected.value = res;
  const { data } = await api.get(`/front-desk/registration-cards/${res.id}`);
  cardData.value = data.data;
};

const print = async (res) => {
  await view(res);
  setTimeout(() => window.print(), 300);
};

watch(filters, load);
onMounted(load);
</script>
