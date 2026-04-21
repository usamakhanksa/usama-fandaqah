<template>
  <aside class="h-screen w-72 overflow-y-auto bg-navy text-beige">
    <div class="flex items-center justify-between border-b border-sage/40 px-4 py-4">
      <h1 class="text-lg font-semibold">PMS</h1>
      <button class="rounded-md bg-coral px-3 py-1 text-sm font-medium" type="button" @click="ui.toggleSidebar()">
        {{ ui.sidebarCollapsed ? 'Open' : 'Hide' }}
      </button>
    </div>

    <nav class="p-3">
      <template v-for="item in sidebarConfig" :key="item.route || item.label">
        <SidebarItem :item="item" />
      </template>
    </nav>
  </aside>
</template>

<script setup>
import { sidebarConfig } from '../config/sidebarConfig';
import { useUiStore } from '../stores/ui';
import SidebarItem from './SidebarMenuItem.vue';

const ui = useUiStore();
</script>
