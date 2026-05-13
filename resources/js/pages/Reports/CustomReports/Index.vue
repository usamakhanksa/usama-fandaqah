<template>
  <div class="p-6 bg-[#f8f9fa] min-h-full space-y-6">
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold text-[#2a273c]">Custom Reports</h1>
        <p class="text-slate-500">Build and manage custom reports for your hotel</p>
      </div>
      <Link href="/reports/custom-reports/create" class="px-4 py-2 bg-[#e95a54] text-white rounded-xl hover:bg-[#d64a45] flex items-center gap-2">
        <PlusIcon class="w-4 h-4" /> Create Report
      </Link>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
      <div class="p-6 border-b border-slate-50">
        <h3 class="font-bold text-[#2a273c]">Saved Reports</h3>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-left">
          <thead class="bg-slate-50 text-slate-400 text-xs font-bold uppercase">
            <tr>
              <th class="px-6 py-4">Name</th>
              <th class="px-6 py-4">Module</th>
              <th class="px-6 py-4">Shared</th>
              <th class="px-6 py-4">Created</th>
              <th class="px-6 py-4">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <tr v-for="report in reports.data" :key="report.id">
              <td class="px-6 py-4 text-sm font-medium text-[#2a273c]">{{ report.name }}</td>
              <td class="px-6 py-4 text-sm">{{ report.module }}</td>
              <td class="px-6 py-4 text-sm">
                <span :class="report.is_shared ? 'text-emerald-600' : 'text-slate-400'">
                  {{ report.is_shared ? 'Yes' : 'No' }}
                </span>
              </td>
              <td class="px-6 py-4 text-sm">{{ new Date(report.created_at).toLocaleDateString() }}</td>
              <td class="px-6 py-4 text-sm">
                <div class="flex items-center gap-2">
                  <Link :href="`/reports/custom-reports/${report.id}`" class="text-[#e95a54] hover:underline">View</Link>
                  <Link :href="`/reports/custom-reports/${report.id}/edit`" class="text-slate-600 hover:underline">Edit</Link>
                  <button @click="deleteReport(report.id)" class="text-red-600 hover:underline">Delete</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="p-6" v-if="reports.links">
        <Pagination :links="reports.links" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { Plus as PlusIcon } from 'lucide-vue-next';
import Pagination from '@/components/Pagination.vue';

const props = defineProps({
  reports: Object,
});

const deleteReport = (id) => {
  if (confirm('Are you sure you want to delete this report?')) {
    router.delete(`/reports/custom-reports/${id}`);
  }
};
</script>

<style scoped>
.shadow-sm { box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05); }
</style>