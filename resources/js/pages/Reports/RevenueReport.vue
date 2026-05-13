<template>
  <div class="p-6 bg-[#f8f9fa] min-h-full space-y-6">
    <!-- Header/Filter -->
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold text-[#2a273c]">Revenue Report</h1>
        <p class="text-slate-500">Financial performance and KPIs</p>
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
      </div>
    </div>

    <div v-if="loading" class="flex justify-center py-20">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-emerald-500"></div>
    </div>

    <div v-else class="space-y-6">
      <!-- Summary KPI Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <KPICard title="Total Revenue" :value="data.summary.total_revenue" unit="SR" color="text-emerald-600" />
        <KPICard title="ADR" :value="data.summary.adr" unit="SR" color="text-[#2a273c]" />
        <KPICard title="RevPAR" :value="data.summary.revpar" unit="SR" color="text-[#2a273c]" />
        <KPICard title="GOPPAR" :value="data.summary.goppar" unit="SR" color="text-[#2a273c]" />
      </div>

      <!-- Trend Chart -->
      <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100">
        <h3 class="text-xl font-bold text-[#2a273c] mb-8 flex items-center gap-2">
          <TrendingUpIcon class="w-5 h-5 text-emerald-500" /> Daily Revenue Trend (SR)
        </h3>
        <div class="h-80">
          <Line :data="trendChartData" :options="trendChartOptions" />
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Revenue by Source (Pie Chart) -->
        <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100">
          <h3 class="text-lg font-bold text-[#2a273c] mb-6 flex items-center gap-2">
            <PieChartIcon class="w-5 h-5 text-blue-500" /> Revenue by Source
          </h3>
          <div class="h-64 flex justify-center">
            <Pie :data="sourceChartData" :options="pieOptions" />
          </div>
        </div>

        <!-- Revenue by Segment (Bar Chart) -->
        <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100">
          <h3 class="text-lg font-bold text-[#2a273c] mb-6 flex items-center gap-2">
            <BarChartIcon class="w-5 h-5 text-orange-500" /> Revenue by Segment
          </h3>
          <div class="h-64">
            <Bar :data="segmentChartData" :options="barOptions" />
          </div>
        </div>
      </div>

      <!-- Detailed Revenue Table -->
      <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="p-6 border-b border-slate-50">
          <h3 class="font-bold text-[#2a273c]">Daily Revenue Breakdown</h3>
        </div>
        <table class="w-full text-left">
          <thead class="bg-slate-50 text-slate-400 text-xs font-bold uppercase">
            <tr>
              <th class="px-6 py-4">Date</th>
              <th class="px-6 py-4 text-right">Revenue (SR)</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <tr v-for="item in data.trend" :key="item.date">
              <td class="px-6 py-4 text-sm font-medium text-[#2a273c]">{{ item.date }}</td>
              <td class="px-6 py-4 text-sm text-right font-bold text-emerald-600">{{ item.revenue.toLocaleString() }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import { 
  Line, Pie, Bar 
} from 'vue-chartjs';
import { 
  Chart as ChartJS, 
  Title, Tooltip, Legend, LineElement, CategoryScale, LinearScale, PointElement, ArcElement, BarElement 
} from 'chart.js';
import { 
  TrendingUp as TrendingUpIcon,
  PieChart as PieChartIcon,
  BarChart3 as BarChartIcon
} from 'lucide-vue-next';

ChartJS.register(Title, Tooltip, Legend, LineElement, CategoryScale, LinearScale, PointElement, ArcElement, BarElement);

const props = defineProps(['startDate', 'endDate']);
const filters = ref({
  start_date: props.startDate,
  end_date: props.endDate
});
const loading = ref(true);
const data = ref({
  summary: {},
  by_source: [],
  by_segment: [],
  trend: []
});

const fetchData = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/reports/revenue/generate', { params: filters.value });
    data.value = response.data;
  } catch (error) {
    console.error('Error fetching revenue report:', error);
  } finally {
    loading.value = false;
  }
};

onMounted(fetchData);

// Chart Configurations
const trendChartData = computed(() => ({
  labels: data.value.trend.map(t => t.date),
  datasets: [{
    label: 'Revenue (SR)',
    data: data.value.trend.map(t => t.revenue),
    borderColor: '#10b981',
    backgroundColor: 'rgba(16, 185, 129, 0.1)',
    tension: 0.4,
    fill: true,
    pointRadius: 4,
    pointBackgroundColor: '#10b981'
  }]
}));

const trendChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false },
    tooltip: { mode: 'index', intersect: false }
  },
  scales: {
    y: { beginAtZero: true, ticks: { callback: v => v.toLocaleString() + ' SR' } },
    x: { grid: { display: false } }
  }
};

const sourceChartData = computed(() => ({
  labels: data.value.by_source.map(s => s.name),
  datasets: [{
    data: data.value.by_source.map(s => s.total),
    backgroundColor: ['#3b82f6', '#10b981', '#f97316', '#8b5cf6', '#ec4899']
  }]
}));

const segmentChartData = computed(() => ({
  labels: data.value.by_segment.map(s => s.segment || 'Other'),
  datasets: [{
    label: 'Revenue (SR)',
    data: data.value.by_segment.map(s => s.total),
    backgroundColor: '#f59e0b',
    borderRadius: 8
  }]
}));

const pieOptions = { responsive: true, maintainAspectRatio: false };
const barOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: { legend: { display: false } },
  scales: {
    y: { beginAtZero: true },
    x: { grid: { display: false } }
  }
};

// Internal Components
const KPICard = ({ title, value, unit, color }) => {
  return (
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
      <p class="text-slate-500 text-sm font-medium">{title}</p>
      <h3 class={`text-3xl font-bold mt-1 ${color}`}>
        {value?.toLocaleString()} <span class="text-sm font-normal text-slate-400">{unit}</span>
      </h3>
    </div>
  );
};
</script>

<style scoped>
.shadow-sm { box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05); }
</style>
