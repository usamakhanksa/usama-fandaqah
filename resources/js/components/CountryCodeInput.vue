<template>
  <div class="flex gap-2">
    <select class="input !w-24" :value="code" @change="$emit('update:code', $event.target.value)">
      <option v-for="c in countries" :key="c.id" :value="c.phone_code">{{ c.phone_code }}</option>
    </select>
    <input class="input flex-1" :value="number" @input="$emit('update:number', $event.target.value)" placeholder="1234567890">
  </div>
</template>
<script setup>
defineProps({ countries: { type: Array, default: () => [] }, code: String, number: String });
defineEmits(['update:code', 'update:number']);
</script>
