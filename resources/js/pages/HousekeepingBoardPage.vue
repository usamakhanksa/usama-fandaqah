<template>
  <div class="p-6 space-y-6 bg-[#f8f9fa] min-h-full">
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-50">
      <h1 class="text-2xl font-bold text-[#2a273c]">Housekeeping Board</h1>
      <p class="text-xs font-black text-[#e95a54] uppercase tracking-widest mt-1">Visual Housekeeping Task Board</p>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
      <div v-for="col in columns" :key="col.key" class="bg-white rounded-3xl shadow-sm border border-slate-50 p-4">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-xs font-black text-[#2a273c] uppercase tracking-widest">{{ col.label }}</h3>
          <span class="bg-slate-100 text-slate-600 px-2 py-1 rounded-full text-[10px] font-black">{{ (board[col.key] || []).length }}</span>
        </div>
        <div class="space-y-2">
          <div v-for="task in (board[col.key] || [])" :key="task.id"
            class="bg-slate-50 rounded-2xl p-3 cursor-pointer hover:bg-slate-100 transition-colors">
            <p class="text-sm font-bold text-[#2a273c]">Room {{ task.unit?.unit_number || task.unit_id }}</p>
            <p class="text-[10px] text-slate-400 font-medium capitalize mt-1">{{ task.task_type }}</p>
            <div class="flex gap-2 mt-2">
              <button v-if="col.key !== 'completed'" @click="advance(task, col.next)" class="bg-[#e95a54] text-white px-2 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest">→ {{ col.nextLabel }}</button>
            </div>
          </div>
          <p v-if="!(board[col.key] || []).length" class="text-center text-slate-300 text-xs py-4">Empty</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../services/api';

const board = ref({});
const columns = [
  { key: 'pending',     label: 'Pending',     next: 'in_progress', nextLabel: 'Start' },
  { key: 'in_progress', label: 'In Progress',  next: 'inspection',  nextLabel: 'Inspect' },
  { key: 'inspection',  label: 'Inspection',   next: 'completed',   nextLabel: 'Complete' },
  { key: 'completed',   label: 'Completed',    next: null,          nextLabel: '' },
];

const load = async () => {
  const { data } = await api.get('/rooms-module/housekeeping-board');
  board.value = data.data || {};
};

const advance = async (task, status) => {
  if (!status) return;
  await api.put(`/rooms-module/housekeeping-tasks/${task.id}`, { status });
  load();
};

onMounted(load);
</script>
