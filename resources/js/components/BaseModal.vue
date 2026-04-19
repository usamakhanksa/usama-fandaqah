<template>
  <teleport to="body">
    <div v-if="modelValue" class="fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4" @click.self="$emit('update:modelValue', false)">
      <div class="bg-white rounded-xl w-full max-w-xl p-4 shadow-xl">
        <div class="font-bold mb-3">{{ title }}</div>
        <slot />
      </div>
    </div>
  </teleport>
</template>
<script setup>
defineProps({ modelValue: Boolean, title: String });
defineEmits(['update:modelValue']);
</script>
