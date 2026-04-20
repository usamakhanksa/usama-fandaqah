<template>
  <div class="flex gap-6 overflow-x-auto pb-4 h-[calc(100vh-350px)]">
    <!-- Kanban Columns -->
    <div 
      v-for="column in columns" 
      :key="column.id" 
      class="flex-shrink-0 w-80 bg-slate-50/50 rounded-[2rem] p-4 flex flex-col border border-slate-100"
    >
      <div class="flex justify-between items-center mb-4 px-2">
        <div class="flex items-center gap-2">
          <div 
            class="w-2 h-2 rounded-full" 
            :style="{ backgroundColor: column.color }"
          ></div>
          <h3 class="text-xs font-black uppercase tracking-widest text-slate-500">{{ column.name }}</h3>
        </div>
        <span class="bg-white px-2 py-0.5 rounded-lg text-[10px] font-bold text-slate-400 border border-slate-100">
          {{ getColumnItems(column.id).length }}
        </span>
      </div>

      <!-- draggable area (simulated) -->
      <div class="flex-1 space-y-4 overflow-y-auto">
        <div 
          v-for="item in getColumnItems(column.id)" 
          :key="item.id" 
          class="bg-white p-5 rounded-2xl shadow-sm border border-slate-50 hover:border-[#e95a54] transition-all cursor-move group"
        >
          <div class="flex justify-between items-start mb-3">
             <span class="text-[10px] font-bold text-[#e95a54] bg-[#e95a54]/10 px-2 py-0.5 rounded-md">#{{ item.code }}</span>
             <button class="text-slate-300 hover:text-slate-600">
               <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
             </button>
          </div>
          
          <h4 class="font-bold text-[#2a273c] text-sm mb-1">{{ item.guest?.name }}</h4>
          <p class="text-[11px] text-slate-400 font-medium mb-4">Room: {{ item.room?.number }}</p>
          
          <div class="flex items-center justify-between pt-4 border-t border-slate-50">
            <div class="flex items-center gap-1 text-slate-400">
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
              <span class="text-[10px] font-bold">{{ item.check_in }}</span>
            </div>
            <div class="flex -space-x-2">
              <div v-for="i in 1" :key="i" class="w-6 h-6 rounded-full border-2 border-white bg-slate-200 flex items-center justify-center text-[8px] font-bold text-slate-600 uppercase">
                {{ item.guest?.name?.charAt(0) }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  items: { type: Array, default: () => [] }
});

const columns = [
  { id: 1, name: 'Pending', color: '#fbcdab' },
  { id: 2, name: 'Confirmed', color: '#8f9793' },
  { id: 3, name: 'Checked In', color: '#e95a54' },
  { id: 4, name: 'Checked Out', color: '#2a273c' }
];

const getColumnItems = (statusId) => {
  return props.items.filter(item => item.reservation_status_id == statusId);
};
</script>

<style scoped>
.cursor-move { cursor: grab; }
.cursor-move:active { cursor: grabbing; }
/* Scrollbar styling for the columns */
.overflow-y-auto::-webkit-scrollbar {
  width: 4px;
}
.overflow-y-auto::-webkit-scrollbar-thumb {
  background: #e2e8f0;
  border-radius: 10px;
}
</style>
