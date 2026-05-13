<template>
  <div class="p-6 bg-[#f8f9fa] min-h-full space-y-6">
    <!-- Header/Filter -->
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold text-[#2a273c]">Housekeeping Discrepancy Report</h1>
        <p class="text-slate-500">Status mismatches and cleaning performance</p>
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
          <div class="text-slate-400 text-xs font-bold uppercase">Status Mismatches</div>
          <div class="text-3xl font-bold text-red-500 mt-2">{{ summary.mismatched_rooms }}</div>
          <div class="text-xs text-slate-500 mt-1">FD vs HK status diff</div>
        </div>
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
          <div class="text-slate-400 text-xs font-bold uppercase">Not Cleaned After Checkout</div>
          <div class="text-3xl font-bold text-orange-500 mt-2">{{ summary.not_cleaned_after_checkout }}</div>
          <div class="text-xs text-slate-500 mt-1">rooms pending cleaning</div>
        </div>
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
          <div class="text-slate-400 text-xs font-bold uppercase">Maintenance Blocks</div>
          <div class="text-3xl font-bold text-blue-500 mt-2">{{ summary.maintenance_blocks }}</div>
          <div class="text-xs text-slate-500 mt-1">rooms out of order</div>
        </div>
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
          <div class="text-slate-400 text-xs font-bold uppercase">Avg Cleaning Time</div>
          <div class="text-3xl font-bold text-emerald-500 mt-2">{{ summary.avg_cleaning_time_minutes }}m</div>
          <div class="text-xs text-slate-500 mt-1">post checkout</div>
        </div>
      </div>

      <!-- Mismatched Rooms Table -->
      <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="p-6 border-b border-slate-50">
          <h3 class="font-bold text-[#2a273c]">Status Mismatches (Front Desk vs Housekeeping)</h3>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-slate-50 text-slate-400 text-xs font-bold uppercase">
              <tr>
                <th class="px-6 py-4 text-left">Room</th>
                <th class="px-6 py-4 text-center">FD Status</th>
                <th class="px-6 py-4 text-center">HK Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
              <tr v-for="room in mismatches" :key="room.id">
                <td class="px-6 py-4 text-sm font-medium text-[#2a273c]">{{ room.room_number }}</td>
                <td class="px-6 py-4 text-sm text-center">
                  <span class="px-2 py-1 text-xs font-bold rounded-full" :class="getStatusClass(room.front_desk_status)">
                    {{ room.front_desk_status }}
                  </span>
                </td>
                <td class="px-6 py-4 text-sm text-center">
                  <span class="px-2 py-1 text-xs font-bold rounded-full" :class="getStatusClass(room.housekeeping_status)">
                    {{ room.housekeeping_status }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Not Cleaned After Checkout -->
      <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="p-6 border-b border-slate-50">
          <h3 class="font-bold text-[#2a273c]">Rooms Not Cleaned After Checkout</h3>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-slate-50 text-slate-400 text-xs font-bold uppercase">
              <tr>
                <th class="px-6 py-4 text-left">Room</th>
                <th class="px-6 py-4 text-left">Checkout Time</th>
                <th class="px-6 py-4 text-left">Minutes Since Checkout</th>
                <th class="px-6 py-4 text-center">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
              <tr v-for="room in uncleaned" :key="room.room_number">
                <td class="px-6 py-4 text-sm font-medium text-[#2a273c]">{{ room.room_number }}</td>
                <td class="px-6 py-4 text-sm text-slate-500">{{ formatDateTime(room.checkout_time) }}</td>
                <td class="px-6 py-4 text-sm text-center font-bold text-red-600">{{ room.minutes_to_clean || 'N/A' }} min</td>
                <td class="px-6 py-4 text-sm text-center">
                  <span class="px-2 py-1 text-xs font-bold rounded-full bg-red-100 text-red-700">
                    Overdue
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Maintenance Blocks -->
      <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="p-6 border-b border-slate-50">
          <h3 class="font-bold text-[#2a273c]">Maintenance Issues Blocking Sales</h3>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-slate-50 text-slate-400 text-xs font-bold uppercase">
              <tr>
                <th class="px-6 py-4 text-left">Room</th>
                <th class="px-6 py-4 text-left">Issue</th>
                <th class="px-6 py-4 text-left">Expected Completion</th>
                <th class="px-6 py-4 text-left">Created By</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
              <tr v-for="item in maintenance" :key="item.id">
                <td class="px-6 py-4 text-sm font-medium text-[#2a273c]">{{ item.unit?.name || 'N/A' }}</td>
                <td class="px-6 py-4 text-sm text-slate-500">{{ item.note }}</td>
                <td class="px-6 py-4 text-sm text-slate-500">{{ item.expected_at ? formatDate(item.expected_at) : 'TBD' }}</td>
                <td class="px-6 py-4 text-sm text-slate-500">{{ item.creator?.name || 'System' }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Cleaning Time Trend -->
      <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100">
        <h3 class="text-lg font-bold text-[#2a273c] mb-6 flex items-center gap-2">
          <ActivityIcon class="w-5 h-5 text-blue-500" /> Daily Average Cleaning Time (minutes)
        </h3>
        <div class="h-80">
          <Line :data="trendChartData" :options="trendChartOptions" />
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
import { Activity as ActivityIcon } from 'lucide-vue-next';

ChartJS.register(Title, Tooltip, Legend, LineElement, CategoryScale, LinearScale, PointElement);

const props = defineProps(['startDate', 'endDate']);
const filters = ref({
  start_date: props.startDate,
  end_date: props.endDate
});
const loading = ref(true);
const summary = ref({});
const mismatches = ref([]);
const uncleaned = ref([]);
const maintenance = ref([]);
const cleaningTrend = ref([]);
const avgCleaningTime = ref(0);

const fetchData = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/reports/housekeeping-discrepancy/generate', { params: filters.value });
    summary.value = response.data.summary;
    mismatches.value = response.data.mismatches;
    uncleaned.value = response.data.uncleaned_rooms;
    maintenance.value = response.data.maintenance_blocks;
    cleaningTrend.value = response.data.cleaning_trend;
    avgCleaningTime.value = response.data.avg_cleaning_time;
  } catch (error) {
    console.error('Error fetching housekeeping discrepancy report:', error);
  } finally {
    loading.value = false;
  }
};

onMounted(fetchData);

const formatCurrency = (value) => {
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'SAR' }).format(value || 0);
};

