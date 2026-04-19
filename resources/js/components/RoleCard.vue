<template>
  <div :class="['border rounded-xl px-3 py-2 bg-white flex items-center gap-2 cursor-pointer', active ? 'border-rose-400 shadow-sm' : 'border-slate-200']" @click="$emit('select')">
    <div class="flex-1 min-w-0">
      <p class="font-semibold text-sm truncate">{{ role.name }}</p>
    </div>
    <AvatarGroup :avatars="role.avatars || []" :count="role.user_count" />
    <span v-if="role.overflow_count" class="text-xs text-slate-500">+{{ role.overflow_count }}</span>
    <button class="text-slate-500 hover:text-slate-800" @click.stop="$emit('menu')">⋮</button>
  </div>
</template>

<script setup>
import AvatarGroup from './AvatarGroup.vue';
defineProps({ role: Object, active: Boolean });
defineEmits(['select', 'menu']);
</script>
