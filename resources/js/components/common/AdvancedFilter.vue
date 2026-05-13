<template>
  <div class="bg-white rounded-[32px] border border-slate-100 shadow-sm p-6 mb-6">
    <div class="flex flex-wrap gap-4">
      <!-- Search -->
      <div class="flex-1 min-w-[200px] relative">
        <input 
          v-model="localFilters.search" 
          type="text" 
          :placeholder="t('common.search_placeholder')"
          class="w-full bg-slate-50 border-none rounded-2xl py-3 pl-12 pr-4 text-sm font-medium focus:ring-2 ring-rose-200 transition-all"
          @input="debounceSearch"
        />
        <Search class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" />
      </div>

      <!-- Date Range -->
      <div class="flex items-center gap-2 bg-slate-50 rounded-2xl px-4 py-2">
        <Calendar class="w-4 h-4 text-slate-400" />
        <input v-model="localFilters.date_from" type="date" class="bg-transparent border-none text-sm font-bold text-slate-700 focus:ring-0" />
        <span class="text-slate-300 font-bold">→</span>
        <input v-model="localFilters.date_to" type="date" class="bg-transparent border-none text-sm font-bold text-slate-700 focus:ring-0" />
      </div>

      <!-- Additional Filters Slot -->
      <slot name="extra" :filters="localFilters"></slot>

      <!-- Actions -->
      <div class="flex gap-2">
        <button 
          @click="resetFilters" 
          class="px-6 py-3 bg-slate-50 text-slate-500 rounded-2xl font-bold hover:bg-slate-100 transition-all flex items-center gap-2"
        >
          <RotateCcw class="w-4 h-4" />
          {{ t('common.reset') }}
        </button>
        
        <button 
          @click="$emit('filter', localFilters)" 
          class="px-8 py-3 bg-slate-900 text-white rounded-2xl font-bold hover:bg-rose-600 shadow-lg shadow-slate-200 transition-all flex items-center gap-2"
        >
          <Filter class="w-4 h-4" />
          {{ t('common.apply') }}
        </button>

        <!-- Export -->
        <button 
          v-if="canExport"
          @click="$emit('export')" 
          class="px-4 py-3 bg-emerald-50 text-emerald-600 rounded-2xl font-bold hover:bg-emerald-100 transition-all flex items-center gap-2"
        >
          <Download class="w-4 h-4" />
          {{ t('common.export') }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute, useRouter } from 'vue-router';
import { Search, Calendar, Filter, RotateCcw, Download } from 'lucide-vue-next';
const props = defineProps({
  initialFilters: { type: Object, default: () => ({}) },
  canExport: { type: Boolean, default: false }
});

const emit = defineEmits(['filter', 'export']);
const { t } = useI18n();
const route = useRoute();
const router = useRouter();

const localFilters = ref({
  search: '',
  date_from: '',
  date_to: '',
  status: '',
  ...props.initialFilters
});

const debounce = (fn, delay) => {
  let timeoutId;
  return (...args) => {
    if (timeoutId) clearTimeout(timeoutId);
    timeoutId = setTimeout(() => fn(...args), delay);
  };
};

const debounceSearch = debounce(() => {
  emit('filter', localFilters.value);
  syncToUrl();
}, 500);

// URL Persistence
const syncToUrl = () => {
  const query = { ...route.query, ...localFilters.value };
  // Remove empty values
  Object.keys(query).forEach(key => {
    if (!query[key]) delete query[key];
  });
  router.push({ query });
};

const resetFilters = () => {
  localFilters.value = {
    search: '',
    date_from: '',
    date_to: '',
    status: '',
    ...props.initialFilters
  };
  emit('filter', localFilters.value);
  syncToUrl();
};

// Initialize from URL
onMounted(() => {
  Object.keys(localFilters.value).forEach(key => {
    if (route.query[key]) {
      localFilters.value[key] = route.query[key];
    }
  });
  emit('filter', localFilters.value);
});

watch(localFilters, () => {
  // We don't auto-emit everything to avoid too many requests, 
  // except for search which is debounced
}, { deep: true });
</script>
