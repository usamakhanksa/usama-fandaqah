<template>
  <RouterLink 
    :to="to" 
    class="sidebar-item group" 
    :class="{ 'active': $route.path === to }"
  >
    <div class="item-icon">
       <slot name="icon">
         <span>{{ icon }}</span>
       </slot>
    </div>
    <span class="item-label">{{ label }}</span>
    <div v-if="$route.path === to" class="active-indicator"></div>
  </RouterLink>
</template>

<script setup>
defineProps({ 
  to: { type: String, required: true }, 
  icon: String, 
  label: { type: String, required: true }
});
</script>

<style scoped>
.sidebar-item {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 12px 16px;
  margin: 4px 12px;
  border-radius: 16px;
  text-decoration: none;
  color: #64748b;
  font-weight: 500;
  font-size: 14px;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  overflow: hidden;
}

.item-icon {
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  transition: transform 0.3s ease;
}

.item-label {
  flex: 1;
  white-space: nowrap;
}

/* Hover State */
.sidebar-item:hover {
  background-color: #f8fafc;
  color: #e95a54;
}

.sidebar-item:hover .item-icon {
  transform: translateX(2px);
}

/* Active State - Red Bubble Style Like Image */
.sidebar-item.active {
  background: linear-gradient(135deg, #e95a54 0%, #ef4444 100%);
  color: white;
  box-shadow: 0 10px 20px -5px rgba(233, 90, 84, 0.4);
  transform: scale(1.02);
}

.sidebar-item.active .item-icon {
  color: white;
}

.sidebar-item.active .item-label {
  font-weight: 700;
}
</style>
