<template>
  <div class="p-6 bg-[#f8f9fa] min-h-full space-y-6">
    <!-- Header/Filter -->
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold text-[#2a273c]">No-Show Report</h1>
        <p class="text-slate-500">No-show analysis with revenue impact</p>
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
          <div class="text-slate-400 text-xs font-bold uppercase">No-Show Count</div>
          <div class="text-3xl font-bold text-red-500 mt-2">{{ stats.no_show_count }}</div>
          <div class="text-xs text-slate-500 mt-1">of {{ stats.total_reservations }} reservations</div>
        </div>
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
          <div class="text-slate-400 text-xs font-bold uppercase">No-Show Rate</div>
          <div class="text-3xl font-bold text-orange-500 mt-2">{{ stats.no_show_rate }}%</div>
          <div class="text-xs text-slate-500 mt-1">vs total arrivals</div>
        </div>
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
          <div class="text-slate-400 text-xs font-bold uppercase">Potential Revenue Lost</div>
          <div class="text-3xl font-bold text-red-600 mt-2">{{ formatCurrency(stats.lost_revenue) }}</div>
          <div class="text-xs text-slate-500 mt-1">Collected: {{ formatCurrency(stats.collected_charges) }}</div>
        </div>
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
          <div class="text-slate-400 text-xs font-bold uppercase">Charge Applied</div>
          <div class="text-3xl font-bold text-emerald-500 mt-2">{{ stats.charge_percentage }}%</div>
          <div class="text-xs text-slate-500 mt-1">of potential revenue</div>
        </div>
      </div>

      <!-- By Source & Room Type -->
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
            <HomeIcon class="w-5 h-5 text-orange-500" /> By Room Type
          </h3>
          <div class="h-64">
            <Bar :data="roomTypeChartData" :options="barOptions" />
          </div>
        </div>
      </div>

      <!-- By Day of Week -->
      <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100">
        <h3 class="text-lg font-bold text-[#2a273c] mb-6 flex items-center gap-2">
          <CalendarIcon class="w-5 h-5 text-purple-500" /> No-Shows by Day of Week
        </h3>
        <div class="h-64">
          <Bar :data="dayOfWeekChartData" :options="barOptions" />
        </div>
      </div>

      <!-- Details Table -->
      <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="p-6 border-b border-slate-50">
          <h3 class="font-bold text-[#2a273c]">No-Show Details</h3>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-slate-50 text-slate-400 text-xs font-bold uppercase">
              <tr>
                <th class="px-6 py-4 text-left">Reservation Code</th>
                <th class="px-6 py-4 text-left">Guest</th>
                <th class="px-6 py-4 text-left">Check-in</th>
                <th class="px-6 py-4 text-left">Source</th>
                <th class="px-6 py-4 text-right">Amount</th>
                <th class="px-6 py-4 text-right">No-Show Charge</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
              <tr v-for="detail in stats.details" :key="detail.id">
                <td class="px-6 py-4 text-sm font-medium text-[#2a273c]">{{ detail.code }}</td>
                <td class="px-6 py-4 text-sm text-slate-500">{{ detail.guest?.full_name || 'N/A' }}</td>
                <td class="px-6 py-4 text-sm text-slate-500">{{ detail.check_in }}</td>
                <td class="px-6 py-4 text-sm text-slate-500">{{ detail.source?.name || 'N/A' }}</td>
                <td class="px-6 py-4 text-sm text-right text-red-600 font-bold">{{ formatCurrency(detail.total_amount) }}</td>
                <td class="px-6 py-4 text-sm text-right" :class="detail.no_show_charge ? 'text-emerald-600' : 'text-slate-400'">
                  {{ formatCurrency(detail.no_show_charge || 0) }}
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
import { Bar } from 'vue-chartjs';
import {
  Chart as ChartJS,
  Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale
} from 'chart.js';
import { Building2 as Building2Icon, Home as HomeIcon, Calendar as CalendarIcon } from 'lucide-vue-next';

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale);

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

const fetchData = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/reports/no-show/generate', { params: filters.value });
    stats.value = response.data.stats;
    bySource.value = response.data.by_source;
    byRoomType.value = response.data.by_room_type;
    byDayOfWeek.value = response.data.by_day_of_week;
  } catch (error) {
    console.error('Error fetching no-show report:', error);
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
    label: 'No-Show Count',
    data: bySource.value.map(s => s.no_show_count),
    backgroundColor: '#3b82f6',
    borderRadius: 8
  }]
}));

const roomTypeChartData = computed(() => ({
  labels: byRoomType.value.map(r => r.name),
  datasets: [{
    label: 'No-Show Count',
    data: byRoomType.value.map(r => r.no_show_count),
    backgroundColor: '#f97316',
    borderRadius: 8
  }]
}));

const dayOfWeekChartData = computed(() => ({
  labels: byDayOfWeek.value.map(d => d.day_name),
  datasets: [{
    label: 'No-Shows',
    data: byDayOfWeek.value.map(d => d.no_show_count),
    backgroundColor: '#8b5cf6',
    borderRadius: 8
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

const exportCSV = () => {
  window.location.href = `/reports/no-show/export?start_date=${filters.value.start_date}&end_date=${filters.value.end_date}&format=csv`;
};
</script>

<style scoped>
.shadow-sm { box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05); }
</style>
