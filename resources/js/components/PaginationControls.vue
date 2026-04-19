<template>
  <div class="flex items-center justify-between text-xs text-slate-500 py-3">
    <div>Showing {{ meta.from || 0 }} to {{ meta.to || 0 }} out of {{ meta.total || 0 }} results found</div>
    <div class="flex items-center gap-1">
      <button class="px-2 py-1 rounded border" :disabled="!meta.current_page || meta.current_page===1" @click="$emit('page', meta.current_page-1)">Prev</button>
      <button v-for="p in pages" :key="p" @click="$emit('page', p)" class="w-7 h-7 rounded" :class="meta.current_page===p ? 'bg-rose-500 text-white':'border'">{{ p }}</button>
      <button class="px-2 py-1 rounded border" :disabled="!meta.last_page || meta.current_page===meta.last_page" @click="$emit('page', meta.current_page+1)">Next</button>
    </div>
  </div>
</template>
<script setup>
import { computed } from 'vue';
const props = defineProps({ meta: { type: Object, default: () => ({}) } });
const pages = computed(() => Array.from({ length: props.meta.last_page || 1 }, (_, i) => i + 1).slice(0, 5));
</script>
