<template><button class="card p-3 text-center" @click="$emit('click')"><span class="px-4 py-1 rounded-full text-xs" :class="active?'bg-black text-white':'bg-slate-100'">{{ label }}</span><p class="text-xs text-slate-500 mt-2">{{ label }}</p></button></template>
<script setup>defineProps({label:String,active:Boolean}); defineEmits(['click']);</script>
