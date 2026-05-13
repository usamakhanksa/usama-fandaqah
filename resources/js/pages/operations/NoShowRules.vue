<template>
  <div class="no-show-rules bg-slate-50 min-h-screen pb-10 px-6">
    <div class="max-w-6xl mx-auto py-10">
      <div class="mb-8 flex justify-between items-center">
        <div>
          <h1 class="text-3xl font-bold text-slate-800">No-Show Charge Rules</h1>
          <p class="text-slate-500 mt-2">Configure automated penalties for non-arrival reservations.</p>
        </div>
        <button @click="openCreateModal" class="bg-indigo-600 text-white px-6 py-2 rounded-xl font-bold shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition-all flex items-center gap-2">
          <PlusIcon class="w-5 h-5" />
          Add New Rule
        </button>
      </div>

      <!-- Timeline / Calendar Visualization -->
      <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 mb-8 overflow-x-auto">
        <h3 class="text-lg font-bold text-slate-800 mb-6">Policy Timeline</h3>
        <div class="relative min-w-[800px] h-64 border-l border-b border-slate-100 mt-4">
          <!-- Timeline Header (Months) -->
          <div class="absolute -top-8 left-0 right-0 flex justify-between text-xs font-bold text-slate-400">
             <span v-for="month in timelineMonths" :key="month">{{ month }}</span>
          </div>

          <!-- Rule Tracks -->
          <div v-for="(rule, index) in rules" :key="rule.id" 
               class="absolute h-8 rounded-lg flex items-center px-3 text-xs font-bold text-white shadow-sm transition-all hover:scale-[1.02] cursor-pointer"
               :style="getRuleStyle(rule, index)"
               @click="editRule(rule)"
          >
            {{ rule.name }} ({{ rule.charge_type === 'fixed' ? '$' + rule.charge_amount : rule.charge_amount + '%' }})
          </div>
          
          <!-- Legend -->
          <div class="absolute -bottom-12 left-0 flex gap-6 text-xs font-bold uppercase tracking-wider">
            <div class="flex items-center gap-2">
              <div class="w-3 h-3 rounded-full bg-emerald-500"></div>
              <span class="text-slate-500">Zero Charge</span>
            </div>
            <div class="flex items-center gap-2">
              <div class="w-3 h-3 rounded-full bg-amber-500"></div>
              <span class="text-slate-500">Fixed Charge</span>
            </div>
            <div class="flex items-center gap-2">
              <div class="w-3 h-3 rounded-full bg-rose-500"></div>
              <span class="text-slate-500">Percentage Charge</span>
            </div>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Rules List -->
        <div class="lg:col-span-2 space-y-4">
          <div v-for="rule in rules" :key="rule.id" class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex justify-between items-center group hover:border-indigo-200 transition-all">
            <div class="flex items-center gap-4">
               <div class="w-12 h-12 rounded-xl flex items-center justify-center" :class="getTypeClass(rule)">
                 <component :is="rule.charge_type === 'fixed' ? 'DollarSignIcon' : 'PercentIcon'" class="w-6 h-6" />
               </div>
               <div>
                 <h4 class="font-bold text-slate-800">{{ rule.name }}</h4>
                 <p class="text-slate-400 text-sm">
                   {{ rule.start_date }} to {{ rule.end_date }} • Applies to {{ rule.applies_to }}
                 </p>
               </div>
            </div>
            <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-all">
               <button @click="editRule(rule)" class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg">
                 <Edit2Icon class="w-5 h-5" />
               </button>
               <button @click="confirmDelete(rule)" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg">
                 <Trash2Icon class="w-5 h-5" />
               </button>
            </div>
          </div>
        </div>

        <!-- Preview Column -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 h-fit sticky top-6">
          <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
            <EyeIcon class="w-5 h-5 text-indigo-600" />
            No-Show Preview
          </h3>
          <p class="text-xs text-slate-400 mb-6 uppercase font-bold">Projected charges for today's non-arrivals</p>
          
          <div v-if="previewLoading" class="space-y-4 animate-pulse">
            <div v-for="i in 3" :key="i" class="h-12 bg-slate-50 rounded-xl"></div>
          </div>
          <div v-else-if="previewData.length === 0" class="text-center py-8">
            <div class="w-12 h-12 bg-slate-50 text-slate-300 rounded-full flex items-center justify-center mx-auto mb-3">
              <UserXIcon class="w-6 h-6" />
            </div>
            <p class="text-slate-400 text-sm">No no-shows projected for today.</p>
          </div>
          <div v-else class="space-y-4">
             <div v-for="item in previewData" :key="item.reservation_id" class="p-4 bg-slate-50 rounded-xl border border-slate-100">
                <div class="flex justify-between items-start mb-1">
                  <p class="font-bold text-slate-800 text-sm">{{ item.customer_name }}</p>
                  <p class="text-indigo-600 font-bold text-sm">${{ item.projected_charge }}</p>
                </div>
                <p class="text-xs text-slate-400">Policy: {{ item.applicable_rule }}</p>
             </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Rule Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-6">
       <div class="bg-white w-full max-w-lg rounded-3xl shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-200">
          <div class="p-8 border-b border-slate-100 flex justify-between items-center">
            <h3 class="text-xl font-bold text-slate-800">{{ editingId ? 'Edit Rule' : 'Create New Rule' }}</h3>
            <button @click="showModal = false" class="text-slate-400 hover:text-slate-600">
              <XIcon class="w-6 h-6" />
            </button>
          </div>
          <div class="p-8 space-y-6">
             <div class="space-y-2">
               <label class="text-sm font-bold text-slate-500 uppercase">Rule Name</label>
               <input v-model="form.name" type="text" placeholder="e.g., Seasonal Penalty" class="w-full border-2 border-slate-100 rounded-xl px-4 py-3 focus:border-indigo-500 outline-none transition-all" />
             </div>
             <div class="grid grid-cols-2 gap-4">
               <div class="space-y-2">
                 <label class="text-sm font-bold text-slate-500 uppercase">Start Date</label>
                 <input v-model="form.start_date" type="date" class="w-full border-2 border-slate-100 rounded-xl px-4 py-3 focus:border-indigo-500 outline-none" />
               </div>
               <div class="space-y-2">
                 <label class="text-sm font-bold text-slate-500 uppercase">End Date</label>
                 <input v-model="form.end_date" type="date" class="w-full border-2 border-slate-100 rounded-xl px-4 py-3 focus:border-indigo-500 outline-none" />
               </div>
             </div>
             <div class="grid grid-cols-2 gap-4">
               <div class="space-y-2">
                 <label class="text-sm font-bold text-slate-500 uppercase">Charge Type</label>
                 <select v-model="form.charge_type" class="w-full border-2 border-slate-100 rounded-xl px-4 py-3 focus:border-indigo-500 outline-none">
                    <option value="fixed">Fixed Amount</option>
                    <option value="percentage">Percentage</option>
                 </select>
               </div>
               <div class="space-y-2">
                 <label class="text-sm font-bold text-slate-500 uppercase">Value</label>
                 <input v-model="form.charge_amount" type="number" class="w-full border-2 border-slate-100 rounded-xl px-4 py-3 focus:border-indigo-500 outline-none" />
               </div>
             </div>
             <div class="space-y-2">
                 <label class="text-sm font-bold text-slate-500 uppercase">Applies To</label>
                 <select v-model="form.applies_to" class="w-full border-2 border-slate-100 rounded-xl px-4 py-3 focus:border-indigo-500 outline-none">
                    <option value="all">All Reservation Types</option>
                    <option value="daily">Daily Only</option>
                    <option value="monthly">Monthly Only</option>
                 </select>
               </div>
          </div>
          <div class="p-8 bg-slate-50 flex gap-4">
             <button @click="showModal = false" class="flex-1 bg-white text-slate-600 border border-slate-200 py-4 rounded-2xl font-bold hover:bg-slate-50 transition-all">
               Cancel
             </button>
             <button @click="saveRule" :disabled="saving" class="flex-1 bg-indigo-600 text-white py-4 rounded-2xl font-bold shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition-all disabled:opacity-50">
               {{ saving ? 'Saving...' : (editingId ? 'Update Policy' : 'Create Policy') }}
             </button>
          </div>
       </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { 
  PlusIcon, Edit2Icon, Trash2Icon, DollarSignIcon, PercentIcon, 
  EyeIcon, UserXIcon, XIcon 
} from 'lucide-vue-next';
import api from '../../services/api';
import Swal from 'sweetalert2';

