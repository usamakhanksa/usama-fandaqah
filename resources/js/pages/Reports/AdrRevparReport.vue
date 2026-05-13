<template>
  <div class="p-6 bg-[#f8f9fa] min-h-full space-y-6">
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold text-[#2a273c]">ADR & RevPAR Report</h1>
        <p class="text-slate-500">Average Daily Rate and Revenue per Available Room analysis</p>
      </div>
      <div class="flex items-center gap-3">
        <button @click="exportReport" class="px-4 py-2 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 flex items-center gap-2">
          <DownloadIcon class="w-4 h-4" /> Export
        </button>
      </div>
    </div>

    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="text-xs font-bold text-slate-400 uppercase">From Date</label>
          <input type="date" v-model="filters.start_date" @change="fetchData" class="w-full border border-slate-200 rounded-lg px-3 py-2 mt-1" />
        </div>
        <div>
          <label class="text-xs font-bold text-slate-400 uppercase">To Date</label>
          <input type="date" v-model="filters.end_date" @change="fetchData" class="w-full border border-slate-200 rounded-lg px-3 py-2 mt-1" />
        </div>
        <div>
          <label class="text-xs font-bold text-slate-400 uppercase">Room Type</label>
          <select v-model="filters.room_type_id" @change="fetchData" class="w-full border border-slate-200 rounded-lg px-3 py-2 mt-1">
            <option :value="null">All Room Types</option>
            <option v-for="rt in roomTypes" :key="rt.id" :value="rt.id">{{ rt.name }}</option>
          </select>
        </div>
        <div>
          <label class="text-xs font-bold text-slate-400 uppercase">Aggregation</label>
          <select v-model="filters.aggregation" @change="fetchData" class="w-full border border-slate-200 rounded-lg px-3 py-2 mt-1">
            <option value="daily">Daily</option>
            <option value="weekly">Weekly</option>
            <option value="monthly">Monthly</option>
          </select>
        </div>
      </div>
    </div>

    <div class="flex items-center gap-4 bg-white p-4 rounded-2xl shadow-sm border border-slate-100">
      <span class="text-sm font-medium text-slate-600">Moving Average:</span>
      <label class="flex items-center gap-2 cursor-pointer">
        <input type="radio" v-model="filters.moving_average" :value="null" @change="fetchData" class="text-[#e95a54]" />
        <span class="text-sm">None</span>
      </label>
      <label class="flex items-center gap-2 cursor-pointer">
        <input type="radio" v-model="filters.moving_average" value="7" @change="fetchData" class="text-[#e95a54]" />
        <span class="text-sm">7-Day</span>
      </label>
      <label class="flex items-center gap-2 cursor-pointer">
        <input type="radio" v-model="filters.moving_average" value="30" @change="fetchData" class="text-[#e95a54]" />
        <span class="text-sm">30-Day</span>
      </label>
    </div>

    <div v-if="loading" class="flex justify-center py-20">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-[#e95a54]"></div>
    </div>

    <div v-else class="space-y-6">
      <!-- Summary Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <KPICard title="Avg ADR" :value="averages.adr" unit="SR" color="text-[#2a273c]" />
        <KPICard title="Avg RevPAR" :value="averages.revpar" unit="SR" color="text-emerald-600" />
        <KPICard title="Total Rooms Sold" :value="totals.rooms_sold" color="text-blue-600" />
        <KPICard title="Total Revenue" :value="totals.room_revenue" unit="SR" color="text-orange-600" />
      </div>

      <!-- Chart -->
      <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
        <h3 class="text-xl font-bold text-[#2a273c] mb-6">ADR & RevPAR Trend</h3>
        <div class="h-80">
          <Line :data="chartData" :options="chartOptions" />
        </div>
      </div>

      <!-- Data Table -->
      <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="p-6 border-b border-slate-50">
          <h3 class="font-bold text-[#2a273c]">Detailed Data</h3>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-left">
            <thead class="bg-slate-50 text-slate-400 text-xs font-bold uppercase">
              <tr>
                <th class="px-6 py-4">Date</th>
                <th class="px-6 py-4 text-right">Rooms Sold</th>
                <th class="px-6 py-4 text-right">Room Revenue</th>
                <th class="px-6 py-4 text-right">ADR</th>
                <th class="px-6 py-4 text-right">Total Rooms</th>
                <th class="px-6 py-4 text-right">RevPAR</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
              <tr v-for="item in data" :key="item.date || item.period">
                <td class="px-6 py-4 text-sm font-medium text-[#2a273c]">{{ item.date || item.period }}</td>
                <td class="px-6 py-4 text-sm text-right">{{ item.rooms_sold }}</td>
                <td class="px-6 py-4 text-sm text-right font-bold text-emerald-600">{{ item.room_revenue?.toLocaleString() }}</td>
                <td class="px-6 py-4 text-sm text-right font-bold text-[#2a273c]">{{ item.adr }}</td>
                <td class="px-6 py-4 text-sm text-right">{{ item.total_rooms }}</td>
                <td class="px-6 py-4 text-sm text-right font-bold text-blue-600">{{ item.revpar }}</td>
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
import { Download as DownloadIcon } from 'lucide-vue-next';

ChartJS.register(Title, Tooltip, Legend, LineElement, CategoryScale, LinearScale, PointElement);

const props = defineProps({
  startDate: String,
  endDate: String,
  roomTypes: Array,
});

const filters = ref({
  start_date: props.startDate,
  end_date: props.endDate,
  room_type_id: null,
  aggregation: 'daily',
  moving_average: null,
});

const loading = ref(true);
const data = ref([]);
const maData = ref({});
const roomTypes = ref(props.roomTypes || []);

const fetchData = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/reports/adr-revpar/generate', { params: filters.value });
    data.value = response.data.daily;
    maData.value = {
      ma_adr_7: response.data.ma_adr_7 || [],
      ma_adr_30: response.data.ma_adr_30 || [],
      ma_revpar_7: response.data.ma_revpar_7 || [],
      ma_revpar_30: response.data.ma_revpar_30 || [],
    };
  } catch (error) {
    console.error('Error fetching ADR RevPAR report:', error);
  } finally {
    loading.value = false;
  }
};

