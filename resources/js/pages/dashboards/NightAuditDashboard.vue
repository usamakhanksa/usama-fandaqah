<template>
  <div class="dashboard-page bg-slate-50 min-h-screen pb-10">
    <div class="px-6 max-w-[1600px] mx-auto pt-6">
      <div class="mb-6 flex justify-between items-end">
        <div>
          <h1 class="text-2xl font-bold text-slate-800">{{ $t('dashboard.night_audit_status') }}</h1>
          <p class="text-slate-500 text-sm">{{ $t('dashboard.night_audit_status_desc') }}</p>
        </div>
        <div class="flex gap-3">
          <router-link to="/operations/night-audit" class="px-4 py-2 bg-white border border-slate-200 text-slate-700 rounded-lg hover:bg-slate-50 font-medium transition-colors flex items-center gap-2">
            <HistoryIcon class="w-4 h-4" />
            {{ $t('dashboard.view_history') }}
          </router-link>
          <button @click="triggerRun" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium transition-colors flex items-center gap-2">
            <PlayCircleIcon class="w-4 h-4" />
            {{ $t('dashboard.run_now') }}
          </button>
        </div>
      </div>

      <div v-if="loading" class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div v-for="i in 4" :key="i" class="h-32 bg-slate-200 animate-pulse rounded-xl"></div>
      </div>

      <div v-else>
        <!-- KPI Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <KpiCard 
            :title="$t('dashboard.current_business_date')"
            :value="metrics.business_date"
            :icon="CalendarIcon"
            color="indigo"
          />
          
          <KpiCard 
            :title="$t('dashboard.last_run_time')"
            :value="lastRunFormatted"
            :icon="ClockIcon"
            color="emerald"
          >
            <template #subtitle>
               <div class="text-xs text-slate-500 mt-1 flex items-center gap-1">
                 <span v-if="metrics.last_run" :class="statusColorClass(metrics.last_run.status)" class="font-bold capitalize">{{ metrics.last_run.status }}</span>
                 <span v-else>{{ $t('common.no_data') }}</span>
               </div>
            </template>
          </KpiCard>

          <KpiCard 
            :title="$t('dashboard.next_scheduled_run')"
            :value="nextScheduledFormatted"
            :icon="CalendarClockIcon"
            color="amber"
          >
            <template #subtitle>
              <div class="text-xs text-slate-500 mt-1">
                {{ metrics.auto_enabled ? $t('dashboard.auto_run_enabled') : $t('dashboard.auto_run_disabled') }}
              </div>
            </template>
          </KpiCard>

          <KpiCard 
            :title="$t('dashboard.run_status')"
            :value="overallStatus"
            :icon="ActivityIcon"
            :color="statusColor(overallStatusRaw)"
          />
        </div>

        <!-- Failed Steps Details -->
        <div v-if="hasFailedSteps" class="bg-white rounded-xl shadow-sm border border-rose-100 overflow-hidden mb-8">
          <div class="p-5 border-b border-rose-100 flex justify-between items-center bg-rose-50/50">
            <h3 class="text-lg font-semibold text-rose-800 flex items-center gap-2">
              <AlertCircleIcon class="w-5 h-5 text-rose-500" />
              {{ $t('dashboard.failed_steps_details') }}
            </h3>
          </div>
          <div class="p-5">
             <div v-if="Array.isArray(metrics.needs_attention)">
                <ul class="list-disc pl-5 text-rose-600 space-y-1">
                   <li v-for="(error, index) in metrics.needs_attention" :key="index">{{ error }}</li>
                </ul>
             </div>
             <div v-else-if="typeof metrics.needs_attention === 'object' && metrics.needs_attention !== null">
                <div v-for="(error, key) in metrics.needs_attention" :key="key" class="mb-2 last:mb-0">
                   <strong class="text-rose-700 capitalize">{{ key }}:</strong> <span class="text-rose-600">{{ error }}</span>
                </div>
             </div>
             <div v-else class="text-rose-600">
               {{ metrics.needs_attention }}
             </div>
          </div>
        </div>

        <!-- Recent Logs/Info block (Optional add-on based on requirement for run status indicator) -->
        <div v-if="metrics.last_run" class="bg-white rounded-xl shadow-sm border border-slate-100 p-6 flex flex-col items-center justify-center text-center">
            <div class="w-16 h-16 rounded-full flex items-center justify-center mb-4" :class="metrics.last_run.status === 'success' ? 'bg-emerald-100 text-emerald-600' : (metrics.last_run.status === 'failed' ? 'bg-rose-100 text-rose-600' : 'bg-amber-100 text-amber-600')">
               <CheckCircleIcon v-if="metrics.last_run.status === 'success'" class="w-8 h-8" />
               <XCircleIcon v-else-if="metrics.last_run.status === 'failed'" class="w-8 h-8" />
               <LoaderIcon v-else class="w-8 h-8 animate-spin" />
            </div>
            <h3 class="text-xl font-bold text-slate-800 mb-2">{{ $t('dashboard.latest_run') }}: <span class="capitalize">{{ metrics.last_run.status }}</span></h3>
            <p class="text-slate-500 max-w-md">
               {{ $t('dashboard.run_number') }}: {{ metrics.last_run.run_number || '-' }} <br>
               {{ $t('dashboard.started_at') }}: {{ metrics.last_run.started_at }} <br>
               <span v-if="metrics.last_run.completed_at">{{ $t('dashboard.completed_at') }}: {{ metrics.last_run.completed_at }}</span>
            </p>
        </div>

      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import KpiCard from '../../components/dashboards/KpiCard.vue';
