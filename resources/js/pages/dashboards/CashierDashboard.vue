<template>
  <div class="dashboard-page bg-slate-50 min-h-screen pb-10">
    <DashboardFilterBar 
      :filters="['businessDate', 'team']" 
      :teams="teams"
      @update="fetchData"
      @refresh="fetchData(currentFilters)"
      @export="exportData"
    />

    <div class="px-6 max-w-[1600px] mx-auto">
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800">{{ $t('dashboard.cashier_dashboard') }}</h1>
        <p class="text-slate-500 text-sm">{{ $t('dashboard.cashier_desc') }}</p>
      </div>

      <div v-if="loading" class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div v-for="i in 4" :key="i" class="h-32 bg-slate-200 animate-pulse rounded-xl"></div>
      </div>

      <div v-else class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
        <KpiCard 
          :title="$t('dashboard.total_collection')"
          :value="metrics.totalCollection"
          prefix="SAR "
          :icon="CreditCardIcon"
          color="emerald"
        />
        <KpiCard 
          :title="$t('dashboard.cash_collected')"
          :value="metrics.cashCollected"
          prefix="SAR "
          :icon="BanknoteIcon"
          color="indigo"
        />
        <KpiCard 
          :title="$t('dashboard.card_collected')"
          :value="metrics.cardCollected"
          prefix="SAR "
          :icon="CreditCardIcon"
          color="blue"
        />
        <KpiCard 
          :title="$t('dashboard.refunds')"
          :value="metrics.refunds"
          prefix="SAR "
          :icon="RefreshCwIcon"
          color="amber"
        />
      </div>

      <!-- Cashier Shifts -->
      <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden mb-8">
        <div class="p-5 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
          <h3 class="text-lg font-semibold text-slate-800">{{ $t('dashboard.active_shifts') }}</h3>
        </div>
        
        <div class="overflow-x-auto">
          <table class="w-full text-start">
            <thead class="bg-slate-50 text-slate-500 text-xs uppercase font-semibold border-b border-slate-200">
              <tr>
                <th class="px-6 py-4 text-start">{{ $t('dashboard.cashier') }}</th>
                <th class="px-6 py-4 text-start">{{ $t('dashboard.shift_open') }}</th>
                <th class="px-6 py-4 text-start">{{ $t('dashboard.transactions') }}</th>
                <th class="px-6 py-4 text-start">{{ $t('dashboard.collection') }}</th>
                <th class="px-6 py-4 text-start">{{ $t('dashboard.status') }}</th>
              </tr>
            </thead>
            <tbody v-if="loading">
              <tr v-for="i in 3" :key="i">
                <td colspan="5" class="px-6 py-4"><div class="h-4 bg-slate-100 rounded animate-pulse w-full"></div></td>
              </tr>
            </tbody>
            <tbody v-else-if="!shifts.length">
              <tr>
                <td colspan="5" class="p-8">
                  <EmptyState :title="$t('dashboard.no_active_shifts')" :description="$t('dashboard.no_active_shifts_desc')" />
                </td>
              </tr>
            </tbody>
            <tbody v-else class="divide-y divide-slate-100">
              <tr v-for="shift in shifts" :key="shift.id" class="hover:bg-slate-50 transition-colors">
                <td class="px-6 py-4 font-medium text-slate-800">{{ shift.user_name }}</td>
                <td class="px-6 py-4 text-slate-500">{{ shift.opened_at }}</td>
                <td class="px-6 py-4 text-slate-800 font-medium">{{ shift.tx_count }}</td>
                <td class="px-6 py-4 text-emerald-600 font-bold">SAR {{ shift.total_collection.toLocaleString() }}</td>
                <td class="px-6 py-4">
                  <span class="px-3 py-1 bg-blue-100 text-blue-700 text-xs font-bold rounded-full uppercase" v-if="shift.status === 'open'">{{ $t('common.open') }}</span>
                  <span class="px-3 py-1 bg-slate-100 text-slate-700 text-xs font-bold rounded-full uppercase" v-else>{{ $t('common.closed') }}</span>
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
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import DashboardFilterBar from '../../components/dashboards/DashboardFilterBar.vue';
import KpiCard from '../../components/dashboards/KpiCard.vue';
import EmptyState from '../../components/dashboards/EmptyState.vue';
import { CreditCardIcon, BanknoteIcon, RefreshCwIcon } from 'lucide-vue-next';
import api from '../../services/api';

const { t } = useI18n();

const loading = ref(true);
const teams = ref([]);
const currentFilters = ref({});

const metrics = ref({
  totalCollection: 0,
  cashCollected: 0,
  cardCollected: 0,
  refunds: 0
});

const shifts = ref([]);

const fetchData = async (filters) => {
  currentFilters.value = filters;
  loading.value = true;
  try {
    const { data } = await api.get('/dashboard/cashier', { params: filters });
    metrics.value = data.metrics;
    shifts.value = data.shifts;
  } catch (error) {
    console.error(error);
  } finally {
    loading.value = false;
  }
};

const exportData = () => {
  window.open(`/api/dashboard/cashier/export?businessDate=${currentFilters.value.businessDate}`, '_blank');
};
</script>
