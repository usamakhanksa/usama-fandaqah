<template>
  <tr class="border-b last:border-b-0">
    <td class="py-2 px-2">
      <div class="flex items-center gap-2">
        <button @click="toggle('enabled', !local.enabled)" :class="['w-9 h-5 rounded-full relative transition', local.enabled ? 'bg-green-500' : 'bg-slate-300']"><span :class="['absolute top-0.5 w-4 h-4 rounded-full bg-white transition', local.enabled ? 'left-4' : 'left-0.5']"/></button>
        <span class="text-sm">{{ local.name }}</span>
      </div>
    </td>
    <td v-for="key in ['anyone','create','edit','view','remove']" :key="key" class="text-center">
      <input type="checkbox" :checked="local[key]" @change="toggle(key,$event.target.checked)" class="accent-green-600 w-4 h-4" />
    </td>
  </tr>
</template>

<script setup>
import { reactive, watch } from 'vue';
const props = defineProps({ item: Object });
const emit = defineEmits(['update']);
const local = reactive({});
watch(() => props.item, (v) => Object.assign(local, v || {}), { immediate: true, deep: true });
function toggle(field, value) {
  local[field] = value;
  emit('update', { ...local });
}
</script>
