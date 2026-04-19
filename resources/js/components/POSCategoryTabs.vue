<template>
  <div class="flex gap-2 overflow-x-auto pb-1">
    <button v-for="tab in tabs" :key="tab.id || 0" class="px-3 py-1.5 rounded-lg text-xs whitespace-nowrap" :class="activeId===tab.id ? 'bg-black text-white' : 'bg-white border border-slate-200'" @click="$emit('select', tab)">{{ tab.name }}</button>
  </div>
</template>
<script setup>
defineProps({ tabs: Array, activeId: Number });
defineEmits(['select']);
</script>
