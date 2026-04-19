<template>
  <div class="min-h-screen flex bg-slate-100 font-['Outfit']">
    <SidebarNav v-if="route.path !== '/login'" />
    <div class="flex-1 flex flex-col min-w-0">
      <HeaderBar v-if="route.path !== '/login'" />
      <router-view />
    </div>
  </div>
</template>

<script setup>
import { useRoute } from 'vue-router';
import SidebarNav from './components/SidebarNav.vue';
import HeaderBar from './components/HeaderBar.vue';
const route = useRoute();
</script>