onMounted(fetchData);

const averages = computed(() => {
  if (!data.value.length) return { adr: 0, revpar: 0 };
  const adrSum = data.value.reduce((sum, d) => sum + (d.adr || 0), 0);
  const revparSum = data.value.reduce((sum, d) => sum + (d.revpar || 0), 0);
  return {
    adr: Math.round(adrSum / data.value.length),
    revpar: Math.round(revparSum / data.value.length),
  };
});

const totals = computed(() => {
  return {
    rooms_sold: data.value.reduce((sum, d) => sum + (d.rooms_sold || 0), 0),
    room_revenue: data.value.reduce((sum, d) => sum + (d.room_revenue || 0), 0),
  };
});

const chartData = computed(() => {
  const showMa = filters.value.moving_average === '7' || filters.value.moving_average === '30';
  const maKey = filters.value.moving_average ? `ma_adr_${filters.value.moving_average}` : null;
  const maRevparKey = filters.value.moving_average ? `ma_revpar_${filters.value.moving_average}` : null;

  const datasets = [
    {
      label: 'ADR (SR)',
      data: data.value.map(d => d.adr),
      borderColor: '#2a273c',
      backgroundColor: 'rgba(42, 39, 60, 0.1)',
      tension: 0.4,
      yAxisID: 'y',
    },
    {
      label: 'RevPAR (SR)',
      data: data.value.map(d => d.revpar),
      borderColor: '#10b981',
      backgroundColor: 'rgba(16, 185, 129, 0.1)',
      tension: 0.4,
      yAxisID: 'y',
    },
  ];

  if (showMa && maData.value[maKey]) {
    datasets.push({
      label: `ADR ${filters.value.moving_average}-Day MA`,
      data: maData.value[maKey].map(d => d.adr),
      borderColor: '#e95a54',
      borderDash: [5, 5],
      tension: 0.4,
      yAxisID: 'y',
    });
  }

  if (showMa && maData.value[maRevparKey]) {
    datasets.push({
      label: `RevPAR ${filters.value.moving_average}-Day MA`,
      data: maData.value[maRevparKey].map(d => d.revpar),
      borderColor: '#f59e0b',
      borderDash: [5, 5],
      tension: 0.4,
      yAxisID: 'y',
    });
  }

  return {
    labels: data.value.map(d => d.date || d.period),
    datasets,
  };
});

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { position: 'top' },
    tooltip: { mode: 'index', intersect: false },
  },
  scales: {
    y: {
      type: 'linear',
      display: true,
      position: 'left',
      beginAtZero: true,
      ticks: { callback: v => v.toLocaleString() + ' SR' },
    },
  },
};

const exportReport = () => {
  const params = new URLSearchParams(filters.value).toString();
  window.location.href = `/reports/adr-revpar/export?${params}`;
};

const KPICard = ({ title, value, unit, color }) => {
  return (
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
      <p class="text-slate-500 text-sm font-medium">{title}</p>
      <h3 class={`text-3xl font-bold mt-1 ${color}`}>
        {value?.toLocaleString() || 0} <span class="text-sm font-normal text-slate-400">{unit || ''}</span>
      </h3>
    </div>
  );
};
</script>

<style scoped>
.shadow-sm { box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05); }
</style>