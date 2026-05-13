<template>
  <div class="p-6 bg-slate-50 min-h-screen">
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-2xl font-bold text-slate-800">{{ $t('Qoyod Synchronization') }}</h1>
        <p class="text-slate-500 text-sm mt-1">{{ $t('Sync invoices, payments, and credit notes with Qoyod accounting') }}</p>
      </div>
      <div class="flex gap-3">
        <button 
          @click="showSyncModal = true"
          class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-all shadow-md font-medium"
        >
          <RefreshCw class="w-4 h-4" />
          {{ $t('Trigger Sync') }}
        </button>
      </div>
    </div>

    <!-- Stats/Status Card -->
    <div v-if="lastStatus" class="bg-white border border-slate-200 rounded-xl p-6 mb-6 shadow-sm">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
          <div class="p-3 rounded-full" :class="statusBgClass(lastStatus.status)">
            <CloudSyncIcon v-if="lastStatus.status === 'in_progress'" class="w-6 h-6 text-blue-600 animate-pulse" />
            <CheckCircle2 v-else-if="lastStatus.status === 'completed'" class="w-6 h-6 text-emerald-600" />
            <AlertCircle v-else class="w-6 h-6 text-rose-600" />
          </div>
          <div>
            <p class="text-sm text-slate-500 uppercase tracking-wider font-semibold">{{ $t('Last Sync Status') }}</p>
            <h2 class="text-xl font-bold text-slate-800">
              {{ $t(lastStatus.sync_type) }} - {{ $t(lastStatus.status) }}
            </h2>
            <p class="text-xs text-slate-400 mt-1">
              {{ $t('Started') }}: {{ formatDate(lastStatus.started_at) }} 
              <span v-if="lastStatus.completed_at">| {{ $t('Completed') }}: {{ formatDate(lastStatus.completed_at) }}</span>
            </p>
          </div>
        </div>
        <div class="text-right">
          <div class="text-2xl font-bold text-slate-800">{{ lastStatus.records_synced }}</div>
          <div class="text-xs text-slate-500 uppercase tracking-wider">{{ $t('Records Synced') }}</div>
        </div>
      </div>
    </div>

    <!-- Logs Table -->
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
      <div class="p-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
        <h3 class="font-bold text-slate-800">{{ $t('Synchronization Logs') }}</h3>
      </div>
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-slate-50 border-b border-slate-200">
            <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $t('Sync Type') }}</th>
            <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $t('Status') }}</th>
            <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $t('Records') }}</th>
            <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $t('Started At') }}</th>
            <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $t('Duration') }}</th>
            <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">{{ $t('Actions') }}</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <tr v-for="log in logs.data" :key="log.id" class="hover:bg-slate-50/50 transition-colors">
            <td class="px-6 py-4">
              <div class="font-medium text-slate-800 uppercase text-xs tracking-wide">{{ log.sync_type }}</div>
              <div class="text-[10px] text-slate-400">{{ $t('By') }}: {{ log.triggerer?.name }}</div>
            </td>
            <td class="px-6 py-4">
              <span class="px-2 py-1 rounded-full text-[10px] font-bold uppercase tracking-tighter" :class="statusClass(log.status)">
                {{ log.status }}
              </span>
            </td>
            <td class="px-6 py-4">
              <div class="flex items-center gap-2">
                <span class="text-emerald-600 font-bold">{{ log.records_synced }}</span>
                <span class="text-slate-300">/</span>
                <span class="text-rose-500">{{ log.records_failed }}</span>
              </div>
            </td>
            <td class="px-6 py-4 text-xs text-slate-600">{{ formatDate(log.started_at) }}</td>
            <td class="px-6 py-4 text-xs text-slate-600">{{ getDuration(log) }}</td>
            <td class="px-6 py-4 text-right">
              <div class="flex justify-end gap-2">
                <button v-if="log.status === 'failed'" class="p-1.5 text-slate-400 hover:text-primary transition-colors" :title="$t('Retry')">
                  <RotateCcw class="w-4 h-4" />
                </button>
                <button class="p-1.5 text-slate-400 hover:text-slate-600 transition-colors" :title="$t('View Response')">
                  <FileJson class="w-4 h-4" />
                </button>
              </div>
            </td>
          </tr>
          <tr v-if="logs.data.length === 0">
            <td colspan="6" class="px-6 py-12 text-center text-slate-400">
              <div class="flex flex-col items-center gap-2">
                <CloudSyncIcon class="w-12 h-12 text-slate-200" />
                <p>{{ $t('No synchronization logs found') }}</p>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Sync Modal -->
    <Modal :show="showSyncModal" @close="showSyncModal = false">
      <div class="p-6">
        <h3 class="text-lg font-bold text-slate-800 mb-4">{{ $t('Trigger Qoyod Synchronization') }}</h3>
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ $t('Sync Type') }}</label>
            <select v-model="syncForm.type" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg outline-none focus:ring-2 focus:ring-primary/20 transition-all">
              <option value="full">{{ $t('Full Sync (Recommended)') }}</option>
              <option value="invoices">{{ $t('Invoices Only') }}</option>
              <option value="payments">{{ $t('Payments Only') }}</option>
              <option value="credit_notes">{{ $t('Credit Notes Only') }}</option>
              <option value="accounts">{{ $t('Chart of Accounts Only') }}</option>
            </select>
          </div>
          <div v-if="syncForm.type !== 'accounts'" class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">{{ $t('From Date') }}</label>
              <input v-model="syncForm.from" type="date" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg outline-none focus:ring-2 focus:ring-primary/20 transition-all">
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">{{ $t('To Date') }}</label>
              <input v-model="syncForm.to" type="date" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg outline-none focus:ring-2 focus:ring-primary/20 transition-all">
            </div>
          </div>
        </div>
        <div class="mt-6 flex justify-end gap-3">
          <button @click="showSyncModal = false" class="px-4 py-2 text-slate-600 hover:bg-slate-100 rounded-lg transition-all">{{ $t('Cancel') }}</button>
          <button @click="triggerSync" :disabled="syncForm.processing" class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-all shadow-md font-medium disabled:opacity-50 flex items-center gap-2">
            <RefreshCw class="w-4 h-4" :class="{ 'animate-spin': syncForm.processing }" />
            {{ $t('Start Sync') }}
          </button>
        </div>
      </div>
    </Modal>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import { 
  RefreshCw, CheckCircle2, AlertCircle, CloudSyncIcon, RotateCcw, FileJson 
} from 'lucide-vue-next';
import Modal from '@/Components/Modal.vue';
import dayjs from 'dayjs';

