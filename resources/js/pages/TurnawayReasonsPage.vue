<template>
  <div class="p-6 space-y-6 bg-[#f8f9fa] min-h-full flex flex-col">
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-50 flex items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-[#2a273c]">Turnaway Reasons</h1>
        <p class="text-xs font-black text-[#e95a54] uppercase tracking-widest mt-1">Reason Settings</p>
      </div>
      <button @click="showForm = true; editing = null; resetForm()" class="bg-[#e95a54] text-white px-4 py-3 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-opacity-90 flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
        Add Reason
      </button>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-slate-50 overflow-hidden flex-1 flex flex-col">
      <div class="overflow-x-auto flex-1">
        <table class="w-full border-collapse">
          <thead>
            <tr class="bg-slate-50/50 border-b border-slate-50">
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Name (EN)</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Name (AR)</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Status</span></th>
              <th class="p-4 text-end"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Actions</span></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <tr v-for="r in reasons" :key="r.id" class="hover:bg-slate-50/30 transition-colors group">
              <td class="p-4"><span class="text-sm font-bold text-[#2a273c]">{{ r.name }}</span></td>
              <td class="p-4"><span class="text-xs text-slate-600" dir="rtl">{{ r.name_ar || '—' }}</span></td>
              <td class="p-4"><span :class="r.status ? 'bg-emerald-100 text-emerald-600' : 'bg-slate-100 text-slate-500'" class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest">{{ r.status ? 'Active' : 'Inactive' }}</span></td>
              <td class="p-4 text-end">
                <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                  <button @click="edit(r)" class="bg-slate-100 text-[#2a273c] px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest hover:bg-slate-200">Edit</button>
                  <button @click="remove(r)" class="bg-red-100 text-red-600 px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest">Delete</button>
                </div>
              </td>
            </tr>
            <tr v-if="!reasons.length"><td colspan="4" class="p-16 text-center text-slate-400 text-sm">No reasons found.</td></tr>
          </tbody>
        </table>
      </div>
    </div>

    <div v-if="showForm" class="fixed inset-0 bg-black/60 flex items-center justify-center z-50">
      <div class="bg-white rounded-3xl p-8 w-full max-w-md space-y-4">
        <h3 class="text-xl font-bold text-[#2a273c]">{{ editing ? 'Edit Reason' : 'Add Reason' }}</h3>
        <div><label class="label">Name (EN) *</label><input v-model="form.name" class="input" placeholder="Reason name"></div>
        <div><label class="label">Name (AR)</label><input v-model="form.name_ar" class="input" dir="rtl" placeholder="اسم السبب"></div>
        <div><label class="label">Status</label><select v-model="form.status" class="input"><option :value="1">Active</option><option :value="0">Inactive</option></select></div>
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

const reasons = ref([]);
const showForm = ref(false);
const editing = ref(null);
const processing = ref(false);
const form = reactive({ name: '', name_ar: '', status: 1 });

const resetForm = () => Object.assign(form, { name: '', name_ar: '', status: 1 });

const load = async () => {
  const { data } = await api.get('/guests-module/turnaway-reasons');
  reasons.value = Array.isArray(data) ? data : (data.data || []);
};

const edit = (r) => { editing.value = r; Object.assign(form, r); showForm.value = true; };

const save = async () => {
  processing.value = true;
  try {
    editing.value
      ? await api.put(`/guests-module/turnaway-reasons/${editing.value.id}`, form)
      : await api.post('/guests-module/turnaway-reasons', form);
    showForm.value = false;
    load();
  } finally { processing.value = false; }
};

const remove = async (r) => {
  if (!confirm(`Delete reason ${r.name}?`)) return;
  await api.delete(`/guests-module/turnaway-reasons/${r.id}`);
  load();
};

onMounted(load);
</script>

<style scoped>
.label { @apply block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2; }
.input { @apply w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-bold text-[#2a273c] focus:ring-2 focus:ring-[#e95a54]; }
</style>
