<template>
  <div class="grid lg:grid-cols-6 md:grid-cols-3 grid-cols-2 gap-3">
    <div v-for="c in cards" :key="c.key" class="card p-3">
      <div class="text-xs text-slate-400">{{ c.title }}</div>
      <div class="text-xl font-bold">{{ metrics[c.key] ?? 0 }}<span v-if="c.suffix" class="text-sm">{{ c.suffix }}</span></div>
      <div class="text-[11px] text-slate-400">Yearly status <span :class="(metrics.delta?.[c.key] ?? 0) >= 0 ? 'text-emerald-500' : 'text-rose-500'">{{ (metrics.delta?.[c.key] ?? 0) }}%</span></div>
    </div>
  </div>
</template>
<script setup>
defineProps({ metrics: { type: Object, default: () => ({}) } });
const cards = [
  { key: 'total_rooms', title: 'Total Rooms' },
  { key: 'available_rooms', title: 'Available Rooms' },
  { key: 'not_ready_rooms', title: 'Not Ready Rooms' },
  { key: 'booked_rooms', title: 'Booked Rooms' },
  { key: 'unit_occupancy', title: 'Unit Occupancy' },
  { key: 'total_guests', title: 'Total Guests' },
];
</script>
