<template>
  <div class="p-6 space-y-6 bg-[#f8f9fa] min-h-full flex flex-col">
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-50 flex items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-[#2a273c]">Unit Options</h1>
        <p class="text-xs font-black text-[#e95a54] uppercase tracking-widest mt-1">Room Option Settings</p>
      </div>
      <button @click="showForm = true; editing = null; resetForm()" class="bg-[#e95a54] text-white px-4 py-3 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-opacity-90 flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
        Add Option
      </button>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-slate-50 overflow-hidden flex-1 flex flex-col">
      <div class="overflow-x-auto flex-1">
        <table class="w-full border-collapse">
          <thead>
            <tr class="bg-slate-50/50 border-b border-slate-50">
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Name</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Price</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Status</span></th>
              <th class="p-4 text-end"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Actions</span></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <tr v-for="o in options" :key="o.id" class="hover:bg-slate-50/30 transition-colors group">
              <td class="p-4"><span class="text-sm font-bold text-[#2a273c]">{{ o.name }}</span></td>
              <td class="p-4"><span class="text-xs font-bold text-[#e95a54]">{{ o.price || 0 }} SAR</span></td>
              <td class="p-4"><span :class="o.active ? 'bg-emerald-100 text-emerald-600' : 'bg-slate-100 text-slate-500'" class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest">{{ o.active ? 'Active' : 'Inactive' }}</span></td>
              <td class="p-4 text-end">
                <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                  <button @click="edit(o)" class="bg-slate-100 text-[#2a273c] px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest hover:bg-slate-200">Edit</button>
                  <button @click="remove(o)" class="bg-red-100 text-red-600 px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest">Delete</button>
                </div>
              </td>
            </tr>
            <tr v-if="!options.length"><td colspan="4" class="p-16 text-center text-slate-400 text-sm">No options found.</td></tr>
          </tbody>
        </table>
      </div>
    </div>

    <div v-if="showForm" class="fixed inset-0 bg-black/60 flex items-center justify-center z-50">
      <div class="bg-white rounded-3xl p-8 w-full max-w-md space-y-4">
        <h3 class="text-xl font-bold text-[#2a273c]">{{ editing ? 'Edit Option' : 'Add Option' }}</h3>
        <div><label class="label">Name *</label><input v-model="form.name" class="input" placeholder="Option name"></div>
        <div><label class="label">Price (SAR)</label><input v-model="form.price" type="number" class="input" placeholder="0"></div>
        <div><label class="label">Description</label><textarea v-model="form.description" class="input" rows="2"></textarea></div>
        <div><label class="label">Active</label><select v-model="form.active" class="input"><option :value="true">Yes</option><option :value="false">No</option></select></div>
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

const options = ref([]);
const showForm = ref(false);
const editing = ref(null);
const processing = ref(false);
const form = reactive({ name: '', price: 0, description: '', active: true });

const resetForm = () => Object.assign(form, { name: '', price: 0, description: '', active: true });

const load = async () => {
  const { data } = await api.get('/rooms-module/unit-options');
  options.value = data.data || [];
};

const edit = (o) => { editing.value = o; Object.assign(form, o); showForm.value = true; };

const save = async () => {
  processing.value = true;
  try {
    editing.value
      ? await api.put(`/rooms-module/unit-options/${editing.value.id}`, form)
      : await api.post('/rooms-module/unit-options', form);
    showForm.value = false;
    load();
  } finally { processing.value = false; }
};

const remove = async (o) => {
  if (!confirm(`Delete option ${o.name}?`)) return;
  await api.delete(`/rooms-module/unit-options/${o.id}`);
  load();
};

onMounted(load);
</script>

<style scoped>
.label { @apply block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2; }
.input { @apply w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-bold text-[#2a273c] focus:ring-2 focus:ring-[#e95a54]; }
</style>
