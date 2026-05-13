<template>
  <div class="p-6 space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-black text-slate-900 tracking-tight">Company Profiles</h1>
        <p class="text-slate-500 text-sm font-medium">Manage corporate accounts, tax details, and billing entities</p>
      </div>
      <button @click="openCreateModal" class="bg-slate-900 text-white px-6 py-2.5 rounded-2xl flex items-center gap-2 hover:bg-rose-600 transition-all font-bold text-sm shadow-lg shadow-slate-200">
        <Plus class="w-4 h-4" />
        Add Company
      </button>
    </div>

    <!-- Filter Component -->
    <AdvancedFilter 
      :can-export="true"
      @filter="onFilter"
      @export="onExport"
    >
      <template #extra="{ filters }">
        <select v-model="filters.status" class="bg-slate-50 border-none rounded-2xl px-4 py-3 text-sm font-bold text-slate-700 outline-none focus:ring-2 ring-rose-200">
          <option value="">All Statuses</option>
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
        </select>
      </template>
    </AdvancedFilter>

    <!-- Table -->
    <div class="bg-white rounded-[32px] border border-slate-100 shadow-sm overflow-hidden relative">
      <div v-if="loading" class="absolute inset-0 bg-white/50 backdrop-blur-sm z-10 flex items-center justify-center">
        <div class="w-8 h-8 border-4 border-rose-500 border-t-transparent rounded-full animate-spin"></div>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left">
          <thead>
            <tr class="bg-slate-50/50">
              <th @click="toggleSort('company_name')" class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest cursor-pointer hover:text-slate-900">
                Company Name <ArrowUpDown v-if="filters.sort_by === 'company_name'" class="w-3 h-3 inline ml-1" />
              </th>
              <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Contact Info</th>
              <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Tax Number</th>
              <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Guests</th>
              <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Status</th>
              <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <tr v-for="item in items" :key="item.id" :class="{'opacity-50 grayscale': item.deleted_at}" class="hover:bg-slate-50/50 transition-colors group">
              <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 bg-slate-100 rounded-xl flex items-center justify-center text-slate-400 font-bold uppercase">
                    {{ item.company_name?.charAt(0) }}
                  </div>
                  <div>
                    <div class="text-sm font-black text-slate-900">{{ item.company_name }}</div>
                    <div class="text-[10px] text-slate-400 font-medium">{{ item.city?.name }}, {{ item.country?.name || 'KSA' }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4">
                <div class="text-sm font-bold text-slate-700">{{ item.email }}</div>
                <div class="text-[10px] text-slate-400 font-bold tracking-tighter">{{ item.mobile_number }}</div>
              </td>
              <td class="px-6 py-4">
                <code class="text-xs bg-slate-50 px-2 py-1 rounded-lg text-slate-600 font-mono">{{ item.tax_number || 'N/A' }}</code>
              </td>
              <td class="px-6 py-4">
                <div class="flex items-center gap-1.5">
                  <Users class="w-3.5 h-3.5 text-slate-300" />
                  <span class="text-sm font-bold text-slate-700">{{ item.guests_count || 0 }}</span>
                </div>
              </td>
              <td class="px-6 py-4">
                <span v-if="!item.deleted_at" :class="item.status === 'active' ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-50 text-amber-600'" class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest">
                  {{ item.status || 'active' }}
                </span>
                <span v-else class="bg-rose-50 text-rose-600 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest">Deleted</span>
              </td>
              <td class="px-6 py-4 text-right">
                <div class="flex items-center justify-end gap-1">
                  <button v-if="!item.deleted_at" @click="openEditModal(item)" class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-all">
                    <Pencil class="w-4 h-4" />
                  </button>
                  <button v-if="!item.deleted_at" @click="confirmDelete(item)" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-all">
                    <Trash2 class="w-4 h-4" />
                  </button>
                  <button v-else @click="restoreItem(item.id)" class="p-2 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl transition-all">
                    <RefreshCcw class="w-4 h-4" />
                  </button>
                  <button @click="viewDetails(item)" class="p-2 text-slate-400 hover:text-slate-900 hover:bg-slate-100 rounded-xl transition-all">
                    <Eye class="w-4 h-4" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="pagination.total > 0" class="p-6 border-t border-slate-50 flex items-center justify-between">
        <span class="text-xs font-bold text-slate-400">Showing {{ pagination.from }}-{{ pagination.to }} of {{ pagination.total }}</span>
        <div class="flex gap-2">
          <button @click="changePage(pagination.current_page - 1)" :disabled="pagination.current_page === 1" class="p-2 border border-slate-100 rounded-xl hover:bg-slate-50 disabled:opacity-50">
            <ChevronLeft class="w-4 h-4" />
          </button>
          <button @click="changePage(pagination.current_page + 1)" :disabled="pagination.current_page === pagination.last_page" class="p-2 border border-slate-100 rounded-xl hover:bg-slate-50 disabled:opacity-50">
            <ChevronRight class="w-4 h-4" />
          </button>
        </div>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="showModal" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm animate-in fade-in duration-300">
      <div class="bg-white rounded-[40px] w-full max-w-2xl shadow-2xl overflow-hidden animate-in zoom-in-95 duration-300">
        <div class="p-8 border-b border-slate-50 flex items-center justify-between">
          <div>
            <h2 class="text-xl font-black text-slate-900">{{ isEdit ? 'Edit Company' : 'New Company Profile' }}</h2>
            <p class="text-xs text-slate-500 font-medium mt-1">Provide accurate corporate and billing information</p>
          </div>
          <button @click="showModal = false" class="p-2 text-slate-400 hover:bg-slate-50 rounded-full"><X class="w-5 h-5" /></button>
        </div>

        <form @submit.prevent="submitForm" class="p-8 space-y-6 max-h-[70vh] overflow-y-auto custom-scrollbar">
          <div class="grid grid-cols-2 gap-6">
            <div class="col-span-2">
              <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Company Name</label>
              <input v-model="form.company_name" type="text" required class="w-full bg-slate-50 border-none rounded-2xl px-4 py-3 text-sm focus:ring-2 ring-rose-300 outline-none transition-all font-bold" />
            </div>
            
            <div>
              <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Email Address</label>
              <input v-model="form.email" type="email" class="w-full bg-slate-50 border-none rounded-2xl px-4 py-3 text-sm focus:ring-2 ring-rose-300 outline-none transition-all" />
            </div>

            <div>
              <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Mobile Number</label>
              <input v-model="form.mobile_number" type="text" class="w-full bg-slate-50 border-none rounded-2xl px-4 py-3 text-sm focus:ring-2 ring-rose-300 outline-none transition-all" />
            </div>

            <div>
              <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Tax / VAT Number</label>
              <input v-model="form.tax_number" type="text" class="w-full bg-slate-50 border-none rounded-2xl px-4 py-3 text-sm focus:ring-2 ring-rose-300 outline-none transition-all font-mono" />
            </div>

            <div>
              <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Responsible Person</label>
              <input v-model="form.responsible_person_name" type="text" class="w-full bg-slate-50 border-none rounded-2xl px-4 py-3 text-sm focus:ring-2 ring-rose-300 outline-none transition-all" />
            </div>
          </div>

          <div>
            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Full Address</label>
            <textarea v-model="form.address" rows="3" class="w-full bg-slate-50 border-none rounded-2xl px-4 py-3 text-sm focus:ring-2 ring-rose-300 outline-none transition-all"></textarea>
          </div>

          <div class="flex gap-3 pt-4 border-t border-slate-50">
            <button type="button" @click="showModal = false" class="flex-1 px-6 py-4 border border-slate-100 text-slate-500 rounded-2xl font-bold hover:bg-slate-50 transition-all">Cancel</button>
            <button type="submit" :disabled="formLoading" class="flex-[2] bg-slate-900 text-white px-6 py-4 rounded-2xl font-bold hover:bg-rose-600 transition-all shadow-lg disabled:opacity-50 flex items-center justify-center gap-2">
              <span v-if="formLoading" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
              {{ isEdit ? 'Update Company' : 'Create Company' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- View Details Drawer -->
    <div v-if="showDrawer" class="fixed inset-0 z-[70] overflow-hidden pointer-events-none">
      <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm pointer-events-auto transition-opacity" @click="showDrawer = false"></div>
      <div class="absolute right-0 top-0 bottom-0 w-full max-w-lg bg-white shadow-2xl pointer-events-auto transform transition-transform duration-500 ease-out p-10 flex flex-col" :class="showDrawer ? 'translate-x-0' : 'translate-x-full'">
         <div class="flex items-center justify-between mb-10">
            <h2 class="text-2xl font-black text-slate-900 tracking-tight">Company Details</h2>
            <button @click="showDrawer = false" class="p-2 hover:bg-slate-50 rounded-full transition-all text-slate-400 hover:text-slate-900"><X class="w-6 h-6" /></button>
         </div>
         
         <div v-if="selectedItem" class="space-y-8 flex-1 overflow-y-auto custom-scrollbar pr-4">
            <div class="flex items-center gap-6">
               <div class="w-20 h-20 bg-rose-50 rounded-[32px] flex items-center justify-center text-rose-500 text-3xl font-black">{{ selectedItem.company_name?.charAt(0) }}</div>
               <div>
                  <h3 class="text-xl font-black text-slate-900">{{ selectedItem.company_name }}</h3>
                  <p class="text-sm text-slate-500 font-medium">{{ selectedItem.email }}</p>
               </div>
            </div>

            <div class="grid grid-cols-2 gap-8">
               <div>
                  <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Tax Number</p>
                  <p class="text-sm font-bold text-slate-900">{{ selectedItem.tax_number || 'N/A' }}</p>
               </div>
               <div>
                  <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Mobile</p>
                  <p class="text-sm font-bold text-slate-900">{{ selectedItem.mobile_number || 'N/A' }}</p>
               </div>
               <div>
                  <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Address</p>
                  <p class="text-sm font-bold text-slate-900 leading-relaxed">{{ selectedItem.address || 'N/A' }}</p>
               </div>
               <div>
                  <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Guests</p>
                  <div class="flex items-center gap-2 mt-1">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <p class="text-lg font-black text-slate-900">{{ selectedItem.guests_count }}</p>
                  </div>
               </div>
            </div>

            <!-- Activity Log Placeholder -->
            <div class="pt-8 border-t border-slate-50">
               <h4 class="text-xs font-black text-slate-900 uppercase tracking-widest mb-4">Recent Activity</h4>
               <div class="space-y-4">
                  <div class="flex gap-4">
                     <div class="w-8 h-8 rounded-full bg-slate-50 flex items-center justify-center text-slate-400"><History class="w-4 h-4" /></div>
                     <div>
                        <p class="text-xs font-bold text-slate-700">Profile Created</p>
                        <p class="text-[10px] text-slate-400 font-medium">May 03, 2026 by System</p>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { 
  Plus, Search, ArrowUpDown, ChevronLeft, ChevronRight, 
  Pencil, Trash2, Eye, X, Users, History, RefreshCcw 
} from 'lucide-vue-next';
import api from '../../services/api';
import AdvancedFilter from '../../components/common/AdvancedFilter.vue';

const items = ref([]);
const loading = ref(false);
const formLoading = ref(false);
const showModal = ref(false);
const showDrawer = ref(false);
const isEdit = ref(false);
const selectedItem = ref(null);

const filters = ref({
  search: '',
  status: '',
  sort_by: 'company_name',
  sort_order: 'asc',
  page: 1,
  per_page: 15
});

const pagination = ref({
  current_page: 1, last_page: 1, total: 0, from: 0, to: 0
});

const form = ref({
  id: null,
  company_name: '',
  email: '',
  mobile_number: '',
  tax_number: '',
  responsible_person_name: '',
  address: ''
});

const fetchData = async () => {
  loading.value = true;
  try {
    const res = await api.get('/company-profiles', { params: filters.value });
    items.value = res.data.data;
    pagination.value = {
      current_page: res.data.meta?.current_page || res.data.current_page,
      last_page: res.data.meta?.last_page || res.data.last_page,
      total: res.data.meta?.total || res.data.total,
      from: res.data.meta?.from || res.data.from,
      to: res.data.meta?.to || res.data.to
    };
  } catch (err) {
    console.error('Fetch failed', err);
  } finally {
    loading.value = false;
  }
};

const onFilter = (newFilters) => {
  filters.value = { ...filters.value, ...newFilters, page: 1 };
  fetchData();
};

const toggleSort = (field) => {
  if (filters.value.sort_by === field) {
    filters.value.sort_order = filters.value.sort_order === 'asc' ? 'desc' : 'asc';
  } else {
    filters.value.sort_by = field;
    filters.value.sort_order = 'asc';
  }
  fetchData();
};

const changePage = (page) => {
  filters.value.page = page;
  fetchData();
};

const openCreateModal = () => {
  isEdit.value = false;
  form.value = { id: null, company_name: '', email: '', mobile_number: '', tax_number: '', responsible_person_name: '', address: '' };
  showModal.value = true;
};

const openEditModal = (item) => {
  isEdit.value = true;
  form.value = { ...item };
  showModal.value = true;
};

const viewDetails = (item) => {
  selectedItem.value = item;
  showDrawer.value = true;
};

const submitForm = async () => {
  formLoading.value = true;
  try {
    if (isEdit.value) {
      await api.put(`/company-profiles/${form.value.id}`, form.value);
    } else {
      await api.post('/company-profiles', form.value);
    }
    await fetchData();
    showModal.value = false;
  } catch (err) {
    alert(err.response?.data?.message || 'Action failed');
  } finally {
    formLoading.value = false;
  }
};

const confirmDelete = async (item) => {
  if (confirm(`Are you sure you want to delete ${item.company_name}?`)) {
    try {
      await api.delete(`/company-profiles/${item.id}`);
      await fetchData();
    } catch (err) {
      alert('Delete failed');
    }
  }
};

const restoreItem = async (id) => {
  try {
    await api.post(`/company-profiles/${id}/restore`);
    await fetchData();
  } catch (err) {
    alert('Restore failed');
  }
};

const onExport = () => {
  // Implement CSV export logic
};

onMounted(fetchData);
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #f1f5f9;
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #e2e8f0;
}
</style>
