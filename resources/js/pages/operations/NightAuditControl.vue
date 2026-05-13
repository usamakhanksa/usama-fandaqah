<template>
  <div class="night-audit-control bg-slate-50 min-h-screen pb-10 px-6">
    <div class="max-w-4xl mx-auto py-10">
      <div class="mb-8 flex justify-between items-start">
        <div>
          <h1 class="text-3xl font-bold text-slate-800">Night Audit Control</h1>
          <p class="text-slate-500 mt-2">Manage and monitor the daily business date closure and financial freezing.</p>
        </div>
        <div class="bg-indigo-600 text-white px-4 py-2 rounded-lg font-bold shadow-lg">
          Business Date: {{ businessDate || 'Not Set' }}
        </div>
      </div>

      <!-- Setup State (If business date is missing) -->
      <div v-if="!businessDate && !loading" class="bg-white p-8 rounded-2xl shadow-sm border border-slate-100 text-center">
        <div class="w-20 h-20 bg-indigo-50 text-indigo-600 rounded-full flex items-center justify-center mx-auto mb-6">
          <CalendarIcon class="w-10 h-10" />
        </div>
        <h2 class="text-2xl font-bold text-slate-800 mb-4">Initialize Business Date</h2>
        <p class="text-slate-500 mb-8 max-w-md mx-auto">
          The night audit engine requires an initial business date to start tracking daily closures.
        </p>
        <div class="flex gap-4 justify-center">
          <input type="date" v-model="initDate" class="border border-slate-200 rounded-lg px-4 py-2 outline-none focus:ring-2 focus:ring-indigo-500">
          <button @click="initializeDate" :disabled="!initDate" class="bg-indigo-600 text-white px-6 py-2 rounded-lg font-bold hover:bg-indigo-700 disabled:opacity-50">
            Initialize Date
          </button>
        </div>
      </div>

      <!-- Main Control Area -->
      <div v-else class="space-y-6">
        
        <!-- Pre-flight Checks -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
          <div class="p-6 border-b border-slate-100 flex justify-between items-center">
            <h3 class="text-lg font-bold text-slate-800">Pre-flight Readiness</h3>
            <button @click="checkPreflight" class="text-indigo-600 text-sm font-bold hover:underline">
              Refresh Checks
            </button>
          </div>
          <div class="p-6">
            <div v-if="preflightLoading" class="flex items-center gap-4 text-slate-400 animate-pulse">
              <Loader2Icon class="w-5 h-5 animate-spin" />
              Running diagnostics...
            </div>
            <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <div class="check-card" :class="preflight.counts.pending_checkins > 0 ? 'border-rose-100 bg-rose-50' : 'border-emerald-100 bg-emerald-50'">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Arrivals</p>
                <div class="flex items-center justify-between">
                  <span class="text-2xl font-bold" :class="preflight.counts.pending_checkins > 0 ? 'text-rose-600' : 'text-emerald-600'">
                    {{ preflight.counts.pending_checkins }} Pending
                  </span>
                  <CheckCircle2Icon v-if="preflight.counts.pending_checkins === 0" class="w-6 h-6 text-emerald-500" />
                  <AlertCircleIcon v-else class="w-6 h-6 text-rose-500" />
                </div>
              </div>

              <div class="check-card" :class="preflight.counts.pending_checkouts > 0 ? 'border-rose-100 bg-rose-50' : 'border-emerald-100 bg-emerald-50'">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Departures</p>
                <div class="flex items-center justify-between">
                  <span class="text-2xl font-bold" :class="preflight.counts.pending_checkouts > 0 ? 'text-rose-600' : 'text-emerald-600'">
                    {{ preflight.counts.pending_checkouts }} Pending
                  </span>
                  <CheckCircle2Icon v-if="preflight.counts.pending_checkouts === 0" class="w-6 h-6 text-emerald-500" />
                  <AlertCircleIcon v-else class="w-6 h-6 text-rose-500" />
                </div>
              </div>

              <div class="check-card" :class="preflight.counts.open_shifts > 0 ? 'border-rose-100 bg-rose-50' : 'border-emerald-100 bg-emerald-50'">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Cashier Shifts</p>
                <div class="flex items-center justify-between">
                  <span class="text-2xl font-bold" :class="preflight.counts.open_shifts > 0 ? 'text-rose-600' : 'text-emerald-600'">
                    {{ preflight.counts.open_shifts }} Open
                  </span>
                  <CheckCircle2Icon v-if="preflight.counts.open_shifts === 0" class="w-6 h-6 text-emerald-500" />
                  <AlertCircleIcon v-else class="w-6 h-6 text-rose-500" />
                </div>
              </div>
            </div>

            <div v-if="preflight.errors.length" class="mt-6 p-4 bg-rose-50 border border-rose-100 rounded-xl">
              <h4 class="text-rose-700 font-bold text-sm mb-2">Blockers detected:</h4>
              <ul class="text-rose-600 text-sm space-y-1 list-disc list-inside">
                <li v-for="err in preflight.errors" :key="err">{{ err }}</li>
              </ul>
            </div>
          </div>
        </div>

        <!-- Run Controls -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8 text-center">
          <div v-if="running" class="space-y-6">
            <Loader2Icon class="w-16 h-16 animate-spin text-indigo-600 mx-auto" />
            <h3 class="text-xl font-bold text-slate-800">Night Audit in Progress...</h3>
            <p class="text-slate-500">Executing sequential data freezing and business date advancement.</p>
            
            <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
               <div class="bg-indigo-600 h-full transition-all duration-500" :style="{ width: progress + '%' }"></div>
            </div>
          </div>

          <div v-else>
            <h3 class="text-xl font-bold text-slate-800 mb-2">Ready to Close Business Day?</h3>
            <p class="text-slate-500 mb-8">This will finalize all transactions for {{ businessDate }} and advance the system date.</p>
            <button 
              @click="startAudit" 
              :disabled="!preflight.can_run"
              class="bg-indigo-600 text-white px-10 py-4 rounded-2xl font-bold text-lg shadow-xl shadow-indigo-100 hover:bg-indigo-700 disabled:opacity-50 disabled:shadow-none"
            >
              Start Night Audit
            </button>
          </div>
        </div>

        <!-- History / Last Log -->
        <!-- History Table -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
           <div class="p-6 border-b border-slate-100 flex justify-between items-center">
             <h3 class="text-lg font-bold text-slate-800">Night Audit History (Last 30 Days)</h3>
           </div>
           <div class="overflow-x-auto">
             <table class="w-full text-left text-sm">
               <thead class="bg-slate-50 text-slate-500 font-bold">
                 <tr>
                   <th class="px-6 py-4">Business Date</th>
                   <th class="px-6 py-4">Run #</th>
                   <th class="px-6 py-4">Status</th>
                   <th class="px-6 py-4">Triggered By</th>
                   <th class="px-6 py-4">Results</th>
                   <th class="px-6 py-4 text-right">Actions</th>
                 </tr>
               </thead>
               <tbody class="divide-y divide-slate-100">
                 <tr v-for="log in history" :key="log.id" :class="log.run_number > 1 ? 'bg-slate-50/50' : ''">
                   <td class="px-6 py-4 font-bold text-slate-700">{{ log.business_date }}</td>
                   <td class="px-6 py-4">
                     <span v-if="log.run_number > 1" class="bg-amber-100 text-amber-700 px-2 py-0.5 rounded text-xs font-bold">
                       Rerun #{{ log.run_number }}
                     </span>
                     <span v-else class="text-slate-400">#1</span>
                   </td>
                   <td class="px-6 py-4">
                     <span :class="log.status === 'completed' ? 'text-emerald-600' : 'text-rose-600'" class="font-bold capitalize">
                       {{ log.status }}
                     </span>
                   </td>
                   <td class="px-6 py-4 text-slate-500">{{ log.triggered_by }}</td>
                   <td class="px-6 py-4 text-slate-500">
                     {{ log.noshows_flagged }} NS | {{ log.transactions_frozen }} TX
                   </td>
                   <td class="px-6 py-4 text-right">
                     <button 
                       v-if="log.status === 'completed' && canRerun(log.business_date)"
                       @click="handleRerun(log)" 
                       class="text-indigo-600 font-bold hover:underline"
                     >
                       Rerun
                     </button>
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
import { CalendarIcon, Loader2Icon, CheckCircle2Icon, AlertCircleIcon } from 'lucide-vue-next';
import api from '../../services/api';
import Swal from 'sweetalert2';

