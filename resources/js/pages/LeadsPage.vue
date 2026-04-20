<template>
  <div class="p-8 space-y-6 bg-[#f8f9fa] min-h-full">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row justify-between items-end gap-6 bg-white p-8 custom-rounded shadow-sm border border-slate-50">
      <div>
        <h1 class="text-3xl font-black text-[#2a273c]">{{ $t('leads.title') }}</h1>
        <p class="text-[#8f9793] font-medium mt-1">{{ $t('leads.desc') }}</p>
      </div>
      
      <!-- Stats Summary -->
      <div class="flex gap-4 w-full md:w-auto overflow-x-auto pb-2 md:pb-0">
        <div v-for="(val, label) in statsDisplay" :key="label" class="bg-white px-6 py-4 rounded-2xl border border-slate-100 shadow-sm min-w-[140px]">
           <p class="text-[10px] font-black text-[#8f9793] uppercase tracking-widest">{{ label }}</p>
           <p class="text-2xl font-black text-[#2a273c]">{{ val }}</p>
        </div>
      </div>
    </div>

    <!-- Filters & View Toggle -->
    <div class="bg-white p-6 custom-rounded border border-slate-100 shadow-sm flex flex-wrap gap-4 items-center">
      <div class="relative flex-1 min-w-[280px]">
        <SearchIcon class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
        <input 
          v-model="filters.search"
          type="text" 
          :placeholder="$t('table.search_placeholder')" 
          class="w-full pl-11 pr-4 py-3 bg-slate-50 rounded-xl border-none focus:ring-2 focus:ring-[#e95a54] outline-none transition-all text-sm"
        >
      </div>
      
      <select v-model="filters.status" class="px-4 py-3 bg-slate-50 rounded-xl border-none focus:ring-2 focus:ring-[#e95a54] outline-none text-sm font-medium text-slate-600">
        <option value="">{{ $t('table.all_status') }}</option>
        <option value="new">{{ $t('leads.new') }}</option>
        <option value="contacted">{{ $t('leads.contacted') }}</option>
        <option value="qualified">{{ $t('leads.qualified') }}</option>
        <option value="lost">{{ $t('leads.lost') }}</option>
      </select>

      <button @click="fetchLeads" class="bg-[#2a273c] text-white px-8 py-3 rounded-xl font-bold hover:bg-[#e95a54] transition-all text-sm shadow-lg shadow-slate-100">
         {{ $t('leads.apply_filters') }}
      </button>

      <div class="h-8 w-px bg-slate-100 mx-2 hidden lg:block"></div>

      <!-- View Toggle -->
      <div class="flex bg-slate-50 p-1 rounded-xl">
        <button @click="viewMode = 'table'" :class="['p-2 rounded-lg transition-all', viewMode === 'table' ? 'bg-white shadow-sm text-[#e95a54]' : 'text-slate-400']">
          <TableIcon class="w-5 h-5" />
        </button>
        <button @click="viewMode = 'grid'" :class="['p-2 rounded-lg transition-all', viewMode === 'grid' ? 'bg-white shadow-sm text-[#e95a54]' : 'text-slate-400']">
          <LayoutGridIcon class="w-5 h-5" />
        </button>
      </div>
    </div>

    <!-- Table View -->
    <div v-if="viewMode === 'table'" class="bg-white custom-rounded border border-slate-100 shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left">
          <thead>
            <tr class="bg-slate-50/50 border-b border-slate-100">
              <th class="px-8 py-5 text-[10px] font-black text-[#8f9793] uppercase tracking-widest text-start">{{ $t('leads.lead_info') }}</th>
              <th class="px-8 py-5 text-[10px] font-black text-[#8f9793] uppercase tracking-widest text-start">{{ $t('leads.interest') }}</th>
              <th class="px-8 py-5 text-[10px] font-black text-[#8f9793] uppercase tracking-widest text-start">{{ $t('leads.date') }}</th>
              <th class="px-8 py-5 text-[10px] font-black text-[#8f9793] uppercase tracking-widest text-start">{{ $t('leads.status') }}</th>
              <th class="px-8 py-5 text-[10px] font-black text-[#8f9793] uppercase tracking-widest text-end">{{ $t('leads.actions') }}</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <tr v-for="lead in leads" :key="lead.id" class="hover:bg-slate-50/30 transition-colors group">
              <td class="px-8 py-6">
                <div class="flex items-center gap-4">
                  <div class="w-12 h-12 rounded-2xl bg-[#f2f0eb] flex items-center justify-center font-black text-[#e95a54] group-hover:bg-[#e95a54] group-hover:text-white transition-colors">
                    {{ lead.full_name?.charAt(0).toUpperCase() }}
                  </div>
                  <div>
                    <p class="font-bold text-[#2a273c] text-base">{{ lead.full_name }}</p>
                    <p class="text-xs text-[#8f9793] font-medium">{{ lead.email }} | {{ lead.phone }}</p>
                  </div>
                </div>
              </td>
              <td class="px-8 py-6">
                <div class="flex flex-col gap-1 items-start">
                  <span class="px-3 py-1 bg-[#fbcdab]/20 text-[#2a273c] text-[10px] font-black uppercase rounded-lg">{{ lead.product_interest }}</span>
                  <span class="text-[10px] text-slate-400 font-bold ml-1 uppercase tracking-tight">{{ lead.property_type }}</span>
                </div>
              </td>
              <td class="px-8 py-6 text-sm text-slate-500 font-semibold">
                {{ new Date(lead.created_at).toLocaleDateString() }}
              </td>
              <td class="px-8 py-6">
                <span 
                  class="px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest"
                  :style="{ backgroundColor: getStatusBg(lead.status), color: getStatusColor(lead.status) }"
                >
                  {{ $t(`leads.${lead.status}`) }}
                </span>
              </td>
              <td class="px-8 py-6">
                <div class="flex justify-end gap-2 opacity-0 group-hover:opacity-100 transition-all transform translate-x-2 group-hover:translate-x-0">
                  <button @click="updateStatus(lead, 'contacted')" class="p-2.5 text-blue-500 hover:bg-blue-50 rounded-xl transition-colors" title="Mark as Contacted">
                    <PhoneIcon class="w-4 h-4" />
                  </button>
                  <button @click="updateStatus(lead, 'qualified')" class="p-2.5 text-emerald-500 hover:bg-emerald-50 rounded-xl transition-colors" title="Mark as Qualified">
                    <CheckCircle2Icon class="w-4 h-4" />
                  </button>
                  <button @click="deleteLead(lead.id)" class="p-2.5 text-rose-500 hover:bg-rose-50 rounded-xl transition-colors" title="Delete Lead">
                    <Trash2Icon class="w-4 h-4" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Grid View -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
       <div v-for="lead in leads" :key="lead.id" class="bg-white p-8 custom-rounded-large border border-slate-50 shadow-sm hover:shadow-2xl hover:-translate-y-2 transition-all group overflow-hidden relative">
          <div class="absolute top-0 right-0 p-6 flex justify-between w-full pointer-events-none">
             <span class="text-[9px] font-black text-slate-300 uppercase tracking-widest transform -rotate-90 origin-right translate-x-2">{{ lead.source || 'Slider' }}</span>
             <span 
                class="px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest pointer-events-auto"
                :style="{ backgroundColor: getStatusBg(lead.status), color: getStatusColor(lead.status) }"
              >
                {{ $t(`leads.${lead.status}`) }}
              </span>
          </div>
          
          <div class="mt-4 mb-6">
            <div class="w-16 h-16 rounded-3xl bg-[#f2f0eb] flex items-center justify-center font-black text-2xl text-[#e95a54] mb-4 group-hover:scale-110 transition-transform">
              {{ lead.full_name?.charAt(0).toUpperCase() }}
            </div>
            <h3 class="text-2xl font-black text-[#2a273c] mb-1 leading-tight">{{ lead.full_name }}</h3>
            <p class="text-sm text-[#8f9793] font-medium">{{ lead.email }}</p>
          </div>
          
          <div class="flex gap-4 mb-8">
             <div class="bg-slate-50 p-4 rounded-3xl flex-1 border border-slate-100">
                <p class="text-[9px] font-black text-slate-400 uppercase mb-1">Product</p>
                <p class="text-xs font-black text-[#e95a54] uppercase">{{ lead.product_interest }}</p>
             </div>
             <div class="bg-slate-50 p-4 rounded-3xl flex-1 border border-slate-100">
                <p class="text-[9px] font-black text-slate-400 uppercase mb-1">Property</p>
                <p class="text-xs font-black text-[#2a273c] uppercase">{{ lead.property_type }}</p>
             </div>
          </div>
          
          <div class="flex gap-3 pt-2">
             <a :href="'mailto:' + lead.email" class="flex-1 py-4 bg-[#2a273c] text-white rounded-[1.2rem] text-xs font-black text-center shadow-lg shadow-slate-100 hover:bg-[#e95a54] transition-all">{{ $t('leads.contacted') }}</a>
             <button @click="updateStatus(lead, 'qualified')" class="flex-1 py-4 bg-[#f2f0eb] text-[#2a273c] rounded-[1.2rem] text-xs font-black hover:bg-emerald-50 hover:text-emerald-600 transition-all">{{ $t('leads.qualified') }}</button>
          </div>
       </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import api from '../services/api';
