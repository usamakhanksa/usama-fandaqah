<template>
  <div class="p-6 space-y-6 bg-[#f8f9fa] min-h-full flex flex-col">
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-50 flex items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-[#2a273c]">Qoyod Service Mapping</h1>
        <p class="text-xs font-black text-[#e95a54] uppercase tracking-widest mt-1">Service → Qoyod Account Mapping</p>
      </div>
      <button @click="showForm = true; resetForm()" class="bg-[#e95a54] text-white px-4 py-3 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-opacity-90 flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
        Add Mapping
      </button>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-slate-50 overflow-hidden flex-1 flex flex-col">
      <div class="overflow-x-auto flex-1">
        <table class="w-full border-collapse">
          <thead>
            <tr class="bg-slate-50/50 border-b border-slate-50">
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Service</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Qoyod Account</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Qoyod Product</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Status</span></th>
              <th class="p-4 text-end"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Actions</span></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <tr v-for="m in mappings" :key="m.id" class="hover:bg-slate-50/30 transition-colors group">
              <td class="p-4"><span class="text-sm font-bold text-[#2a273c]">{{ m.service?.name_en || m.service?.name || m.service_id }}</span></td>
              <td class="p-4"><span class="text-xs font-mono text-slate-600">{{ m.qoyod_account || '—' }}</span></td>
              <td class="p-4"><span class="text-xs font-mono text-slate-600">{{ m.qoyod_product || '—' }}</span></td>
              <td class="p-4"><span :class="m.is_active ? 'bg-emerald-100 text-emerald-600' : 'bg-slate-100 text-slate-500'" class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest">{{ m.is_active ? 'Active' : 'Inactive' }}</span></td>
              <td class="p-4 text-end">
                <div class="flex items-center justify-end opacity-0 group-hover:opacity-100 transition-opacity">
                  <button @click="remove(m)" class="bg-red-100 text-red-600 px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest">Delete</button>
                </div>
              </td>
            </tr>
            <tr v-if="!mappings.length"><td colspan="5" class="p-16 text-center text-slate-400 text-sm">No mappings found.</td></tr>
          </tbody>
        </table>
      </div>
    </div>

    <div v-if="showForm" class="fixed inset-0 bg-black/60 flex items-center justify-center z-50">
      <div class="bg-white rounded-3xl p-8 w-full max-w-md space-y-4">
        <h3 class="text-xl font-bold text-[#2a273c]">Add Qoyod Mapping</h3>
        <div><label class="label">Service ID *</label><input v-model="form.service_id" type="number" class="input" placeholder="Service ID"></div>
        <div><label class="label">Qoyod Account</label><input v-model="form.qoyod_account" class="input" placeholder="Account code"></div>
        <div><label class="label">Qoyod Product</label><input v-model="form.qoyod_product" class="input" placeholder="Product code"></div>
        <div class="flex gap-3">
          <button @click="showForm = false" class="flex-1 bg-slate-100 text-[#2a273c] py-3 rounded-xl text-xs font-black uppercase tracking-widest">Cancel</button>
          <button @click="store" :disabled="processing" class="flex-1 bg-[#e95a54] text-white py-3 rounded-xl text-xs font-black uppercase tracking-widest disabled:opacity-40">Save</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue';
import api from '../services/api';

const mappings = ref([]);
const showForm = ref(false);
const processing = ref(false);
const form = reactive({ service_id: '', qoyod_account: '', qoyod_product: '', is_active: true });

const resetForm = () => Object.assign(form, { service_id: '', qoyod_account: '', qoyod_product: '', is_active: true });

const load = async () => {
  const { data } = await api.get('/pos-module/service-qoyods');
  mappings.value = data.data || [];
};

const store = async () => {
  processing.value = true;
  try {
    await api.post('/pos-module/service-qoyods', form);
    showForm.value = false;
    load();
  } finally { processing.value = false; }
};

const remove = async (m) => {
  if (!confirm('Delete this mapping?')) return;
  await api.delete(`/pos-module/service-qoyods/${m.id}`);
  load();
};

onMounted(load);
</script>

<style scoped>
.label { @apply block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2; }
.input { @apply w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-bold text-[#2a273c] focus:ring-2 focus:ring-[#e95a54]; }
</style>
