<template>
  <aside class="sidebar-wrapper">
    <div class="sidebar-brand">
      <div class="logo-box">🏨</div>
      <span class="brand-text">fandaqah</span>
    </div>

    <nav class="nav-container">
      <template v-for="node in filteredNav" :key="node.label">
        <div v-if="node.path" class="nav-link-wrap">
          <RouterLink :to="node.path" class="nav-link" :class="{ active: isActive(node) }">
            <component :is="node.icon" class="w-4 h-4" />
            <span>{{ node.label }}</span>
          </RouterLink>
        </div>

        <details v-else class="nav-group" :open="isOpen(node.label)">
          <summary class="group-header">
            <component :is="node.icon" class="w-4 h-4" />
            <span>{{ node.label }}</span>
          </summary>
          <div class="group-children">
            <template v-for="child in node.children" :key="child.label">
              <RouterLink
                v-if="child.path"
                :to="child.path"
                class="sub-link"
                :class="{ active: isActive(child) }"
              >
                <component :is="child.icon" class="w-4 h-4" />
                <span>{{ child.label }}</span>
              </RouterLink>

              <details v-else class="nav-subgroup" :open="isOpen(child.label)">
                <summary class="subgroup-header">
                  <component :is="child.icon" class="w-4 h-4" />
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
                    <component :is="leaf.icon" class="w-4 h-4" />
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
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import { sidebarConfig } from '../config/sidebarConfig';

const route = useRoute();
const userPermissions = computed(() => JSON.parse(localStorage.getItem('permissions') || '[]'));

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
const isOpen = (label) => route.path.includes(label.toLowerCase().split(' ')[0]);
</script>

<style scoped>
.sidebar-wrapper { width: 320px; background: #2a273c; color: #f2f0eb; min-height: 100vh; }
.sidebar-brand { display:flex; align-items:center; gap:10px; padding:20px; border-bottom: 1px solid #8f9793; }
.logo-box { width:34px; height:34px; border-radius:10px; background:#fbcdab; color:#2a273c; display:grid; place-items:center; }
.brand-text { font-weight:700; }
.nav-container { padding: 14px 10px 80px; display:flex; flex-direction:column; gap:8px; overflow:auto; }
.nav-link,.sub-link,.leaf-link,.group-header,.subgroup-header { display:flex; align-items:center; gap:8px; padding:9px 10px; border-radius:10px; color:#f2f0eb; text-decoration:none; font-size:13px; }
.nav-link.active,.sub-link.active,.leaf-link.active { background:#e95a54; color:#f2f0eb; }
.group-header,.subgroup-header { list-style:none; cursor:pointer; }
.group-children { margin-inline-start: 8px; border-inline-start:1px solid #8f9793; padding-inline-start: 8px; }
.subgroup-children { margin-inline-start: 8px; border-inline-start:1px dashed #8f9793; padding-inline-start: 8px; }
.nav-group > summary::-webkit-details-marker,.nav-subgroup > summary::-webkit-details-marker { display:none; }
</style>
