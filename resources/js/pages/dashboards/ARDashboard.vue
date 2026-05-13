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
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800">{{ $t('dashboard.ar_dashboard') }}</h1>
        <p class="text-slate-500 text-sm">{{ $t('dashboard.ar_desc') }}</p>
      </div>

      <div v-if="loading" class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div v-for="i in 4" :key="i" class="h-32 bg-slate-200 animate-pulse rounded-xl"></div>
      </div>

      <div v-else class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
        <KpiCard 
          :title="$t('dashboard.total_receivables')"
          :value="metrics.totalReceivables"
          prefix="SAR "
          :icon="BriefcaseIcon"
          color="indigo"
          :trend="metrics.receivablesTrend"
        />
        <KpiCard 
          :title="$t('dashboard.overdue_amount')"
          :value="metrics.overdueAmount"
          prefix="SAR "
          :icon="AlertCircleIcon"
          color="rose"
        />
        <KpiCard 
          :title="$t('dashboard.avg_collection_days')"
          :value="metrics.avgCollectionDays"
          suffix=" Days"
          :icon="CalendarIcon"
          color="amber"
        />
        <KpiCard 
          :title="$t('dashboard.unallocated_payments')"
          :value="metrics.unallocatedPayments"
          prefix="SAR "
          :icon="CreditCardIcon"
          color="blue"
        />
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-5">
          <h3 class="text-lg font-semibold text-slate-800 mb-4">{{ $t('dashboard.ar_aging') }}</h3>
          <div v-if="loading" class="h-[300px] flex items-center justify-center">
            <div class="w-8 h-8 border-4 border-indigo-200 border-t-indigo-600 rounded-full animate-spin"></div>
          </div>
          <EmptyState v-else-if="!chartData.series.length" />
          <apexchart v-else type="bar" height="300" :options="chartData.options" :series="chartData.series"></apexchart>
        </div>

        <!-- Top Debtors List -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
          <div class="p-5 border-b border-slate-100 bg-slate-50">
            <h3 class="text-lg font-semibold text-slate-800">{{ $t('dashboard.top_debtors') }}</h3>
          </div>
          
          <div v-if="loading" class="p-10 flex justify-center">
            <div class="w-8 h-8 border-4 border-indigo-200 border-t-indigo-600 rounded-full animate-spin"></div>
          </div>
          
          <EmptyState v-else-if="!topDebtors.length" />
          
          <div v-else class="divide-y divide-slate-100">
            <div v-for="debtor in topDebtors" :key="debtor.id" class="p-4 flex justify-between items-center hover:bg-slate-50 transition-colors">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-indigo-50 text-indigo-600 rounded-lg flex items-center justify-center font-bold">
                  {{ debtor.name.charAt(0) }}
                </div>
                <div>
                  <h4 class="font-medium text-slate-800">{{ debtor.name }}</h4>
                  <p class="text-xs text-slate-500">{{ debtor.invoices_count }} {{ $t('dashboard.invoices') }}</p>
                </div>
              </div>
              <div class="text-end">
                <p class="font-bold text-slate-800">SAR {{ debtor.balance.toLocaleString() }}</p>
                <p class="text-xs text-rose-500 font-medium" v-if="debtor.overdue > 0">SAR {{ debtor.overdue.toLocaleString() }} {{ $t('dashboard.overdue') }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import DashboardFilterBar from '../../components/dashboards/DashboardFilterBar.vue';
import KpiCard from '../../components/dashboards/KpiCard.vue';
import EmptyState from '../../components/dashboards/EmptyState.vue';
import { BriefcaseIcon, AlertCircleIcon, CalendarIcon, CreditCardIcon } from 'lucide-vue-next';
import api from '../../services/api';

const { t } = useI18n();

const loading = ref(true);
const teams = ref([]);
const currentFilters = ref({});

const metrics = ref({
  totalReceivables: 0, receivablesTrend: 0,
  overdueAmount: 0,
  avgCollectionDays: 0,
  unallocatedPayments: 0
});

const topDebtors = ref([]);

const chartData = ref({
  series: [],
  options: {
    chart: { toolbar: { show: false }, fontFamily: 'inherit' },
    colors: ['#3b82f6', '#f59e0b', '#ef4444', '#7f1d1d'],
    plotOptions: { bar: { borderRadius: 4, distributed: true } },
    dataLabels: { enabled: false },
    xaxis: { categories: ['Current', '30 Days', '60 Days', '90+ Days'] },
    yaxis: { labels: { formatter: (val) => val.toLocaleString() } },
    legend: { show: false }
  }
});

const fetchData = async (filters) => {
  currentFilters.value = filters;
  loading.value = true;
  try {
    const { data } = await api.get('/dashboard/ar', { params: filters });
    
    metrics.value = data.metrics;
    topDebtors.value = data.topDebtors;
    
    chartData.value.series = [
      { name: t('dashboard.amount'), data: data.chart.aging }
    ];
  } catch (error) {
    console.error(error);
  } finally {
    loading.value = false;
  }
};

const exportData = () => {
  window.open(`/api/dashboard/ar/export?startDate=${currentFilters.value.startDate}&endDate=${currentFilters.value.endDate}`, '_blank');
};
</script>
