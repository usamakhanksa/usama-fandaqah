<template>
  <div class="dashboard-page bg-slate-50 min-h-screen pb-10">
    <DashboardFilterBar 
      :filters="['dateRange', 'team']" 
      :teams="teams"
      @update="fetchData"
      @refresh="fetchData(currentFilters)"
      @export="exportData"
    />

    <div class="px-6 max-w-[1600px] mx-auto">
      
      <!-- Header -->
      <div class="mb-6 flex justify-between items-end">
        <div>
          <h1 class="text-2xl font-bold text-slate-800">{{ $t('dashboard.occupancy_dashboard') }}</h1>
          <p class="text-slate-500 text-sm">{{ $t('dashboard.occupancy_desc') }}</p>
        </div>
      </div>

      <!-- Top Row: Gauge and Stats -->
      <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-8">
        
        <!-- Occupancy Gauge -->
        <div class="lg:col-span-1 bg-white rounded-xl shadow-sm border border-slate-100 p-5 flex flex-col items-center justify-center">
          <h3 class="text-sm font-semibold text-slate-500 mb-2">{{ $t('dashboard.occupancy_rate') }}</h3>
          <div v-if="loading" class="h-[200px] flex items-center justify-center">
            <div class="w-8 h-8 border-4 border-indigo-200 border-t-indigo-600 rounded-full animate-spin"></div>
          </div>
          <apexchart v-else type="radialBar" height="250" :options="gaugeOptions" :series="[occupancyRate]"></apexchart>
        </div>

        <!-- KPI Summary -->
        <div class="lg:col-span-3 grid grid-cols-1 sm:grid-cols-3 gap-6">
          <KpiCard 
            :title="$t('rooms.occupied')"
            :value="metrics.occupied"
            :icon="HomeIcon"
            color="indigo"
          />
          <KpiCard 
            :title="$t('rooms.available')"
            :value="metrics.available"
            :icon="DoorOpenIcon"
            color="emerald"
          />
          <KpiCard 
            :title="$t('rooms.maintenance')"
            :value="metrics.maintenance"
            :icon="WrenchIcon"
            color="amber"
          />
        </div>

      </div>

      <!-- Second Row: Charts -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        
        <!-- Status Breakdown (Pie) -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-5">
          <h3 class="text-lg font-semibold text-slate-800 mb-6">{{ $t('dashboard.room_status_breakdown') }}</h3>
          <div v-if="loading" class="h-[300px] flex items-center justify-center">
            <div class="w-8 h-8 border-4 border-indigo-200 border-t-indigo-600 rounded-full animate-spin"></div>
          </div>
          <apexchart v-else type="donut" height="300" :options="pieOptions" :series="pieSeries"></apexchart>
        </div>

        <!-- Category Occupancy (Bar) -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-5">
          <h3 class="text-lg font-semibold text-slate-800 mb-6">{{ $t('dashboard.occupancy_by_type') }}</h3>
          <div v-if="loading" class="h-[300px] flex items-center justify-center">
            <div class="w-8 h-8 border-4 border-indigo-200 border-t-indigo-600 rounded-full animate-spin"></div>
          </div>
          <apexchart v-else type="bar" height="300" :options="barOptions" :series="barSeries"></apexchart>
        </div>

      </div>

      <!-- Third Row: 30 Day Trend -->
      <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-5 mb-8">
        <h3 class="text-lg font-semibold text-slate-800 mb-6">{{ $t('dashboard.occupancy_trend_30') }}</h3>
        <div v-if="loading" class="h-[350px] flex items-center justify-center">
          <div class="w-8 h-8 border-4 border-indigo-200 border-t-indigo-600 rounded-full animate-spin"></div>
        </div>
        <apexchart v-else type="area" height="350" :options="trendOptions" :series="trendSeries"></apexchart>
      </div>

      <!-- Floor Plan / Room Status Grid -->
      <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
        <div class="flex justify-between items-center mb-8">
          <h3 class="text-xl font-bold text-slate-800">{{ $t('dashboard.visual_floor_plan') }}</h3>
          <div class="flex gap-4 text-xs font-medium">
            <div class="flex items-center gap-1.5">
              <span class="w-3 h-3 rounded bg-emerald-500"></span>
              <span>{{ $t('rooms.available') }}</span>
            </div>
            <div class="flex items-center gap-1.5">
              <span class="w-3 h-3 rounded bg-indigo-500"></span>
              <span>{{ $t('rooms.occupied') }}</span>
            </div>
            <div class="flex items-center gap-1.5">
              <span class="w-3 h-3 rounded bg-amber-500"></span>
              <span>{{ $t('rooms.maintenance') }}</span>
            </div>
            <div class="flex items-center gap-1.5">
              <span class="w-3 h-3 rounded bg-slate-400"></span>
              <span>{{ $t('rooms.dirty') }}</span>
            </div>
          </div>
        </div>

        <div v-if="loading" class="space-y-8">
          <div v-for="i in 3" :key="i" class="space-y-4">
            <div class="h-6 w-32 bg-slate-100 animate-pulse rounded"></div>
            <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8 gap-4">
              <div v-for="j in 8" :key="j" class="h-16 bg-slate-50 animate-pulse rounded-lg"></div>
            </div>
          </div>
        </div>

        <div v-else class="space-y-12">
          <div v-for="(units, floor) in floorPlan" :key="floor" class="space-y-4">
            <div class="flex items-center gap-4">
              <h4 class="text-sm font-bold text-slate-400 uppercase tracking-wider">Floor {{ floor }}</h4>
              <div class="h-px flex-1 bg-slate-100"></div>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8 xl:grid-cols-10 gap-4">
              <div v-for="unit in units" :key="unit.id" 
                class="relative group p-3 rounded-xl border transition-all duration-300 cursor-pointer hover:-translate-y-1"
                :class="[
                  unit.status === 'clean' ? 'bg-emerald-50 border-emerald-100 text-emerald-700 hover:shadow-emerald-100/50 hover:shadow-lg' :
                  (unit.status === 'checked_in' || unit.status === 'booked') ? 'bg-indigo-50 border-indigo-100 text-indigo-700 hover:shadow-indigo-100/50 hover:shadow-lg' :
                  unit.status === 'maintenance' ? 'bg-amber-50 border-amber-100 text-amber-700 hover:shadow-amber-100/50 hover:shadow-lg' :
                  'bg-slate-50 border-slate-200 text-slate-500 hover:shadow-slate-100/50 hover:shadow-lg'
                ]"
              >
                <div class="text-xs font-bold opacity-60 mb-1">{{ unit.category?.name }}</div>
                <div class="text-lg font-black">{{ unit.number }}</div>
                
                <!-- Tooltip / Hover Info -->
                <div class="absolute z-10 bottom-full left-1/2 -translate-x-1/2 mb-2 w-48 p-2 bg-slate-800 text-white text-[10px] rounded shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all pointer-events-none">
                  <div class="font-bold border-b border-slate-700 pb-1 mb-1">Room {{ unit.number }}</div>
                  <div>Category: {{ unit.category?.name }}</div>
                  <div>Status: {{ unit.status }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import DashboardFilterBar from '../../components/dashboards/DashboardFilterBar.vue';
import KpiCard from '../../components/dashboards/KpiCard.vue';
import EmptyState from '../../components/dashboards/EmptyState.vue';
import { HomeIcon, DoorOpenIcon, WrenchIcon, UsersIcon, TrendingUpIcon, CalendarIcon } from 'lucide-vue-next';
import api from '../../services/api';

const { t } = useI18n();

const loading = ref(true);
const teams = ref([]);
const currentFilters = ref({});
const metrics = ref({ total: 0, occupied: 0, available: 0, dirty: 0, maintenance: 0 });
const byCategory = ref([]);
const trendData = ref([]);
const floorPlan = ref({});

const occupancyRate = computed(() => {
  return metrics.value.total > 0 ? Math.round((metrics.value.occupied / metrics.value.total) * 100) : 0;
});

// Gauge Chart Options
const gaugeOptions = {
  chart: { fontFamily: 'inherit' },
  plotOptions: {
    radialBar: {
      startAngle: -135,
      endAngle: 135,
      hollow: { size: '70%' },
      track: { background: '#f1f5f9', strokeWidth: '97%' },
      dataLabels: {
        name: { show: false },
        value: {
          offsetY: 10,
          fontSize: '32px',
          fontWeight: '900',
          formatter: (val) => val + '%'
        }
      }
    }
  },
  colors: ['#4f46e5'],
  stroke: { lineCap: 'round' }
};

// Pie Chart (Status)
const pieSeries = computed(() => [metrics.value.occupied, metrics.value.available, metrics.value.maintenance, metrics.value.dirty]);
const pieOptions = {
  chart: { fontFamily: 'inherit' },
  labels: [t('rooms.occupied'), t('rooms.available'), t('rooms.maintenance'), t('rooms.dirty')],
  colors: ['#6366f1', '#10b981', '#f59e0b', '#94a3b8'],
  legend: { position: 'bottom' },
  plotOptions: { pie: { donut: { size: '65%' } } }
};

// Bar Chart (Category)
const barSeries = computed(() => [{
  name: t('dashboard.occupancy'),
  data: byCategory.value.map(c => c.occupancy_rate)
}]);
const barOptions = {
  chart: { toolbar: { show: false }, fontFamily: 'inherit' },
  plotOptions: { bar: { borderRadius: 4, horizontal: true } },
  colors: ['#818cf8'],
  xaxis: {
    categories: byCategory.value.map(c => c.name),
    labels: { formatter: (v) => v + '%' }
  },
  grid: { strokeDashArray: 4 }
};

// Trend Chart (Line)
const trendSeries = computed(() => [{
  name: t('dashboard.occupancy'),
  data: trendData.value.map(t => t.rate)
}]);
const trendOptions = {
  chart: { toolbar: { show: false }, fontFamily: 'inherit' },
  colors: ['#6366f1'],
  stroke: { curve: 'smooth', width: 4 },
  xaxis: { 
    categories: trendData.value.map(t => t.date),
    type: 'datetime',
    labels: { datetimeFormatter: { month: 'MMM dd', day: 'dd' } }
  },
  yaxis: { max: 100, labels: { formatter: (v) => v + '%' } },
  fill: {
    type: 'gradient',
    gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.1, stops: [0, 90, 100] }
  }
};

const fetchData = async (filters) => {
  currentFilters.value = filters;
  loading.value = true;
  try {
    const { data } = await api.get('/dashboard/occupancy', { params: filters });
    metrics.value = data.metrics;
    byCategory.value = data.by_category;
    trendData.value = data.trend;
    floorPlan.value = data.floor_plan;
  } catch (error) {
    console.error(error);
  } finally {
    loading.value = false;
  }
};

const exportData = () => {
  window.open(`/api/dashboard/occupancy/export?startDate=${currentFilters.value.startDate}&endDate=${currentFilters.value.endDate}`, '_blank');
};

onMounted(async () => {
  try {
    const { data } = await api.get('/user-groups/teams');
    teams.value = data.data || [];
  } catch(e) {}
});
</script>
