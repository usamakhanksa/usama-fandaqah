<template>
  <div class="space-y-4">
    <!-- Header with Search -->
    <div class="flex flex-col md:flex-row justify-between gap-4 items-center">
      <div class="relative w-full md:w-96">
        <i class="pi pi-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
        <input 
          v-model="search" 
          @input="debounceSearch"
          type="text" 
          placeholder="Search categories..." 
          class="w-full bg-white border border-slate-200 rounded-xl pl-11 pr-4 py-3 focus:border-[#e95a54] outline-none transition-all shadow-sm shadow-slate-100"
        >
      </div>
      
      <div class="flex items-center gap-2 text-sm font-medium text-[#2a273c]">
         <span>Show:</span>
         <select v-model="perPage" @change="fetch" class="bg-white border-none rounded-lg px-2 py-1 outline-none text-[#e95a54]">
           <option :value="10">10 Rows</option>
           <option :value="25">25 Rows</option>
           <option :value="50">50 Rows</option>
         </select>
      </div>
    </div>

    <!-- Table Container -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-xl shadow-slate-200/50 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-[#2a273c] text-white">
              <th class="p-5 font-bold uppercase text-[11px] tracking-[0.1em]">Category Name</th>
              <th class="p-5 font-bold uppercase text-[11px] tracking-[0.1em]">Status</th>
              <th class="p-5 font-bold uppercase text-[11px] tracking-[0.1em] text-center">Reservation</th>
              <th class="p-5 font-bold uppercase text-[11px] tracking-[0.1em] text-center">POS View</th>
              <th class="p-5 font-bold uppercase text-[11px] tracking-[0.1em] text-center">Order</th>
              <th class="p-5 font-bold uppercase text-[11px] tracking-[0.1em] text-right">Settings</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <tr v-for="category in responseData.data" :key="category.id" class="hover:bg-[#f2f0eb]/30 transition-colors group">
              <td class="p-5">
                <div class="flex flex-col">
                  <span class="text-[#2a273c] font-bold">{{ category.name?.en || category.name }}</span>
                  <span class="text-slate-400 text-[10px] uppercase font-bold">{{ category.name?.ar }}</span>
                </div>
              </td>
              <td class="p-5">
                <div class="flex items-center gap-2">
                  <span :class="category.status == 1 ? 'bg-[#8f9793]' : 'bg-[#e95a54]'" class="w-2.5 h-2.5 rounded-full ring-4 ring-slate-50"></span>
                  <span :class="category.status == 1 ? 'text-[#8f9793]' : 'text-[#e95a54]'" class="text-xs font-black uppercase tracking-tighter">
                    {{ category.status == 1 ? 'Active' : 'Inactive' }}
                  </span>
                </div>
              </td>
              <td class="p-5 text-center">
                <div class="flex justify-center">
                   <div :class="category.show_in_reservation ? 'bg-green-50 text-green-600 border-green-100' : 'bg-slate-50 text-slate-400 border-slate-100'" class="w-10 h-6 rounded-full border flex items-center justify-center">
                     <i :class="category.show_in_reservation ? 'pi pi-check text-[10px]' : 'pi pi-times text-[10px]'"></i>
                   </div>
                </div>
              </td>
              <td class="p-5 text-center">
                <div class="flex justify-center">
                   <div :class="category.show_in_pos ? 'bg-green-50 text-green-600 border-green-100' : 'bg-slate-50 text-slate-400 border-slate-100'" class="w-10 h-6 rounded-full border flex items-center justify-center">
                     <i :class="category.show_in_pos ? 'pi pi-check text-[10px]' : 'pi pi-times text-[10px]'"></i>
                   </div>
                </div>
              </td>
              <td class="p-5 text-center font-black text-slate-400">{{ category.order }}</td>
              <td class="p-5 text-right">
                <div class="flex justify-end gap-1 opacity-40 group-hover:opacity-100 transition-opacity">
                  <button @click="$emit('view', category)" class="w-9 h-9 rounded-lg hover:bg-slate-100 flex items-center justify-center text-[#2a273c] transition-colors"><i class="pi pi-eye text-xs"></i></button>
                  <button @click="$emit('edit', category)" class="w-9 h-9 rounded-lg hover:bg-slate-100 flex items-center justify-center text-[#2a273c] transition-colors"><i class="pi pi-pencil text-xs"></i></button>
                  <button @click="confirmDelete(category)" class="w-9 h-9 rounded-lg hover:bg-red-50 flex items-center justify-center text-red-400 transition-colors"><i class="pi pi-trash text-xs"></i></button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="responseData.total > 0" class="p-5 bg-slate-50 border-t border-slate-100 flex items-center justify-between text-[11px] font-black uppercase tracking-widest text-slate-400">
        <div>
          Showing <span class="text-[#2a273c]">{{ responseData.from }}-{{ responseData.to }}</span> of <span class="text-[#2a273c]">{{ responseData.total }}</span> entries
        </div>
        <div class="flex gap-2">
          <button 
            @click="page--" 
            :disabled="page <= 1"
            class="w-10 h-10 rounded-xl border bg-white flex items-center justify-center hover:bg-[#f2f0eb] transition-all disabled:opacity-30 disabled:hover:bg-white"
          >
            <i class="pi pi-chevron-left text-[10px]"></i>
          </button>
          <button 
            @click="page++" 
            :disabled="page >= responseData.last_page"
            class="w-10 h-10 rounded-xl border bg-white flex items-center justify-center hover:bg-[#f2f0eb] transition-all disabled:opacity-30 disabled:hover:bg-white"
          >
            <i class="pi pi-chevron-right text-[10px]"></i>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import api from '../services/api';

const emit = defineEmits(['edit', 'view', 'refresh']);
const props = defineProps({
    refreshKey: Number
});

const responseData = ref({ data: [], total: 0, from: 0, to: 0, last_page: 1 });
const search = ref('');
const page = ref(1);
const perPage = ref(10);
let timer = null;

async function fetch() {
  try {
    const res = await api.get('/service-categories', {
      params: { search: search.value, page: page.value, per_page: perPage.value }
    });
    responseData.value = res.data;
  } catch (e) {
    console.error('Failed to load categories');
  }
}

function debounceSearch() {
  clearTimeout(timer);
  timer = setTimeout(() => {
    page.value = 1;
    fetch();
  }, 500);
}

async function confirmDelete(category) {
  if (confirm(`Are you sure you want to delete ${category.name?.en || category.name}?`)) {
    try {
      await api.delete(`/service-categories/${category.id}`);
      fetch();
    } catch (e) {
      alert('Delete failed');
    }
  }
}

watch(page, fetch);
watch(() => props.refreshKey, fetch);
onMounted(fetch);

defineExpose({ fetch });
</script>
