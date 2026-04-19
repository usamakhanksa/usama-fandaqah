<template>
  <div class="card p-3 w-full lg:w-48">
    <div v-for="s in statuses" :key="s.label" class="text-xs font-semibold mb-2" :style="{ color: s.color }">{{ s.label }}</div>
  </div>
</template>
<script setup>
const statuses = [
  { label: 'Paid', color: '#22c55e' },
  { label: 'Pending', color: '#f97316' },
  { label: 'Unpaid', color: '#8b5cf6' },
  { label: 'Failed', color: '#ef4444' },
  { label: 'Refund', color: '#facc15' },
];
</script>
