<template>
  <div class="flex flex-wrap items-center gap-2 text-sm">
    <select class="input !py-2 !w-36" :value="query.sort" @change="update('sort', $event.target.value)">
      <option v-for="s in filters.sort" :key="s" :value="s">Sort: {{ s }}</option>
    </select>
    <select class="input !py-2 !w-36" :value="query.gender" @change="update('gender', $event.target.value)">
      <option v-for="g in filters.gender" :key="g" :value="g">Gender: {{ g }}</option>
    </select>
    <select class="input !py-2 !w-40" :value="query.room_type_id" @change="update('room_type_id', $event.target.value)">
      <option value="">Room Type All</option>
      <option v-for="t in filters.room_types" :key="t.id" :value="t.id">{{ t.name }}</option>
    </select>
    <select class="input !py-2 !w-36" :value="query.status" @change="update('status', $event.target.value)">
      <option value="">All status</option>
      <option v-for="s in filters.statuses" :key="s" :value="s">{{ s }}</option>
    </select>
  </div>
</template>
<script setup>
const props = defineProps({ filters: Object, query: Object });
const emit = defineEmits(['update:query', 'change']);
const update = (key, value) => {
  emit('update:query', { ...props.query, [key]: value, page: 1 });
  props.query[key] = value;
  props.query.page = 1;
  emit('change');
};
</script>
