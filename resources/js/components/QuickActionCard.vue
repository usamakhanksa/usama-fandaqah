<template><div class="card p-3 space-y-2"><button class="bg-green-500 text-white rounded px-2 py-2 w-full text-xs" @click="$emit('check-in')">Check In</button><button class="bg-red-500 text-white rounded px-2 py-2 w-full text-xs" @click="$emit('check-out')">Check Out</button></div></template>
<script setup>defineEmits(['check-in','check-out']);</script>
