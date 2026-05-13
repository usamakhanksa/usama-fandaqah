<template>
  <div class="p-6 space-y-6 bg-[#f8f9fa] min-h-full flex flex-col">
    <!-- Header & Filters -->
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-50 flex flex-col md:flex-row md:items-center justify-between gap-6">
      <div>
        <h1 class="text-2xl font-bold text-[#2a273c]">Digital Signatures</h1>
        <p class="text-xs font-black text-[#e95a54] uppercase tracking-widest mt-1">Signature Records</p>
      </div>

      <div class="flex flex-wrap items-center gap-3">
        <select
          v-model="filters.type"
          class="bg-slate-50 border-none rounded-xl py-3 px-4 text-xs font-bold text-slate-600 focus:ring-2 focus:ring-[#e95a54]"
        >
          <option value="">All Types</option>
          <option value="contract">Contract</option>
          <option value="registration">Registration</option>
          <option value="reservation">Reservation</option>
          <option value="promissory">Promissory</option>
        </select>
        <input
          type="date"
          v-model="filters.date"
          class="bg-slate-50 border-none rounded-xl py-3 px-4 text-xs font-bold text-slate-600 focus:ring-2 focus:ring-[#e95a54]"
        >
      </div>
    </div>

    <!-- Signatures Table -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-50 overflow-hidden flex-1 flex flex-col">
      <div class="overflow-x-auto flex-1">
        <table class="w-full border-collapse">
          <thead>
            <tr class="bg-slate-50/50 border-b border-slate-50">
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Reservation #</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Guest / User</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Type</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Signed At</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">IP Address</span></th>
              <th class="p-4 text-end"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Actions</span></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <tr v-for="sig in signatures" :key="sig.id" class="hover:bg-slate-50/30 transition-colors group">
              <td class="p-4">
                <span class="text-sm font-bold text-[#e95a54]">#{{ sig.ref_id || '—' }}</span>
              </td>
              <td class="p-4">
                <div class="flex flex-col">
                  <span class="text-sm font-bold text-[#2a273c]">{{ sig.user?.name || 'Guest' }}</span>
                  <span class="text-[10px] text-slate-400 font-medium">ID: {{ sig.user_id || '—' }}</span>
                </div>
              </td>
              <td class="p-4">
                <span :class="typeClass(sig.type)" class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest">
                  {{ sig.type }}
                </span>
              </td>
              <td class="p-4">
                <div class="flex flex-col">
                  <span class="text-sm font-bold text-[#2a273c]">{{ formatDate(sig.created_at) }}</span>
                  <span class="text-[10px] text-slate-400 font-medium">{{ formatTime(sig.created_at) }}</span>
                </div>
              </td>
              <td class="p-4">
                <span class="text-xs font-medium text-slate-500 font-mono">{{ sig.ip_address || '—' }}</span>
              </td>
              <td class="p-4 text-end">
                <div class="flex items-center justify-end opacity-0 group-hover:opacity-100 transition-opacity">
                  <button @click="viewSignature(sig)" class="bg-[#2a273c] text-white px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest hover:bg-opacity-90 transition-all">
                    View Image
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="signatures.length === 0">
              <td colspan="6" class="p-20 text-center">
                <div class="flex flex-col items-center">
                  <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-10 h-10 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                  </div>
                  <h3 class="text-lg font-bold text-[#2a273c]">No Signatures Found</h3>
                  <p class="text-xs text-slate-400 font-medium">No signature records match your filters.</p>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="p-6 bg-slate-50/50 border-t border-slate-50 flex items-center justify-between">
        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Showing {{ signatures.length }} records</span>
        <div class="flex gap-2">
          <button v-if="pagination.prev" @click="changePage(pagination.current - 1)" class="p-2 hover:bg-white rounded-xl transition-colors border border-transparent hover:border-slate-100"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg></button>
          <button v-if="pagination.next" @click="changePage(pagination.current + 1)" class="p-2 hover:bg-white rounded-xl transition-colors border border-transparent hover:border-slate-100"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg></button>
        </div>
      </div>
    </div>

    <!-- Signature Image Modal -->
    <div v-if="selectedSig" class="fixed inset-0 bg-black/60 flex items-center justify-center z-50" @click.self="selectedSig = null">
      <div class="bg-white rounded-3xl p-8 w-full max-w-lg">
        <div class="flex items-center justify-between mb-6">
          <h3 class="text-xl font-bold text-[#2a273c]">Signature Image</h3>
          <button @click="selectedSig = null" class="text-slate-400 hover:text-slate-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
          </button>
        </div>
        <div class="bg-slate-50 rounded-2xl p-4 flex items-center justify-center min-h-32">
          <img v-if="signatureImage" :src="signatureImage" alt="Signature" class="max-w-full max-h-48 object-contain">
          <p v-else class="text-xs text-slate-400 font-medium">No signature image available</p>
        </div>
        <div class="mt-4 space-y-1 text-xs text-slate-500">
          <p><span class="font-black uppercase tracking-widest">Type:</span> {{ selectedSig.type }}</p>
          <p><span class="font-black uppercase tracking-widest">Signed:</span> {{ formatDate(selectedSig.created_at) }} {{ formatTime(selectedSig.created_at) }}</p>
          <p><span class="font-black uppercase tracking-widest">IP:</span> {{ selectedSig.ip_address || '—' }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref, watch, computed } from 'vue';
import api from '../services/api';
import dayjs from 'dayjs';

const signatures = ref([]);
const selectedSig = ref(null);
const filters = reactive({ date: '', type: '', per_page: 15, page: 1 });
const pagination = reactive({ current: 1, next: false, prev: false });

const signatureImage = computed(() => {
  if (!selectedSig.value?.signature_base64) return null;
  try {
    return 'data:image/png;base64,' + selectedSig.value.signature_base64;
  } catch {
    return null;
  }
});

const typeClass = (type) => {
  const map = {
    contract: 'bg-blue-100 text-blue-600',
    registration: 'bg-purple-100 text-purple-600',
    reservation: 'bg-emerald-100 text-emerald-600',
    promissory: 'bg-amber-100 text-amber-600',
  };
  return map[type] || 'bg-slate-100 text-slate-600';
};

const formatDate = (d) => dayjs(d).format('DD MMM YYYY');
const formatTime = (d) => dayjs(d).format('hh:mm A');

const load = async () => {
  try {
    const { data } = await api.get('/reservations/signatures', { params: filters });
    signatures.value = data.data || [];
    pagination.next = !!data.next_page_url;
    pagination.prev = !!data.prev_page_url;
    pagination.current = data.current_page || 1;
  } catch (err) {
    console.error('Failed to load signatures', err);
  }
};

const viewSignature = (sig) => { selectedSig.value = sig; };
const changePage = (page) => { filters.page = page; load(); };

watch(() => [filters.date, filters.type], () => { filters.page = 1; load(); });
onMounted(() => load());
</script>
