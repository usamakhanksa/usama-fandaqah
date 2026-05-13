<template>
  <div class="p-6 space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-black text-slate-900 tracking-tight">Travel Agent Partners</h1>
        <p class="text-slate-500 text-sm font-medium">Manage booking sources and commission structures</p>
      </div>
      <button @click="openCreateModal" class="bg-slate-900 text-white px-6 py-2.5 rounded-2xl flex items-center gap-2 hover:bg-rose-600 transition-all font-bold text-sm shadow-lg shadow-slate-200">
        <Plus class="w-4 h-4" />
        New Agent
      </button>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="bg-white p-6 rounded-[32px] border border-slate-100 shadow-sm">
        <div class="flex items-center gap-4">
          <div class="p-3 bg-indigo-50 rounded-2xl text-indigo-600">
            <Users class="w-6 h-6" />
          </div>
          <div>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Agents</p>
            <h3 class="text-2xl font-black text-slate-900">{{ agents.length }}</h3>
          </div>
        </div>
      </div>
      <div class="bg-white p-6 rounded-[32px] border border-slate-100 shadow-sm">
        <div class="flex items-center gap-4">
          <div class="p-3 bg-emerald-50 rounded-2xl text-emerald-600">
            <TrendingUp class="w-6 h-6" />
          </div>
          <div>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Active Partners</p>
            <h3 class="text-2xl font-black text-slate-900">{{ agents.filter(a => a.status).length }}</h3>
          </div>
        </div>
      </div>
      <div class="bg-white p-6 rounded-[32px] border border-slate-100 shadow-sm">
        <div class="flex items-center gap-4">
          <div class="p-3 bg-amber-50 rounded-2xl text-amber-600">
            <Percent class="w-6 h-6" />
          </div>
          <div>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Avg. Commission</p>
            <h3 class="text-2xl font-black text-slate-900">{{ avgCommission }}%</h3>
          </div>
        </div>
      </div>
    </div>

    <!-- Agents Table -->
    <div class="bg-white rounded-[32px] border border-slate-100 shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-slate-50/50">
              <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Agent Name</th>
              <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">IATA Number</th>
              <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Commission</th>
              <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Status</th>
              <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <tr v-for="agent in agents" :key="agent.id" class="hover:bg-slate-50/50 transition-colors group">
              <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 bg-slate-100 rounded-xl flex items-center justify-center text-slate-600 font-bold">
                    {{ agent.name.en?.charAt(0) || 'A' }}
                  </div>
                  <div>
                    <div class="text-sm font-bold text-slate-900">{{ agent.name.en }}</div>
                    <div class="text-[10px] text-slate-400 font-medium">{{ agent.name.ar }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4">
                <span class="text-sm font-mono text-slate-600">{{ agent.iata_number || 'N/A' }}</span>
              </td>
              <td class="px-6 py-4">
                <div class="flex items-center gap-2">
                  <span class="text-sm font-bold text-slate-900">{{ agent.commission_rate }}{{ agent.commission_type === 'percentage' ? '%' : ' SAR' }}</span>
                  <span class="text-[10px] text-slate-400 font-bold uppercase tracking-tighter bg-slate-100 px-1.5 py-0.5 rounded">{{ agent.commission_type }}</span>
                </div>
              </td>
              <td class="px-6 py-4">
                <span :class="agent.status ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-400'" class="text-[10px] font-black uppercase tracking-widest px-2.5 py-1 rounded-lg">
                  {{ agent.status ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td class="px-6 py-4 text-right">
                <div class="flex justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                  <button @click="editAgent(agent)" class="p-2 text-slate-400 hover:text-slate-900 hover:bg-slate-100 rounded-xl transition-all">
                    <Edit3 class="w-4 h-4" />
                  </button>
                  <button @click="deleteAgent(agent.id)" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-all">
                    <Trash2 class="w-4 h-4" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Agent Modal -->
    <div v-if="showModal" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm animate-in fade-in duration-300">
      <div class="bg-white rounded-[40px] w-full max-w-lg shadow-2xl overflow-hidden animate-in zoom-in-95 duration-300">
        <div class="p-8 border-b border-slate-50 flex items-center justify-between">
          <h2 class="text-xl font-black text-slate-900">{{ editingId ? 'Edit Agent' : 'Register New Agent' }}</h2>
          <button @click="showModal = false" class="p-2 text-slate-400 hover:bg-slate-50 rounded-full"><X class="w-5 h-5" /></button>
        </div>
        
        <form @submit.prevent="saveAgent" class="p-8 space-y-6">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Name (English)</label>
              <input v-model="form.name.en" type="text" required class="w-full bg-slate-50 border-none rounded-2xl px-4 py-3 text-sm focus:ring-2 ring-rose-300 outline-none transition-all" />
            </div>
            <div>
              <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Name (Arabic)</label>
              <input v-model="form.name.ar" type="text" required dir="rtl" class="w-full bg-slate-50 border-none rounded-2xl px-4 py-3 text-sm focus:ring-2 ring-rose-300 outline-none transition-all font-arabic" />
            </div>
          </div>

          <div>
            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">IATA Number</label>
            <input v-model="form.iata_number" type="text" class="w-full bg-slate-50 border-none rounded-2xl px-4 py-3 text-sm focus:ring-2 ring-rose-300 outline-none transition-all" placeholder="e.g. 12-3 4567 8" />
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Commission Rate</label>
              <input v-model="form.commission_rate" type="number" step="0.01" required class="w-full bg-slate-50 border-none rounded-2xl px-4 py-3 text-sm focus:ring-2 ring-rose-300 outline-none transition-all" />
            </div>
            <div>
              <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Rate Type</label>
              <select v-model="form.commission_type" class="w-full bg-slate-50 border-none rounded-2xl px-4 py-3 text-sm focus:ring-2 ring-rose-300 outline-none transition-all">
                <option value="percentage">Percentage (%)</option>
                <option value="fixed">Fixed Amount (SAR)</option>
              </select>
            </div>
          </div>

          <div class="flex items-center gap-3 p-4 bg-slate-50 rounded-2xl">
            <input v-model="form.status" type="checkbox" id="status" class="w-5 h-5 rounded-lg border-none text-rose-500 focus:ring-rose-200" />
            <label for="status" class="text-sm font-bold text-slate-700 cursor-pointer">Active Partner</label>
          </div>

          <div class="pt-4 flex gap-3">
            <button type="button" @click="showModal = false" class="flex-1 px-6 py-3 border border-slate-100 text-slate-500 rounded-2xl font-bold hover:bg-slate-50 transition-all">Cancel</button>
            <button type="submit" :disabled="loading" class="flex-[2] bg-slate-900 text-white px-6 py-3 rounded-2xl font-bold hover:bg-rose-600 transition-all shadow-lg shadow-slate-200 disabled:opacity-50">
              {{ loading ? 'Saving...' : (editingId ? 'Update Agent' : 'Register Agent') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { 
  Plus, Users, TrendingUp, Percent, 
  Edit3, Trash2, X, Globe, Save 
} from 'lucide-vue-next';
import api from '../../services/api';

const agents = ref([]);
const showModal = ref(false);
const editingId = ref(null);
const loading = ref(false);

const form = ref({
  name: { en: '', ar: '' },
  iata_number: '',
  commission_rate: 0,
  commission_type: 'percentage',
  status: true,
  is_travel_agent: true
});

const avgCommission = computed(() => {
  if (!agents.value.length) return 0;
  const sum = agents.value.reduce((acc, curr) => acc + parseFloat(curr.commission_rate || 0), 0);
  return (sum / agents.value.length).toFixed(1);
});

const fetchAgents = async () => {
  try {
    const { data } = await api.get('/sources', { params: { is_travel_agent: 1, per_page: 100 } });
    agents.value = data.data;
  } catch (err) {
    console.error('Failed to fetch agents', err);
  }
};

const openCreateModal = () => {
  editingId.value = null;
  form.value = {
    name: { en: '', ar: '' },
    iata_number: '',
    commission_rate: 10,
    commission_type: 'percentage',
    status: true,
    is_travel_agent: true
  };
  showModal.value = true;
};

const editAgent = (agent) => {
  editingId.value = agent.id;
  form.value = { 
    ...agent,
    name: { ...agent.name }
  };
  showModal.value = true;
};

const saveAgent = async () => {
  loading.value = true;
  try {
    if (editingId.value) {
      await api.put(`/sources/${editingId.value}`, form.value);
    } else {
      await api.post('/sources', form.value);
    }
    await fetchAgents();
    showModal.value = false;
  } catch (err) {
    console.error('Failed to save agent', err);
  } finally {
    loading.value = false;
  }
};

const deleteAgent = async (id) => {
  if (!confirm('Are you sure you want to delete this agent?')) return;
  try {
    await api.delete(`/sources/${id}`);
    await fetchAgents();
  } catch (err) {
    console.error('Failed to delete agent', err);
  }
};

onMounted(fetchAgents);
</script>

<style scoped>
.font-arabic {
  font-family: 'Noto Kufi Arabic', sans-serif !important;
}
</style>
