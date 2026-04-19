<template>
  <div class="p-4 space-y-4">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-xl font-bold">Rooms</h1>
        <p class="text-xs text-slate-400">Home &gt; Rooms</p>
      </div>
      <SearchInput v-model="query.search" class="w-72" placeholder="Search rooms..." @submit="fetchRooms"/>
    </div>

    <RoomsMetricsStrip :metrics="metrics" />

    <div class="card p-3">
      <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
        <RoomsFilterBar :filters="filters" v-model:query="query" @change="fetchRooms" />
        <div class="flex gap-2">
          <button class="btn-outline" @click="exportCsv">Export</button>
          <button class="btn-primary" @click="openModal = true">Add New Room</button>
        </div>
      </div>
      <RoomsTable
        :rows="rooms.data"
        :meta="rooms.meta"
        :loading="loading"
        @page="changePage"
        @edit="editRoom"
        @delete="deleteRoom"
        @sort="setSort"
      />
    </div>

    <div class="grid lg:grid-cols-5 gap-4">
      <div class="lg:col-span-4 card p-0 overflow-hidden">
        <table class="w-full text-sm">
          <thead class="bg-slate-50 text-slate-500">
            <tr><th class="p-3 text-left">ID</th><th class="p-3 text-left">Room Name</th><th class="p-3 text-left">Room Type</th><th class="p-3 text-left">Room Floor</th><th class="p-3 text-left">Price Per Day</th><th class="p-3 text-left">Status</th></tr>
          </thead>
          <tbody>
            <tr v-if="rooms.data.length" v-for="r in rooms.data.slice(0,1)" :key="`summary-${r.id}`" class="border-t">
              <td class="p-3">{{ r.id }}</td><td class="p-3">{{ r.room_name }}</td><td class="p-3">{{ r.room_type }}</td><td class="p-3">{{ r.room_floor }}</td><td class="p-3">{{ r.price_per_day }} SAR</td><td class="p-3">{{ r.status }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      <StatusLegendCard />
    </div>

    <FooterBar/>

    <BaseModal v-model="openModal" :title="form.id ? 'Edit Room' : 'Add New Room'">
      <form class="grid grid-cols-2 gap-3" @submit.prevent="saveRoom">
        <input v-model="form.name" class="input" placeholder="Room name" required>
        <input v-model="form.number" class="input" placeholder="Number" required>
        <select v-model="form.room_type_id" class="input" required>
          <option :value="null" disabled>Room type</option>
          <option v-for="t in filters.room_types" :key="t.id" :value="t.id">{{ t.name }}</option>
        </select>
        <input v-model.number="form.price_per_day" type="number" min="0" class="input" placeholder="Price per day" required>
        <select v-model="form.status" class="input" required>
          <option v-for="s in filters.statuses" :key="s" :value="s">{{ s }}</option>
        </select>
        <select v-model="form.gender" class="input">
          <option value="all">All</option><option value="male">Male</option><option value="female">Female</option>
        </select>
        <div class="col-span-2 flex justify-end gap-2 mt-2">
          <button type="button" class="btn-outline" @click="openModal=false">Cancel</button>
          <button type="submit" class="btn-primary">Save</button>
        </div>
      </form>
    </BaseModal>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import api from '../services/api';
import SearchInput from '../components/SearchInput.vue';
import RoomsMetricsStrip from '../components/RoomsMetricsStrip.vue';
import RoomsFilterBar from '../components/RoomsFilterBar.vue';
import RoomsTable from '../components/RoomsTable.vue';
import StatusLegendCard from '../components/StatusLegendCard.vue';
import BaseModal from '../components/BaseModal.vue';
import FooterBar from '../components/FooterBar.vue';

const loading = ref(false);
const metrics = ref({});
const filters = ref({ sort: [], gender: [], room_types: [], statuses: [] });
const rooms = ref({ data: [], meta: {} });
const openModal = ref(false);
const query = reactive({ search: '', sort: 'latest', gender: 'all', room_type_id: '', status: '', page: 1 });
const form = reactive({ id: null, name: '', number: '', room_type_id: null, status: 'available', price_per_day: 0, gender: 'all' });

const loadMeta = async () => {
  const [metricsRes, filtersRes] = await Promise.all([api.get('/rooms/metrics'), api.get('/rooms/filters')]);
  metrics.value = metricsRes.data;
  filters.value = filtersRes.data;
};

const fetchRooms = async () => {
  loading.value = true;
  try {
    const { data } = await api.get('/rooms', { params: query });
    rooms.value = data;
  } finally {
    loading.value = false;
  }
};

const changePage = (page) => { query.page = page; fetchRooms(); };
const setSort = (sort) => { query.sort = sort; fetchRooms(); };
const exportCsv = () => window.open('/api/rooms/export', '_blank');

const resetForm = () => Object.assign(form, { id: null, name: '', number: '', room_type_id: filters.value.room_types?.[0]?.id ?? null, status: 'available', price_per_day: 0, gender: 'all' });
const editRoom = (room) => { openModal.value = true; Object.assign(form, room); form.room_name = undefined; };

const saveRoom = async () => {
  const payload = { ...form, thumbnail: '/assets/avatars/guest1.svg' };
  if (form.id) await api.put(`/rooms/${form.id}`, payload);
  else await api.post('/rooms', payload);
  openModal.value = false;
  resetForm();
  await Promise.all([fetchRooms(), loadMeta()]);
};

const deleteRoom = async (room) => {
  await api.delete(`/rooms/${room.id}`);
  await Promise.all([fetchRooms(), loadMeta()]);
};

onMounted(async () => {
  await loadMeta();
  resetForm();
  await fetchRooms();
});
</script>