const rules = ref([]);
const previewData = ref([]);
const previewLoading = ref(false);
const showModal = ref(false);
const saving = ref(false);
const editingId = ref(null);

const form = ref({
  name: '',
  start_date: '',
  end_date: '',
  charge_type: 'fixed',
  charge_amount: 0,
  applies_to: 'all',
  is_active: true
});

const timelineMonths = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

const fetchRules = async () => {
  const { data } = await api.get('/no-show-rules');
  rules.value = data.data;
};

const fetchPreview = async () => {
  previewLoading.value = true;
  try {
    const { data } = await api.get('/no-show-rules/preview');
    previewData.value = data;
  } finally {
    previewLoading.value = false;
  }
};

const getTypeClass = (rule) => {
  if (rule.charge_amount == 0) return 'bg-emerald-100 text-emerald-600';
  if (rule.charge_type === 'fixed') return 'bg-amber-100 text-amber-600';
  return 'bg-rose-100 text-rose-600';
};

const getRuleStyle = (rule, index) => {
  const start = new Date(rule.start_date);
  const end = new Date(rule.end_date);
  const yearStart = new Date(start.getFullYear(), 0, 1);
  const yearEnd = new Date(start.getFullYear(), 11, 31);
  
  const totalDays = (yearEnd - yearStart) / (1000 * 60 * 60 * 24);
  const left = ((start - yearStart) / (1000 * 60 * 60 * 24) / totalDays) * 100;
  const width = ((end - start) / (1000 * 60 * 60 * 24) / totalDays) * 100;
  
  const bgColor = rule.charge_amount == 0 ? '#10b981' : (rule.charge_type === 'fixed' ? '#f59e0b' : '#f43f5e');
  
  return {
    left: `${left}%`,
    width: `${width}%`,
    top: `${index * 40 + 20}px`,
    backgroundColor: bgColor
  };
};

