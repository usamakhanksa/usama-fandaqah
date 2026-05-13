<template>
  <div class="p-6 bg-[#f8f9fa] min-h-full space-y-6">
    <!-- Header/Filter -->
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold text-[#2a273c]">Paid-Outs Report</h1>
        <p class="text-slate-500">Paid-out expenses and cash disbursements</p>
      </div>
      <div class="flex items-center gap-4 bg-white p-2 rounded-2xl shadow-sm border border-slate-100">
        <div class="flex items-center gap-2 px-3 border-r border-slate-100">
          <span class="text-xs font-bold text-slate-400 uppercase">From</span>
          <input type="date" v-model="filters.start_date" @change="fetchData" class="border-none p-0 text-sm font-bold text-[#2a273c] focus:ring-0" />
        </div>
        <div class="flex items-center gap-2 px-3">
          <span class="text-xs font-bold text-slate-400 uppercase">To</span>
          <input type="date" v-model="filters.end_date" @change="fetchData" class="border-none p-0 text-sm font-bold text-[#2a273c] focus:ring-0" />
        </div>
        <button @click="exportCSV" class="px-4 py-1.5 bg-emerald-500 text-white text-sm font-bold rounded-xl hover:bg-emerald-600">Export</button>
      </div>
    </div>

    <div v-if="loading" class="flex justify-center py-20">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-emerald-500"></div>
    </div>

    <div v-else class="space-y-6">
      <!-- Summary Cards -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
          <div class="text-slate-400 text-xs font-bold uppercase">Total Paid-Outs</div>
          <div class="text-3xl font-bold text-red-500 mt-2">{{ formatCurrency(summary.total_paid_outs) }}</div>
          <div class="text-xs text-slate-500 mt-1">{{ summary.paid_out_count }} transactions</div>
        </div>
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
          <div class="text-slate-400 text-xs font-bold uppercase">Avg Paid-Out</div>
          <div class="text-3xl font-bold text-blue-500 mt-2">{{ formatCurrency(summary.average_paid_out) }}</div>
          <div class="text-xs text-slate-500 mt-1">per transaction</div>
        </div>
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
          <div class="text-slate-400 text-xs font-bold uppercase">Total Revenue (Period)</div>
          <div class="text-3xl font-bold text-emerald-500 mt-2">{{ formatCurrency(summary.total_revenue) }}</div>
          <div class="text-xs text-slate-500 mt-1">for comparison</div>
        </div>
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
          <div class="text-slate-400 text-xs font-bold uppercase">Paid-Out / Revenue Ratio</div>
          <div class="text-3xl font-bold text-orange-500 mt-2">{{ summary.paid_out_to_revenue_ratio }}%</div>
          <div class="text-xs text-slate-500 mt-1">expense ratio</div>
        </div>
      </div>

      <!-- By Category & By Cashier -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100">
          <h3 class="text-lg font-bold text-[#2a273c] mb-6 flex items-center gap-2">
            <FolderIcon class="w-5 h-5 text-blue-500" /> By Category
          </h3>
          <div class="h-64">
            <Bar :data="categoryChartData" :options="barOptions" />
          </div>
        </div>
        <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100">
          <h3 class="text-lg font-bold text-[#2a273c] mb-6 flex items-center gap-2">
            <UserIcon class="w-5 h-5 text-orange-500" /> By Cashier
          </h3>
          <div class="h-64 overflow-auto">
            <table class="w-full">
              <thead class="bg-slate-50 text-slate-400 text-xs font-bold uppercase sticky top-0">
                <tr>
                  <th class="px-4 py-3 text-left">Cashier</th>
                  <th class="px-4 py-3 text-right">Count</th>
                  <th class="px-4 py-3 text-right">Amount</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-50">
                <tr v-for="item in byCashier" :key="item.cashier_name">
                  <td class="px-4 py-3 text-sm">{{ item.cashier_name }}</td>
                  <td class="px-4 py-3 text-sm text-center">{{ item.count }}</td>
                  <td class="px-4 py-3 text-sm text-right text-red-600">{{ formatCurrency(item.total_amount) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Daily Trend -->
      <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100">
        <h3 class="text-lg font-bold text-[#2a273c] mb-6 flex items-center gap-2">
          <TrendingUpIcon class="w-5 h-5 text-green-500" /> Daily Paid-Outs Trend
        </h3>
        <div class="h-80">
          <Line :data="trendChartData" :options="trendChartOptions" />
        </div>
      </div>

      <!-- Details Table -->
      <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="p-6 border-b border-slate-50">
          <h3 class="font-bold text-[#2a273c]">Paid-Out Details</h3>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-slate-50 text-slate-400 text-xs font-bold uppercase">
              <tr>
                <th class="px-6 py-4 text-left">Date</th>
                <th class="px-6 py-4 text-left">Description</th>
                <th class="px-6 py-4 text-left">Category</th>
                <th class="px-6 py-4 text-left">Cashier</th>
                <th class="px-6 py-4 text-right">Amount</th>
                <th class="px-6 py-4 text-center">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
              <tr v-for="po in summary.paid_outs" :key="po.id">
                <td class="px-6 py-4 text-sm">{{ po.paid_out_date }}</td>
                <td class="px-6 py-4 text-sm">{{ po.description }}</td>
                <td class="px-6 py-4 text-sm">
                  <span class="px-2 py-1 text-xs rounded-full" :class="getCategoryClass(po.category)">
                    {{ po.category }}
                  </span>
                </td>
                <td class="px-6 py-4 text-sm">{{ po.creator?.name || 'N/A' }}</td>
                <td class="px-6 py-4 text-sm text-right font-bold text-red-600">{{ formatCurrency(po.amount) }}</td>
                <td class="px-6 py-4 text-sm text-center">
                  <span class="px-2 py-1 text-xs font-bold rounded-full" :class="getStatusClass(po.status)">
                    {{ po.status }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import { Bar, Line } from 'vue-chartjs';
import {
  Chart as ChartJS,
  Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale,
  LineElement, PointElement
} from 'chart.js';
import { Folder as FolderIcon, User as UserIcon, TrendingUp as TrendingUpIcon } from 'lucide-vue-next';

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, LineElement, PointElement);

const filters = ref({
  start_date: '2025-05-01',
  end_date: '2025-05-31',
});
const loading = ref(true);
const summary = ref({});
const byCategory = ref([]);
const byCashier = ref([]);
const dailyTrend = ref([]);

const fetchData = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/reports/paid-outs/generate', { params: filters.value });
    summary.value = response.data.summary;
    byCategory.value = response.data.by_category;
    byCashier.value = response.data.by_cashier;
    dailyTrend.value = response.data.daily_trend;
  } catch (error) {
    console.error('Error fetching paid-outs report:', error);
  } finally {
    loading.value = false;
  }
};

onMounted(fetchData);

const formatCurrency = (value) => {
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'SAR' }).format(value || 0);
};

