<template><span :class="classes" class="px-2 py-1 rounded-md text-xs font-medium">{{ label }}</span></template>
<script setup>
import { computed } from 'vue';
const props = defineProps({ type: { type: String, default: 'normal' } });
const label = computed(() => props.type === 'vip' ? 'VIP' : props.type === 'company' ? 'Company' : 'Normal');
const classes = computed(() => ({ vip: 'bg-emerald-100 text-emerald-600', company: 'bg-blue-100 text-blue-600', normal: 'bg-amber-100 text-amber-600' }[props.type] || 'bg-slate-100 text-slate-600'));
</script>
