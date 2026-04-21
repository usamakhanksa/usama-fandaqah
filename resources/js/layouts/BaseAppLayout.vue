<template>
  <div :dir="currentDir" :class="['base-layout', isArabic ? 'font-arabic' : '']">
    <SidebarNav v-if="!isLogin" />
    <div class="content-shell">
      <HeaderBar v-if="!isLogin" />
      <main class="content-main">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import { useI18n } from 'vue-i18n';
import SidebarNav from '../components/SidebarNav.vue';
import HeaderBar from '../components/HeaderBar.vue';

const route = useRoute();
const { locale } = useI18n();

const currentDir = computed(() => locale.value === 'ar' ? 'rtl' : 'ltr');
const isArabic = computed(() => locale.value === 'ar');
const isLogin = computed(() => route.path === '/login');
</script>

<style scoped>
.base-layout { min-height: 100vh; display: flex; background: var(--soft-beige); color: var(--dark-navy); }
.content-shell { flex: 1; min-width: 0; display: flex; flex-direction: column; overflow: hidden; }
.content-main { flex: 1; overflow-y: auto; }
</style>
