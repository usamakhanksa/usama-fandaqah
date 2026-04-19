<template>
<div class="p-6 space-y-6">
  <div class="card p-4 flex justify-between items-center">
    <h1 class="text-2xl font-bold">Reports</h1>
    <p class="text-sm text-slate-400">Home > Reports</p>
  </div>
  
  <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
    <button v-for="sc in reports" :key="sc.slug" @click="selected=sc" class="card p-6 flex flex-col items-center justify-center gap-3 hover:shadow-md transition-all active:scale-95 group">
      <div class="w-12 h-12 rounded-2xl bg-slate-50 flex items-center justify-center text-2xl group-hover:bg-rose-50 transition-colors">{{ sc.icon }}</div>
      <span class="font-semibold text-center text-sm leading-tight">{{ sc.title }}</span>
    </button>
  </div>

  <BaseModal v-if="selected" @close="selected=null" :title="selected.title" class="max-w-4xl">
    <div class="space-y-4">
      <div class="flex justify-between items-center">
        <div class="flex gap-2">
          <input type="date" class="input py-1" v-model="filters.from">
          <input type="date" class="input py-1" v-model="filters.to">
          <button @click="load" class="btn-primary py-1 px-4">Filter</button>
        </div>
        <button class="btn-outline py-1 px-4">Export CSV</button>
      </div>

      <div v-if="loading" class="text-center py-10">Loading report data...</div>
      <div v-else class="overflow-x-auto border rounded-xl">
        <table class="w-full text-sm text-left">
          <thead class="bg-slate-50 border-b">
            <tr>
              <th v-for="h in headers" :key="h" class="p-3 font-semibold">{{ h }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in data" :key="row.id" class="border-b hover:bg-slate-50">
              <td v-for="k in keys" :key="k" class="p-3">{{ row[k] }}</td>
            </tr>
            <tr v-if="!data.length">
              <td :colspan="headers.length" class="p-10 text-center text-slate-400">No data found for this period.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </BaseModal>
</div>
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import api from '../services/api';
import BaseModal from '../components/BaseModal.vue';

const selected = ref(null);
const loading = ref(false);
const data = ref([]);
const filters = ref({ from: '', to: '' });

const reports = [
  { title: 'Deposits', slug: 'deposits', icon: '📥' },
  { title: 'Withdraws', slug: 'withdraws', icon: '📤' },
  { title: 'Safe Movement Report', slug: 'safe-movement', icon: '🛡️' },
  { title: 'Customer Movement', slug: 'customer-movement', icon: '👥' },
  { title: 'Services Report', slug: 'services', icon: '🛎️' },
  { title: 'Monthly Report', slug: 'monthly', icon: '📅' },
  { title: 'Units Movement Report', slug: 'units-movement', icon: '🏢' },
  { title: 'Occupancy Ratio', slug: 'occupancy', icon: '📊' },
  { title: 'Cleaning Movement', slug: 'cleaning', icon: '🧹' },
  { title: 'Maintenance Movement', slug: 'maintenance', icon: '🛠️' },
  { title: 'Reservation Transfers', slug: 'transfers' , icon: '🔄' },
  { title: 'Revenues & Taxes', slug: 'revenues' , icon: '⚖️' },
  { title: 'Reservation Resources', slug: 'resources' , icon: '📅' },
  { title: 'Employee Contracts', slug: 'contracts' , icon: '📄' },
  { title: 'Invoices Report', slug: 'invoices' , icon: '🧾' },
  { title: 'Daily report', slug: 'daily' , icon: '☀️' },
];

const headers = computed(() => ['ID', 'Reference', 'Date', 'Amount', 'Status']);
const keys = computed(() => ['id', 'reference_number', 'date', 'amount', 'status']);

const load = async () => {
  if (!selected.value) return;
  loading.value = true;
  try {
    const res = await api.get(`/reports/${selected.value.slug}`, { params: filters.value });
    data.value = res.data.data || res.data;
  } catch (e) {
    data.value = [];
  }
  loading.value = false;
};

watch(selected, load);

</script>
