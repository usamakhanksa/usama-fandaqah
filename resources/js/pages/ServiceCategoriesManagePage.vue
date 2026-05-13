<template>
  <div class="p-6 space-y-6 bg-[#f8f9fa] min-h-full flex flex-col">
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-50 flex items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-[#2a273c]">Service Categories</h1>
        <p class="text-xs font-black text-[#e95a54] uppercase tracking-widest mt-1">Category Management</p>
      </div>
      <div class="flex gap-3">
        <div class="relative"><input v-model="search" type="text" placeholder="Search..." class="bg-slate-50 border-none rounded-xl py-3 px-4 pl-10 text-xs font-bold text-slate-600 focus:ring-2 focus:ring-[#e95a54]"><svg class="w-4 h-4 text-slate-400 absolute left-3 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg></div>
        <button @click="showForm = true; editing = null; resetForm()" class="bg-[#e95a54] text-white px-4 py-3 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-opacity-90 flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
          Add Category
        </button>
      </div>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-slate-50 overflow-hidden flex-1 flex flex-col">
      <div class="overflow-x-auto flex-1">
        <table class="w-full border-collapse">
          <thead>
            <tr class="bg-slate-50/50 border-b border-slate-50">
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Name</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Type</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">In Reservation</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">In POS</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Order</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Status</span></th>
              <th class="p-4 text-end"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Actions</span></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <tr v-for="c in categories" :key="c.id" class="hover:bg-slate-50/30 transition-colors group">
              <td class="p-4"><span class="text-sm font-bold text-[#2a273c]">{{ getName(c.name) }}</span></td>
              <td class="p-4"><span class="text-xs text-slate-600">{{ c.rev_type || '—' }}</span></td>
              <td class="p-4"><span :class="c.show_in_reservation ? 'text-emerald-600' : 'text-slate-400'" class="text-xs font-bold">{{ c.show_in_reservation ? 'Yes' : 'No' }}</span></td>
              <td class="p-4"><span :class="c.show_in_pos ? 'text-emerald-600' : 'text-slate-400'" class="text-xs font-bold">{{ c.show_in_pos ? 'Yes' : 'No' }}</span></td>
              <td class="p-4"><span class="text-xs text-slate-600">{{ c.order }}</span></td>
              <td class="p-4"><span :class="c.status ? 'bg-emerald-100 text-emerald-600' : 'bg-slate-100 text-slate-500'" class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest">{{ c.status ? 'Active' : 'Inactive' }}</span></td>
              <td class="p-4 text-end">
                <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                  <button @click="edit(c)" class="bg-slate-100 text-[#2a273c] px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest hover:bg-slate-200">Edit</button>
                  <button @click="remove(c)" class="bg-red-100 text-red-600 px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest">Delete</button>
                </div>
              </td>
            </tr>
            <tr v-if="!categories.length"><td colspan="7" class="p-16 text-center text-slate-400 text-sm">No categories found.</td></tr>
          </tbody>
        </table>
      </div>
    </div>

    <div v-if="showForm" class="fixed inset-0 bg-black/60 flex items-center justify-center z-50">
      <div class="bg-white rounded-3xl p-8 w-full max-w-md space-y-4">
        <h3 class="text-xl font-bold text-[#2a273c]">{{ editing ? 'Edit Category' : 'Add Category' }}</h3>
        <div class="grid grid-cols-2 gap-4">
          <div><label class="label">Name (EN) *</label><input v-model="form.name" class="input" placeholder="Category name"></div>
          <div><label class="label">Name (AR)</label><input v-model="form.name_ar" class="input" dir="rtl" placeholder="اسم الفئة"></div>
          <div><label class="label">Type</label><input v-model="form.rev_type" class="input" placeholder="room/pos/laundry"></div>
          <div><label class="label">Order</label><input v-model="form.order" type="number" class="input" placeholder="0"></div>
          <div><label class="label">In Reservation</label><select v-model="form.show_in_reservation" class="input"><option :value="true">Yes</option><option :value="false">No</option></select></div>
          <div><label class="label">In POS</label><select v-model="form.show_in_pos" class="input"><option :value="true">Yes</option><option :value="false">No</option></select></div>
          <div><label class="label">Status</label><select v-model="form.status" class="input"><option :value="1">Active</option><option :value="0">Inactive</option></select></div>
        </div>
        <div class="flex gap-3 pt-2">
          <button @click="showForm = false" class="flex-1 bg-slate-100 text-[#2a273c] py-3 rounded-xl text-xs font-black uppercase tracking-widest">Cancel</button>
          <button @click="save" :disabled="processing" class="flex-1 bg-[#e95a54] text-white py-3 rounded-xl text-xs font-black uppercase tracking-widest disabled:opacity-40">{{ processing ? 'Saving...' : 'Save' }}</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, watch, onMounted } from 'vue';
import api from '../services/api';

const categories = ref([]);
const showForm = ref(false);
const editing = ref(null);
const processing = ref(false);
const search = ref('');
const form = reactive({ name: '', name_ar: '', rev_type: '', order: 0, show_in_reservation: true, show_in_pos: true, status: 1 });

const resetForm = () => Object.assign(form, { name: '', name_ar: '', rev_type: '', order: 0, show_in_reservation: true, show_in_pos: true, status: 1 });
const getName = (n) => { try { const p = typeof n === 'string' ? JSON.parse(n) : n; return p?.en || n; } catch { return n; } };

const load = async () => {
  const { data } = await api.get('/pos-module/service-categories', { params: { search: search.value } });
  categories.value = data.data || [];
};

const edit = (c) => {
  editing.value = c;
  const n = typeof c.name === 'string' ? JSON.parse(c.name) : c.name;
  Object.assign(form, { ...c, name: n?.en || '', name_ar: n?.ar || '' });
  showForm.value = true;
};

const save = async () => {
  processing.value = true;
  try {
    editing.value
      ? await api.put(`/pos-module/service-categories/${editing.value.id}`, form)
      : await api.post('/pos-module/service-categories', form);
    showForm.value = false;
    load();
  } finally { processing.value = false; }
};

const remove = async (c) => {
  if (!confirm(`Delete category?`)) return;
  await api.delete(`/pos-module/service-categories/${c.id}`);
  load();
};

watch(search, load);
onMounted(load);
</script>

<style scoped>
.label { @apply block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2; }
.input { @apply w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-bold text-[#2a273c] focus:ring-2 focus:ring-[#e95a54]; }
</style>
