<template>
  <div class="p-6 bg-[#f8f9fa] min-h-full space-y-6">
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold text-[#2a273c]">{{ isEdit ? 'Edit Report' : 'Create Custom Report' }}</h1>
        <p class="text-slate-500">Build your custom report with selected columns and filters</p>
      </div>
    </div>

    <form @submit.prevent="saveReport">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
          <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
            <h3 class="text-lg font-bold text-[#2a273c] mb-4">Report Details</h3>
            <div class="space-y-4">
              <div>
                <label class="text-xs font-bold text-slate-400 uppercase">Name</label>
                <input v-model="form.name" type="text" required class="w-full border border-slate-200 rounded-lg px-3 py-2 mt-1" />
              </div>
              <div>
                <label class="text-xs font-bold text-slate-400 uppercase">Description</label>
                <textarea v-model="form.description" class="w-full border border-slate-200 rounded-lg px-3 py-2 mt-1" rows="2"></textarea>
              </div>
              <div class="flex items-center gap-2">
                <input v-model="form.is_shared" type="checkbox" id="is_shared" class="rounded" />
                <label for="is_shared" class="text-sm text-slate-600">Share with team</label>
              </div>
            </div>
          </div>

          <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
            <h3 class="text-lg font-bold text-[#2a273c] mb-4">Module & Columns</h3>
            
            <div class="mb-4">
              <label class="text-xs font-bold text-slate-400 uppercase">Module</label>
              <select v-model="form.module" @change="loadColumns" class="w-full border border-slate-200 rounded-lg px-3 py-2 mt-1">
                <option value="reservations">Reservations</option>
                <option value="finance">Finance</option>
                <option value="rooms">Rooms</option>
                <option value="guests">Guests</option>
                <option value="pos">POS</option>
              </select>
            </div>

            <div>
              <label class="text-xs font-bold text-slate-400 uppercase mb-2 block">Selected Columns (Drag to reorder)</label>
              <draggable v-model="form.columns" item-key="key" class="space-y-2">
                <template #item="{element, index}">
                  <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
                    <span class="text-sm">{{ element.label }}</span>
                    <button @click="removeColumn(index)" type="button" class="text-red-500 hover:text-red-700">
                      <XIcon class="w-4 h-4" />
                    </button>
                  </div>
                </template>
              </draggable>
            </div>

            <div class="mt-4">
              <label class="text-xs font-bold text-slate-400 uppercase mb-2 block">Available Columns</label>
              <div class="max-h-60 overflow-y-auto border border-slate-200 rounded-lg p-2">
                <div v-for="col in availableColumns" :key="col.key" class="flex items-center justify-between p-2 hover:bg-slate-50 rounded cursor-pointer" @click="addColumn(col)">
                  <span class="text-sm">{{ col.label }}</span>
                  <PlusIcon class="w-4 h-4 text-slate-400" />
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
            <h3 class="text-lg font-bold text-[#2a273c] mb-4">Sort & Group</h3>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="text-xs font-bold text-slate-400 uppercase">Sort By</label>
                <select v-model="form.sort_by" class="w-full border border-slate-200 rounded-lg px-3 py-2 mt-1">
                  <option :value="null">None</option>
                  <option v-for="col in form.columns" :key="col.key" :value="col.key">{{ col.label }}</option>
                </select>
              </div>
              <div>
                <label class="text-xs font-bold text-slate-400 uppercase">Sort Direction</label>
                <select v-model="form.sort_direction" class="w-full border border-slate-200 rounded-lg px-3 py-2 mt-1">
                  <option value="asc">Ascending</option>
                  <option value="desc">Descending</option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <div class="space-y-6">
          <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
            <h3 class="text-lg font-bold text-[#2a273c] mb-4">Preview</h3>
            <button @click="previewReport" type="button" class="w-full px-4 py-2 bg-slate-100 rounded-lg hover:bg-slate-200 mb-4">
              Preview Report
            </button>
            <div v-if="previewData.length" class="overflow-x-auto">
              <table class="w-full text-xs">
                <thead class="bg-slate-50">
                  <tr>
                    <th v-for="col in form.columns" :key="col.key" class="px-2 py-1 text-left">{{ col.label }}</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(row, i) in previewData" :key="i" class="border-t">
                    <td v-for="col in form.columns" :key="col.key" class="px-2 py-1">{{ row[col.key] ?? row[col.key?.split('.')[1]] ?? '' }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
            <button type="submit" class="w-full px-4 py-2 bg-[#e95a54] text-white rounded-xl hover:bg-[#d64a45]">
              {{ isEdit ? 'Update Report' : 'Save Report' }}
            </button>
          </div>
        </div>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { Plus as PlusIcon, X as XIcon } from 'lucide-vue-next';
import axios from 'axios';
import draggable from 'vue3-draggable';

const props = defineProps({
  report: Object,
});

const isEdit = computed(() => !!props.report);

const form = ref({
  name: props.report?.name || '',
  description: props.report?.description || '',
  module: props.report?.module || 'reservations',
  columns: props.report?.columns || [],
  filters: props.report?.filters || {},
  sort_by: props.report?.sort_by || null,
  sort_direction: props.report?.sort_direction || 'asc',
  is_shared: props.report?.is_shared || false,
});

const availableColumns = ref([]);
const previewData = ref([]);

const loadColumns = async () => {
  try {
    const response = await axios.get(`/reports/custom-reports/columns/${form.value.module}`);
    availableColumns.value = response.data.columns;
  } catch (error) {
    console.error('Error loading columns:', error);
  }
};

onMounted(loadColumns);

const addColumn = (col) => {
  if (!form.value.columns.find(c => c.key === col.key)) {
    form.value.columns.push({ key: col.key, label: col.label });
  }
};

const removeColumn = (index) => {
  form.value.columns.splice(index, 1);
};

const previewReport = async () => {
  try {
    const response = await axios.post('/reports/custom-reports/preview', {
      module: form.value.module,
      columns: form.value.columns,
      filters: form.value.filters,
      sort_by: form.value.sort_by,
      sort_direction: form.value.sort_direction,
    });
    previewData.value = response.data.results;
  } catch (error) {
    console.error('Error previewing report:', error);
  }
};

const saveReport = () => {
  const url = isEdit.value ? `/reports/custom-reports/${props.report.id}` : '/reports/custom-reports';
  const method = isEdit.value ? 'put' : 'post';

  router[method](url, form.value);
};
</script>

<style scoped>
.shadow-sm { box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05); }
</style>