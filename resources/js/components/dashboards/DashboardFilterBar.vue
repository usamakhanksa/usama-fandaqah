<template>
  <div class="sticky top-0 z-40 bg-white/80 backdrop-blur-md border-b border-slate-200 py-3 px-6 shadow-sm mb-6 flex flex-wrap gap-4 items-center justify-between">
    <div class="flex items-center gap-4 flex-wrap flex-1">
      
      <!-- Date Range -->
      <div v-if="filters.includes('dateRange')" class="filter-group">
        <label class="filter-label">{{ $t('dashboard.date_range') }}</label>
        <div class="flex items-center border border-slate-200 rounded-lg overflow-hidden bg-white">
          <input type="date" v-model="localFilters.startDate" class="filter-input border-e border-slate-200">
          <span class="px-2 text-slate-400 text-sm">-</span>
          <input type="date" v-model="localFilters.endDate" class="filter-input">
        </div>
      </div>

      <!-- Business Date -->
      <div v-if="filters.includes('businessDate')" class="filter-group">
        <label class="filter-label">{{ $t('dashboard.business_date') }}</label>
        <input type="date" v-model="localFilters.businessDate" class="filter-input border border-slate-200 rounded-lg bg-white">
      </div>

      <!-- Teams/Hotels -->
      <div v-if="filters.includes('team')" class="filter-group">
        <label class="filter-label">{{ $t('dashboard.property') }}</label>
        <select v-model="localFilters.teamId" class="filter-input border border-slate-200 rounded-lg bg-white min-w-[150px]">
          <option value="">{{ $t('common.all') }}</option>
          <option v-for="team in teams" :key="team.id" :value="team.id">{{ team.name }}</option>
        </select>
      </div>
      
      <button @click="applyFilters" class="btn-primary mt-5">
        {{ $t('common.apply') }}
      </button>
    </div>

    <!-- Actions -->
    <div class="flex items-center gap-2 mt-5 sm:mt-0">
      <button @click="$emit('export')" class="btn-outline flex items-center gap-2">
        <DownloadIcon class="w-4 h-4" />
        {{ $t('common.export') }}
      </button>
      <button @click="$emit('refresh')" class="btn-icon">
        <RefreshCwIcon class="w-5 h-5 text-slate-500" />
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import { DownloadIcon, RefreshCwIcon } from 'lucide-vue-next';

const props = defineProps({
  filters: { type: Array, default: () => ['dateRange', 'team'] },
  teams: { type: Array, default: () => [] }
});

const emit = defineEmits(['update', 'export', 'refresh']);

const today = new Date().toISOString().split('T')[0];
const lastMonth = new Date();
lastMonth.setMonth(lastMonth.getMonth() - 1);

const localFilters = ref({
  startDate: lastMonth.toISOString().split('T')[0],
  endDate: today,
  businessDate: today,
  teamId: ''
});

const applyFilters = () => {
  emit('update', localFilters.value);
};
</script>

<style scoped>
.filter-group {
  display: flex;
  flex-direction: column;
  gap: 4px;
}
.filter-label {
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  color: #64748b;
  letter-spacing: 0.5px;
}
.filter-input {
  padding: 8px 12px;
  font-size: 13px;
  color: #334155;
  outline: none;
}
.filter-input:focus {
  border-color: #3b82f6;
}
.btn-primary {
  background: #2563eb;
  color: white;
  padding: 8px 16px;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 500;
  transition: all 0.2s;
}
.btn-primary:hover {
  background: #1d4ed8;
}
.btn-outline {
  background: white;
  color: #475569;
  border: 1px solid #cbd5e1;
  padding: 8px 16px;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 500;
  transition: all 0.2s;
}
.btn-outline:hover {
  background: #f8fafc;
  color: #1e293b;
}
.btn-icon {
  background: white;
  border: 1px solid #cbd5e1;
  padding: 8px;
  border-radius: 8px;
  transition: all 0.2s;
}
.btn-icon:hover {
  background: #f8fafc;
}
</style>
