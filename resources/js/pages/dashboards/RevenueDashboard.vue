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
      <div class="mb-6 flex justify-between items-end">
        <div>
          <h1 class="text-2xl font-bold text-slate-800">{{ $t('dashboard.revenue_dashboard') }}</h1>
          <p class="text-slate-500 text-sm">{{ $t('dashboard.revenue_desc') }}</p>
        </div>
      </div>

      <div v-if="loading" class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-8">
        <div v-for="i in 5" :key="i" class="h-32 bg-slate-200 animate-pulse rounded-xl"></div>
      </div>

      <!-- KPI Cards -->
      <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
        <KpiCard 
          :title="$t('dashboard.today_revenue')"
          :value="metrics.today"
          prefix="SAR "
          :icon="CreditCardIcon"
          color="emerald"
        />
        <KpiCard 
          :title="$t('dashboard.mtd_revenue')"
          :value="metrics.mtd"
          prefix="SAR "
          :icon="CalendarDaysIcon"
          color="indigo"
        />
        <KpiCard 
          :title="$t('dashboard.ytd_revenue')"
          :value="metrics.ytd"
          prefix="SAR "
          :icon="CalendarIcon"
          color="blue"
        />
        <KpiCard 
          :title="$t('dashboard.adr')"
          :value="metrics.adr"
          prefix="SAR "
          :icon="TrendingUpIcon"
          color="amber"
        />
        <KpiCard 
          :title="$t('dashboard.revpar')"
          :value="metrics.revpar"
          prefix="SAR "
          :icon="BarChart3Icon"
          color="violet"
        />
      </div>

      <!-- Charts & Table Area -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        
        <!-- Revenue Trend Chart -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-slate-100 p-5">
          <h3 class="text-lg font-semibold text-slate-800 mb-6">{{ $t('dashboard.revenue_trend_30') }}</h3>
          <div v-if="loading" class="h-[300px] flex items-center justify-center">
            <div class="w-8 h-8 border-4 border-emerald-200 border-t-emerald-600 rounded-full animate-spin"></div>
          </div>
          <EmptyState v-else-if="!trendSeries[0].data.length" />
          <apexchart v-else type="area" height="300" :options="trendOptions" :series="trendSeries"></apexchart>
        </div>

        <!-- Revenue by Source Pie -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-5">
          <h3 class="text-lg font-semibold text-slate-800 mb-6">{{ $t('dashboard.revenue_by_source') }}</h3>
          <div v-if="loading" class="h-[300px] flex items-center justify-center">
            <div class="w-8 h-8 border-4 border-emerald-200 border-t-emerald-600 rounded-full animate-spin"></div>
          </div>
          <EmptyState v-else-if="!sourceSeries.length" />
          <apexchart v-else type="donut" height="300" :options="sourceOptions" :series="sourceSeries"></apexchart>
        </div>

        <!-- Top Rooms Table -->
        <div class="lg:col-span-3 bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
          <div class="p-5 border-b border-slate-100">
            <h3 class="text-lg font-semibold text-slate-800">{{ $t('dashboard.top_revenue_rooms') }}</h3>
          </div>
          <div v-if="loading" class="p-5 space-y-4">
             <div v-for="i in 5" :key="i" class="h-10 bg-slate-50 animate-pulse rounded"></div>
          </div>
          <div v-else-if="!topUnits.length" class="p-10 text-center text-slate-400">
            {{ $t('common.no_data') }}
          </div>
          <div v-else class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider">
                  <th class="px-6 py-4 font-semibold">{{ $t('table.unit_no') }}</th>
                  <th class="px-6 py-4 font-semibold">{{ $t('dashboard.revenue_generated') }}</th>
                  <th class="px-6 py-4 font-semibold text-right">{{ $t('dashboard.contribution') }}</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <tr v-for="(unit, index) in topUnits" :key="index" class="hover:bg-slate-50 transition-colors">
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold text-xs">
                        #{{ index + 1 }}
                      </div>
                      <span class="font-bold text-slate-700">{{ unit.number }}</span>
                    </div>
                  </td>
                  <td class="px-6 py-4 font-medium text-slate-800" dir="ltr">SAR {{ Number(unit.revenue).toLocaleString() }}</td>
                  <td class="px-6 py-4 text-right">
                    <div class="flex items-center justify-end gap-2">
                      <span class="text-sm font-semibold text-slate-600" dir="ltr">
                        {{ metrics.ytd > 0 ? ((unit.revenue / metrics.ytd) * 100).toFixed(1) : 0 }}%
                      </span>
                      <div class="w-24 h-2 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full bg-emerald-500 rounded-full" :style="`width: ${metrics.ytd > 0 ? (unit.revenue / metrics.ytd) * 100 : 0}%`"></div>
                      </div>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
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
import { CreditCardIcon, CalendarDaysIcon, CalendarIcon, TrendingUpIcon, BarChart3Icon } from 'lucide-vue-next';
import api from '../../services/api';

const { t } = useI18n();

const loading = ref(true);
const teams = ref([]);
const currentFilters = ref({});

const metrics = ref({ today: 0, mtd: 0, ytd: 0, adr: 0, revpar: 0 });
const bySource = ref([]);
const trendData = ref([]);
const topUnits = ref([]);

// Trend Chart (Line)
const trendSeries = computed(() => [{
  name: t('dashboard.revenue'),
  data: trendData.value.map(t => t.revenue)
}]);
const trendOptions = {
  chart: { toolbar: { show: false }, fontFamily: 'inherit' },
  colors: ['#10b981'],
  stroke: { curve: 'smooth', width: 3 },
  xaxis: { 
    categories: trendData.value.map(t => t.date),
    type: 'datetime',
    labels: { datetimeFormatter: { month: 'MMM dd', day: 'dd' } }
  },
  yaxis: { labels: { formatter: (v) => 'SAR ' + v.toLocaleString() } },
  fill: {
    type: 'gradient',
    gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.1, stops: [0, 90, 100] }
  }
};

// Source Pie Chart
const sourceSeries = computed(() => bySource.value.map(s => Number(s.total)));
const sourceOptions = computed(() => ({
  chart: { fontFamily: 'inherit' },
  labels: bySource.value.map(s => s.name),
  colors: ['#10b981', '#3b82f6', '#f59e0b', '#6366f1', '#ec4899', '#8b5cf6'],
  legend: { position: 'bottom' },
  plotOptions: { pie: { donut: { size: '65%' } } },
  tooltip: { y: { formatter: (val) => 'SAR ' + val.toLocaleString() } }
}));

const fetchData = async (filters) => {
  currentFilters.value = filters;
  loading.value = true;
  try {
    const { data } = await api.get('/dashboard/revenue', { params: filters });
    metrics.value = data.metrics;
    bySource.value = data.by_source;
    trendData.value = data.trend;
    topUnits.value = data.top_units;
  } catch (error) {
    console.error(error);
  } finally {
    loading.value = false;
  }
};

const exportData = () => {
  window.open(`/api/dashboard/revenue/export?startDate=${currentFilters.value.startDate}&endDate=${currentFilters.value.endDate}`, '_blank');
};

onMounted(async () => {
  try {
    const { data } = await api.get('/user-groups/teams');
    teams.value = data.data || [];
  } catch(e) {}
});
</script>
