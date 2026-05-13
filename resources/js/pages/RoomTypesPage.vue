<template>
  <div class="p-4 space-y-4">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-xl font-bold">Room Types</h1>
        <p class="text-xs text-slate-400">Home &gt; Room Types</p>
      </div>
      <SearchInput v-model="query.search" class="w-72" placeholder="Search room types..." @submit="fetchRoomTypes" />
    </div>

    <div class="card p-3">
      <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
        <div class="flex gap-2">
          <button class="btn-primary" @click="openModal = true; resetForm()">Add Room Type</button>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
          <thead class="bg-slate-50 text-slate-500">
            <tr>
              <th class="p-3">ID</th>
              <th class="p-3">Name</th>
              <th class="p-3">Base Price</th>
              <th class="p-3">Created</th>
              <th class="p-3">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="type in roomTypes.data" :key="type.id" class="border-t">
              <td class="p-3">{{ type.id }}</td>
              <td class="p-3">{{ type.name }}</td>
              <td class="p-3">{{ type.base_price ?? '0.00' }}</td>
              <td class="p-3">{{ formatDate(type.created_at) }}</td>
              <td class="p-3 flex gap-2">
                <button class="btn-outline" @click="editRoomType(type)">Edit</button>
                <button class="btn-danger" @click="deleteRoomType(type)">Delete</button>
              </td>
            </tr>
            <tr v-if="!roomTypes.data.length">
              <td class="p-3 text-center" colspan="5">No room types found.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="py-3">
        <button v-if="roomTypes.meta.prev_page_url" class="btn-outline mr-2" @click="changePage(roomTypes.meta.current_page - 1)">Previous</button>
        <button v-if="roomTypes.meta.next_page_url" class="btn-outline" @click="changePage(roomTypes.meta.current_page + 1)">Next</button>
      </div>
    </div>

    <BaseModal v-model="openModal" :title="form.id ? 'Edit Room Type' : 'Add Room Type'">
      <form class="grid grid-cols-2 gap-3" @submit.prevent="saveRoomType">
        <input v-model="form.name" class="input" placeholder="Name" required />
        <input v-model.number="form.base_price" class="input" type="number" min="0" step="0.01" placeholder="Base price" required />

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

const roomTypes = ref({ data: [], meta: {} });
const query = reactive({ search: '', page: 1, per_page: 20 });
const openModal = ref(false);
const form = reactive({ id: null, name: '', base_price: 0 });
const loading = ref(false);

const fetchRoomTypes = async () => {
  loading.value = true;
  try {
    const { data } = await api.get('/room-types', { params: query });
    roomTypes.value = data;
  } finally {
    loading.value = false;
  }
};

const changePage = (page) => {
  query.page = page;
  fetchRoomTypes();
};

const resetForm = () => {
  form.id = null;
  form.name = '';
  form.base_price = 0;
};

const editRoomType = (type) => {
  form.id = type.id;
  form.name = type.name;
  form.base_price = type.base_price;
  openModal.value = true;
};

const saveRoomType = async () => {
  if (form.id) {
    await api.put(`/room-types/${form.id}`, { name: form.name, base_price: form.base_price });
  } else {
    await api.post('/room-types', { name: form.name, base_price: form.base_price });
  }
  openModal.value = false;
  resetForm();
  await fetchRoomTypes();
};

const deleteRoomType = async (type) => {
  if (!confirm('Delete this room type?')) return;
  await api.delete(`/room-types/${type.id}`);
  await fetchRoomTypes();
};

const formatDate = (value) => {
  if (!value) return '';
  return new Date(value).toLocaleString();
};

onMounted(() => {
  fetchRoomTypes();
});
</script>
