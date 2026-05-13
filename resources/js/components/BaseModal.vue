<template>
  <teleport to="body">
    <div v-if="isOpen" class="fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4" @click.self="close">
      <div class="bg-white rounded-xl w-full max-w-2xl p-6 shadow-xl max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-lg font-bold">{{ title }}</h2>
          <button @click="close" class="text-slate-400 hover:text-slate-600 text-2xl leading-none">×</button>
        </div>
        <slot />
      </div>
    </div>
  </teleport>
</template>
<script setup>
import { computed } from 'vue';

const props = defineProps({ 
  modelValue: Boolean, 
  title: String 
});

const emit = defineEmits(['update:modelValue', 'close']);

const isOpen = computed(() => props.modelValue);

const close = () => {
  emit('update:modelValue', false);
  emit('close');
};
</script>