import { 
  CalendarIcon, 
  ClockIcon, 
  CalendarClockIcon, 
  ActivityIcon,
  PlayCircleIcon,
  HistoryIcon,
  AlertCircleIcon,
  CheckCircleIcon,
  XCircleIcon,
  LoaderIcon
} from 'lucide-vue-next';
import api from '../../services/api';

const { t } = useI18n();

const loading = ref(true);

const metrics = ref({
  business_date: null,
  last_run: null,
  pending_runs: 0,
  needs_attention: [],
  next_scheduled_run: null,
  auto_enabled: false
});

const lastRunFormatted = computed(() => {
  if (!metrics.value.last_run || !metrics.value.last_run.started_at) return t('common.no_data');
  const date = new Date(metrics.value.last_run.started_at);
  const pad = (n) => n.toString().padStart(2, '0');
  return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())} ${pad(date.getHours())}:${pad(date.getMinutes())}`;
});

const nextScheduledFormatted = computed(() => {
  if (!metrics.value.next_scheduled_run) return t('common.not_scheduled');
  return metrics.value.next_scheduled_run;
});

const overallStatusRaw = computed(() => {
  if (metrics.value.pending_runs > 0) return 'pending';
  if (metrics.value.last_run) {
     return metrics.value.last_run.status;
  }
  return 'unknown';
});

const overallStatus = computed(() => {
  const status = overallStatusRaw.value;
  return status.charAt(0).toUpperCase() + status.slice(1);
});

const hasFailedSteps = computed(() => {
  if (metrics.value.last_run && metrics.value.last_run.status === 'failed') {
      if (Array.isArray(metrics.value.needs_attention) && metrics.value.needs_attention.length > 0) return true;
      if (typeof metrics.value.needs_attention === 'object' && metrics.value.needs_attention !== null && Object.keys(metrics.value.needs_attention).length > 0) return true;
  }
  return false;
});

const statusColor = (status) => {
  if (status === 'success') return 'emerald';
  if (status === 'failed') return 'rose';
  if (status === 'pending') return 'amber';
  return 'slate';
};

const statusColorClass = (status) => {
  if (status === 'success') return 'text-emerald-600';
  if (status === 'failed') return 'text-rose-600';
  if (status === 'pending') return 'text-amber-600';
  return 'text-slate-600';
};

const fetchData = async () => {
  loading.value = true;
  try {
    const { data } = await api.get('/dashboard/night-audit');
    metrics.value = data;
  } catch (error) {
    console.error(error);
  } finally {
    loading.value = false;
  }
};

const triggerRun = async () => {
   if(!confirm(t('dashboard.confirm_run_audit'))) return;
   
   try {
      // Assuming endpoint exists for manual run, otherwise just alert
      await api.post('/night-audit/run');
      alert(t('dashboard.audit_started'));
      fetchData();
   } catch (error) {
      console.error(error);
      alert(t('dashboard.audit_start_failed'));
   }
}

onMounted(() => {
  fetchData();
});
</script>
