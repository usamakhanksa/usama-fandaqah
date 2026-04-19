<template><div class="min-h-screen flex"><SidebarNav/><div class="flex-1"><HeaderBar/><router-view/></div></div></template>
<script setup>
import SidebarNav from './components/SidebarNav.vue';
import HeaderBar from './components/HeaderBar.vue';
</script>
