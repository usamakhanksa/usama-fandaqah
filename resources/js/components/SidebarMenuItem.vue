<template>
  <div class="mb-1">
    <RouterLink
      v-if="item.path"
      :to="item.path"
      class="flex items-center gap-2 rounded-md px-3 py-2 text-sm transition"
      :class="isActive(item.path) ? 'bg-coral text-white' : 'text-beige hover:bg-sage/20'"
    >
      <component :is="item.icon" class="h-4 w-4" />
      <span>{{ item.label }}</span>
    </RouterLink>

    <details v-else class="rounded-md" :open="isOpen">
      <summary class="flex cursor-pointer list-none items-center gap-2 rounded-md px-3 py-2 text-sm text-beige hover:bg-sage/20">
        <component :is="item.icon" class="h-4 w-4" />
        <span>{{ item.label }}</span>
      </summary>

      <div class="ml-4 mt-1 border-l border-sage/40 pl-2">
        <SidebarMenuItem
          v-for="child in item.children"
          :key="child.route || child.label"
          :item="child"
        />
      </div>
    </details>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';

const props = defineProps({
  item: {
    type: Object,
    required: true,
  },
});

const route = useRoute();
const isActive = (path) => route.path === path;

const hasActiveDescendant = (node) => {
  if (!node.children) return false;
  return node.children.some((child) => child.path === route.path || hasActiveDescendant(child));
};

const isOpen = computed(() => hasActiveDescendant(props.item));
</script>
