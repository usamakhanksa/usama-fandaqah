<template>
  <div class="bg-white rounded-xl border overflow-hidden">
    <table class="w-full text-sm">
      <thead class="bg-slate-50"><tr><th class="p-3 text-left">#</th><th class="p-3 text-left">Service Name</th><th class="p-3 text-left">Show in Reservation</th><th class="p-3 text-left">Show in POS</th><th class="p-3 text-left">Status</th></tr></thead>
      <tbody>
        <tr v-for="service in services.data" :key="service.id" class="border-t"><td class="p-3">{{ service.id }}</td><td class="p-3">{{ service.name_en || service.name }}</td><td class="p-3">{{ service.show_in_reservation ? 'Yes' : 'No' }}</td><td class="p-3">{{ service.show_in_pos ? 'Yes' : 'No' }}</td><td class="p-3"><span class="px-2 py-1 rounded bg-emerald-50 text-emerald-600 text-xs">{{ service.status }}</span></td></tr>
      </tbody>
    </table>
  </div>
</template>
<script setup>
defineProps({ services: Object });
</script>
