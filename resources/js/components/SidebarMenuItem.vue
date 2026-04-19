<template>
  <RouterLink :to="to" class="flex items-center gap-2 px-3 py-2 rounded-xl text-sm" :class="$route.path===to ? 'bg-rose-500 text-white':'text-slate-500 hover:bg-slate-100'">
    <span>{{ icon }}</span><span>{{ label }}</span>
  </RouterLink>
</template>
<script setup>
defineProps({ to: String, icon: String, label: String });
</script>
