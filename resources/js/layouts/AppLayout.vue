<template>
  <div class="min-h-screen bg-beige text-navy">
    <header v-if="route.path !== '/login'" class="flex h-14 items-center justify-between bg-navy px-4 text-beige">
      <h2 class="font-semibold">Property Management System</h2>
    </header>

    <div class="flex" :class="route.path === '/login' ? 'h-screen' : 'min-h-[calc(100vh-56px)]'">
      <Sidebar v-if="!ui.sidebarCollapsed && route.path !== '/login'" />
      <main class="flex-1" :class="route.path === '/login' ? '' : 'p-6'">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import { useRoute } from 'vue-router';
import Sidebar from '../components/Sidebar.vue';
import { useUiStore } from '../stores/ui';

const ui = useUiStore();
const route = useRoute();
</script>
