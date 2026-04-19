<template>
  <section class="bg-white rounded-2xl border p-4">
    <div class="flex items-center justify-between mb-3">
      <h3 class="font-semibold">User Role</h3>
      <button class="bg-black text-white rounded-md w-8 h-8" @click="$emit('create')">+</button>
    </div>
    <SearchInput v-model="search" placeholder="Search roles" class="mb-3" />
    <div class="space-y-2 max-h-[540px] overflow-auto pr-1">
      <RoleCard v-for="role in filtered" :key="role.id" :role="role" :active="selectedRoleId===role.id" @select="$emit('select', role)" @menu="$emit('menu', role)"/>
    </div>
  </section>
</template>

<script setup>
import { computed, ref } from 'vue';
import SearchInput from './SearchInput.vue';
import RoleCard from './RoleCard.vue';
const props = defineProps({ roles: Array, selectedRoleId: Number });
defineEmits(['select', 'create', 'menu']);
const search = ref('');
const filtered = computed(() => (props.roles || []).filter((r) => r.name.toLowerCase().includes(search.value.toLowerCase())));
</script>