const businessDate = ref('');
const loading = ref(true);
const preflightLoading = ref(false);
const running = ref(false);
const progress = ref(0);
const initDate = ref('');
const lastLog = ref(null);
const history = ref([]);

const preflight = ref({
  can_run: false,
  errors: [],
  counts: { pending_checkins: 0, pending_checkouts: 0, open_shifts: 0 }
});

const fetchStatus = async () => {
  loading.value = true;
  try {
    const { data } = await api.get('/night-audit/status');
    businessDate.value = data.business_date;
    lastLog.value = data.last_log;
    history.value = data.history;
  } finally {
    loading.value = false;
  }
};

const checkPreflight = async () => {
  preflightLoading.value = true;
  try {
    const { data } = await api.get('/night-audit/preflight');
    preflight.value = data;
  } finally {
    preflightLoading.value = false;
  }
};

const initializeDate = async () => {
  try {
    await api.post('/night-audit/init-date', { date: initDate.value });
    await fetchStatus();
    await checkPreflight();
  } catch (e) {
    Swal.fire('Error', e.response?.data?.message || 'Failed to initialize date', 'error');
  }
};

const startAudit = async () => {
  const result = await Swal.fire({
    title: 'Start Night Audit?',
    text: "This will freeze all financial data for the current business date.",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Yes, proceed',
    confirmButtonColor: '#4f46e5'
  });

  if (!result.isConfirmed) return;

  running.value = true;
  progress.value = 10;
  
  try {
    // Simulated progress while waiting for backend
    const interval = setInterval(() => {
      if (progress.value < 90) progress.value += 5;
    }, 1000);

    const { data } = await api.post('/night-audit/run');
    clearInterval(interval);
    progress.value = 100;
    
    Swal.fire('Success', data.message, 'success');
    await fetchStatus();
    await checkPreflight();
  } catch (e) {
    Swal.fire('Audit Failed', e.response?.data?.message || 'Critical failure during audit sequencing', 'error');
  } finally {
    running.value = false;
  }
};

