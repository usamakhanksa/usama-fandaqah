<template>
  <div class="p-6 bg-[#f8f9fa] min-h-full space-y-6">
    <!-- Header/Filter -->
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold text-[#2a273c]">Occupancy Report</h1>
        <p class="text-slate-500">Analyze occupancy trends and distribution</p>
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
      <!-- Main Trend Chart -->
      <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100">
        <h3 class="text-xl font-bold text-[#2a273c] mb-8 flex items-center gap-2">
          <TrendingUpIcon class="w-5 h-5 text-emerald-500" /> Daily Occupancy Trend (%)
        </h3>
        <div class="h-80">
          <Line :data="trendChartData" :options="trendChartOptions" />
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Room Type Distribution -->
        <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100">
          <h3 class="text-lg font-bold text-[#2a273c] mb-6 flex items-center gap-2">
            <HotelIcon class="w-5 h-5 text-blue-500" /> By Room Type
          </h3>
          <div class="h-64">
            <Bar :data="roomTypeChartData" :options="barOptions" />
          </div>
        </div>

        <!-- Floor Distribution -->
        <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100">
          <h3 class="text-lg font-bold text-[#2a273c] mb-6 flex items-center gap-2">
            <LayersIcon class="w-5 h-5 text-orange-500" /> By Floor
          </h3>
          <div class="h-64">
            <Bar :data="floorChartData" :options="barOptions" />
          </div>
        </div>
      </div>

      <!-- Detailed Table -->
      <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="p-6 border-b border-slate-50">
          <h3 class="font-bold text-[#2a273c]">Daily Statistics Table</h3>
        </div>
        <table class="w-full">
          <thead class="bg-slate-50 text-slate-400 text-xs font-bold uppercase">
            <tr>
              <th class="px-6 py-4 text-left">Date</th>
              <th class="px-6 py-4 text-center">Total Rooms</th>
              <th class="px-6 py-4 text-center">Occupied</th>
              <th class="px-6 py-4 text-center">OOO</th>
              <th class="px-6 py-4 text-right">Occupancy %</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <tr v-for="stat in data.daily_stats" :key="stat.date">
              <td class="px-6 py-4 text-sm font-medium text-[#2a273c]">{{ stat.date }}</td>
              <td class="px-6 py-4 text-sm text-center text-slate-500">{{ stat.total_rooms }}</td>
              <td class="px-6 py-4 text-sm text-center text-emerald-600 font-bold">{{ stat.occupied }}</td>
              <td class="px-6 py-4 text-sm text-center text-red-400">{{ stat.ooo }}</td>
              <td class="px-6 py-4 text-sm text-right font-bold text-[#2a273c]">{{ stat.occupancy_percentage }}%</td>
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
  Line, Bar 
} from 'vue-chartjs';
import { 
  Chart as ChartJS, 
  Title, Tooltip, Legend, LineElement, CategoryScale, LinearScale, PointElement, BarElement 
} from 'chart.js';
import { 
  TrendingUp as TrendingUpIcon,
  Hotel as HotelIcon,
  Layers as LayersIcon
} from 'lucide-vue-next';

ChartJS.register(Title, Tooltip, Legend, LineElement, CategoryScale, LinearScale, PointElement, BarElement);

const props = defineProps(['startDate', 'endDate']);
const filters = ref({
  start_date: props.startDate,
  end_date: props.endDate
});
const loading = ref(true);
const data = ref({
  daily_stats: [],
  by_room_type: [],
  by_floor: []
});

const fetchData = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/reports/occupancy/generate', { params: filters.value });
    data.value = response.data;
  } catch (error) {
    console.error('Error fetching occupancy report:', error);
  } finally {
    loading.value = false;
  }
};

onMounted(fetchData);

// Chart Configurations
const trendChartData = computed(() => ({
  labels: data.value.daily_stats.map(s => s.date),
  datasets: [{
    label: 'Occupancy %',
    data: data.value.daily_stats.map(s => s.occupancy_percentage),
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
    y: { min: 0, max: 100, ticks: { callback: v => v + '%' } },
    x: { grid: { display: false } }
  }
};

const roomTypeChartData = computed(() => ({
  labels: data.value.by_room_type.map(r => r.name),
  datasets: [{
    label: 'Occupancy %',
    data: data.value.by_room_type.map(r => r.occupancy_percentage),
    backgroundColor: '#3b82f6',
    borderRadius: 8
  }]
}));

const floorChartData = computed(() => ({
  labels: data.value.by_floor.map(f => f.name),
  datasets: [{
    label: 'Occupancy %',
    data: data.value.by_floor.map(f => f.occupancy_percentage),
    backgroundColor: '#f97316',
    borderRadius: 8
  }]
}));

const barOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: { legend: { display: false } },
  scales: {
    y: { min: 0, max: 100, ticks: { callback: v => v + '%' } },
    x: { grid: { display: false } }
  }
};
</script>

<style scoped>
.shadow-sm { box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05); }
</style>
