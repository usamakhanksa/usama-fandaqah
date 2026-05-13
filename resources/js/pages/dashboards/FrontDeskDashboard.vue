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
        <h1 class="text-2xl font-bold text-slate-800">{{ $t('dashboard.front_desk_dashboard') }}</h1>
        <p class="text-slate-500 text-sm">{{ $t('dashboard.front_desk_desc') }}</p>
      </div>

      <div v-if="loading" class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div v-for="i in 4" :key="i" class="h-32 bg-slate-200 animate-pulse rounded-xl"></div>
      </div>

      <!-- KPI Cards -->
      <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <router-link to="/reservations?status=confirmed&date=today" class="block h-full transition-transform hover:-translate-y-1">
          <KpiCard 
            :title="$t('dashboard.arrivals_today')"
            :value="metrics.arrivals_today"
            :icon="LogInIcon"
            color="emerald"
            class="h-full"
          />
        </router-link>
        
        <router-link to="/reservations?status=checked_in&date=today" class="block h-full transition-transform hover:-translate-y-1">
          <KpiCard 
            :title="$t('dashboard.departures_today')"
            :value="metrics.departures_today"
            :icon="LogOutIcon"
            color="amber"
            class="h-full"
          />
        </router-link>

        <KpiCard 
          :title="$t('dashboard.in_house_guests')"
          :value="metrics.in_house"
          :icon="UsersIcon"
          color="indigo"
        />
        <KpiCard 
          :title="$t('dashboard.walk_ins')"
          :value="metrics.walk_ins"
          :icon="FootprintsIcon"
          color="blue"
        />
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        
        <!-- Pending Check-ins -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden flex flex-col h-full">
          <div class="p-5 border-b border-slate-100 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-slate-800 flex items-center gap-2">
              <LogInIcon class="w-5 h-5 text-emerald-500" />
              {{ $t('dashboard.pending_checkins') }}
              <span class="bg-emerald-100 text-emerald-700 text-xs font-bold px-2 py-0.5 rounded-full">{{ lists.pending_checkins?.length || 0 }}</span>
            </h3>
          </div>
          <div class="overflow-y-auto flex-1 max-h-[400px]">
            <table class="w-full text-left border-collapse">
              <thead class="sticky top-0 bg-slate-50 z-10">
                <tr class="text-slate-500 text-xs uppercase tracking-wider">
                  <th class="px-6 py-4 font-semibold">{{ $t('table.guest') }}</th>
                  <th class="px-6 py-4 font-semibold">{{ $t('table.unit') }}</th>
                  <th class="px-6 py-4 font-semibold text-right">{{ $t('table.action') }}</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <tr v-if="!lists.pending_checkins?.length">
                  <td colspan="3" class="px-6 py-8 text-center text-slate-400 text-sm">{{ $t('common.no_data') }}</td>
                </tr>
                <tr v-for="res in lists.pending_checkins" :key="res.id" class="hover:bg-slate-50 transition-colors">
                  <td class="px-6 py-4">
                    <div class="font-medium text-slate-800">{{ res.guest?.first_name }} {{ res.guest?.last_name }}</div>
                    <div class="text-xs text-slate-500">{{ res.code }}</div>
                  </td>
                  <td class="px-6 py-4 font-medium">{{ res.unit?.number || '-' }}</td>
                  <td class="px-6 py-4 text-right">
                    <router-link :to="`/reservations/${res.id}`" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">View</router-link>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Pending Check-outs -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden flex flex-col h-full">
          <div class="p-5 border-b border-slate-100 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-slate-800 flex items-center gap-2">
              <LogOutIcon class="w-5 h-5 text-amber-500" />
              {{ $t('dashboard.pending_checkouts') }}
              <span class="bg-amber-100 text-amber-700 text-xs font-bold px-2 py-0.5 rounded-full">{{ lists.pending_checkouts?.length || 0 }}</span>
            </h3>
          </div>
          <div class="overflow-y-auto flex-1 max-h-[400px]">
            <table class="w-full text-left border-collapse">
              <thead class="sticky top-0 bg-slate-50 z-10">
                <tr class="text-slate-500 text-xs uppercase tracking-wider">
                  <th class="px-6 py-4 font-semibold">{{ $t('table.guest') }}</th>
                  <th class="px-6 py-4 font-semibold">{{ $t('table.unit') }}</th>
                  <th class="px-6 py-4 font-semibold text-right">{{ $t('table.action') }}</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <tr v-if="!lists.pending_checkouts?.length">
                  <td colspan="3" class="px-6 py-8 text-center text-slate-400 text-sm">{{ $t('common.no_data') }}</td>
                </tr>
                <tr v-for="res in lists.pending_checkouts" :key="res.id" class="hover:bg-slate-50 transition-colors">
                  <td class="px-6 py-4">
                    <div class="font-medium text-slate-800">{{ res.guest?.first_name }} {{ res.guest?.last_name }}</div>
                    <div class="text-xs text-slate-500">{{ res.code }}</div>
                  </td>
                  <td class="px-6 py-4 font-medium">{{ res.unit?.number || '-' }}</td>
                  <td class="px-6 py-4 text-right">
                    <router-link :to="`/reservations/${res.id}`" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">View</router-link>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- No Show Candidates -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden flex flex-col h-full">
          <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-rose-50/50">
            <h3 class="text-lg font-semibold text-rose-800 flex items-center gap-2">
              <ClockIcon class="w-5 h-5 text-rose-500" />
              {{ $t('dashboard.no_show_candidates') }}
              <span class="bg-rose-100 text-rose-700 text-xs font-bold px-2 py-0.5 rounded-full">{{ lists.no_show_candidates?.length || 0 }}</span>
            </h3>
          </div>
          <div class="overflow-y-auto flex-1 max-h-[400px]">
            <table class="w-full text-left border-collapse">
              <thead class="sticky top-0 bg-slate-50 z-10">
                <tr class="text-slate-500 text-xs uppercase tracking-wider">
                  <th class="px-6 py-4 font-semibold">{{ $t('table.guest') }}</th>
                  <th class="px-6 py-4 font-semibold">{{ $t('table.check_in') }}</th>
                  <th class="px-6 py-4 font-semibold text-right">{{ $t('table.action') }}</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <tr v-if="!lists.no_show_candidates?.length">
                  <td colspan="3" class="px-6 py-8 text-center text-slate-400 text-sm">{{ $t('common.no_data') }}</td>
                </tr>
                <tr v-for="res in lists.no_show_candidates" :key="res.id" class="hover:bg-slate-50 transition-colors">
                  <td class="px-6 py-4">
                    <div class="font-medium text-slate-800">{{ res.guest?.first_name }} {{ res.guest?.last_name }}</div>
                    <div class="text-xs text-slate-500">{{ res.code }}</div>
                  </td>
                  <td class="px-6 py-4 text-sm">{{ res.check_in }}</td>
                  <td class="px-6 py-4 text-right">
                    <router-link :to="`/reservations/${res.id}`" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">View</router-link>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Unpaid Balances -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden flex flex-col h-full">
          <div class="p-5 border-b border-slate-100 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-slate-800 flex items-center gap-2">
              <CreditCardIcon class="w-5 h-5 text-indigo-500" />
              {{ $t('dashboard.unpaid_balances') }}
              <span class="bg-indigo-100 text-indigo-700 text-xs font-bold px-2 py-0.5 rounded-full">{{ lists.unpaid_balances?.length || 0 }}</span>
            </h3>
          </div>
          <div class="overflow-y-auto flex-1 max-h-[400px]">
            <table class="w-full text-left border-collapse">
              <thead class="sticky top-0 bg-slate-50 z-10">
                <tr class="text-slate-500 text-xs uppercase tracking-wider">
                  <th class="px-6 py-4 font-semibold">{{ $t('table.guest') }}</th>
                  <th class="px-6 py-4 font-semibold">{{ $t('table.balance') }}</th>
                  <th class="px-6 py-4 font-semibold text-right">{{ $t('table.action') }}</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <tr v-if="!lists.unpaid_balances?.length">
                  <td colspan="3" class="px-6 py-8 text-center text-slate-400 text-sm">{{ $t('common.no_data') }}</td>
                </tr>
                <tr v-for="res in lists.unpaid_balances" :key="res.id" class="hover:bg-slate-50 transition-colors">
                  <td class="px-6 py-4">
                    <div class="font-medium text-slate-800">{{ res.guest?.first_name }} {{ res.guest?.last_name }}</div>
                    <div class="text-xs text-slate-500">{{ res.unit?.number || '-' }}</div>
                  </td>
                  <td class="px-6 py-4 font-medium text-rose-600" dir="ltr">SAR {{ Number(res.balance).toLocaleString() }}</td>
                  <td class="px-6 py-4 text-right">
                    <router-link :to="`/reservations/${res.id}`" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">View</router-link>
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
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import DashboardFilterBar from '../../components/dashboards/DashboardFilterBar.vue';
import KpiCard from '../../components/dashboards/KpiCard.vue';
import { LogInIcon, LogOutIcon, UsersIcon, FootprintsIcon, ClockIcon, CreditCardIcon } from 'lucide-vue-next';
import api from '../../services/api';

const { t } = useI18n();

const loading = ref(true);
const teams = ref([]);
const currentFilters = ref({});

const metrics = ref({ arrivals_today: 0, departures_today: 0, in_house: 0, walk_ins: 0, vip_arrivals: 0 });
const lists = ref({ pending_checkins: [], pending_checkouts: [], no_show_candidates: [], unpaid_balances: [] });

const fetchData = async (filters) => {
  currentFilters.value = filters;
  loading.value = true;
  try {
    const { data } = await api.get('/dashboard/front-desk', { params: filters });
    metrics.value = data.metrics;
    lists.value = data.lists;
  } catch (error) {
    console.error(error);
  } finally {
    loading.value = false;
  }
};

const exportData = () => {
  window.open(`/api/dashboard/front-desk/export?startDate=${currentFilters.value.startDate}&endDate=${currentFilters.value.endDate}`, '_blank');
};

onMounted(async () => {
  try {
    const { data } = await api.get('/user-groups/teams');
    teams.value = data.data || [];
  } catch(e) {}
});
</script>
