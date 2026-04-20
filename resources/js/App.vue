<template>
  <div :dir="currentDir" :class="['min-h-screen flex bg-slate-100 font-[\'Outfit\']', isArabic ? 'font-arabic' : '']">
    <SidebarNav v-if="route.path !== '/login'" />
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
      <HeaderBar v-if="route.path !== '/login'" />
      <main class="flex-1 overflow-y-auto">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import { useI18n } from 'vue-i18n';
import SidebarNav from './components/SidebarNav.vue';
import HeaderBar from './components/HeaderBar.vue';

const route = useRoute();
const { locale } = useI18n();

const currentDir = computed(() => locale.value === 'ar' ? 'rtl' : 'ltr');
const isArabic = computed(() => locale.value === 'ar');
</script>

<style>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Noto+Kufi+Arabic:wght@400;600;700&display=swap');

.font-arabic {
  font-family: 'Noto Kufi Arabic', sans-serif !important;
}

/* RTL Layout Fixes */
[dir="rtl"] .ml-auto {
  margin-right: auto;
  margin-left: 0;
}
[dir="rtl"] .mr-auto {
  margin-left: auto;
  margin-right: 0;
}
</style>
