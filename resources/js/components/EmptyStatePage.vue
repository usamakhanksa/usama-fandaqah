<template>
  <section class="rounded-xl border border-sage/30 bg-white p-8">
    <h1 class="text-2xl font-bold text-navy">{{ title }}</h1>
    <p class="mt-3 text-sage">{{ summaryText }}</p>

    <div v-if="loading" class="mt-6 text-sm text-slate-500">Loading module data...</div>
    <div v-else-if="rows.length" class="mt-6 overflow-x-auto">
      <table class="min-w-full divide-y divide-slate-200 text-sm">
        <thead class="bg-slate-50">
          <tr>
            <th v-for="header in headers" :key="header" class="px-3 py-2 text-left font-semibold text-slate-700">{{ header }}</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <tr v-for="(row, index) in rows" :key="index">
            <td v-for="header in headers" :key="`${index}-${header}`" class="px-3 py-2 text-slate-600">{{ row[header] ?? '-' }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <button class="mt-6 rounded-md bg-coral px-4 py-2 text-sm font-semibold text-white" type="button">
      Add new
    </button>
  </section>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import api from '../services/api';

defineProps({
  title: {
    type: String,
    required: true,
  },
});

const route = useRoute();
const loading = ref(true);
const count = ref(0);
const rows = ref([]);
const headers = ref([]);

const summaryText = computed(() => {
  if (loading.value) return 'Checking seeded module data...';
  if (count.value > 0) return `${count.value} seeded records available for this module.`;
  return 'No records yet. Start by adding your first item.';
});

onMounted(async () => {
  try {
    const { data } = await api.get('/module-data', { params: { path: route.path.replace(/^\//, '') } });
    count.value = data.count || 0;
    rows.value = data.rows || [];
    headers.value = rows.value.length ? Object.keys(rows.value[0]) : [];
  } catch (error) {
    rows.value = [];
    headers.value = [];
  } finally {
    loading.value = false;
  }
});
</script>
