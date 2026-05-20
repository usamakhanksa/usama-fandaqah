<template>
  <div class="p-4 space-y-4">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-xl font-bold">Room Floors</h1>
        <p class="text-xs text-slate-400">Home &gt; Room Floors</p>
      </div>
      <SearchInput v-model="query.search" class="w-72" placeholder="Search floors..." @submit="fetchFloors" />
    </div>

    <div class="card p-3">
      <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
        <button class="btn-primary" @click="openModal = true; resetForm()">Add Floor</button>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
          <thead class="bg-slate-50 text-slate-500">
            <tr>
              <th class="p-3">ID</th>
              <th class="p-3">Name</th>
              <th class="p-3">Level</th>
              <th class="p-3">Created</th>
              <th class="p-3">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="floor in floors.data" :key="floor.id" class="border-t">
              <td class="p-3">{{ floor.id }}</td>
              <td class="p-3">{{ floor.name }}</td>
              <td class="p-3">{{ floor.level }}</td>
              <td class="p-3">{{ formatDate(floor.created_at) }}</td>
              <td class="p-3 flex gap-2">
                <button class="btn-outline" @click="editFloor(floor)">Edit</button>
                <button class="btn-danger" @click="deleteFloor(floor)">Delete</button>
              </td>
            </tr>
            <tr v-if="!floors.data || !floors.data.length">
              <td class="p-3 text-center" colspan="5">No floors found.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="py-3" v-if="floors.meta && floors.meta.links && floors.meta.links.length > 2">
        <button v-if="floors.meta.prev_page_url" class="btn-outline mr-2" @click="changePage(floors.meta.current_page - 1)">Previous</button>
        <button v-if="floors.meta.next_page_url" class="btn-outline" @click="changePage(floors.meta.current_page + 1)">Next</button>
      </div>
    </div>

    <BaseModal v-model="openModal" :title="form.id ? 'Edit Floor' : 'Add Floor'">
      <form class="grid grid-cols-2 gap-3" @submit.prevent="saveFloor">
        <input v-model="form.name" class="input" placeholder="Floor name" required />
        <input v-model.number="form.level" class="input" type="number" min="0" placeholder="Level" required />
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
import BaseModal from '../components/BaseModal.vue';

const floors = ref({ data: [], meta: {} });
const query = reactive({ search: '', page: 1, per_page: 20 });
const openModal = ref(false);
const form = reactive({ id: null, name: '', level: 0 });
const loading = ref(false);

const fetchFloors = async () => {
  loading.value = true;
  try {
    const { data } = await api.get('/room-floors', { params: query });
    floors.value = data;
  } catch (error) {
    console.error("Error fetching floors:", error);
    // Initialize with default values in case of error
    floors.value = { data: [], meta: {} };
  } finally {
    loading.value = false;
  }
};

const changePage = (page) => {
  query.page = page;
  fetchFloors();
};

const resetForm = () => {
  form.id = null;
  form.name = '';
  form.level = 0;
};

const editFloor = (floor) => {
  form.id = floor.id;
  form.name = floor.name;
  form.level = floor.level;
  openModal.value = true;
};

const saveFloor = async () => {
  if (form.id) {
    await api.put(`/room-floors/${form.id}`, { name: form.name, level: form.level });
  } else {
    await api.post('/room-floors', { name: form.name, level: form.level });
  }
  openModal.value = false;
  resetForm();
  await fetchFloors();
};

const deleteFloor = async (floor) => {
  if (!confirm('Delete this floor?')) return;
  await api.delete(`/room-floors/${floor.id}`);
  await fetchFloors();
};

const formatDate = (value) => {
  if (!value) return '';
  return new Date(value).toLocaleString();
};

onMounted(() => {
  fetchFloors();
});
</script>