const canRerun = (date) => {
  if (!businessDate.value) return false;
  const target = new Date(date);
  const current = new Date(businessDate.value);
  const diffDays = Math.ceil((current - target) / (1000 * 60 * 60 * 24));
  return diffDays >= 1 && diffDays <= 30;
};

const handleRerun = async (log) => {
  const target = new Date(log.business_date);
  const current = new Date(businessDate.value);
  const diffDays = Math.ceil((current - target) / (1000 * 60 * 60 * 24));

  let confirmText = "This will recalculate revenue and occupancy for this date.";
  let showReason = false;

  if (diffDays >= 8) {
    confirmText = "This is a historical rerun (8-30 days). A mandatory reason is required.";
    showReason = true;
  } else if (diffDays >= 2) {
    confirmText = "Warning: You are rerunning an audit from several days ago. Ensure you have the proper authorization.";
  }

  const { value: reason, isConfirmed } = await Swal.fire({
    title: `Rerun Audit for ${log.business_date}?`,
    text: confirmText,
    icon: 'warning',
    input: showReason ? 'text' : undefined,
    inputPlaceholder: 'Enter reason for historical rerun',
    inputValidator: (value) => {
      if (showReason && !value) {
        return 'You need to provide a reason!'
      }
    },
    showCancelButton: true,
    confirmButtonText: 'Yes, Rerun',
    confirmButtonColor: '#4f46e5'
  });

  if (!isConfirmed) return;

  running.value = true;
  progress.value = 10;
  
  try {
    const { data } = await api.post(`/night-audit/rerun/${log.id}`, { reason });
    progress.value = 100;
    Swal.fire('Success', data.message, 'success');
    await fetchStatus();
  } catch (e) {
    Swal.fire('Rerun Failed', e.response?.data?.message || 'Failure during rerun', 'error');
  } finally {
    running.value = false;
  }
};

onMounted(async () => {
  await fetchStatus();
  if (businessDate.value) {
    await checkPreflight();
  }
});
</script>

<style scoped>
.check-card {
  padding: 1.25rem;
  border-radius: 1rem;
  border: 1px solid;
}
</style>
