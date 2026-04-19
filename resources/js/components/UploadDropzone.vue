<template>
  <div class="border border-dashed rounded-lg p-4 text-center text-xs text-slate-400">
    <input type="file" class="hidden" ref="file" @change="onSelect">
    <button type="button" class="w-full h-24" @click="file.click()">SELECT OR DRAG IMAGE</button>
    <div v-if="preview" class="mt-2"><img :src="preview" class="h-24 mx-auto rounded"></div>
  </div>
</template>
<script setup>
import { ref } from 'vue';
const emit = defineEmits(['uploaded']);
const file = ref(null);
const preview = ref('');
const onSelect = (e) => {
  const f = e.target.files?.[0];
  if (!f) return;
  preview.value = URL.createObjectURL(f);
  emit('uploaded', f);
};
</script>
