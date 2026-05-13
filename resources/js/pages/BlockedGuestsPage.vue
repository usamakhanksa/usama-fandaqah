<template>
  <div class="p-6 space-y-6 bg-[#f8f9fa] min-h-full flex flex-col">
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-50 flex items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-[#2a273c]">Blocked Guests</h1>
        <p class="text-xs font-black text-[#e95a54] uppercase tracking-widest mt-1">Guest Block List</p>
      </div>
      <button @click="showForm = true; resetForm()" class="bg-[#e95a54] text-white px-4 py-3 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-opacity-90 flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
        Block Guest
      </button>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-slate-50 overflow-hidden flex-1 flex flex-col">
      <div class="overflow-x-auto flex-1">
        <table class="w-full border-collapse">
          <thead>
            <tr class="bg-slate-50/50 border-b border-slate-50">
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Guest Name</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">ID Number</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Reason</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Type</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Blocked By</span></th>
              <th class="p-4 text-end"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Actions</span></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <tr v-for="b in blocked" :key="b.id" class="hover:bg-slate-50/30 transition-colors group">
              <td class="p-4"><span class="text-sm font-bold text-[#2a273c]">{{ b.guest_name }}</span></td>
              <td class="p-4"><span class="text-xs font-mono text-slate-500">{{ b.id_number || '—' }}</span></td>
              <td class="p-4"><span class="text-xs text-slate-600">{{ b.reason }}</span></td>
              <td class="p-4"><span :class="b.block_type === 'permanent' ? 'bg-red-100 text-red-600' : 'bg-amber-100 text-amber-600'" class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest">{{ b.block_type }}</span></td>
              <td class="p-4"><span class="text-xs text-slate-600">{{ b.blocked_by?.name || '—' }}</span></td>
              <td class="p-4 text-end">
                <div class="flex items-center justify-end opacity-0 group-hover:opacity-100 transition-opacity">
                  <button @click="unblock(b)" class="bg-emerald-100 text-emerald-600 px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest">Unblock</button>
                </div>
              </td>
            </tr>
            <tr v-if="!blocked.length"><td colspan="6" class="p-16 text-center text-slate-400 text-sm">No blocked guests.</td></tr>
          </tbody>
        </table>
      </div>
    </div>

    <div v-if="showForm" class="fixed inset-0 bg-black/60 flex items-center justify-center z-50">
      <div class="bg-white rounded-3xl p-8 w-full max-w-md space-y-4">
        <h3 class="text-xl font-bold text-[#2a273c]">Block Guest</h3>
        <div><label class="label">Guest Name *</label><input v-model="form.guest_name" class="input" placeholder="Full name"></div>
        <div><label class="label">ID Number</label><input v-model="form.id_number" class="input" placeholder="ID Number"></div>
        <div><label class="label">Reason *</label><textarea v-model="form.reason" class="input" rows="2" placeholder="Reason for blocking..."></textarea></div>
        <div><label class="label">Block Type</label>
          <select v-model="form.block_type" class="input"><option value="permanent">Permanent</option><option value="temporary">Temporary</option></select>
        </div>
        <div v-if="form.block_type === 'temporary'"><label class="label">End Date *</label><input v-model="form.end_date" type="date" class="input"></div>
        <div class="flex gap-3">
          <button @click="showForm = false" class="flex-1 bg-slate-100 text-[#2a273c] py-3 rounded-xl text-xs font-black uppercase tracking-widest">Cancel</button>
          <button @click="block" :disabled="processing" class="flex-1 bg-[#e95a54] text-white py-3 rounded-xl text-xs font-black uppercase tracking-widest disabled:opacity-40">Block</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue';
import api from '../services/api';

const blocked = ref([]);
const showForm = ref(false);
const processing = ref(false);
const form = reactive({ guest_name: '', id_number: '', reason: '', block_type: 'permanent', end_date: '' });

const resetForm = () => Object.assign(form, { guest_name: '', id_number: '', reason: '', block_type: 'permanent', end_date: '' });

const load = async () => {
  const { data } = await api.get('/guests-module/blocked-guests');
  blocked.value = data.data || [];
};

const block = async () => {
  processing.value = true;
  try {
    await api.post('/guests-module/blocked-guests', form);
    showForm.value = false;
    load();
  } finally { processing.value = false; }
};

const unblock = async (b) => {
  if (!confirm(`Unblock ${b.guest_name}?`)) return;
  await api.delete(`/guests-module/blocked-guests/${b.id}`);
  load();
};

onMounted(load);
</script>

<style scoped>
.label { @apply block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2; }
.input { @apply w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-bold text-[#2a273c] focus:ring-2 focus:ring-[#e95a54]; }
</style>
