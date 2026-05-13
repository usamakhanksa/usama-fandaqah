<template>
  <div class="flex flex-col items-center justify-center p-12 text-center bg-white rounded-xl border border-slate-100 border-dashed">
    <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-4 text-slate-400">
      <component v-if="icon" :is="icon" class="w-8 h-8" />
    </div>
    <h3 class="text-lg font-semibold text-slate-800 mb-1">{{ title }}</h3>
    <p class="text-slate-500 text-sm max-w-sm">{{ description }}</p>
    <slot name="action"></slot>
  </div>
</template>

<script setup>
import { InboxIcon } from 'lucide-vue-next';

defineProps({
  icon: { type: Object, default: InboxIcon },
  title: { type: String, default: 'No Data Available' },
  description: { type: String, default: 'There is no data to display for the selected filters.' }
});
</script>