const openCreateModal = () => {
  editingId.value = null;
  form.value = {
    name: '',
    start_date: new Date().toISOString().split('T')[0],
    end_date: new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
    charge_type: 'fixed',
    charge_amount: 0,
    applies_to: 'all',
    is_active: true
  };
  showModal.value = true;
};

const editRule = (rule) => {
  editingId.value = rule.id;
  form.value = { ...rule };
  showModal.value = true;
};

const saveRule = async () => {
  saving.value = true;
  try {
    if (editingId.value) {
      await api.put(`/no-show-rules/${editingId.value}`, form.value);
    } else {
      await api.post('/no-show-rules', form.value);
    }
    showModal.value = false;
    await fetchRules();
    await fetchPreview();
    Swal.fire('Success', 'Rule saved successfully', 'success');
  } catch (e) {
    Swal.fire('Error', e.response?.data?.message || 'Failed to save rule', 'error');
  } finally {
    saving.value = false;
  }
};

const confirmDelete = async (rule) => {
  const result = await Swal.fire({
    title: 'Delete Rule?',
    text: `Are you sure you want to delete "${rule.name}"?`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#f43f5e'
  });

  if (result.isConfirmed) {
    await api.delete(`/no-show-rules/${rule.id}`);
    await fetchRules();
    await fetchPreview();
  }
};

onMounted(() => {
  fetchRules();
  fetchPreview();
});
</script>

<style scoped>
.no-show-rules {
  font-family: 'Inter', sans-serif;
}
</style>
