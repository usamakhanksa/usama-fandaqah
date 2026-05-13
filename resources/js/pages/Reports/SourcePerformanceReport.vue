<template>
  <div class="p-6 bg-[#f8f9fa] min-h-full space-y-6">
    <!-- Header/Filter -->
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold text-[#2a273c]">Source Performance Report</h1>
        <p class="text-slate-500">Revenue and conversion by booking source</p>
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
      <!-- Performance Table -->
      <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="p-6 border-b border-slate-50">
          <h3 class="font-bold text-[#2a273c]">Source Performance Overview</h3>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-slate-50 text-slate-400 text-xs font-bold uppercase">
              <tr>
                <th class="px-6 py-4 text-left">Source</th>
                <th class="px-6 py-4 text-center">Reservations</th>
                <th class="px-6 py-4 text-right">Total Revenue</th>
                <th class="px-6 py-4 text-right">ADR</th>
                <th class="px-6 py-4 text-center">Cancellation %</th>
                <th class="px-6 py-4 text-center">Comm. %</th>
                <th class="px-6 py-4 text-right">Comm. Amount</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
              <tr v-for="item in performance" :key="item.source_id">
                <td class="px-6 py-4 text-sm font-medium">
                  {{ item.source_name }}
                  <span v-if="item.is_travel_agent" class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full ml-2">OTA</span>
                </td>
                <td class="px-6 py-4 text-sm text-center">{{ item.total_reservations }}</td>
                <td class="px-6 py-4 text-sm text-right font-bold text-emerald-600">{{ formatCurrency(item.total_revenue) }}</td>
                <td class="px-6 py-4 text-sm text-right">{{ formatCurrency(item.adr) }}</td>
                <td class="px-6 py-4 text-sm text-center" :class="item.cancellation_rate > 20 ? 'text-red-600' : 'text-slate-600'">
                  {{ item.cancellation_rate }}%
                </td>
                <td class="px-6 py-4 text-sm text-center text-blue-600">{{ item.commission_rate }}%</td>
                <td class="px-6 py-4 text-sm text-right text-orange-600">{{ formatCurrency(item.commission_amount) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Conversion Rate -->
      <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100">
        <h3 class="text-lg font-bold text-[#2a273c] mb-6 flex items-center gap-2">
          <BarChart2Icon class="w-5 h-5 text-blue-500" /> Booking Conversion Rate
        </h3>
        <div class="h-96 overflow-auto">
          <table class="w-full">
            <thead class="bg-slate-50 text-slate-400 text-xs font-bold uppercase sticky top-0">
              <tr>
                <th class="px-6 py-4 text-left">Source</th>
                <th class="px-6 py-4 text-center">Total Bookings</th>
                <th class="px-6 py-4 text-center">Confirmed</th>
                <th class="px-6 py-4 text-center">Conversion %</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
              <tr v-for="item in conversion_rates" :key="item.source_name">
                <td class="px-6 py-4 text-sm font-medium">{{ item.source_name }}</td>
                <td class="px-6 py-4 text-sm text-center">{{ item.total_bookings }}</td>
                <td class="px-6 py-4 text-sm text-center text-emerald-600">{{ item.confirmed_bookings }}</td>
                <td class="px-6 py-4 text-sm text-center font-bold" :class="item.conversion_rate >= 80 ? 'text-emerald-600' : 'text-orange-600'">
                  {{ item.conversion_rate }}%
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Revenue Trend by Source -->
      <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100">
        <h3 class="text-lg font-bold text-[#2a273c] mb-6 flex items-center gap-2">
          <TrendingUpIcon class="w-5 h-5 text-green-500" /> Revenue Trend by Source (Monthly)
        </h3>
        <div class="h-96">
          <Bar :data="revenueTrendChartData" :options="multiBarOptions" />
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
import { BarChart2 as BarChart2Icon, TrendingUp as TrendingUpIcon } from 'lucide-vue-next';

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, LineElement, PointElement);

const filters = ref({
  start_date: '2025-05-01',
  end_date: '2025-05-31',
});
const loading = ref(true);
const performance = ref([]);
const conversion_rates = ref([]);
const revenue_trend = ref([]);

const fetchData = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/reports/commission/generate', { params: filters.value }); // Adjust route
    performance.value = response.data.performance;
    conversion_rates.value = response.data.conversion_rates;
    revenue_trend.value = response.data.revenue_trend;
  } catch (error) {
    console.error('Error fetching source performance report:', error);
  } finally {
    loading.value = false;
  }
};

onMounted(fetchData);

const formatCurrency = (value) => {
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'SAR' }).format(value || 0);
};

const revenueTrendChartData = computed(() => {
  const sources = [...new Set(revenue_trend.value.map(r => r.source_name))];
  const months = [...new Set(revenue_trend.value.map(r => r.month))];
  
  const datasets = sources.map((source, index) => {
    const colors = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899'];
    return {
      label: source,
      data: months.map(month => {
        const record = revenue_trend.value.find(r => r.source_name === source && r.month === month);
        return record ? record.revenue : 0;
      }),
      backgroundColor: colors[index % colors.length],
      borderRadius: 4
    };
  });

  return {
    labels: months,
    datasets: datasets
  };
});

const multiBarOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { position: 'top' }
  },
  scales: {
    y: { beginAtZero: true, ticks: { callback: v => v / 1000 + 'k' } },
    x: { grid: { display: false } }
  }
};

const exportCSV = () => {
  window.location.href = `/reports/source-performance/export?start_date=${filters.value.start_date}&end_date=${filters.value.end_date}&format=csv`;
};
</script>

<style scoped>
.shadow-sm { box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05); }
</style>
