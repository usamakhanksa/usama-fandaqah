<template>
  <aside :class="['sidebar-wrapper', { collapsed: isSidebarCollapsed }]">
    <div class="sidebar-brand">
      <button class="logo-box" type="button" @click="toggleSidebar" :title="isSidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar'">
        🏨
      </button>
      <span v-if="!isSidebarCollapsed" class="brand-text">fandaqah</span>
    </div>

    <nav class="nav-container">
      <template v-for="node in filteredNav" :key="node.label">
        <div v-if="node.path" class="nav-link-wrap">
          <RouterLink :to="node.path" class="nav-link" :class="{ active: isActive(node) }" :title="node.label">
            <component :is="node.icon" class="w-4 h-4 shrink-0" />
            <span v-if="!isSidebarCollapsed">{{ node.label }}</span>
          </RouterLink>
        </div>

        <details v-else class="nav-group" :open="isGroupOpen(node)">
          <summary class="group-header" :title="node.label">
            <component :is="node.icon" class="w-4 h-4 shrink-0" />
            <span v-if="!isSidebarCollapsed">{{ node.label }}</span>
          </summary>
          <div v-if="!isSidebarCollapsed" class="group-children">
            <template v-for="child in node.children" :key="child.label">
              <RouterLink
                v-if="child.path"
                :to="child.path"
                class="sub-link"
                :class="{ active: isActive(child) }"
              >
                <component :is="child.icon" class="w-4 h-4 shrink-0" />
                <span>{{ child.label }}</span>
              </RouterLink>

              <details v-else class="nav-subgroup" :open="isGroupOpen(child)">
                <summary class="subgroup-header">
                  <component :is="child.icon" class="w-4 h-4 shrink-0" />
                  <span>{{ child.label }}</span>
                </summary>
                <div class="subgroup-children">
                  <RouterLink
                    v-for="leaf in child.children"
                    :key="leaf.label"
                    :to="leaf.path"
                    class="leaf-link"
                    :class="{ active: isActive(leaf) }"
                  >
                    <component :is="leaf.icon" class="w-4 h-4 shrink-0" />
                    <span>{{ leaf.label }}</span>
                  </RouterLink>
                </div>
              </details>
            </template>
          </div>
        </details>
      </template>
    </nav>
  </aside>
</template>

<script setup>
import { computed, ref } from 'vue';
import { useRoute } from 'vue-router';
import { sidebarConfig } from '../config/sidebarConfig';

const route = useRoute();
const isSidebarCollapsed = ref(false);
const userPermissions = computed(() => JSON.parse(localStorage.getItem('permissions') || '[]'));

const toggleSidebar = () => {
  isSidebarCollapsed.value = !isSidebarCollapsed.value;
};

const hasPermission = (permission) => !permission || userPermissions.value.length === 0 || userPermissions.value.includes(permission);

const filterNodes = (nodes) => nodes
  .map((node) => ({ ...node }))
  .filter((node) => {
    if (node.children?.length) {
      node.children = filterNodes(node.children);
      return node.children.length > 0;
    }
    return hasPermission(node.permission);
  });

const filteredNav = computed(() => filterNodes(sidebarConfig));
const isActive = (node) => route.path === node.path;
const isGroupOpen = (node) => node.children?.some((child) => child.path === route.path || child.children?.some((leaf) => leaf.path === route.path));
</script>

<style scoped>
.sidebar-wrapper {
  width: 320px;
  background: var(--dark-navy);
  color: var(--soft-beige);
  min-height: 100vh;
  transition: width 180ms ease;
}
.sidebar-wrapper.collapsed { width: 82px; }
.sidebar-brand { display:flex; align-items:center; gap:10px; padding:20px; border-bottom: 1px solid var(--muted-green); }
.logo-box { width:34px; height:34px; border-radius:10px; background:var(--light-peach); color:var(--dark-navy); display:grid; place-items:center; border:0; cursor:pointer; }
.brand-text { font-weight:700; font-size: 1.1rem; }
.nav-container { padding: 14px 10px 80px; display:flex; flex-direction:column; gap:8px; overflow:auto; }
.nav-link,.sub-link,.leaf-link,.group-header,.subgroup-header { display:flex; align-items:center; gap:8px; padding:9px 10px; border-radius:10px; color:var(--soft-beige); text-decoration:none; font-size:13px; }
.nav-link:hover,.sub-link:hover,.leaf-link:hover { background: color-mix(in srgb, var(--muted-green) 35%, transparent); }
.nav-link.active,.sub-link.active,.leaf-link.active { background:var(--coral-red); color:var(--soft-beige); }
.group-header,.subgroup-header { list-style:none; cursor:pointer; }
.group-children { margin-inline-start: 8px; border-inline-start:1px solid var(--muted-green); padding-inline-start: 8px; }
.subgroup-children { margin-inline-start: 8px; border-inline-start:1px dashed var(--muted-green); padding-inline-start: 8px; }
.nav-group > summary::-webkit-details-marker,.nav-subgroup > summary::-webkit-details-marker { display:none; }
</style>
