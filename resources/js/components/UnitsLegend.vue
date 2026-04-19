<template><div class="flex flex-wrap gap-3 text-xs"><div v-for="i in items" :key="i.slug" class="flex items-center gap-1"><span class="w-2 h-2 rounded-full" :style="{background:i.color}"></span>{{ i.name }} ({{ i.count }})</div></div></template>
<script setup>defineProps({items:Array});</script>