import { useI18n } from 'vue-i18n';
import { 
  SearchIcon, TableIcon, LayoutGridIcon, PhoneIcon, Trash2Icon, CheckCircle2Icon 
} from 'lucide-vue-next';

const { t } = useI18n();
const leads = ref([]);
const stats = ref({ total: 0, new: 0, qualified: 0, contacted: 0 });
const viewMode = ref('table');
const filters = ref({ search: '', status: '' });

const statsDisplay = computed(() => ({
  [t('leads.total_inquiries')]: stats.value.total,
  [t('leads.new')]: stats.value.new,
  [t('leads.qualified')]: stats.value.qualified,
  [t('leads.contacted')]: stats.value.contacted,
}));

const fetchLeads = async () => {
  try {
    const res = await api.get('/leads', { params: filters.value });
    leads.value = res.data.data;
    stats.value = res.data.stats || { total: 0, new: 0, qualified: 0, contacted: 0 };
  } catch (e) {
    console.error('[Leads Fetch Error]', e);
  }
};

const updateStatus = async (lead, status) => {
  try {
    await api.put(`/leads/${lead.id}`, { status });
    fetchLeads();
  } catch (e) {
    console.error(e);
  }
};

const deleteLead = async (id) => {
  if (!confirm('Are you sure you want to delete this inquiry?')) return;
  try {
    await api.delete(`/leads/${id}`);
    fetchLeads();
  } catch (e) {
    console.error(e);
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

<style scoped>
[dir="rtl"] .text-start { text-align: right; }
[dir="rtl"] .text-end { text-align: left; }
.custom-rounded { border-radius: 2rem; }
.custom-rounded-large { border-radius: 2.5rem; }
</style>