const formatDate = (dateStr) => {
  return new Date(dateStr).toLocaleDateString();
};

const formatDateTime = (dateStr) => {
  return new Date(dateStr).toLocaleString();
};

const getStatusClass = (status) => {
  const classes = {
    'clean': 'bg-emerald-100 text-emerald-700',
    'dirty': 'bg-red-100 text-red-700',
    'occupied': 'bg-blue-100 text-blue-700',
    'vacant': 'bg-slate-100 text-slate-700',
    'cleaning': 'bg-yellow-100 text-yellow-700',
  };
  return classes[status?.toLowerCase()] || 'bg-gray-100 text-gray-700';
};

const trendChartData = computed(() => ({
  labels: cleaningTrend.value.map(t => t.date),
  datasets: [{
    label: 'Avg Cleaning Time (min)',
    data: cleaningTrend.value.map(t => t.avg_cleaning_minutes),
    borderColor: '#3b82f6',
    backgroundColor: 'rgba(59, 130, 246, 0.1)',
    tension: 0.4,
    fill: true,
    pointRadius: 4,
    pointBackgroundColor: '#3b82f6'
  }]
}));

const trendChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false }
  },
  scales: {
    y: { beginAtZero: true, grid: { display: true } },
    x: { grid: { display: false } }
  }
};

const exportCSV = () => {
  window.location.href = `/reports/housekeeping-discrepancy/export?start_date=${filters.value.start_date}&end_date=${filters.value.end_date}&format=csv`;
};
</script>

<style scoped>
.shadow-sm { box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05); }
</style>
