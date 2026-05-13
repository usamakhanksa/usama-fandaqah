<template>
  <div class="p-6 space-y-6 bg-[#f8f9fa] min-h-full flex flex-col">
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-50 flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-[#2a273c]">Room Types / Unit Categories</h1>
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
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Base Price</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Max Guests</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Beds</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Rooms</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Status</span></th>
              <th class="p-4 text-end"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Actions</span></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <tr v-for="cat in categories" :key="cat.id" class="hover:bg-slate-50/30 transition-colors group">
              <td class="p-4"><span class="text-sm font-bold text-[#2a273c]">{{ cat.name }}</span></td>
              <td class="p-4"><span class="text-xs font-bold text-[#e95a54]">{{ cat.sunday_day_price || 0 }} SAR</span></td>
              <td class="p-4"><span class="text-xs text-slate-600">{{ (cat.number_of_adults || 0) + (cat.number_of_children || 0) }}</span></td>
              <td class="p-4"><span class="text-xs text-slate-600">{{ cat.number_of_beds || 0 }}</span></td>
              <td class="p-4"><span class="text-xs text-slate-600">{{ cat.all_units_count || 0 }}</span></td>
              <td class="p-4"><span :class="cat.status ? 'bg-emerald-100 text-emerald-600' : 'bg-slate-100 text-slate-500'" class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest">{{ cat.status ? 'Active' : 'Inactive' }}</span></td>
              <td class="p-4 text-end">
                <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                  <button @click="edit(cat)" class="bg-slate-100 text-[#2a273c] px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest hover:bg-slate-200">Edit</button>
                  <button @click="remove(cat)" class="bg-red-100 text-red-600 px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest">Delete</button>
                </div>
              </td>
            </tr>
            <tr v-if="!categories.length"><td colspan="7" class="p-16 text-center text-slate-400 text-sm">No categories found.</td></tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Form Modal -->
    <div v-if="showForm" class="fixed inset-0 bg-black/60 flex items-center justify-center z-50">
      <div class="bg-white rounded-3xl p-8 w-full max-w-lg space-y-4 max-h-[90vh] overflow-y-auto">
        <h3 class="text-xl font-bold text-[#2a273c]">{{ editing ? 'Edit Category' : 'Add Category' }}</h3>
        <div class="grid grid-cols-2 gap-4">
          <div class="col-span-2"><label class="label">Name *</label><input v-model="form.name" class="input" placeholder="Category name"></div>
          <div><label class="label">Base Price (SAR)</label><input v-model="form.sunday_day_price" type="number" class="input" placeholder="0"></div>
          <div><label class="label">Month Price (SAR)</label><input v-model="form.month_price" type="number" class="input" placeholder="0"></div>
          <div><label class="label">Max Adults</label><input v-model="form.number_of_adults" type="number" class="input" placeholder="2"></div>
          <div><label class="label">Max Children</label><input v-model="form.number_of_children" type="number" class="input" placeholder="0"></div>
          <div><label class="label">Beds</label><input v-model="form.number_of_beds" type="number" class="input" placeholder="1"></div>
          <div><label class="label">Status</label>
            <select v-model="form.status" class="input"><option :value="1">Active</option><option :value="0">Inactive</option></select>
          </div>
          <div class="col-span-2"><label class="label">Description</label><textarea v-model="form.description" class="input" rows="2"></textarea></div>
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
const form = reactive({ name: '', description: '', sunday_day_price: 0, month_price: 0, number_of_adults: 2, number_of_children: 0, number_of_beds: 1, status: 1 });

const resetForm = () => Object.assign(form, { name: '', description: '', sunday_day_price: 0, month_price: 0, number_of_adults: 2, number_of_children: 0, number_of_beds: 1, status: 1 });

const load = async () => {
  const { data } = await api.get('/rooms-module/unit-categories', { params: { search: search.value } });
  categories.value = data.data || [];
};

const edit = (cat) => { editing.value = cat; Object.assign(form, cat); showForm.value = true; };

const save = async () => {
  processing.value = true;
  try {
    editing.value
      ? await api.put(`/rooms-module/unit-categories/${editing.value.id}`, form)
      : await api.post('/rooms-module/unit-categories', form);
    showForm.value = false;
    load();
  } finally { processing.value = false; }
};

const remove = async (cat) => {
  if (!confirm(`Delete category ${cat.name}?`)) return;
  await api.delete(`/rooms-module/unit-categories/${cat.id}`);
  load();
};

watch(search, load);
onMounted(load);
</script>

<style scoped>
.label { @apply block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2; }
.input { @apply w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-bold text-[#2a273c] focus:ring-2 focus:ring-[#e95a54]; }
</style>
