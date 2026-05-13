<template>
  <div class="p-6 bg-[#f8f9fa] min-h-full space-y-6">
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold text-[#2a273c]">{{ report.name }}</h1>
        <p class="text-slate-500">{{ report.description }}</p>
      </div>
      <div class="flex items-center gap-3">
        <button @click="exportReport" class="px-4 py-2 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 flex items-center gap-2">
          <DownloadIcon class="w-4 h-4" /> Export
        </button>
        <Link :href="`/reports/custom-reports/${report.id}/edit`" class="px-4 py-2 bg-[#e95a54] text-white rounded-xl hover:bg-[#d64a45]">
          Edit Report
        </Link>
      </div>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
      <div class="p-6 border-b border-slate-50">
        <h3 class="font-bold text-[#2a273c]">Report Results</h3>
      </div>
      
      <div v-if="loading" class="flex justify-center py-20">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-[#e95a54]"></div>
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full text-left">
          <thead class="bg-slate-50 text-slate-400 text-xs font-bold uppercase">
            <tr>
              <th v-for="col in columns" :key="col.key" class="px-6 py-4">{{ col.label }}</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <tr v-for="(row, i) in results.data" :key="i">
              <td v-for="col in columns" :key="col.key" class="px-6 py-4 text-sm">
                {{ formatValue(row, col.key) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Download as DownloadIcon } from 'lucide-vue-next';
import axios from 'axios';

const props = defineProps({
  report: Object,
  columns: Array,
});

const loading = ref(true);
const results = ref({ data: [] });
const columns = ref(props.columns || []);

const fetchResults = async () => {
  loading.value = true;
  try {
    const response = await axios.get(`/reports/custom-reports/${props.report.id}/run`);
    results.value = response.data.results;
    if (response.data.columns) {
      columns.value = response.data.columns;
    }
  } catch (error) {
    console.error('Error fetching report results:', error);
  } finally {
    loading.value = false;
  }
};

onMounted(fetchResults);

const formatValue = (row, key) => {
  if (key.includes('.')) {
    const parts = key.split('.');
    let val = row;
    for (const part of parts) {
      val = val?.[part] ?? '';
    }
    return val;
  }
  return row[key] ?? '';
};

const exportReport = () => {
  window.location.href = `/reports/custom-reports/${props.report.id}/export?format=csv`;
};
</script>

<style scoped>
.shadow-sm { box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05); }
</style>