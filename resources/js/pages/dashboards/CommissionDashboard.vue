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
        <h1 class="text-2xl font-bold text-slate-800">{{ $t('dashboard.commission_dashboard') }}</h1>
        <p class="text-slate-500 text-sm">{{ $t('dashboard.commission_desc') }}</p>
      </div>

      <div v-if="loading" class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div v-for="i in 3" :key="i" class="h-32 bg-slate-200 animate-pulse rounded-xl"></div>
      </div>

      <div v-else class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-8">
        <KpiCard 
          :title="$t('dashboard.total_commissions')"
          :value="metrics.totalCommissions"
          prefix="SAR "
          :icon="BriefcaseIcon"
          color="indigo"
        />
        <KpiCard 
          :title="$t('dashboard.pending_commissions')"
          :value="metrics.pendingCommissions"
          prefix="SAR "
          :icon="AlertCircleIcon"
          color="amber"
        />
        <KpiCard 
          :title="$t('dashboard.paid_commissions')"
          :value="metrics.paidCommissions"
          prefix="SAR "
          :icon="CheckCircleIcon"
          color="emerald"
        />
      </div>

      <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden mb-8">
        <div class="p-5 border-b border-slate-100 bg-slate-50">
          <h3 class="text-lg font-semibold text-slate-800">{{ $t('dashboard.top_agents') }}</h3>
        </div>
        
        <div class="overflow-x-auto">
          <table class="w-full text-start">
            <thead class="bg-slate-50 text-slate-500 text-xs uppercase font-semibold border-b border-slate-200">
              <tr>
                <th class="px-6 py-4 text-start">{{ $t('dashboard.agent_name') }}</th>
                <th class="px-6 py-4 text-start">{{ $t('dashboard.bookings') }}</th>
                <th class="px-6 py-4 text-start">{{ $t('dashboard.revenue_generated') }}</th>
                <th class="px-6 py-4 text-start">{{ $t('dashboard.commission_earned') }}</th>
              </tr>
            </thead>
            <tbody v-if="loading">
              <tr v-for="i in 3" :key="i">
                <td colspan="4" class="px-6 py-4"><div class="h-4 bg-slate-100 rounded animate-pulse w-full"></div></td>
              </tr>
            </tbody>
            <tbody v-else-if="!agents.length">
              <tr>
                <td colspan="4" class="p-8">
                  <EmptyState :title="$t('dashboard.no_agents')" />
                </td>
              </tr>
            </tbody>
            <tbody v-else class="divide-y divide-slate-100">
              <tr v-for="agent in agents" :key="agent.id" class="hover:bg-slate-50 transition-colors">
                <td class="px-6 py-4 font-medium text-slate-800">{{ agent.name }}</td>
                <td class="px-6 py-4 text-slate-500">{{ agent.bookings_count }}</td>
                <td class="px-6 py-4 text-slate-800 font-medium">SAR {{ agent.revenue.toLocaleString() }}</td>
                <td class="px-6 py-4 text-emerald-600 font-bold">SAR {{ agent.commission.toLocaleString() }}</td>
              </tr>
            </tbody>
          </table>
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
import { BriefcaseIcon, AlertCircleIcon, CheckCircleIcon } from 'lucide-vue-next';
import api from '../../services/api';

const { t } = useI18n();

const loading = ref(true);
const teams = ref([]);
const currentFilters = ref({});

const metrics = ref({
  totalCommissions: 0,
  pendingCommissions: 0,
  paidCommissions: 0
});

const agents = ref([]);

const fetchData = async (filters) => {
  currentFilters.value = filters;
  loading.value = true;
  try {
    const { data } = await api.get('/dashboard/commissions', { params: filters });
    metrics.value = data.metrics;
    agents.value = data.agents;
  } catch (error) {
    console.error(error);
  } finally {
    loading.value = false;
  }
};

const exportData = () => {
  window.open(`/api/dashboard/commissions/export?startDate=${currentFilters.value.startDate}&endDate=${currentFilters.value.endDate}`, '_blank');
};
</script>
