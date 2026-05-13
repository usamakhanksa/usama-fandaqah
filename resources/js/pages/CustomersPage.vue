<template>
  <div class="p-6 space-y-6 bg-[#f8f9fa] min-h-full flex flex-col">
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-50 flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-[#2a273c]">Customer Profiles</h1>
        <p class="text-xs font-black text-[#e95a54] uppercase tracking-widest mt-1">Customer Management</p>
      </div>
      <div class="flex gap-3">
        <div class="relative"><input v-model="filters.search" type="text" placeholder="Search name, phone, email, ID..." class="bg-slate-50 border-none rounded-xl py-3 px-4 pl-10 text-xs font-bold text-slate-600 focus:ring-2 focus:ring-[#e95a54] w-64"><svg class="w-4 h-4 text-slate-400 absolute left-3 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg></div>
        <button @click="showForm = true; editing = null; resetForm()" class="bg-[#e95a54] text-white px-4 py-3 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-opacity-90 flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
          Add Customer
        </button>
      </div>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-slate-50 overflow-hidden flex-1 flex flex-col">
      <div class="overflow-x-auto flex-1">
        <table class="w-full border-collapse">
          <thead>
            <tr class="bg-slate-50/50 border-b border-slate-50">
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Name</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Phone</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Email</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">ID Number</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Type</span></th>
              <th class="p-4 text-end"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Actions</span></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <tr v-for="c in customers" :key="c.id" class="hover:bg-slate-50/30 transition-colors group">
              <td class="p-4"><span class="text-sm font-bold text-[#2a273c]">{{ c.name }}</span></td>
              <td class="p-4"><span class="text-xs text-slate-600">{{ c.phone || '—' }}</span></td>
              <td class="p-4"><span class="text-xs text-slate-600">{{ c.email || '—' }}</span></td>
              <td class="p-4"><span class="text-xs font-mono text-slate-500">{{ c.id_number || '—' }}</span></td>
              <td class="p-4"><span class="text-xs text-slate-600">{{ c.customer_type || '—' }}</span></td>
              <td class="p-4 text-end">
                <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                  <button @click="edit(c)" class="bg-slate-100 text-[#2a273c] px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest hover:bg-slate-200">Edit</button>
                  <button @click="remove(c)" class="bg-red-100 text-red-600 px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest">Delete</button>
                </div>
              </td>
            </tr>
            <tr v-if="!customers.length"><td colspan="6" class="p-16 text-center text-slate-400 text-sm">No customers found.</td></tr>
          </tbody>
        </table>
      </div>
      <div class="p-6 bg-slate-50/50 border-t border-slate-50 flex items-center justify-between">
        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ pagination.total }} customers</span>
        <div class="flex gap-2">
          <button v-if="pagination.prev" @click="changePage(pagination.current - 1)" class="p-2 hover:bg-white rounded-xl border border-transparent hover:border-slate-100"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg></button>
          <button v-if="pagination.next" @click="changePage(pagination.current + 1)" class="p-2 hover:bg-white rounded-xl border border-transparent hover:border-slate-100"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg></button>
        </div>
      </div>
    </div>

    <!-- Form Modal -->
    <div v-if="showForm" class="fixed inset-0 bg-black/60 flex items-center justify-center z-50">
      <div class="bg-white rounded-3xl p-8 w-full max-w-lg space-y-4 max-h-[90vh] overflow-y-auto">
        <h3 class="text-xl font-bold text-[#2a273c]">{{ editing ? 'Edit Customer' : 'Add Customer' }}</h3>
        <div class="grid grid-cols-2 gap-4">
          <div class="col-span-2"><label class="label">Full Name *</label><input v-model="form.name" class="input" placeholder="Full name"></div>
          <div><label class="label">Phone</label><input v-model="form.phone" class="input" placeholder="+966..."></div>
          <div><label class="label">Email</label><input v-model="form.email" type="email" class="input" placeholder="email@example.com"></div>
          <div><label class="label">ID Type</label>
            <select v-model="form.id_type" class="input">
              <option :value="1">National ID</option><option :value="3">GCC ID</option><option :value="4">Residence</option><option :value="5">Passport</option>
            </select>
          </div>
          <div><label class="label">ID Number</label><input v-model="form.id_number" class="input" placeholder="ID Number"></div>
          <div><label class="label">Gender</label>
            <select v-model="form.gender" class="input"><option value="male">Male</option><option value="female">Female</option></select>
          </div>
          <div><label class="label">Customer Type</label>
            <select v-model="form.customer_type" class="input">
              <option :value="1">Citizen</option><option :value="2">Gulf Citizen</option><option :value="3">Visitor</option><option :value="4">Resident</option>
            </select>
          </div>
          <div class="col-span-2"><label class="label">Address</label><input v-model="form.address" class="input" placeholder="Address"></div>
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

const customers = ref([]);
const showForm = ref(false);
const editing = ref(null);
const processing = ref(false);
const filters = reactive({ search: '', page: 1 });
const pagination = reactive({ current: 1, next: false, prev: false, total: 0 });
const form = reactive({ name: '', phone: '', email: '', id_type: 1, id_number: '', gender: 'male', customer_type: 1, address: '' });

const resetForm = () => Object.assign(form, { name: '', phone: '', email: '', id_type: 1, id_number: '', gender: 'male', customer_type: 1, address: '' });

const load = async () => {
  const { data } = await api.get('/guests-module/customers', { params: filters });
  customers.value = data.data || [];
  pagination.next = !!data.next_page_url;
  pagination.prev = !!data.prev_page_url;
  pagination.current = data.current_page || 1;
  pagination.total = data.total || 0;
};

const edit = (c) => { editing.value = c; Object.assign(form, c); showForm.value = true; };

const save = async () => {
  processing.value = true;
  try {
    editing.value
      ? await api.put(`/guests-module/customers/${editing.value.id}`, form)
      : await api.post('/guests-module/customers', form);
    showForm.value = false;
    load();
  } finally { processing.value = false; }
};

const remove = async (c) => {
  if (!confirm(`Delete customer ${c.name}?`)) return;
  await api.delete(`/guests-module/customers/${c.id}`);
  load();
};

const changePage = (page) => { filters.page = page; load(); };
watch(filters, () => { filters.page = 1; load(); });
onMounted(load);
</script>

<style scoped>
.label { @apply block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2; }
.input { @apply w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-bold text-[#2a273c] focus:ring-2 focus:ring-[#e95a54]; }
</style>
