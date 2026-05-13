<template>
  <div class="dashboard-page bg-slate-50 min-h-screen pb-10">
    <DashboardFilterBar 
      :filters="['date', 'team']" 
      :teams="teams"
      @update="fetchData"
      @refresh="fetchData(currentFilters)"
    />

    <div class="px-6 max-w-[1600px] mx-auto pt-6">
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800">{{ $t('dashboard.integration_health') }}</h1>
        <p class="text-slate-500 text-sm">{{ $t('dashboard.integration_health_desc') }}</p>
      </div>

      <div v-if="loading" class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div v-for="i in 6" :key="i" class="h-32 bg-slate-200 animate-pulse rounded-xl"></div>
      </div>

      <div v-else>
        <!-- Integration Status Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-8">
          <div v-for="integration in metrics.integrations" :key="integration.id" class="bg-white rounded-xl shadow-sm border border-slate-100 p-5 flex flex-col hover:border-indigo-100 transition-colors">
            <div class="flex justify-between items-start mb-4">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center" :class="getIconColorClass(integration)">
                  <NetworkIcon class="w-5 h-5" />
                </div>
                <div>
                  <h3 class="font-bold text-slate-800">{{ integration.name }}</h3>
                  <div class="flex items-center gap-1.5 mt-0.5">
                    <span class="w-2 h-2 rounded-full" :class="integration.status === 'connected' ? 'bg-emerald-500' : 'bg-rose-500'"></span>
                    <span class="text-xs font-medium capitalize" :class="integration.status === 'connected' ? 'text-emerald-700' : 'text-rose-700'">
                      {{ integration.status }}
                    </span>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="flex-1 text-sm text-slate-600 space-y-2 mb-4">
               <div class="flex justify-between">
                 <span class="text-slate-400">{{ $t('dashboard.last_sync') }}:</span>
                 <span class="font-medium" :class="!integration.last_sync_at ? 'text-slate-400' : ''">{{ formatDateTime(integration.last_sync_at) }}</span>
               </div>
               <div class="flex justify-between">
                 <span class="text-slate-400">{{ $t('dashboard.failed_syncs') }}:</span>
                 <span class="font-bold" :class="integration.failed_syncs > 0 ? 'text-rose-600' : 'text-emerald-600'">{{ integration.failed_syncs }}</span>
               </div>
            </div>

            <div class="mt-auto grid grid-cols-2 gap-2">
               <button @click="testConnection(integration)" class="px-3 py-1.5 bg-slate-50 border border-slate-200 text-slate-700 text-xs font-medium rounded hover:bg-slate-100 transition-colors">
                 {{ $t('dashboard.test_connection') }}
               </button>
               <button @click="retryFailed(integration)" :disabled="integration.failed_syncs === 0" class="px-3 py-1.5 bg-indigo-50 border border-indigo-100 text-indigo-700 text-xs font-medium rounded hover:bg-indigo-100 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                 {{ $t('dashboard.retry_failed') }}
               </button>
            </div>
          </div>
          
          <!-- Empty state if no integrations -->
          <div v-if="!metrics.integrations.length" class="col-span-full py-10 text-center text-slate-500 border-2 border-dashed border-slate-200 rounded-xl">
             {{ $t('dashboard.no_integrations_found') }}
          </div>
        </div>

        <!-- Recent Integration Errors -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden mb-8">
          <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50">
            <h3 class="text-lg font-semibold text-slate-800 flex items-center gap-2">
              <AlertOctagonIcon class="w-5 h-5 text-rose-500" />
              {{ $t('dashboard.recent_integration_errors') }}
              <span v-if="metrics.recent_errors.length" class="bg-rose-100 text-rose-700 text-xs font-bold px-2 py-0.5 rounded-full">{{ metrics.recent_errors.length }}</span>
            </h3>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead class="bg-white border-b border-slate-100">
                <tr class="text-slate-500 text-xs uppercase tracking-wider">
                  <th class="px-6 py-4 font-semibold">{{ $t('table.date') }}</th>
                  <th class="px-6 py-4 font-semibold">{{ $t('table.integration') }}</th>
                  <th class="px-6 py-4 font-semibold">{{ $t('table.endpoint') }}</th>
                  <th class="px-6 py-4 font-semibold">{{ $t('table.response_code') }}</th>
                  <th class="px-6 py-4 font-semibold text-right">{{ $t('table.action') }}</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <tr v-if="!metrics.recent_errors.length">
                  <td colspan="5" class="px-6 py-8 text-center text-slate-400 text-sm">{{ $t('common.no_data') }}</td>
                </tr>
                <tr v-for="err in metrics.recent_errors" :key="err.id" class="hover:bg-slate-50 transition-colors">
                  <td class="px-6 py-4 text-sm text-slate-600 whitespace-nowrap">{{ formatDateTime(err.created_at) }}</td>
                  <td class="px-6 py-4 font-medium text-slate-800 whitespace-nowrap">
                     <span class="px-2 py-1 bg-slate-100 rounded text-xs">{{ err.integration_name }}</span>
                  </td>
                  <td class="px-6 py-4 text-sm text-slate-600 break-all max-w-xs">
                     <span class="font-mono text-xs bg-slate-50 px-1 py-0.5 rounded border border-slate-100">{{ err.method }}</span>
                     {{ err.endpoint }}
                  </td>
                  <td class="px-6 py-4">
                     <span class="px-2 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-700">
                       {{ err.response_code }}
                     </span>
                  </td>
                  <td class="px-6 py-4 text-right">
                    <button class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">{{ $t('common.view') }}</button>
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
import { 
  NetworkIcon,
  AlertOctagonIcon
} from 'lucide-vue-next';
import api from '../../services/api';

const { t } = useI18n();

const loading = ref(true);
const teams = ref([]);
const currentFilters = ref({});

const metrics = ref({
  integrations: [],
  recent_errors: []
});

const getIconColorClass = (integration) => {
   if (integration.status === 'connected') {
      return 'bg-emerald-50 text-emerald-600 border border-emerald-100';
   }
   return 'bg-rose-50 text-rose-600 border border-rose-100';
};

const formatDateTime = (dateStr) => {
   if (!dateStr) return t('common.never');
   const date = new Date(dateStr);
   if(isNaN(date)) return dateStr;
   const pad = (n) => n.toString().padStart(2, '0');
   return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())} ${pad(date.getHours())}:${pad(date.getMinutes())}`;
};

const fetchData = async (filters) => {
  currentFilters.value = filters || currentFilters.value;
  loading.value = true;
  try {
    const { data } = await api.get('/dashboard/integration-health', { params: currentFilters.value });
    metrics.value = data;
  } catch (error) {
    console.error(error);
  } finally {
    loading.value = false;
  }
};

const testConnection = async (integration) => {
   try {
      await api.post(`/integrations/${integration.id}/test`);
      alert(t('dashboard.connection_success'));
      fetchData();
   } catch(e) {
      alert(t('dashboard.connection_failed'));
   }
};

const retryFailed = async (integration) => {
   if(!confirm(t('dashboard.confirm_retry_failed'))) return;
   try {
      await api.post(`/integrations/${integration.id}/retry`);
      alert(t('dashboard.retry_initiated'));
      fetchData();
   } catch(e) {
      alert(t('dashboard.retry_failed'));
   }
};

onMounted(async () => {
  try {
    const { data } = await api.get('/user-groups/teams');
    teams.value = data.data || [];
  } catch(e) {}
  // Will be fetched automatically by DashboardFilterBar if it emits update,
  // but to be safe, fetch it initially:
  // fetchData({});
});
</script>
