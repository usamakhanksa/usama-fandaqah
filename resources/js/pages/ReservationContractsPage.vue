<template>
  <div class="p-6 space-y-6 bg-[#f8f9fa] min-h-full flex flex-col">
    <!-- Header & Quick Filters -->
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-50 flex flex-col md:flex-row md:items-center justify-between gap-6">
      <div>
        <h1 class="text-2xl font-bold text-[#2a273c]">Digital Contracts</h1>
        <p class="text-xs font-black text-[#e95a54] uppercase tracking-widest mt-1">Generated Contracts for Reservations</p>
      </div>
      
      <div class="flex flex-wrap items-center gap-3">
        <select 
          v-model="filters.status" 
          class="bg-slate-50 border-none rounded-xl py-3 px-4 text-xs font-bold text-slate-600 focus:ring-2 focus:ring-[#e95a54]"
        >
          <option value="">All Status</option>
          <option value="draft">Draft</option>
          <option value="pending">Pending</option>
          <option value="signed">Signed</option>
        </select>
        <input 
          type="date" 
          v-model="filters.date" 
          class="bg-slate-50 border-none rounded-xl py-3 px-4 text-xs font-bold text-slate-600 focus:ring-2 focus:ring-[#e95a54]"
          placeholder="Filter by Date"
        >
        <div class="relative">
          <input 
            type="text" 
            v-model="filters.search" 
            class="bg-slate-50 border-none rounded-xl py-3 px-4 text-xs font-bold text-slate-600 focus:ring-2 focus:ring-[#e95a54] pl-10"
            placeholder="Search Contract / Guest..."
          >
          <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
        </div>
        <button 
          @click="showGenerateModal = true"
          class="bg-[#e95a54] text-white px-4 py-3 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-opacity-90 transition-all flex items-center gap-2"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
          Generate Contract
        </button>
      </div>
    </div>

    <!-- Contracts Table -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-50 overflow-hidden flex-1 flex flex-col">
      <div class="overflow-x-auto flex-1">
        <table class="w-full border-collapse">
          <thead>
            <tr class="bg-slate-50/50 border-b border-slate-50">
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Reservation #</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Guest</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Contract #</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Status</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Generated At</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Signed At</span></th>
              <th class="p-4 text-end"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Actions</span></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <tr v-for="contract in contracts" :key="contract.id" class="hover:bg-slate-50/30 transition-colors group">
              <td class="p-4">
                <span class="text-sm font-bold text-[#e95a54]">#{{ contract.reservation?.code || contract.reservation_id }}</span>
              </td>
              <td class="p-4">
                <div class="flex flex-col">
                  <span class="text-sm font-bold text-[#2a273c]">{{ contract.reservation?.customer?.name || 'N/A' }}</span>
                  <span class="text-[10px] text-slate-400 font-medium">Guest ID: {{ contract.reservation?.customer_id || 'N/A' }}</span>
                </div>
              </td>
              <td class="p-4">
                <span class="text-sm font-bold text-[#2a273c]">{{ contract.contract_number || contract.uuid?.substring(0, 8) }}</span>
              </td>
              <td class="p-4">
                <span :class="statusClass(contract.status)" class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest">
                  {{ contract.status }}
                </span>
              </td>
              <td class="p-4">
                <div class="flex flex-col">
                  <span class="text-sm font-bold text-[#2a273c]">{{ formatDate(contract.generated_at) }}</span>
                  <span class="text-[10px] text-slate-400 font-medium">{{ formatTime(contract.generated_at) }}</span>
                </div>
              </td>
              <td class="p-4">
                <div class="flex flex-col" v-if="contract.signed_at">
                  <span class="text-sm font-bold text-[#2a273c]">{{ formatDate(contract.signed_at) }}</span>
                  <span class="text-[10px] text-slate-400 font-medium">{{ formatTime(contract.signed_at) }}</span>
                </div>
                <span v-else class="text-[10px] text-slate-400 font-medium">-</span>
              </td>
              <td class="p-4 text-end">
                <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                  <button @click="viewContract(contract)" class="bg-slate-100 text-[#2a273c] px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest hover:bg-slate-200 transition-colors">
                    View
                  </button>
                  <button @click="downloadContract(contract)" class="bg-[#2a273c] text-white px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest hover:bg-opacity-90 transition-all">
                    Download
                  </button>
                  <button v-if="contract.status !== 'signed'" @click="signContract(contract)" class="bg-emerald-500 text-white px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest hover:bg-opacity-90 transition-all">
                    Sign
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="contracts.length === 0">
              <td colspan="7" class="p-20 text-center">
                 <div class="flex flex-col items-center">
                    <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mb-4">
                       <svg class="w-10 h-10 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-[#2a273c]">No Contracts Found</h3>
                    <p class="text-xs text-slate-400 font-medium">There are no digital contracts matching your filters.</p>
                 </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="p-6 bg-slate-50/50 border-t border-slate-50 flex items-center justify-between">
         <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Showing {{ contracts.length }} contracts</span>
         <div class="flex gap-2">
            <button v-if="pagination.prev" @click="changePage(pagination.current - 1)" class="p-2 hover:bg-white rounded-xl transition-colors border border-transparent hover:border-slate-100"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg></button>
            <button v-if="pagination.next" @click="changePage(pagination.current + 1)" class="p-2 hover:bg-white rounded-xl transition-colors border border-transparent hover:border-slate-100"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg></button>
         </div>
      </div>
    </div>

    <!-- Generate Contract Modal -->
    <div v-if="showGenerateModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
      <div class="bg-white rounded-3xl p-8 w-full max-w-md">
        <h3 class="text-xl font-bold text-[#2a273c] mb-4">Generate New Contract</h3>
        <div class="space-y-4">
          <div>
            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Reservation ID</label>
            <input 
              v-model="generateForm.reservation_id" 
              type="number" 
              class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-bold text-[#2a273c] focus:ring-2 focus:ring-[#e95a54]"
              placeholder="Enter Reservation ID"
            >
          </div>
          <div>
            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Status</label>
            <select 
              v-model="generateForm.status" 
              class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-bold text-[#2a273c] focus:ring-2 focus:ring-[#e95a54]"
            >
              <option value="draft">Draft</option>
              <option value="pending">Pending</option>
            </select>
          </div>
        </div>
        <div class="flex gap-3 mt-6">
          <button @click="showGenerateModal = false" class="flex-1 bg-slate-100 text-[#2a273c] py-3 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-slate-200 transition-colors">
            Cancel
          </button>
          <button @click="generateContract" :disabled="generating" class="flex-1 bg-[#e95a54] text-white py-3 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-opacity-90 transition-all disabled:opacity-50">
            {{ generating ? 'Generating...' : 'Generate' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import api from '../services/api';
import dayjs from 'dayjs';
import Swal from 'sweetalert2';

const router = useRouter();
const contracts = ref([]);
const filters = reactive({ 
  date: '', 
  status: '',
  search: '',
  per_page: 15,
  page: 1
});
const pagination = reactive({ current: 1, next: false, prev: false });
const showGenerateModal = ref(false);
const generating = ref(false);
const generateForm = reactive({
  reservation_id: '',
  status: 'draft'
});

const statusClass = (status) => {
  const classes = {
    draft: 'bg-slate-100 text-slate-600',
    pending: 'bg-amber-100 text-amber-600',
    signed: 'bg-emerald-100 text-emerald-600',
    rejected: 'bg-red-100 text-red-600'
  };
  return classes[status] || 'bg-slate-100 text-slate-600';
};

const formatDate = (date) => date ? dayjs(date).format('DD MMM YYYY') : '-';
const formatTime = (date) => date ? dayjs(date).format('hh:mm A') : '-';

const load = async () => {
  try {
    const { data } = await api.get('/reservations/contracts', { params: filters });
    contracts.value = data.data || [];
    pagination.next = !!data.next_page_url;
    pagination.prev = !!data.prev_page_url;
    pagination.current = data.current_page || 1;
  } catch (err) {
    console.error('Failed to load contracts', err);
    Swal.fire({
      title: 'Error',
      text: 'Failed to load contracts',
      icon: 'error',
      confirmButtonColor: '#2a273c'
    });
  }
};

const viewContract = (contract) => {
  Swal.fire({
    title: 'Contract Details',
    html: `
      <div class="text-start space-y-2 text-sm">
        <p><strong>Contract #:</strong> ${contract.contract_number || contract.uuid?.substring(0, 8)}</p>
        <p><strong>Reservation:</strong> #${contract.reservation?.code || contract.reservation_id}</p>
        <p><strong>Guest:</strong> ${contract.reservation?.customer?.name || 'N/A'}</p>
        <p><strong>Status:</strong> ${contract.status.toUpperCase()}</p>
        <p><strong>Generated:</strong> ${contract.generated_at ? dayjs(contract.generated_at).format('YYYY-MM-DD HH:mm:ss') : 'N/A'}</p>
        <p><strong>Signed:</strong> ${contract.signed_at ? dayjs(contract.signed_at).format('YYYY-MM-DD HH:mm:ss') : 'Not signed'}</p>
        <p><strong>Version:</strong> ${contract.version}</p>
      </div>
    `,
    icon: 'info',
    confirmButtonColor: '#2a273c'
  });
};

const downloadContract = async (contract) => {
  try {
    const { data } = await api.get(`/reservations/contracts/${contract.id}/download`);
    if (data.data?.download_url) {
      window.open(data.data.download_url, '_blank');
    } else {
      Swal.fire({
        title: 'Error',
        text: 'Download URL not available',
        icon: 'error',
        confirmButtonColor: '#2a273c'
      });
    }
  } catch (err) {
    console.error('Failed to download contract', err);
    Swal.fire({
      title: 'Error',
      text: 'Failed to get download URL',
      icon: 'error',
      confirmButtonColor: '#2a273c'
    });
  }
};

const signContract = async (contract) => {
  try {
    const result = await Swal.fire({
      title: 'Sign Contract?',
      text: `Are you sure you want to mark contract ${contract.contract_number || contract.uuid?.substring(0, 8)} as signed?`,
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#10b981',
      cancelButtonColor: '#64748b',
      confirmButtonText: 'Yes, Sign It'
    });

    if (result.isConfirmed) {
      await api.post(`/reservations/contracts/${contract.id}/sign`);
      Swal.fire({
        title: 'Signed!',
        text: 'Contract has been marked as signed.',
        icon: 'success',
        confirmButtonColor: '#2a273c'
      });
      load();
    }
  } catch (err) {
    console.error('Failed to sign contract', err);
    Swal.fire({
      title: 'Error',
      text: 'Failed to sign contract',
      icon: 'error',
      confirmButtonColor: '#2a273c'
    });
  }
};

const generateContract = async () => {
  if (!generateForm.reservation_id) {
    Swal.fire({
      title: 'Error',
      text: 'Please enter a reservation ID',
      icon: 'error',
      confirmButtonColor: '#2a273c'
    });
    return;
  }

  generating.value = true;
  try {
    await api.post('/reservations/contracts', generateForm);
    Swal.fire({
      title: 'Success!',
      text: 'Contract generated successfully',
      icon: 'success',
      confirmButtonColor: '#2a273c'
    });
    showGenerateModal.value = false;
    generateForm.reservation_id = '';
    generateForm.status = 'draft';
    load();
  } catch (err) {
    console.error('Failed to generate contract', err);
    Swal.fire({
      title: 'Error',
      text: err.response?.data?.message || 'Failed to generate contract',
      icon: 'error',
      confirmButtonColor: '#2a273c'
    });
  } finally {
    generating.value = false;
  }
};

const changePage = (page) => {
  filters.page = page;
  load();
};

watch(() => [filters.date, filters.status, filters.search], () => {
  filters.page = 1;
  load();
});

onMounted(() => {
  load();
});
</script>
