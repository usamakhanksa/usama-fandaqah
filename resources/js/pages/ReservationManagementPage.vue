<template>
  <div class="p-4 space-y-4">
    <div class="card p-4">
      <h1 class="text-xl font-bold">Reservations Management</h1>
      <p class="text-xs text-slate-400">Home &gt; Reservations Management</p>
    </div>

    <div class="card p-4 grid md:grid-cols-5 gap-3">
      <input v-model="filters.search" class="input md:col-span-2" placeholder="Search by room number / booking id / guest">
      <input v-model="filters.from" type="date" class="input">
      <input v-model="filters.to" type="date" class="input">
      <select v-model="filters.status" class="input">
        <option value="">All Status</option>
        <option value="confirmed">Confirmed</option>
        <option value="pending">Pending</option>
      </select>
    </div>

    <div class="grid lg:grid-cols-5 gap-3">
      <div class="card p-4" v-for="card in metrics" :key="card.label">
        <p class="text-xs text-slate-500">{{ card.label }}</p>
        <p class="text-2xl font-bold mt-1">{{ card.value }}</p>
      </div>
    </div>

    <div class="card p-4 space-y-3">
      <div class="flex justify-between items-center">
        <div class="flex items-center gap-2 text-xs text-slate-500">
          <span>SORT: A-Z</span>
          <span>•</span>
          <span>ROOM TYPE: ALL</span>
        </div>
        <div class="flex gap-2">
          <button class="btn-outline" @click="exportCsv">Export</button>
          <router-link to="/reservations/create" class="btn-primary">Add New Reservation</router-link>
        </div>
      </div>

      <div class="overflow-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="text-left text-slate-500 border-b">
              <th class="py-3">#</th>
              <th>Booking Number</th>
              <th>Visitor Name</th>
              <th>Unit Number</th>
              <th>Arrive</th>
              <th>Depart</th>
              <th>Price Per Day</th>
              <th>Status</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in rows" :key="row.id" class="border-b last:border-b-0">
              <td class="py-3">{{ row.id }}</td>
              <td>{{ row.code }}</td>
              <td>{{ row.guest?.name }}</td>
              <td>{{ row.room?.number }}</td>
              <td>{{ row.check_in }}</td>
              <td>{{ row.check_out }}</td>
              <td>{{ row.room?.price_per_day }} SAR</td>
              <td>
                <span class="px-3 py-1 rounded-full text-xs" :class="row.reservation_status_id === 2 ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'">
                  {{ row.reservation_status_id === 2 ? 'Confirmed' : 'Pending' }}
                </span>
              </td>
              <td>
                <router-link :to="`/reservations/management/${row.booking_id || row.id}`" class="text-rose-500">View</router-link>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="flex justify-between items-center text-xs text-slate-500">
        <p>Showing {{ rows.length }} rows</p>
        <button class="btn-outline" @click="load">Refresh</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue';
import api from '../services/api';

const filters = reactive({ search: '', from: '', to: '', status: '' });
const rows = ref([]);

const metrics = computed(() => {
  const revenue = rows.value.reduce((sum, r) => sum + Number(r.room?.price_per_day || 0), 0);
  const confirmed = rows.value.filter((r) => r.reservation_status_id === 2).length;
  const pending = rows.value.length - confirmed;
  const bookedRooms = new Set(rows.value.map((r) => r.room_id)).size;

  return [
    { label: 'Total Revenue', value: `${revenue.toLocaleString()} SAR` },
    { label: 'Total Rent', value: `${revenue.toLocaleString()} SAR` },
    { label: 'Pending Bookings', value: pending },
    { label: 'Confirmed', value: confirmed },
    { label: 'Booked Rooms', value: bookedRooms },
  ];
});

const load = async () => {
  const { data } = await api.get('/reservations', { params: { search: filters.search } });
  rows.value = (data.data || []).map((row) => ({ ...row, booking_id: row.booking?.id || row.id }));
};

const exportCsv = () => {
  const headers = ['Booking Number', 'Visitor Name', 'Unit Number', 'Arrive', 'Depart'];
  const body = rows.value.map((row) => [row.code, row.guest?.name, row.room?.number, row.check_in, row.check_out].join(','));
  const csv = [headers.join(','), ...body].join('\n');
  const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
  const url = URL.createObjectURL(blob);
  const link = document.createElement('a');
  link.href = url;
  link.download = 'reservations-management.csv';
  link.click();
  URL.revokeObjectURL(url);
};

watch(() => [filters.search, filters.from, filters.to, filters.status], load);
onMounted(load);
</script>
