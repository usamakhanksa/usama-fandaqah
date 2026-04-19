<template>
  <div class="flex flex-wrap gap-2 items-center text-xs text-slate-500">
    <select class="input !w-28" v-model="local.sort" @change="emitChange"><option value="name_asc">SORT A-Z</option><option value="name_desc">SORT Z-A</option></select>
    <select class="input !w-28" v-model="local.gender" @change="emitChange"><option value="all">GENDER ALL</option><option value="male">Male</option><option value="female">Female</option></select>
    <select class="input !w-28" v-model="local.type" @change="emitChange"><option value="all">TYPE ALL</option><option value="vip">VIP</option><option value="normal">Normal</option><option value="company">Company</option></select>
  </div>
</template>
<script setup>
import { reactive, watch } from 'vue';
const props = defineProps({ query: { type: Object, required: true } });
const emit = defineEmits(['change']);
const local = reactive({ ...props.query });
watch(() => props.query, v => Object.assign(local, v));
const emitChange = () => emit('change', { ...local });
</script>
