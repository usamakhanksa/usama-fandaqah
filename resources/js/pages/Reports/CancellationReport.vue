<template>
  <div class="p-6 bg-[#f8f9fa] min-h-full space-y-6">
    <!-- Header/Filter -->
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold text-[#2a273c]">Cancellation Report</h1>
        <p class="text-slate-500">Cancellation analysis with pattern breakdown</p>
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
      <!-- Stats Cards -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
          <div class="text-slate-400 text-xs font-bold uppercase">Cancellations</div>
          <div class="text-3xl font-bold text-red-500 mt-2">{{ stats.cancellation_count }}</div>
          <div class="text-xs text-slate-500 mt-1">of {{ stats.total_reservations }} bookings</div>
        </div>
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
          <div class="text-slate-400 text-xs font-bold uppercase">Cancellation Rate</div>
          <div class="text-3xl font-bold text-orange-500 mt-2">{{ stats.cancellation_rate }}%</div>
          <div class="text-xs text-slate-500 mt-1">industry avg: 15%</div>
        </div>
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
          <div class="text-slate-400 text-xs font-bold uppercase">Revenue Lost</div>
          <div class="text-3xl font-bold text-red-600 mt-2">{{ formatCurrency(stats.lost_revenue) }}</div>
          <div class="text-xs text-slate-500 mt-1">Net loss: {{ formatCurrency(stats.net_loss) }}</div>
        </div>
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
          <div class="text-slate-400 text-xs font-bold uppercase">Refunded</div>
          <div class="text-3xl font-bold text-blue-500 mt-2">{{ formatCurrency(stats.refunded_amount) }}</div>
          <div class="text-xs text-slate-500 mt-1">Partially recovered</div>
        </div>
      </div>

      <!-- By Source, Lead Time, Reason -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100">
          <h3 class="text-lg font-bold text-[#2a273c] mb-6 flex items-center gap-2">
            <Building2Icon class="w-5 h-5 text-blue-500" /> By Source
          </h3>
          <div class="h-64">
            <Bar :data="sourceChartData" :options="barOptions" />
          </div>
        </div>
        <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100">
          <h3 class="text-lg font-bold text-[#2a273c] mb-6 flex items-center gap-2">
            <ClockIcon class="w-5 h-5 text-purple-500" /> By Lead Time (Days Before Arrival)
          </h3>
          <div class="h-64">
            <Bar :data="leadTimeChartData" :options="barOptions" />
          </div>
        </div>
      </div>

      <!-- Cancellation Reasons -->
      <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100">
        <h3 class="text-lg font-bold text-[#2a273c] mb-6 flex items-center gap-2">
          <FileTextIcon class="w-5 h-5 text-orange-500" /> Cancellation Reasons
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="h-80">
            <Doughnut :data="reasonChartData" :options="doughnutOptions" />
          </div>
          <div class="overflow-auto">
            <table class="w-full">
              <thead class="bg-slate-50 text-slate-400 text-xs font-bold uppercase">
                <tr>
                  <th class="px-4 py-3 text-left">Reason</th>
                  <th class="px-4 py-3 text-center">Count</th>
                  <th class="px-4 py-4 text-right">Amount Lost</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-50">
                <tr v-for="reason in byReason" :key="reason.cancellation_reason || 'Unknown'">
                  <td class="px-4 py-3 text-sm text-[#2a273c]">{{ reason.cancellation_reason || 'Not Specified' }}</td>
                  <td class="px-4 py-3 text-sm text-center">{{ reason.count }}</td>
                  <td class="px-4 py-3 text-sm text-right text-red-600">{{ formatCurrency(reason.total_amount) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Seasonal Pattern -->
      <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100">
        <h3 class="text-lg font-bold text-[#2a273c] mb-6 flex items-center gap-2">
          <CalendarIcon class="w-5 h-5 text-green-500" /> Seasonal Cancellation Pattern
        </h3>
        <div class="h-80">
          <Line :data="seasonChartData" :options="trendChartOptions" />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import { Bar, Doughnut, Line } from 'vue-chartjs';
import {
  Chart as ChartJS,
  Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale,
  ArcElement, LineElement, PointElement
} from 'chart.js';
import { Building2 as Building2Icon, Clock as ClockIcon, FileText as FileTextIcon, Calendar as CalendarIcon } from 'lucide-vue-next';

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, ArcElement, LineElement, PointElement);

const props = defineProps(['startDate', 'endDate']);
const filters = ref({
  start_date: props.startDate,
  end_date: props.endDate
});
const loading = ref(true);
const stats = ref({});
const bySource = ref([]);
const byRoomType = ref([]);
const byDayOfWeek = ref([]);
const byLeadTime = ref([]);
const byReason = ref([]);
const bySeason = ref([]);

const fetchData = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/reports/cancellation/generate', { params: filters.value });
    stats.value = response.data.stats;
    bySource.value = response.data.by_source;
    byRoomType.value = response.data.by_room_type;
    byDayOfWeek.value = response.data.by_day_of_week;
    byLeadTime.value = response.data.by_lead_time;
    byReason.value = response.data.by_reason;
    bySeason.value = response.data.by_season;
  } catch (error) {
    console.error('Error fetching cancellation report:', error);
  } finally {
    loading.value = false;
  }
};

onMounted(fetchData);

const formatCurrency = (value) => {
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'SAR' }).format(value || 0);
};

const sourceChartData = computed(() => ({
  labels: bySource.value.map(s => s.name),
  datasets: [{
    label: 'Cancellations',
    data: bySource.value.map(s => s.cancellation_count),
    backgroundColor: '#3b82f6',
    borderRadius: 8
  }]
}));

const leadTimeChartData = computed(() => ({
  labels: byLeadTime.value.map(l => l.lead_time_bucket),
  datasets: [{
    label: 'Cancellations',
    data: byLeadTime.value.map(l => l.cancellation_count),
    backgroundColor: '#8b5cf6',
    borderRadius: 8
  }]
}));

const reasonChartData = computed(() => ({
  labels: byReason.value.map(r => r.cancellation_reason || 'Not Specified'),
  datasets: [{
    label: 'Cancellations by Reason',
    data: byReason.value.map(r => r.count),
    backgroundColor: ['#ef4444', '#f59e0b', '#10b981', '#3b82f6', '#8b5cf6'],
    borderWidth: 0
  }]
}));

const seasonChartData = computed(() => ({
  labels: bySeason.value.map(s => s.month),
  datasets: [{
    label: 'Cancellations',
    data: bySeason.value.map(s => s.cancellation_count),
    borderColor: '#10b981',
    backgroundColor: 'rgba(16, 185, 129, 0.1)',
    tension: 0.4,
    fill: true,
    pointRadius: 5,
    pointBackgroundColor: '#10b981'
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

const doughnutOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { position: 'right' }
  }
};

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
  window.location.href = `/reports/cancellation/export?start_date=${filters.value.start_date}&end_date=${filters.value.end_date}&format=csv`;
};
</script>

<style scoped>
.shadow-sm { box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05); }
</style>
