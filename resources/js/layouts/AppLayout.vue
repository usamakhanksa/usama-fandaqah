<template>
  <div class="min-h-screen bg-beige text-navy">
    <header class="flex h-14 items-center justify-between bg-navy px-4 text-beige">
      <h2 class="font-semibold">Property Management System</h2>
    </header>

    <div class="flex min-h-[calc(100vh-56px)]">
      <Sidebar v-if="!ui.sidebarCollapsed" />
      <main class="flex-1 p-6">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import Sidebar from '../components/Sidebar.vue';
import { useUiStore } from '../stores/ui';

const ui = useUiStore();
</script>
