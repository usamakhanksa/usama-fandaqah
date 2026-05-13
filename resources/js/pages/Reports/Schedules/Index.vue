<template>
  <div class="p-6 bg-[#f8f9fa] min-h-full space-y-6">
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold text-[#2a273c]">Report Schedules</h1>
        <p class="text-slate-500">Schedule automatic report delivery by email</p>
      </div>
      <Link href="/reports/report-schedules/create" class="px-4 py-2 bg-[#e95a54] text-white rounded-xl hover:bg-[#d64a45] flex items-center gap-2">
        <PlusIcon class="w-4 h-4" /> Create Schedule
      </Link>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
      <div class="p-6 border-b border-slate-50">
        <h3 class="font-bold text-[#2a273c]">Scheduled Reports</h3>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-left">
          <thead class="bg-slate-50 text-slate-400 text-xs font-bold uppercase">
            <tr>
              <th class="px-6 py-4">Name</th>
              <th class="px-6 py-4">Report Type</th>
              <th class="px-6 py-4">Frequency</th>
              <th class="px-6 py-4">Next Run</th>
              <th class="px-6 py-4">Status</th>
              <th class="px-6 py-4">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <tr v-for="schedule in schedules.data" :key="schedule.id">
              <td class="px-6 py-4 text-sm font-medium text-[#2a273c]">{{ schedule.name }}</td>
              <td class="px-6 py-4 text-sm">{{ schedule.report_type }}</td>
              <td class="px-6 py-4 text-sm">{{ schedule.frequency }}</td>
              <td class="px-6 py-4 text-sm">{{ schedule.next_run_at ? new Date(schedule.next_run_at).toLocaleString() : '-' }}</td>
              <td class="px-6 py-4 text-sm">
                <button @click="toggleSchedule(schedule)" :class="schedule.is_active ? 'text-emerald-600' : 'text-slate-400'">
                  {{ schedule.is_active ? 'Active' : 'Inactive' }}
                </button>
              </td>
              <td class="px-6 py-4 text-sm">
                <div class="flex items-center gap-2">
                  <Link :href="`/reports/report-schedules/${schedule.id}/edit`" class="text-slate-600 hover:underline">Edit</Link>
                  <button @click="runNow(schedule)" class="text-blue-600 hover:underline">Run Now</button>
                  <button @click="deleteSchedule(schedule.id)" class="text-red-600 hover:underline">Delete</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="p-6" v-if="schedules.links">
        <Pagination :links="schedules.links" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { Plus as PlusIcon } from 'lucide-vue-next';
import Pagination from '@/components/Pagination.vue';
import axios from 'axios';

const props = defineProps({
  schedules: Object,
});

const toggleSchedule = async (schedule) => {
  await axios.post(`/reports/report-schedules/${schedule.id}/toggle`);
  schedule.is_active = !schedule.is_active;
};

const runNow = async (schedule) => {
  await axios.post(`/reports/report-schedules/${schedule.id}/run-now`);
  alert('Report has been sent to recipients.');
};

const deleteSchedule = (id) => {
  if (confirm('Are you sure you want to delete this schedule?')) {
    router.delete(`/reports/report-schedules/${id}`);
  }
};
</script>

<style scoped>
.shadow-sm { box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05); }
</style>