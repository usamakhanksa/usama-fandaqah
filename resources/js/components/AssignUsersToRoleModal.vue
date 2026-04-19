<template>
  <BaseModal :model-value="show" title="Assign Users" @update:modelValue="$emit('close')">
    <div class="max-h-80 overflow-auto space-y-2">
      <label v-for="user in users" :key="user.id" class="flex items-center gap-2 text-sm">
        <input type="checkbox" :value="user.id" v-model="selected" />
        <img :src="user.avatar" class="w-6 h-6 rounded-full" />
        {{ user.name }}
      </label>
    </div>
    <div class="flex justify-end gap-2 mt-3">
      <button class="btn-secondary" @click="$emit('close')">Cancel</button>
      <button class="btn-primary" @click="$emit('save', selected)">Assign</button>
    </div>
  </BaseModal>
</template>

<script setup>
import { ref, watch } from 'vue';
import BaseModal from './BaseModal.vue';
const props = defineProps({ show: Boolean, users: Array, initial: Array });
defineEmits(['save', 'close']);
const selected = ref([]);
watch(() => props.initial, (v) => selected.value = [...(v || [])], { immediate: true });
</script>
