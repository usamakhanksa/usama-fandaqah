<template>
  <div class="p-6 space-y-4">
    <div class="card p-4 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold">{{ title }}</h1>
        <p class="text-xs text-slate-500">Home > Reservations Management > {{ title }}</p>
      </div>
      <div class="flex gap-2" v-if="module==='fund-movement'">
        <button class="px-4 py-2 rounded-full text-xs" :class="view==='receipt'?'bg-black text-white border-black':'btn-outline'" @click="view='receipt';load()">Receipt</button>
        <button class="px-4 py-2 rounded-full text-xs" :class="view==='bills'?'bg-black text-white border-black':'btn-outline'" @click="view='bills';load()">Bills Of Exchange</button>
      </div>
    </div>

    <div class="card p-4 grid grid-cols-2 lg:grid-cols-6 gap-3" :class="{'lg:grid-cols-4': module==='fund-movement', 'lg:grid-cols-3':module==='bills'}">
      <div v-for="metric in data.metrics" :key="metric.label" class="border rounded-xl p-3">
        <p class="text-sm font-bold" :class="metric.color==='green'?'text-green-600':metric.color==='red'?'text-rose-500':'text-slate-900'">{{ format(metric.value) }} SAR</p>
        <p class="text-xs text-slate-500">{{ metric.label }}</p>
      </div>
    </div>

    <div class="card p-4">
      <div class="flex justify-between items-center mb-3">
        <p class="text-xs text-slate-500 uppercase">Sort: A-Z</p>
        <div class="flex gap-2">
          <button class="btn-outline">Export</button>
          <router-link v-if="['receipts','expenses'].includes(module)" :to="createRoute" class="btn-primary">Add {{ module==='receipts' ? 'Receipt' : 'Expense' }}</router-link>
        </div>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="text-left text-slate-500 border-b"><tr><th class="py-3">#</th><th v-for="h in headers" :key="h" class="py-3">{{ h }}</th><th class="py-3">Actions</th></tr></thead>
          <tbody>
            <tr v-for="row in rows" :key="row.id" class="border-b hover:bg-slate-50">
              <td class="py-3">{{ row.id }}</td>
              <td v-for="key in keys" :key="key" class="py-3">{{ row[key] ?? row.employee?.name ?? '-' }}</td>
              <td class="py-3"><span class="text-slate-400">•••</span></td>
            </tr>
          </tbody>
        </table>
      </div>
      <p class="text-xs text-slate-500 mt-3">Showing {{ rows.length }} items out of {{ data.rows.total || rows.length }} results found</p>
    </div>
    <FooterBar/>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import api from '../services/api';
import FooterBar from '../components/FooterBar.vue';

const route = useRoute();
const module = computed(() => route.path.replace('/financial/', '') || 'receipts');
const view = ref('receipt');
const data = reactive({ metrics: [], rows: { data: [] } });

const map = {
  receipts: { title: 'Receipts Management', headers: ['Receipt Number', 'Reason', 'Amount', 'Employee', 'Date', 'Payment Method', 'Status'], keys: ['receipt_number', 'reason', 'amount', 'employee_name', 'date', 'payment_method', 'status'] },
  expenses: { title: 'Expense Management', headers: ['Expense Number', 'Reason', 'Amount', 'Employee', 'Date', 'Payment Method', 'Status'], keys: ['expense_number', 'reason', 'amount', 'employee_name', 'date', 'payment_method', 'status'] },
  bills: { title: 'Bills management', headers: ['Bill Number', 'Creation Date', 'Reason', 'Amount', 'Employee', 'Collection Date', 'Status'], keys: ['bill_number', 'creation_date', 'reason', 'amount', 'employee_name', 'collection_date', 'status'] },
  'fund-movement': { title: 'Fund Movement Report', headers: ['Reference Number', 'Reason', 'Amount', 'Employee', 'Date', 'Payment Method', 'Status'], keys: ['reference_number', 'reason', 'amount', 'employee_name', 'date', 'payment_method', 'status'] },
  'credit-notes': { title: 'Credit Notes Management', headers: ['Note Number', 'Invoice Number', 'Booking Number', 'Amount', 'Employee', 'Creation Date', 'Visitor Name'], keys: ['note_number', 'invoice_number', 'booking_number', 'amount', 'employee_name', 'creation_date', 'visitor_name'] },
};

const title = computed(() => map[module.value]?.title ?? 'Financial Management');
const headers = computed(() => map[module.value]?.headers ?? []);
const keys = computed(() => map[module.value]?.keys ?? []);
const rows = computed(() => (data.rows.data || []).map((r) => ({ ...r, employee_name: r.employee?.name || '-' })));
const createRoute = computed(() => module.value === 'expenses' ? '/financial/expenses/create' : '/financial/receipts/create');

const format = (value) => Number(value || 0).toLocaleString();

const load = async () => {
  const res = await api.get(`/financial/${module.value}`, { params: module.value === 'fund-movement' ? { view: view.value } : {} });
  Object.assign(data, res.data);
};

watch(module, load);
onMounted(load);
</script>
