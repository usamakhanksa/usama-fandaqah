<template>
  <div class="p-6 bg-[#f8f9fa] min-h-full space-y-6">
    <!-- Header/Filter -->
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold text-[#2a273c]">Forecast Report</h1>
        <p class="text-slate-500">Occupancy forecast based on confirmed reservations</p>
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
      <!-- Period Summary Cards -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div v-for="period in periodSummary" :key="period.period_days" class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
          <div class="text-slate-400 text-xs font-bold uppercase">{{ period.period_days }} Days</div>
          <div class="text-2xl font-bold text-[#2a273c] mt-2">{{ period.average_occupancy }}%</div>
          <div class="text-xs text-slate-500 mt-1">Avg occupancy</div>
          <div class="flex justify-between mt-3 text-xs">
            <span class="text-blue-500">Peak: {{ period.peak_occupancy }}%</span>
            <span class="text-orange-500">Low: {{ period.lowest_occupancy }}%</span>
          </div>
        </div>
      </div>

      <!-- Forecast Trend Chart -->
      <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100">
        <h3 class="text-xl font-bold text-[#2a273c] mb-8 flex items-center gap-2">
          <TrendingUpIcon class="w-5 h-5 text-emerald-500" /> Occupancy Forecast vs Actual
        </h3>
        <div class="h-80">
          <Line :data="trendChartData" :options="trendChartOptions" />
        </div>
      </div>

      <!-- Detailed Table -->
      <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="p-6 border-b border-slate-50">
          <h3 class="font-bold text-[#2a273c]">Daily Forecast Details</h3>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-slate-50 text-slate-400 text-xs font-bold uppercase">
              <tr>
                <th class="px-6 py-4 text-left">Date</th>
                <th class="px-6 py-4 text-center">Total Rooms</th>
                <th class="px-6 py-4 text-center">Confirmed</th>
                <th class="px-6 py-4 text-center">Forecast %</th>
                <th class="px-6 py-4 text-center">Actual %</th>
                <th class="px-6 py-4 text-center">Variance</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
              <tr v-for="row in data.forecast" :key="row.date">
                <td class="px-6 py-4 text-sm font-medium text-[#2a273c]">{{ row.date }}</td>
                <td class="px-6 py-4 text-sm text-center text-slate-500">{{ row.total_rooms }}</td>
                <td class="px-6 py-4 text-sm text-center text-emerald-600 font-bold">{{ row.confirmed_reservations }}</td>
                <td class="px-6 py-4 text-sm text-center text-blue-600">{{ row.forecast_occupancy }}%</td>
                <td class="px-6 py-4 text-sm text-center text-emerald-600">{{ row.actual_occupancy }}%</td>
                <td class="px-6 py-4 text-sm text-center font-bold" :class="row.variance >= 0 ? 'text-blue-600' : 'text-red-500'">
                  {{ row.variance >= 0 ? '+' : '' }}{{ row.variance }}%
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
import { Line } from 'vue-chartjs';
import {
  Chart as ChartJS,
  Title, Tooltip, Legend, LineElement, CategoryScale, LinearScale, PointElement
} from 'chart.js';
import { TrendingUp as TrendingUpIcon } from 'lucide-vue-next';

ChartJS.register(Title, Tooltip, Legend, LineElement, CategoryScale, LinearScale, PointElement);

const props = defineProps(['initialPeriod', 'startDate', 'endDate']);
const filters = ref({
  start_date: props.startDate,
  end_date: props.endDate
});
const loading = ref(true);
const data = ref({
  forecast: [],
  period_summary: []
});

const fetchData = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/reports/forecast-history/generate', { params: filters.value });
    data.value = response.data;
  } catch (error) {
    console.error('Error fetching forecast:', error);
  } finally {
    loading.value = false;
  }
};

onMounted(fetchData);

const trendChartData = computed(() => ({
  labels: data.value.forecast.map(f => f.date),
  datasets: [
    {
      label: 'Forecast Occupancy %',
      data: data.value.forecast.map(f => f.forecast_occupancy),
      borderColor: '#3b82f6',
      backgroundColor: 'rgba(59, 130, 246, 0.1)',
      tension: 0.4,
      fill: true,
      pointRadius: 3,
    },
    {
      label: 'Actual Occupancy %',
      data: data.value.forecast.map(f => f.actual_occupancy),
      borderColor: '#10b981',
      backgroundColor: 'rgba(16, 185, 129, 0.1)',
      tension: 0.4,
      fill: true,
      pointRadius: 3,
    }
  ]
}));

const trendChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { position: 'top' },
    tooltip: { mode: 'index', intersect: false }
  },
  scales: {
    y: { min: 0, max: 100, ticks: { callback: v => v + '%' } },
    x: { grid: { display: false } }
  }
};

const exportCSV = () => {
  window.location.href = `/reports/forecast-history/export?start_date=${filters.value.start_date}&end_date=${filters.value.end_date}&format=csv`;
};
</script>

<style scoped>
.shadow-sm { box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05); }
</style>
