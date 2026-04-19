<template>
  <header class="bg-white border-b px-4 py-2 flex items-center justify-between gap-3">
    <div class="flex items-center gap-3">
      <button class="p-2 rounded hover:bg-slate-100">☰</button>
      <div class="w-8 h-8 rounded-full bg-emerald-100 border border-emerald-300"></div>
      <div class="w-8 h-8 rounded-full bg-purple-100 border border-purple-300"></div>
    </div>
    <SearchInput class="max-w-md flex-1" />
    <div class="flex items-center gap-3">
      <NotificationDropdown />
      <UserMenu />
    </div>
  </header>
</template>
<script setup>
import SearchInput from './SearchInput.vue';
import NotificationDropdown from './NotificationDropdown.vue';
import UserMenu from './UserMenu.vue';
</script>