const categoryChartData = computed(() => ({
  labels: byCategory.value.map(c => c.category),
  datasets: [{
    label: 'Amount',
    data: byCategory.value.map(c => c.total_amount),
    backgroundColor: '#3b82f6',
    borderRadius: 8
  }]
}));

const trendChartData = computed(() => ({
  labels: dailyTrend.value.map(d => d.date),
  datasets: [{
    label: 'Daily Paid-Outs',
    data: dailyTrend.value.map(d => d.total_amount),
    borderColor: '#ef4444',
    backgroundColor: 'rgba(239, 68, 68, 0.1)',
    tension: 0.4,
    fill: true,
    pointRadius: 4,
    pointBackgroundColor: '#ef4444'
  }]
}));

const barOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: { legend: { display: false } },
  scales: {
    y: { beginAtZero: true, grid: { display: true } },
    x: { grid: { display: false } }
  }
};

const trendChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: { legend: { display: false } },
  scales: {
    y: { beginAtZero: true, grid: { display: true } },
    x: { grid: { display: false } }
  }
};

const getCategoryClass = (category) => {
  const classes = {
    'Utilities': 'bg-blue-100 text-blue-700',
    'Supplies': 'bg-green-100 text-green-700',
    'Maintenance': 'bg-orange-100 text-orange-700',
    'Transportation': 'bg-purple-100 text-purple-700',
    'Food': 'bg-yellow-100 text-yellow-700',
  };
  return classes[category] || 'bg-gray-100 text-gray-700';
};

const getStatusClass = (status) => {
  const classes = {
    'pending': 'bg-yellow-100 text-yellow-700',
    'approved': 'bg-blue-100 text-blue-700',
    'rejected': 'bg-red-100 text-red-700',
  };
  return classes[status] || 'bg-gray-100 text-gray-700';
};

const exportCSV = () => {
  window.location.href = `/reports/paid-outs/export?start_date=${filters.value.start_date}&end_date=${filters.value.end_date}&format=csv`;
};
</script>

<style scoped>
.shadow-sm { box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05); }
</style>