const props = defineProps({
  logs: Object,
  lastStatus: Object,
});

const showSyncModal = ref(false);

const syncForm = useForm({
  type: 'full',
  from: dayjs().subtract(1, 'day').format('YYYY-MM-DD'),
  to: dayjs().format('YYYY-MM-DD'),
});

function triggerSync() {
  syncForm.post(route('finance.qoyod-sync.sync'), {
    onSuccess: () => {
      showSyncModal.value = false;
    }
  });
}

function formatDate(date) {
  return dayjs(date).format('MMM DD, YYYY HH:mm');
}

function getDuration(log) {
  if (!log.completed_at) return '-';
  const start = dayjs(log.started_at);
  const end = dayjs(log.completed_at);
  const diff = end.diff(start, 'second');
  return diff + 's';
}

function statusClass(status) {
  switch (status) {
    case 'completed': return 'bg-emerald-100 text-emerald-600';
    case 'in_progress': return 'bg-blue-100 text-blue-600';
    case 'failed': return 'bg-rose-100 text-rose-600';
    default: return 'bg-slate-100 text-slate-600';
  }
}

function statusBgClass(status) {
  switch (status) {
    case 'completed': return 'bg-emerald-50';
    case 'in_progress': return 'bg-blue-50';
    case 'failed': return 'bg-rose-50';
    default: return 'bg-slate-50';
  }
}
</script>

<style scoped>
.text-primary { color: #e95a54; }
.bg-primary { background-color: #e95a54; }
</style>
