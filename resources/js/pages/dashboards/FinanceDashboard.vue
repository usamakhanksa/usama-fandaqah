<template>
  <div class="dashboard-page bg-slate-50 min-h-screen pb-10">
    <DashboardFilterBar 
      :filters="['date', 'team']" 
      :teams="teams"
      @update="fetchData"
      @refresh="fetchData(currentFilters)"
      @export="exportData"
    />

    <div class="px-6 max-w-[1600px] mx-auto">
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800">{{ $t('dashboard.finance_dashboard') }}</h1>
        <p class="text-slate-500 text-sm">{{ $t('dashboard.finance_desc') }}</p>
      </div>

      <div v-if="loading" class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-8">
        <div v-for="i in 5" :key="i" class="h-32 bg-slate-200 animate-pulse rounded-xl"></div>
      </div>

      <!-- KPI Cards -->
      <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
        <KpiCard 
          :title="$t('dashboard.todays_collections')"
          :value="formatCurrency(totalCollectionsToday)"
          :icon="BanknoteIcon"
          color="emerald"
        >
          <template #subtitle>
            <div class="text-xs text-slate-500 flex gap-2 mt-1">
              <span v-for="(amount, method) in collectionsBreakdown" :key="method">
                {{ method }}: {{ formatCurrency(amount) }}
              </span>
            </div>
          </template>
        </KpiCard>
        
        <KpiCard 
          :title="$t('dashboard.open_cashier_shifts')"
          :value="metrics.open_cashier_shifts"
          :icon="UserCheckIcon"
          color="indigo"
        />

        <KpiCard 
          :title="$t('dashboard.promissory_outstanding')"
          :value="formatCurrency(metrics.promissory_outstanding)"
          :icon="FileTextIcon"
          color="amber"
        />

        <KpiCard 
          :title="$t('dashboard.invoices_pending_zatca')"
          :value="metrics.invoices_pending_zatca"
          :icon="ShieldAlertIcon"
          color="rose"
        />

        <KpiCard 
          :title="$t('dashboard.credit_notes_today')"
          :value="metrics.credit_notes_today"
          :icon="FileMinusIcon"
          color="blue"
        />
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        
        <!-- Payment Method Breakdown Pie Chart -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-5 flex flex-col h-full">
          <div class="border-b border-slate-100 pb-3 mb-4 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-slate-800 flex items-center gap-2">
              <PieChartIcon class="w-5 h-5 text-indigo-500" />
              {{ $t('dashboard.payment_method_breakdown') }}
            </h3>
          </div>
          <div class="flex-1 min-h-[300px]">
             <VueApexCharts
                v-if="chartOptions.labels.length"
                type="pie"
                height="350"
                :options="chartOptions"
                :series="chartSeries"
              />
              <div v-else class="h-full flex items-center justify-center text-slate-400">
                {{ $t('common.no_data') }}
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
import { BanknoteIcon, UserCheckIcon, FileTextIcon, ShieldAlertIcon, FileMinusIcon, PieChartIcon } from 'lucide-vue-next';
import api from '../../services/api';
import VueApexCharts from 'vue3-apexcharts';

const { t } = useI18n();

const loading = ref(true);
const teams = ref([]);
const currentFilters = ref({});

const metrics = ref({
  collections_today: [],
  open_cashier_shifts: 0,
  promissory_outstanding: 0,
  invoices_pending_zatca: 0,
  credit_notes_today: 0,
  payment_method_breakdown: []
});

const totalCollectionsToday = computed(() => {
  if (!metrics.value.collections_today) return 0;
  return metrics.value.collections_today.reduce((sum, item) => sum + parseFloat(item.total), 0);
});

const collectionsBreakdown = computed(() => {
  const breakdown = {};
  if (metrics.value.collections_today) {
    metrics.value.collections_today.forEach(item => {
      breakdown[item.method] = parseFloat(item.total);
    });
  }
  return breakdown;
});

const chartSeries = computed(() => {
  if (!metrics.value.payment_method_breakdown) return [];
  return metrics.value.payment_method_breakdown.map(item => parseFloat(item.total));
});

const chartOptions = computed(() => {
  const labels = metrics.value.payment_method_breakdown ? metrics.value.payment_method_breakdown.map(item => item.method) : [];
  return {
    chart: { type: 'pie' },
    labels: labels,
    responsive: [{ breakpoint: 480, options: { chart: { width: 200 }, legend: { position: 'bottom' } } }],
    tooltip: {
      y: {
        formatter: function (val) {
          return "SAR " + val.toLocaleString()
        }
      }
    }
  };
});

const formatCurrency = (val) => {
  return Number(val || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const fetchData = async (filters) => {
  currentFilters.value = filters;
  loading.value = true;
  try {
    const { data } = await api.get('/dashboard/finance', { params: filters });
    metrics.value = data;
  } catch (error) {
    console.error(error);
  } finally {
    loading.value = false;
  }
};

const exportData = async () => {
  try {
    const format = prompt('Export format (pdf/excel)', 'excel');
    if(!['pdf', 'excel'].includes(format)) return;
    window.open(`/api/dashboard/finance/export?format=${format}&startDate=${currentFilters.value.startDate}&endDate=${currentFilters.value.endDate}`, '_blank');
  } catch (e) {
    console.error(e);
  }
};

onMounted(async () => {
  try {
    const { data } = await api.get('/user-groups/teams');
    teams.value = data.data || [];
  } catch(e) {}
});
</script>
