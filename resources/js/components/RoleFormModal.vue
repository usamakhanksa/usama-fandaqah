<template>
  <BaseModal :model-value="show" :title="modelValue?.id ? 'Edit Role' : 'Add Role'" @update:modelValue="$emit('close')">
    <div class="space-y-3">
      <label class="block text-sm">Role Name</label>
      <input v-model="form.name" class="w-full border rounded-lg px-3 py-2" />
      <p v-if="error" class="text-red-500 text-xs">{{ error }}</p>
      <div class="flex justify-end gap-2">
        <button class="btn-secondary" @click="$emit('close')">Cancel</button>
        <button class="btn-primary" @click="submit">Save</button>
      </div>
    </div>
  </BaseModal>
</template>

<script setup>
import { reactive, watch, ref } from 'vue';
import BaseModal from './BaseModal.vue';
const props = defineProps({ show: Boolean, modelValue: Object });
const emit = defineEmits(['save', 'close']);
const form = reactive({ name: '' });
const error = ref('');
watch(() => props.modelValue, (v) => { form.name = v?.name || ''; }, { immediate: true });
function submit() {
  if (!form.name.trim()) { error.value = 'Role name is required'; return; }
  error.value = '';
  emit('save', { ...props.modelValue, name: form.name.trim() });
}
</script>
