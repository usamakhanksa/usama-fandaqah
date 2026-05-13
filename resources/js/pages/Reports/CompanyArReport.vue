<template>
  <div class="p-6 bg-[#f8f9fa] min-h-full space-y-6">
    <!-- Header/Filter -->
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold text-[#2a273c]">Company AR Report</h1>
        <p class="text-slate-500">Accounts receivable aging by company</p>
      </div>
      <div class="flex items-center gap-4 bg-white p-2 rounded-2xl shadow-sm border border-slate-100">
        <div class="flex items-center gap-2 px-3">
          <span class="text-xs font-bold text-slate-400 uppercase">As of Date</span>
          <input type="date" v-model="asOfDate" @change="fetchData" class="border-none p-0 text-sm font-bold text-[#2a273c] focus:ring-0" />
        </div>
        <button @click="exportCSV" class="px-4 py-1.5 bg-emerald-500 text-white text-sm font-bold rounded-xl hover:bg-emerald-600">Export</button>
      </div>
    </div>

    <div v-if="loading" class="flex justify-center py-20">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-emerald-500"></div>
    </div>

    <div v-else class="space-y-6">
      <!-- Aging Summary Cards -->
      <div class="grid grid-cols-2 md:grid-cols-6 gap-4">
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
          <div class="text-slate-400 text-xs font-bold uppercase">Total AR</div>
          <div class="text-3xl font-bold text-blue-600 mt-2">{{ formatCurrency(aging_summary.total) }}</div>
        </div>
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
          <div class="text-slate-400 text-xs font-bold uppercase">Current</div>
          <div class="text-3xl font-bold text-emerald-500 mt-2">{{ formatCurrency(aging_summary.current) }}</div>
          <div class="text-xs text-slate-500 mt-1">{{ getPercentage(aging_summary.current, aging_summary.total) }}%</div>
        </div>
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
          <div class="text-slate-400 text-xs font-bold uppercase">30 Days</div>
          <div class="text-3xl font-bold text-orange-500 mt-2">{{ formatCurrency(aging_summary.days_30) }}</div>
          <div class="text-xs text-slate-500 mt-1">{{ getPercentage(aging_summary.days_30, aging_summary.total) }}%</div>
        </div>
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
          <div class="text-slate-400 text-xs font-bold uppercase">60 Days</div>
          <div class="text-3xl font-bold text-yellow-500 mt-2">{{ formatCurrency(aging_summary.days_60) }}</div>
          <div class="text-xs text-slate-500 mt-1">{{ getPercentage(aging_summary.days_60, aging_summary.total) }}%</div>
        </div>
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
          <div class="text-slate-400 text-xs font-bold uppercase">90 Days</div>
          <div class="text-3xl font-bold text-orange-600 mt-2">{{ formatCurrency(aging_summary.days_90) }}</div>
          <div class="text-xs text-slate-500 mt-1">{{ getPercentage(aging_summary.days_90, aging_summary.total) }}%</div>
        </div>
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
          <div class="text-slate-400 text-xs font-bold uppercase">120+ Days</div>
          <div class="text-3xl font-bold text-red-600 mt-2">{{ formatCurrency(aging_summary.days_120_plus) }}</div>
          <div class="text-xs text-slate-500 mt-1">{{ getPercentage(aging_summary.days_120_plus, aging_summary.total) }}%</div>
        </div>
      </div>

      <!-- AR Turnover Ratio -->
      <div class="bg-gradient-to-r from-blue-600 to-blue-700 p-8 rounded-3xl shadow-sm text-white">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
          <div>
            <div class="text-blue-200 text-sm font-bold uppercase">AR Turnover Ratio</div>
            <div class="text-4xl font-bold mt-1">{{ turnover_ratio.turnover_ratio }}x</div>
          </div>
          <div>
            <div class="text-blue-200 text-sm font-bold uppercase">Days Sales Outstanding</div>
            <div class="text-4xl font-bold mt-1">{{ turnover_ratio.days_sales_outstanding }} days</div>
          </div>
          <div>
            <div class="text-blue-200 text-sm font-bold uppercase">Credit Sales (Period)</div>
            <div class="text-2xl font-bold mt-2">{{ formatCurrency(turnover_ratio.credit_sales) }}</div>
          </div>
          <div>
            <div class="text-blue-200 text-sm font-bold uppercase">Average AR</div>
            <div class="text-2xl font-bold mt-2">{{ formatCurrency(turnover_ratio.average_ar) }}</div>
          </div>
        </div>
      </div>

      <!-- Company AR Details -->
      <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="p-6 border-b border-slate-50">
          <h3 class="font-bold text-[#2a273c]">Outstanding Balances by Company</h3>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-slate-50 text-slate-400 text-xs font-bold uppercase">
              <tr>
                <th class="px-6 py-4 text-left">Company</th>
                <th class="px-6 py-4 text-right">Current</th>
                <th class="px-6 py-4 text-right">30 Days</th>
                <th class="px-6 py-4 text-right">60 Days</th>
                <th class="px-6 py-4 text-right">90 Days</th>
                <th class="px-6 py-4 text-right">120+ Days</th>
                <th class="px-6 py-4 text-right">Total</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
              <tr v-for="item in ar_summary" :key="item.company_id">
                <td class="px-6 py-4 text-sm font-medium">{{ item.company_name }}</td>
                <td class="px-6 py-4 text-sm text-right text-emerald-600">{{ formatCurrency(item.current) }}</td>
                <td class="px-6 py-4 text-sm text-right text-orange-600">{{ formatCurrency(item.days_30) }}</td>
                <td class="px-6 py-4 text-sm text-right text-yellow-600">{{ formatCurrency(item.days_60) }}</td>
                <td class="px-6 py-4 text-sm text-right text-orange-700">{{ formatCurrency(item.days_90) }}</td>
                <td class="px-6 py-4 text-sm text-right text-red-600 font-bold">{{ formatCurrency(item.days_120_plus) }}</td>
                <td class="px-6 py-4 text-sm text-right font-bold text-[#2a273c]">{{ formatCurrency(item.total_outstanding) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const asOfDate = ref('2025-05-31');
const loading = ref(true);
const ar_summary = ref([]);
const aging_summary = ref({});
const turnover_ratio = ref({});

const fetchData = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/reports/company-ar/generate', { params: { as_of_date: asOfDate.value } });
    ar_summary.value = response.data.ar_summary;
    aging_summary.value = response.data.aging_summary;
    turnover_ratio.value = response.data.turnover_ratio;
  } catch (error) {
    console.error('Error fetching company AR report:', error);
  } finally {
    loading.value = false;
  }
};

onMounted(fetchData);

const formatCurrency = (value) => {
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'SAR' }).format(value || 0);
};

const getPercentage = (value, total) => {
  if (!total) return 0;
  return ((value / total) * 100).toFixed(1);
};

const exportCSV = () => {
  window.location.href = `/reports/company-ar/export?as_of_date=${asOfDate.value}&format=csv`;
};
</script>

<style scoped>
.shadow-sm { box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05); }
</style>
