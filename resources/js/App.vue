<template>
  <BaseAppLayout>
    <router-view />
  </BaseAppLayout>
</template>

<script setup>
import BaseAppLayout from './layouts/BaseAppLayout.vue';
</script>

<style>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Noto+Kufi+Arabic:wght@400;600;700&display=swap');

.font-arabic { font-family: 'Noto Kufi Arabic', sans-serif !important; }
[dir="rtl"] .ml-auto { margin-right: auto; margin-left: 0; }
[dir="rtl"] .mr-auto { margin-left: auto; margin-right: 0; }
</style>
