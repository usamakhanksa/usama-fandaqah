<template>
  <div class="flex items-center justify-between group">
    <div class="flex items-center gap-4">
      <div class="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center text-slate-400 group-hover:bg-[#f2f0eb] group-hover:text-[#2a273c] transition-colors">
        <component :is="iconComponent" class="w-5 h-5" />
      </div>
      <span class="text-slate-500 font-medium group-hover:text-[#2a273c] transition-colors">{{ label }}</span>
    </div>
    <span class="font-bold text-[#2a273c]" :style="{ color: color }">{{ value }}</span>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  label: String,
  value: [String, Number],
  icon: String,
  color: { type: String, default: '#2a273c' }
});

const iconComponent = computed(() => {
  // Simple mapping to SVG icons (using basic shapes for brevity)
  const icons = {
    rooms: 'svg-rooms',
    guests: 'svg-guests',
    profit: 'svg-profit'
  };
  return icons[props.icon] || 'div';
});
</script>

<style scoped>
/* Inline SVGs as simple placeholders */
svg-rooms { display: block; border-radius: 2px; border: 2px solid currentColor; margin: 4px; }
</style>
