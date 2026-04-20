<template>
  <div class="p-8">
    <div class="flex justify-between items-end mb-8">
      <div>
        <h1 class="text-3xl font-black text-[#2a273c]">Leads & Support</h1>
        <p class="text-[#8f9793] font-medium">Manage inquiries captured from the feature slider</p>
      </div>
      
      <!-- Stats Summary -->
      <div class="flex gap-4">
        <div v-for="(val, label) in stats" :key="label" class="bg-white px-6 py-4 rounded-2xl border border-slate-100 shadow-sm">
           <p class="text-[10px] font-black text-[#8f9793] uppercase tracking-widest">{{ label }}</p>
           <p class="text-2xl font-black text-[#2a273c]">{{ val }}</p>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm mb-6 flex flex-wrap gap-4 items-center">
      <div class="relative flex-1 min-w-[200px]">
        <SearchIcon class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
        <input 
          v-model="filters.search"
          type="text" 
          placeholder="Search Leads..." 
          class="w-full pl-11 pr-4 py-3 bg-[#f2f0eb]/50 rounded-xl border-2 border-transparent focus:border-[#e95a54] outline-none transition-all"
        >
      </div>
      
      <select v-model="filters.status" class="px-4 py-3 bg-[#f2f0eb]/50 rounded-xl border-2 border-transparent focus:border-[#e95a54] outline-none">
        <option value="">All Status</option>
        <option value="new">New</option>
        <option value="contacted">Contacted</option>
        <option value="qualified">Qualified</option>
        <option value="lost">Lost</option>
      </select>

      <button @click="fetchLeads" class="bg-[#2a273c] text-white px-6 py-3 rounded-xl font-bold hover:bg-[#e95a54] transition-all">
         Apply Filters
      </button>

      <!-- View Toggle -->
      <div class="flex bg-[#f2f0eb] p-1 rounded-xl ml-auto">
        <button 
          @click="viewMode = 'table'" 
          class="p-2 rounded-lg transition-all"
          :class="viewMode === 'table' ? 'bg-white shadow-sm text-[#e95a54]' : 'text-slate-400'"
        >
          <TableIcon class="w-5 h-5" />
        </button>
        <button 
          @click="viewMode = 'grid'" 
          class="p-2 rounded-lg transition-all"
          :class="viewMode === 'grid' ? 'bg-white shadow-sm text-[#e95a54]' : 'text-slate-400'"
        >
          <LayoutGridIcon class="w-5 h-5" />
        </button>
      </div>
    </div>

    <!-- Table View -->
    <div v-if="viewMode === 'table'" class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
      <table class="w-full text-left">
        <thead>
          <tr class="bg-slate-50/50 border-b border-slate-100">
            <th class="px-6 py-5 text-[11px] font-black text-[#8f9793] uppercase tracking-widest">Lead Info</th>
            <th class="px-6 py-5 text-[11px] font-black text-[#8f9793] uppercase tracking-widest">Interest</th>
            <th class="px-6 py-5 text-[11px] font-black text-[#8f9793] uppercase tracking-widest">Date</th>
            <th class="px-6 py-5 text-[11px] font-black text-[#8f9793] uppercase tracking-widest">Status</th>
            <th class="px-6 py-5 text-[11px] font-black text-[#8f9793] uppercase tracking-widest">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
          <tr v-for="lead in leads" :key="lead.id" class="hover:bg-slate-50/30 transition-colors group">
            <td class="px-6 py-5">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center font-bold text-[#e95a54]">
                  {{ lead.full_name[0] }}
                </div>
                <div>
                  <p class="font-bold text-[#2a273c]">{{ lead.full_name }}</p>
                  <p class="text-xs text-[#8f9793]">{{ lead.email }} | {{ lead.phone }}</p>
                </div>
              </div>
            </td>
            <td class="px-6 py-5">
              <div class="space-y-1">
                <span class="px-2 py-0.5 bg-[#fbcdab]/20 text-[#e95a54] text-[10px] font-black uppercase rounded">{{ lead.product_interest }}</span>
                <p class="text-[10px] text-slate-400 ml-1">{{ lead.property_type }}</p>
              </div>
            </td>
            <td class="px-6 py-5 text-sm text-slate-500">
              {{ new Date(lead.created_at).toLocaleDateString() }}
            </td>
            <td class="px-6 py-5">
              <span 
                class="px-3 py-1 rounded-full text-[10px] font-black uppercase"
                :style="{ backgroundColor: getStatusBg(lead.status), color: getStatusColor(lead.status) }"
              >
                {{ lead.status }}
              </span>
            </td>
            <td class="px-6 py-5">
              <div class="flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                <button @click="updateStatus(lead, 'contacted')" class="p-2 text-blue-500 hover:bg-blue-50 rounded-lg">
                  <PhoneIcon class="w-4 h-4" />
                </button>
                <button @click="deleteLead(lead.id)" class="p-2 text-red-500 hover:bg-red-50 rounded-lg">
                  <Trash2Icon class="w-4 h-4" />
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Grid View -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
       <div v-for="lead in leads" :key="lead.id" class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all">
          <div class="flex justify-between mb-4">
             <span class="text-[10px] font-black text-[#8f9793] uppercase tracking-widest">{{ lead.source }}</span>
             <span 
                class="px-2 py-0.5 rounded text-[9px] font-black uppercase"
                :style="{ backgroundColor: getStatusBg(lead.status), color: getStatusColor(lead.status) }"
              >
                {{ lead.status }}
              </span>
          </div>
          <p class="text-xl font-black text-[#2a273c] mb-1">{{ lead.full_name }}</p>
          <p class="text-xs text-[#8f9793] mb-4">{{ lead.email }}</p>
          
          <div class="flex gap-2 mb-6">
             <div class="bg-slate-50 p-2 rounded-xl flex-1 text-center">
                <p class="text-[9px] font-bold text-slate-400 uppercase">Product</p>
                <p class="text-xs font-black text-[#e95a54]">{{ lead.product_interest }}</p>
             </div>
             <div class="bg-slate-50 p-2 rounded-xl flex-1 text-center">
                <p class="text-[9px] font-bold text-slate-400 uppercase">Property</p>
                <p class="text-xs font-black text-[#2a273c]">{{ lead.property_type }}</p>
             </div>
          </div>
          
          <div class="flex gap-2">
             <button class="flex-1 py-2 bg-[#2a273c] text-white rounded-xl text-xs font-bold">Contact</button>
             <button @click="updateStatus(lead, 'qualified')" class="flex-1 py-2 bg-[#8f9793]/10 text-[#8f9793] rounded-xl text-xs font-bold">Qualify</button>
          </div>
       </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../services/api';
import { 
  SearchIcon, 
  TableIcon, 
  LayoutGridIcon, 
  PhoneIcon, 
  Trash2Icon,
  CheckCircle2Icon
} from 'lucide-vue-next';

const leads = ref([]);
const stats = ref({
  'Total Inquiries': 0,
  'New': 0,
  'Qualified': 0,
  'Contacted': 0
});
const viewMode = ref('table');
const filters = ref({
  search: '',
  status: ''
});

const fetchLeads = async () => {
  try {
    const res = await api.get('/leads', { params: filters.value });
    leads.ref.value = res.data.data;
    stats.value = {
      'Total Inquiries': res.data.stats.total,
      'New': res.data.stats.new,
      'Qualified': res.data.stats.qualified,
      'Contacted': res.data.stats.contacted,
    }
  } catch (e) {
    console.error(e);
  }
};

const updateStatus = async (lead, status) => {
  try {
    await api.put(`/leads/${lead.id}`, { status });
    fetchLeads();
  } catch (e) {
    alert('Failed to update status');
  }
};

const deleteLead = async (id) => {
  if (!confirm('Are you sure?')) return;
  try {
    await api.delete(`/leads/${id}`);
    fetchLeads();
  } catch (e) {
    alert('Failed to delete lead');
  }
};

const getStatusBg = (status) => {
  const map = { new: '#e95a5415', contacted: '#3b82f615', qualified: '#10b98115', lost: '#64748b15' };
  return map[status] || '#f1f5f9';
};

const getStatusColor = (status) => {
  const map = { new: '#e95a54', contacted: '#3b82f6', qualified: '#10b981', lost: '#64748b' };
  return map[status] || '#64748b';
};

onMounted(fetchLeads);
</script>
