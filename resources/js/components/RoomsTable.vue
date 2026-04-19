<template>
  <div>
    <table class="w-full text-sm">
      <thead class="text-left text-slate-400 border-b">
        <tr>
          <th class="p-2">ID</th>
          <th class="p-2">Room Name</th>
          <th class="p-2">Room Type</th>
          <th class="p-2">Room Floor</th>
          <th class="p-2 cursor-pointer" @click="$emit('sort','price_asc')">Price Per Day</th>
          <th class="p-2">Status</th>
          <th class="p-2">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="r in rows" :key="r.id" class="border-b hover:bg-slate-50">
          <td class="p-2">{{ r.id }}</td>
          <td class="p-2"><div class="flex items-center gap-2"><img :src="r.thumbnail" class="w-8 h-8 rounded-full object-cover"> <span>{{ r.room_name }}</span></div></td>
          <td class="p-2">{{ r.room_type }}</td>
          <td class="p-2">{{ r.room_floor }}</td>
          <td class="p-2">{{ r.price_per_day }} SAR</td>
          <td class="p-2"><span class="px-2 py-1 rounded-full text-xs" :class="statusClass(r.status)">{{ r.status }}</span></td>
          <td class="p-2">
            <button class="text-blue-500 mr-2" @click="$emit('edit',r)">Edit</button>
            <button class="text-rose-500" @click="$emit('delete',r)">Delete</button>
          </td>
        </tr>
      </tbody>
    </table>
    <PaginationControls :meta="meta || {}" @page="$emit('page',$event)" />
  </div>
</template>
<script setup>
import PaginationControls from './PaginationControls.vue';
defineProps({ rows: { type: Array, default: () => [] }, meta: Object, loading: Boolean });
defineEmits(['page', 'edit', 'delete', 'sort']);
const statusClass = (s) => ({
  available: 'bg-emerald-100 text-emerald-600',
  occupied: 'bg-blue-100 text-blue-600',
  reserved: 'bg-purple-100 text-purple-600',
  maintenance: 'bg-amber-100 text-amber-600',
  cleaning: 'bg-cyan-100 text-cyan-600',
  not_ready: 'bg-rose-100 text-rose-600',
}[s] || 'bg-slate-100 text-slate-600');
</script>
