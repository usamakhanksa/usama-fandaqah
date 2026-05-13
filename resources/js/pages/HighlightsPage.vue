<template>
  <div class="p-6 space-y-6 bg-[#f8f9fa] min-h-full flex flex-col">
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-50 flex items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-[#2a273c]">Highlights</h1>
        <p class="text-xs font-black text-[#e95a54] uppercase tracking-widest mt-1">Customer Label Colors</p>
      </div>
      <button @click="showForm = true; editing = null; resetForm()" class="bg-[#e95a54] text-white px-4 py-3 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-opacity-90 flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
        Add Highlight
      </button>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <div v-for="h in highlights" :key="h.id" class="bg-white rounded-2xl p-4 shadow-sm border border-slate-50 flex items-center justify-between group">
        <div class="flex items-center gap-3">
          <div :style="{ background: h.color }" class="w-8 h-8 rounded-full"></div>
          <div>
            <p class="text-sm font-bold text-[#2a273c]">{{ typeof h.name === 'object' ? h.name.en : h.name }}</p>
            <p class="text-[10px] text-slate-400 font-mono">{{ h.color }}</p>
          </div>
        </div>
        <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
          <button @click="edit(h)" class="p-1.5 bg-slate-100 rounded-lg hover:bg-slate-200"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg></button>
          <button @click="remove(h)" class="p-1.5 bg-red-100 rounded-lg hover:bg-red-200"><svg class="w-3 h-3 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg></button>
        </div>
      </div>
      <div v-if="!highlights.length" class="col-span-4 p-16 text-center text-slate-400 text-sm bg-white rounded-2xl">No highlights found.</div>
    </div>

    <div v-if="showForm" class="fixed inset-0 bg-black/60 flex items-center justify-center z-50">
      <div class="bg-white rounded-3xl p-8 w-full max-w-sm space-y-4">
        <h3 class="text-xl font-bold text-[#2a273c]">{{ editing ? 'Edit Highlight' : 'Add Highlight' }}</h3>
        <div><label class="label">Name *</label><input v-model="form.name" class="input" placeholder="Highlight name"></div>
        <div>
          <label class="label">Color *</label>
          <div class="flex items-center gap-3">
            <input v-model="form.color" type="color" class="w-12 h-12 rounded-xl border-none cursor-pointer">
            <input v-model="form.color" class="input flex-1" placeholder="#2196F3">
          </div>
        </div>
        <div class="flex gap-3">
          <button @click="showForm = false" class="flex-1 bg-slate-100 text-[#2a273c] py-3 rounded-xl text-xs font-black uppercase tracking-widest">Cancel</button>
          <button @click="save" :disabled="processing" class="flex-1 bg-[#e95a54] text-white py-3 rounded-xl text-xs font-black uppercase tracking-widest disabled:opacity-40">Save</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue';
import api from '../services/api';

const highlights = ref([]);
const showForm = ref(false);
const editing = ref(null);
const processing = ref(false);
const form = reactive({ name: '', color: '#2196F3', status: 1 });

const resetForm = () => Object.assign(form, { name: '', color: '#2196F3', status: 1 });

const load = async () => {
  const { data } = await api.get('/guests-module/highlights');
  highlights.value = Array.isArray(data) ? data : (data.data || []);
};

const edit = (h) => {
  editing.value = h;
  form.name = typeof h.name === 'object' ? h.name.en : h.name;
  form.color = h.color;
  form.status = h.status;
  showForm.value = true;
};

const save = async () => {
  processing.value = true;
  try {
    editing.value
      ? await api.put(`/guests-module/highlights/${editing.value.id}`, form)
      : await api.post('/guests-module/highlights', form);
    showForm.value = false;
    load();
  } finally { processing.value = false; }
};

const remove = async (h) => {
  if (!confirm('Delete this highlight?')) return;
  await api.delete(`/guests-module/highlights/${h.id}`);
  load();
};

onMounted(load);
</script>

<style scoped>
.label { @apply block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2; }
.input { @apply w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-bold text-[#2a273c] focus:ring-2 focus:ring-[#e95a54]; }
</style>
