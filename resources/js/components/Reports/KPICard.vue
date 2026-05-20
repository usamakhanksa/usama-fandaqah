<template>
  <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
    <p class="text-slate-500 text-sm font-medium">{{ title }}</p>
    <h3 :class="['text-3xl font-bold mt-1', color]">
      {{ formattedValue }} <span class="text-sm font-normal text-slate-400">{{ unit }}</span>
    </h3>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  title: String,
  value: [Number, String],
  unit: String,
  color: String
});

const formattedValue = computed(() => {
  if (props.value === null || props.value === undefined) return '0';
  return typeof props.value === 'number' ? props.value.toLocaleString() : props.value;
});
</script>
