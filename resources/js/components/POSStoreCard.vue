<template>
  <button class="bg-white rounded-xl border p-6 text-center w-full" :class="active ? 'border-rose-400' : 'border-slate-200'" @click="$emit('select', store)">
    <img :src="store.image" class="h-12 mx-auto mb-3 object-contain" />
    <div class="font-semibold text-sm">{{ store.name }}</div>
  </button>
</template>
<script setup>
defineProps({ store: Object, active: Boolean });
defineEmits(['select']);
</script>
