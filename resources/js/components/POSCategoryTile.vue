<template>
  <button class="bg-white rounded-xl border border-slate-200 p-4 text-center" @click="$emit('select', category)">
    <img :src="category.icon || '/assets/avatars/guest1.svg'" class="w-10 h-10 mx-auto mb-2" />
    <div class="text-sm font-semibold">{{ category.name }}</div>
  </button>
</template>
<script setup>
defineProps({ category: Object });
defineEmits(['select']);
</script>
