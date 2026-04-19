<template>
<div class="p-6 space-y-6">
  <div class="card p-4 flex justify-between items-center">
    <div class="flex items-center gap-2">
      <router-link to="/channel-manager" class="text-xl text-slate-400 hover:text-slate-600">←</router-link>
      <h1 class="text-2xl font-bold">Channel Reservations</h1>
    </div>
    <p class="text-sm text-slate-400">Home > Channel Manager > Reservations</p>
  </div>

  <div class="grid md:grid-cols-2 gap-4">
    <div class="card p-4 bg-rose-50 border-rose-100 flex items-center justify-between">
      <div><p class="text-slate-500 text-sm">Total Reservations</p><p class="text-2xl font-bold">{{ rows.length }}</p></div>
      <div class="text-4xl">📅</div>
    </div>
    <div class="card p-4 bg-emerald-50 border-emerald-100 flex items-center justify-between">
      <div><p class="text-slate-500 text-sm">Total Revenue</p><p class="text-2xl font-bold">{{ totalRevenue }} SR</p></div>
      <div class="text-4xl">💰</div>
    </div>
  </div>

  <div class="card p-4 space-y-4">
    <div class="flex flex-wrap gap-2">
      <input type="text" placeholder="Search by ID, Guest name..." class="input py-1 max-w-xs">
      <select class="input py-1 max-w-[120px]"><option>Channel</option></select>
      <select class="input py-1 max-w-[120px]"><option>Status</option></select>
      <button class="btn-primary py-1 px-4 ml-auto">Refresh</button>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-sm text-left">
        <thead class="bg-slate-50 border-b">
          <tr>
            <th class="p-3 font-semibold text-slate-500">Booking ID</th>
            <th class="p-3 font-semibold text-slate-500">Guest</th>
            <th class="p-3 font-semibold text-slate-500">Channel</th>
            <th class="p-3 font-semibold text-slate-500">Check In</th>
            <th class="p-3 font-semibold text-slate-500">Check Out</th>
            <th class="p-3 font-semibold text-slate-500">Amount</th>
            <th class="p-3 font-semibold text-slate-500">Status</th>
            <th class="p-3 font-semibold text-slate-500">Unit Number</th>
            <th class="p-3 font-semibold text-slate-500">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="row in rows" :key="row.id" class="border-b hover:bg-slate-50 transition-colors">
            <td class="p-3 font-medium text-rose-500">#{{ row.booking_id }}</td>
            <td class="p-3">{{ row.guest_name }}</td>
            <td class="p-3"><span class="px-2 py-0.5 bg-slate-100 rounded-full text-[11px] uppercase">{{ channels[row.channel_id] || 'OTA' }}</span></td>
            <td class="p-3">{{ row.check_in }}</td>
            <td class="p-3">{{ row.check_out }}</td>
            <td class="p-3 font-semibold">{{ row.amount }} SR</td>
            <td class="p-3"><span class="px-2 py-0.5 rounded-full text-[11px] font-bold" :class="statusColor(row.status)">{{ row.status }}</span></td>
            <td class="p-3 font-bold">{{ row.unit_number || 'N/A' }}</td>
            <td class="p-3"><button class="text-slate-400 hover:text- rose-500">Select Unit</button></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
</template>

<script setup>
import { onMounted, ref, computed } from 'vue';
import api from '../services/api';

const rows = ref([]);
const channels = ref({});

const totalRevenue = computed(() => rows.value.reduce((acc, r) => acc + Number(r.amount), 0).toLocaleString());

const statusColor = (s) => {
  if (s === 'Confirmed') return 'bg-emerald-100 text-emerald-700';
  if (s === 'Modified') return 'bg-blue-100 text-blue-700';
  return 'bg-rose-100 text-rose-700';
};

onMounted(async () => {
  const [resRows, resChannels] = await Promise.all([
    api.get('/channel-manager/reservations'),
    api.get('/pos/stores'), // Reuse channel-like lookup if direct OTA channel list not found
  ]);
  rows.value = resRows.data;
  resChannels.data.forEach(c => channels.value[c.id] = c.name);
});
</script>
