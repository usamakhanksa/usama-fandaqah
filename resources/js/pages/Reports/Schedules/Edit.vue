<template>
  <div class="p-6 bg-[#f8f9fa] min-h-full space-y-6">
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold text-[#2a273c]">Edit Schedule</h1>
        <p class="text-slate-500">Update report schedule settings</p>
      </div>
    </div>

    <form @submit.prevent="saveSchedule">
      <div class="max-w-2xl space-y-6">
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
          <h3 class="text-lg font-bold text-[#2a273c] mb-4">Schedule Details</h3>
          <div class="space-y-4">
            <div>
              <label class="text-xs font-bold text-slate-400 uppercase">Name</label>
              <input v-model="form.name" type="text" required class="w-full border border-slate-200 rounded-lg px-3 py-2 mt-1" />
            </div>
            <div>
              <label class="text-xs font-bold text-slate-400 uppercase">Report Type</label>
              <select v-model="form.report_type" class="w-full border border-slate-200 rounded-lg px-3 py-2 mt-1">
                <option value="daily">Daily Report</option>
                <option value="occupancy">Occupancy Report</option>
                <option value="revenue">Revenue Report</option>
                <option value="adr_revpar">ADR & RevPAR Report</option>
                <option value="custom">Custom Report</option>
              </select>
            </div>
            <div v-if="form.report_type === 'custom'">
              <label class="text-xs font-bold text-slate-400 uppercase">Custom Report</label>
              <select v-model="form.custom_report_id" class="w-full border border-slate-200 rounded-lg px-3 py-2 mt-1">
                <option :value="null">Select Report</option>
                <option v-for="r in customReports" :key="r.id" :value="r.id">{{ r.name }}</option>
              </select>
            </div>
          </div>
        </div>

        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
          <h3 class="text-lg font-bold text-[#2a273c] mb-4">Schedule Settings</h3>
          <div class="space-y-4">
            <div>
              <label class="text-xs font-bold text-slate-400 uppercase">Frequency</label>
              <select v-model="form.frequency" class="w-full border border-slate-200 rounded-lg px-3 py-2 mt-1">
                <option value="daily">Daily</option>
                <option value="weekly">Weekly</option>
                <option value="monthly">Monthly</option>
              </select>
            </div>
            <div v-if="form.frequency === 'weekly'">
              <label class="text-xs font-bold text-slate-400 uppercase">Day of Week</label>
              <select v-model="form.day_of_week" class="w-full border border-slate-200 rounded-lg px-3 py-2 mt-1">
                <option :value="0">Sunday</option>
                <option :value="1">Monday</option>
                <option :value="2">Tuesday</option>
                <option :value="3">Wednesday</option>
                <option :value="4">Thursday</option>
                <option :value="5">Friday</option>
                <option :value="6">Saturday</option>
              </select>
            </div>
            <div v-if="form.frequency === 'monthly'">
              <label class="text-xs font-bold text-slate-400 uppercase">Day of Month</label>
              <input v-model="form.day_of_month" type="number" min="1" max="31" class="w-full border border-slate-200 rounded-lg px-3 py-2 mt-1" />
            </div>
            <div>
              <label class="text-xs font-bold text-slate-400 uppercase">Time</label>
              <input v-model="form.time" type="time" class="w-full border border-slate-200 rounded-lg px-3 py-2 mt-1" />
            </div>
          </div>
        </div>

        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
          <h3 class="text-lg font-bold text-[#2a273c] mb-4">Recipients & Format</h3>
          <div class="space-y-4">
            <div>
              <label class="text-xs font-bold text-slate-400 uppercase">Recipients (Email addresses)</label>
              <div class="flex flex-wrap gap-2 mt-2">
                <span v-for="(email, i) in form.recipients" :key="i" class="inline-flex items-center gap-1 px-3 py-1 bg-slate-100 rounded-full text-sm">
                  {{ email }}
                  <button @click="removeRecipient(i)" type="button" class="text-red-500">&times;</button>
                </span>
              </div>
              <div class="flex gap-2 mt-2">
                <input v-model="newEmail" type="email" placeholder="Add email" class="flex-1 border border-slate-200 rounded-lg px-3 py-2" />
                <button @click="addRecipient" type="button" class="px-4 py-2 bg-slate-100 rounded-lg hover:bg-slate-200">Add</button>
              </div>
            </div>
            <div>
              <label class="text-xs font-bold text-slate-400 uppercase">Format</label>
              <select v-model="form.format" class="w-full border border-slate-200 rounded-lg px-3 py-2 mt-1">
                <option value="pdf">PDF</option>
                <option value="excel">Excel</option>
                <option value="both">Both PDF & Excel</option>
              </select>
            </div>
          </div>
        </div>

        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
          <button type="submit" class="w-full px-4 py-2 bg-[#e95a54] text-white rounded-xl hover:bg-[#d64a45]">
            Save Schedule
          </button>
        </div>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
  schedule: Object,
});

const customReports = ref([]);
const newEmail = ref('');

const form = ref({
  name: props.schedule?.name || '',
  report_type: props.schedule?.report_type || 'daily',
  custom_report_id: props.schedule?.custom_report_id || null,
  frequency: props.schedule?.frequency || 'daily',
  day_of_week: props.schedule?.day_of_week ?? 1,
  day_of_month: props.schedule?.day_of_month ?? 1,
  time: props.schedule?.time || '08:00:00',
  recipients: props.schedule?.recipients || [],
  format: props.schedule?.format || 'pdf',
});

onMounted(async () => {
  const response = await axios.get('/reports/custom-reports');
  customReports.value = response.data.reports?.data || [];
});

const addRecipient = () => {
  if (newEmail.value && !form.value.recipients.includes(newEmail.value)) {
    form.value.recipients.push(newEmail.value);
    newEmail.value = '';
  }
};

const removeRecipient = (index) => {
  form.value.recipients.splice(index, 1);
};

const saveSchedule = () => {
  router.put(`/reports/report-schedules/${props.schedule.id}`, form.value);
};
</script>

<style scoped>
.shadow-sm { box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05); }
</style>