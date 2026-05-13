<template>
  <div class="p-6 bg-[#f8f9fa] min-h-full space-y-6">
    <!-- Header/Filter -->
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold text-[#2a273c]">Turnaway Report</h1>
        <p class="text-slate-500">Guests turned away due to unavailability</p>
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
          <div class="text-slate-400 text-xs font-bold uppercase">Total Turnaways</div>
          <div class="text-3xl font-bold text-red-500 mt-2">{{ summary.total_turnaways }}</div>
          <div class="text-xs text-slate-500 mt-1">lost opportunities</div>
        </div>
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
          <div class="text-slate-400 text-xs font-bold uppercase">Est. Revenue Loss</div>
          <div class="text-3xl font-bold text-orange-500 mt-2">{{ formatCurrency(summary.estimated_revenue_loss) }}</div>
          <div class="text-xs text-slate-500 mt-1">potential income lost</div>
        </div>
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
          <div class="text-slate-400 text-xs font-bold uppercase">Avg Room Rate</div>
          <div class="text-3xl font-bold text-blue-500 mt-2">{{ formatCurrency(summary.average_room_rate) }}</div>
          <div class="text-xs text-slate-500 mt-1">used for estimation</div>
        </div>
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
          <div class="text-slate-400 text-xs font-bold uppercase">Avg Daily Turnaways</div>
          <div class="text-3xl font-bold text-purple-500 mt-2">{{ Math.round(summary.total_turnaways / 30) }}</div>
          <div class="text-xs text-slate-500 mt-1">per day</div>
        </div>
      </div>

      <!-- By Reason & By Room Type -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100">
          <h3 class="text-lg font-bold text-[#2a273c] mb-6 flex items-center gap-2">
            <AlertTriangleIcon class="w-5 h-5 text-red-500" /> By Reason
          </h3>
          <div class="h-64 overflow-auto">
            <table class="w-full">
              <thead class="bg-slate-50 text-slate-400 text-xs font-bold uppercase sticky top-0">
                <tr>
                  <th class="px-4 py-3 text-left">Reason</th>
                  <th class="px-4 py-3 text-center">Count</th>
                  <th class="px-4 py-3 text-right">Est. Loss</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-50">
                <tr v-for="item in byReason" :key="item.reason">
                  <td class="px-4 py-3 text-sm">{{ item.reason }}</td>
                  <td class="px-4 py-3 text-sm text-center font-bold">{{ item.turnaway_count }}</td>
                  <td class="px-4 py-3 text-sm text-right text-red-600">{{ formatCurrency(item.estimated_loss) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100">
          <h3 class="text-lg font-bold text-[#2a273c] mb-6 flex items-center gap-2">
            <HomeIcon class="w-5 h-5 text-blue-500" /> By Room Type Requested
          </h3>
          <div class="h-64">
            <Bar :data="roomTypeChartData" :options="barOptions" />
          </div>
        </div>
      </div>

      <!-- Daily Trend -->
      <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100">
        <h3 class="text-lg font-bold text-[#2a273c] mb-6 flex items-center gap-2">
          <ActivityIcon class="w-5 h-5 text-purple-500" /> Turnaway Trend
        </h3>
        <div class="h-80">
          <Line :data="trendChartData" :options="trendChartOptions" />
        </div>
      </div>

      <!-- Details Table -->
      <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="p-6 border-b border-slate-50">
          <h3 class="font-bold text-[#2a273c]">Turnaway Details</h3>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-slate-50 text-slate-400 text-xs font-bold uppercase">
              <tr>
                <th class="px-6 py-4 text-left">Date</th>
                <th class="px-6 py-4 text-left">Guest</th>
                <th class="px-6 py-4 text-left">Phone</th>
                <th class="px-6 py-4 text-left">Room Type</th>
                <th class="px-6 py-4 text-left">Reason</th>
                <th class="px-6 py-4 text-right">Est. Value</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
              <tr v-for="t in summary.turnaways" :key="t.id">
                <td class="px-6 py-4 text-sm">{{ t.date }}</td>
                <td class="px-6 py-4 text-sm font-medium">{{ t.guest_name }}</td>
                <td class="px-6 py-4 text-sm">{{ t.guest_phone }}</td>
                <td class="px-6 py-4 text-sm">{{ t.room_type_requested }}</td>
                <td class="px-6 py-4 text-sm">{{ t.reason?.name || 'N/A' }}</td>
                <td class="px-6 py-4 text-sm text-right font-bold text-red-600">{{ formatCurrency(t.estimated_value || 0) }}</td>
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
import { AlertTriangle as AlertTriangleIcon, Home as HomeIcon, Activity as ActivityIcon } from 'lucide-vue-next';

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, LineElement, PointElement);

const filters = ref({
  start_date: '2025-05-01',
  end_date: '2025-05-31',
});
const loading = ref(true);
const summary = ref({});
const byReason = ref([]);
const byRoomType = ref([]);
const dailyTrend = ref([]);

const fetchData = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/reports/turnaway/generate', { params: filters.value });
    summary.value = response.data.summary;
    byReason.value = response.data.by_reason;
    byRoomType.value = response.data.by_room_type;
    dailyTrend.value = response.data.daily_trend;
  } catch (error) {
    console.error('Error fetching turnaway report:', error);
  } finally {
    loading.value = false;
  }
};

onMounted(fetchData);

const formatCurrency = (value) => {
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'SAR' }).format(value || 0);
};

const roomTypeChartData = computed(() => ({
  labels: byRoomType.value.map(r => r.room_type_requested),
  datasets: [{
    label: 'Turnaway Count',
    data: byRoomType.value.map(r => r.turnaway_count),
    backgroundColor: '#8b5cf6',
    borderRadius: 8
  }]
}));

const trendChartData = computed(() => ({
  labels: dailyTrend.value.map(d => d.date),
  datasets: [{
    label: 'Turnaways',
    data: dailyTrend.value.map(d => d.turnaway_count),
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

const exportCSV = () => {
  window.location.href = `/reports/turnaway/export?start_date=${filters.value.start_date}&end_date=${filters.value.end_date}&format=csv`;
};
</script>

<style scoped>
.shadow-sm { box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05); }
</style>
