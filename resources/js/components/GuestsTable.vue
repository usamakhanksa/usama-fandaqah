<template>
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-slate-50 text-slate-500"><tr><th class="p-3 text-left">#</th><th class="p-3 text-left">Name</th><th class="p-3 text-left">Phone Number</th><th class="p-3 text-left">Email</th><th class="p-3 text-left">Card ID</th><th class="p-3 text-left">Booking</th><th class="p-3 text-left">Type</th><th class="p-3 text-left">Actions</th></tr></thead>
      <tbody>
        <tr v-for="row in rows" :key="row.id" class="border-t">
          <td class="p-3 text-slate-400">{{ row.id }}</td>
          <td class="p-3"><div class="flex items-center gap-2"><img :src="row.avatar || '/assets/avatars/guest1.svg'" class="w-8 h-8 rounded-full object-cover"><span>{{ row.name || row.company_name }}</span></div></td>
          <td class="p-3">{{ row.phone || row.mobile_number }}</td>
          <td class="p-3">{{ row.email }}</td>
          <td class="p-3">{{ row.card_id || row.id_number }}</td>
          <td class="p-3">{{ row.booking || row.bookings || 0 }}</td>
          <td class="p-3"><GuestTypeBadge :type="row.type"/></td>
          <td class="p-3"><div class="flex gap-1"><button class="btn-outline !px-2 !py-1" @click="$emit('edit', row)">Edit</button><button class="btn-outline !px-2 !py-1" @click="$emit('delete', row)">Delete</button></div></td>
        </tr>
      </tbody>
    </table>
    <div class="flex items-center justify-between pt-3 text-xs text-slate-500">
      <span>Showing {{ meta.from ?? 0 }} to {{ meta.to ?? 0 }} out of {{ meta.total ?? 0 }} Total Guests</span>
      <PaginationControls :meta="meta" @page="$emit('page', $event)"/>
    </div>
  </div>
</template>
<script setup>
import GuestTypeBadge from './GuestTypeBadge.vue';
import PaginationControls from './PaginationControls.vue';
defineProps({ rows: Array, meta: { type: Object, default: () => ({}) } });
defineEmits(['page', 'edit', 'delete']);
</script>
