<template>
  <section class="bg-white rounded-2xl border p-4">
    <h3 class="font-semibold">User Permissions Management</h3>
    <p class="text-sm text-slate-500 mb-2">Permissions</p>
    <div class="max-h-[560px] overflow-auto">
      <table class="w-full text-sm">
        <thead class="bg-slate-50 sticky top-0">
          <tr>
            <th class="text-left p-2">Permission name</th>
            <th class="p-2">Anyone</th>
            <th class="p-2">Create</th>
            <th class="p-2">Edit</th>
            <th class="p-2">View</th>
            <th class="p-2">Remove</th>
          </tr>
        </thead>
        <tbody>
          <PermissionToggleRow v-for="p in permissions" :key="p.id" :item="p" @update="$emit('update', $event)" />
        </tbody>
      </table>
    </div>
  </section>
</template>

<script setup>
import PermissionToggleRow from './PermissionToggleRow.vue';
defineProps({ permissions: Array });
defineEmits(['update']);
</script>
