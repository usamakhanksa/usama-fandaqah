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
          <h1 class="text-2xl font-bold text-slate-800">{{ $t('dashboard.overview') }}</h1>
          <p class="text-slate-500 text-sm">{{ $t('dashboard.overview_desc') }}</p>
        </div>
      </div>

      <!-- Alerts Section -->
      <div v-if="alerts && alerts.length > 0" class="mb-8 space-y-3">
        <div v-for="(alert, index) in alerts" :key="index" 
          :class="[
            'p-4 rounded-lg flex items-center gap-3 border',
            alert.type === 'danger' ? 'bg-red-50 border-red-100 text-red-700' : 
            alert.type === 'warning' ? 'bg-amber-50 border-amber-100 text-amber-700' : 
            'bg-blue-50 border-blue-100 text-blue-700'
          ]"
        >
          <AlertCircleIcon v-if="alert.type === 'danger'" class="w-5 h-5" />
          <AlertTriangleIcon v-else-if="alert.type === 'warning'" class="w-5 h-5" />
          <InfoIcon v-else class="w-5 h-5" />
          <span class="font-medium">{{ alert.message }}</span>
        </div>
      </div>

      <!-- KPIs Row 1 -->
      <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
        <KpiCard 
          :title="$t('dashboard.occupancy_rate')"
          :value="metrics.occupancyRate"
          suffix="%"
          :icon="HomeIcon"
          color="indigo"
        />
        <KpiCard 
          :title="$t('dashboard.available_rooms')"
          :value="metrics.availableRooms"
          :icon="DoorOpenIcon"
          color="emerald"
        />
        <KpiCard 
          :title="$t('dashboard.arrivals_today')"
          :value="metrics.arrivalsToday"
          :icon="ArrowDownLeftIcon"
          color="blue"
        />
        <KpiCard 
          :title="$t('dashboard.departures_today')"
          :value="metrics.departuresToday"
          :icon="ArrowUpRightIcon"
          color="amber"
        />
      </div>

      <!-- KPIs Row 2 -->
      <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6 mb-8">
        <KpiCard 
          :title="$t('dashboard.in_house_guests')"
          :value="metrics.inHouseGuests"
          :icon="UsersIcon"
          color="violet"
        />
        <KpiCard 
          :title="$t('dashboard.revenue_today')"
          :value="metrics.totalRevenue"
          prefix="SAR "
          :icon="CreditCardIcon"
          color="emerald"
        />
        <KpiCard 
          :title="$t('dashboard.mtd_revenue')"
          :value="metrics.mtdRevenue"
          prefix="SAR "
          :icon="TrendingUpIcon"
          color="indigo"
        />
      </div>

      <!-- Quick Links & Trends -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        
        <!-- Quick Links -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-5">
          <h3 class="text-lg font-semibold text-slate-800 mb-6 flex items-center gap-2">
            <ZapIcon class="w-5 h-5 text-amber-500" />
            {{ $t('dashboard.quick_links') }}
          </h3>
          <div class="grid grid-cols-2 gap-4">
            <router-link to="/units/check-in" class="flex flex-col items-center justify-center p-4 rounded-xl bg-slate-50 hover:bg-indigo-50 border border-slate-100 hover:border-indigo-100 transition-all group">
              <ArrowDownLeftIcon class="w-8 h-8 text-slate-400 group-hover:text-indigo-600 mb-2" />
              <span class="text-sm font-medium text-slate-700 group-hover:text-indigo-700">{{ $t('dashboard.check_in') }}</span>
            </router-link>
            <router-link to="/units/check-out" class="flex flex-col items-center justify-center p-4 rounded-xl bg-slate-50 hover:bg-amber-50 border border-slate-100 hover:border-amber-100 transition-all group">
              <ArrowUpRightIcon class="w-8 h-8 text-slate-400 group-hover:text-amber-600 mb-2" />
              <span class="text-sm font-medium text-slate-700 group-hover:text-amber-700">{{ $t('dashboard.check_out') }}</span>
            </router-link>
            <router-link to="/reservations/create" class="flex flex-col items-center justify-center p-4 rounded-xl bg-slate-50 hover:bg-emerald-50 border border-slate-100 hover:border-emerald-100 transition-all group">
              <PlusCircleIcon class="w-8 h-8 text-slate-400 group-hover:text-emerald-600 mb-2" />
              <span class="text-sm font-medium text-slate-700 group-hover:text-emerald-700 text-center">{{ $t('dashboard.new_reservation') }}</span>
            </router-link>
            <router-link to="/operations/night-audit" class="flex flex-col items-center justify-center p-4 rounded-xl bg-slate-50 hover:bg-violet-50 border border-slate-100 hover:border-violet-100 transition-all group">
              <MoonIcon class="w-8 h-8 text-slate-400 group-hover:text-violet-600 mb-2" />
              <span class="text-sm font-medium text-slate-700 group-hover:text-violet-700">{{ $t('dashboard.night_audit') }}</span>
            </router-link>
          </div>
        </div>

        <!-- Revenue Trends -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-slate-100 p-5">
          <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-semibold text-slate-800">{{ $t('dashboard.occupancy_revenue_trends') }}</h3>
            <div class="flex gap-4">
              <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-indigo-500"></span>
                <span class="text-xs text-slate-500">{{ $t('dashboard.revenue') }}</span>
              </div>
              <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                <span class="text-xs text-slate-500">{{ $t('dashboard.occupancy') }}</span>
              </div>
            </div>
          </div>
          <div v-if="loading" class="h-[300px] flex items-center justify-center">
            <div class="w-8 h-8 border-4 border-indigo-200 border-t-indigo-600 rounded-full animate-spin"></div>
          </div>
          <EmptyState v-else-if="!chartData.series.length" />
          <apexchart v-else type="line" height="300" :options="chartData.options" :series="chartData.series"></apexchart>
        </div>

      </div>

      <!-- Recent Activity Feed -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-slate-100 p-5">
          <h3 class="text-lg font-semibold text-slate-800 mb-6 flex items-center gap-2">
            <ActivityIcon class="w-5 h-5 text-indigo-500" />
            {{ $t('dashboard.recent_activity') }}
          </h3>
          <div v-if="loading" class="space-y-4">
            <div v-for="i in 5" :key="i" class="h-16 bg-slate-50 animate-pulse rounded-lg"></div>
          </div>
          <div v-else-if="recentActivity.length === 0" class="py-10 text-center text-slate-400">
            {{ $t('dashboard.no_recent_activity') }}
          </div>
          <div v-else class="space-y-4">
            <div v-for="activity in recentActivity" :key="activity.id" class="flex items-start gap-4 p-3 rounded-lg hover:bg-slate-50 transition-colors border border-transparent hover:border-slate-100">
              <div :class="[
                'p-2 rounded-lg shrink-0',
                activity.event === 'created' ? 'bg-emerald-50 text-emerald-600' :
                activity.event === 'updated' ? 'bg-blue-50 text-blue-600' :
                'bg-slate-100 text-slate-600'
              ]">
                <PlusIcon v-if="activity.event === 'created'" class="w-4 h-4" />
                <Edit3Icon v-else-if="activity.event === 'updated'" class="w-4 h-4" />
                <SettingsIcon v-else class="w-4 h-4" />
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm text-slate-700">
                  <span class="font-semibold">{{ activity.causer?.name || 'System' }}</span>
                  {{ activity.description }}
                </p>
                <p class="text-xs text-slate-400 mt-1">{{ formatTime(activity.created_at) }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Room Status Summary -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-5 h-fit">
          <h3 class="text-lg font-semibold text-slate-800 mb-6">{{ $t('dashboard.room_summary') }}</h3>
          <div class="space-y-4">
            <div class="flex justify-between items-center p-3 bg-indigo-50 rounded-lg">
              <span class="text-sm font-medium text-indigo-700">{{ $t('rooms.occupied') }}</span>
              <span class="text-lg font-bold text-indigo-800">{{ rooms.occupied }}</span>
            </div>
            <div class="flex justify-between items-center p-3 bg-emerald-50 rounded-lg">
              <span class="text-sm font-medium text-emerald-700">{{ $t('rooms.available') }}</span>
              <span class="text-lg font-bold text-emerald-800">{{ rooms.available }}</span>
            </div>
            <div class="flex justify-between items-center p-3 bg-amber-50 rounded-lg">
              <span class="text-sm font-medium text-amber-700">{{ $t('rooms.maintenance') }}</span>
              <span class="text-lg font-bold text-amber-800">{{ rooms.maintenance }}</span>
            </div>
          </div>
        </div>

      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import DashboardFilterBar from '../../components/dashboards/DashboardFilterBar.vue';
import KpiCard from '../../components/dashboards/KpiCard.vue';
import EmptyState from '../../components/dashboards/EmptyState.vue';
import { 
  CreditCardIcon, HomeIcon, TrendingUpIcon, CalendarIcon, UsersIcon, 
  DoorOpenIcon, ArrowDownLeftIcon, ArrowUpRightIcon, ZapIcon, PlusCircleIcon, 
  MoonIcon, ActivityIcon, PlusIcon, Edit3Icon, SettingsIcon, AlertCircleIcon, 
  AlertTriangleIcon, InfoIcon 
} from 'lucide-vue-next';
import api from '../../services/api';

const { t } = useI18n();

const loading = ref(true);
const teams = ref([]);
const currentFilters = ref({});
const alerts = ref([]);
const recentActivity = ref([]);
const rooms = ref({ occupied: 0, available: 0, maintenance: 0 });

const metrics = ref({
  totalRevenue: 0, 
  occupancyRate: 0, 
  availableRooms: 0,
  arrivalsToday: 0,
  departuresToday: 0,
  inHouseGuests: 0,
  mtdRevenue: 0
});

const chartData = ref({
  series: [],
  options: {
    chart: { toolbar: { show: false }, fontFamily: 'inherit' },
    colors: ['#4f46e5', '#10b981'],
    dataLabels: { enabled: false },
    stroke: { curve: 'smooth', width: [3, 3] },
    xaxis: { categories: [] },
    yaxis: [
      { title: { text: t('dashboard.revenue') }, labels: { formatter: (val) => val.toLocaleString() } },
      { opposite: true, title: { text: t('dashboard.occupancy') + ' %' }, max: 100 }
    ],
    tooltip: { shared: true, intersect: false }
  }
});

const fetchData = async (filters) => {
  currentFilters.value = filters;
  loading.value = true;
  try {
    const { data } = await api.get('/dashboard/overview', { params: filters });
    
    metrics.value = data.metrics;
    alerts.value = data.alerts;
    recentActivity.value = data.recentActivity;
    rooms.value = data.rooms;
    
    chartData.value.series = [
      { name: t('dashboard.revenue'), type: 'area', data: data.chart.revenue },
      { name: t('dashboard.occupancy'), type: 'line', data: data.chart.occupancy }
    ];
    chartData.value.options.xaxis.categories = data.chart.dates;

  } catch (error) {
    console.error('Dashboard fetch error', error);
  } finally {
    loading.value = false;
  }
};

const formatTime = (dateStr) => {
  const date = new Date(dateStr);
  return date.toLocaleString([], { dateStyle: 'short', timeStyle: 'short' });
};

const exportData = () => {
  window.open(`/api/dashboard/overview/export?startDate=${currentFilters.value.startDate}&endDate=${currentFilters.value.endDate}`, '_blank');
};

onMounted(async () => {
  try {
    const { data } = await api.get('/user-groups/teams').catch(() => ({ data: { data: [] } }));
    teams.value = data.data || [];
  } catch(e) {
    teams.value = [];
  }
  
  await fetchData({});
});
</script>
