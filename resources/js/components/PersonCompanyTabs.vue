<template>
  <div class="card p-3 w-36">
    <button class="w-full text-left px-3 py-2 rounded-lg" :class="tab==='person' ? 'bg-slate-100 font-medium':''" @click="$emit('update:tab','person')">Person</button>
    <button class="w-full text-left px-3 py-2 rounded-lg" :class="tab==='company' ? 'bg-slate-100 font-medium':''" @click="$emit('update:tab','company')">Company</button>
  </div>
</template>
<script setup>
defineProps({ tab: String });
defineEmits(['update:tab']);
</script>
