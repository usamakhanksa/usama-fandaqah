<template>
  <div class="relative" @mouseleave="open=false">
    <button @click="open=!open"><slot name="trigger" /></button>
    <div v-if="open" class="absolute right-0 mt-2 bg-white rounded-xl shadow border min-w-40 z-20">
      <slot />
    </div>
  </div>
</template>
<script setup>
import { ref } from 'vue';
const open = ref(false);
</script>
