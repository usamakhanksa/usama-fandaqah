<template>
  <form class="relative" @submit.prevent="$emit('submit')">
    <input :value="modelValue" @input="$emit('update:modelValue', $event.target.value)" :placeholder="placeholder || 'Search...'" class="input pl-9 w-full" />
    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">⌕</span>
  </form>
</template>
<script setup>
defineProps({ modelValue: String, placeholder: String });
defineEmits(['update:modelValue', 'submit']);
</script>
