<template>
  <aside class="sidebar-wrapper">
    <!-- Logo Section -->
    <div class="sidebar-brand">
      <div class="logo-box">
        <img :src="'/assets/avatars/admin.svg'" class="brand-img" alt="fandaqah">
      </div>
      <span class="brand-text">fandaqah</span>
    </div>

    <!-- Navigation -->
    <nav class="nav-container">
      <div class="nav-scroll">
        <!-- Dynamic Menu Items -->
        <template v-for="item in menuItems" :key="item.key">
          
          <!-- Single Item -->
          <SidebarMenuItem 
            v-if="!item.children || item.children.length === 0" 
            :to="item.route" 
            :label="locale === 'ar' ? item.label_ar : item.label_en"
            :active="item.is_active"
          >
            <template #icon>
              <component :is="getIcon(item.icon)" class="w-5 h-5" />
            </template>
          </SidebarMenuItem>

          <!-- Group Item -->
          <div v-else class="nav-group">
            <button 
              @click="toggleGroup(item.key)" 
              class="group-toggle"
              :class="{ 'expanded': openGroups[item.key], 'active-group': item.is_active }"
            >
              <component :is="getIcon(item.icon)" class="w-5 h-5 flex-shrink-0" />
              <span class="flex-1 text-start">{{ locale === 'ar' ? item.label_ar : item.label_en }}</span>
              <ChevronDownIcon class="w-4 h-4 transition-transform duration-300" :class="{ 'rotate-180': openGroups[item.key] }" />
            </button>
            
            <div v-if="openGroups[item.key]" class="submenu animate-in slide-in-from-top-2 duration-300">
              <div class="dotted-line"></div>
              <SidebarMenuItem 
                v-for="child in item.children" 
                :key="child.key" 
                :to="child.route" 
                :label="locale === 'ar' ? child.label_ar : child.label_en"
                :active="child.is_active"
              >
                <template #icon>
                  <div class="dot-icon"></div>
                </template>
              </SidebarMenuItem>
            </div>
          </div>

        </template>

        <!-- Admin Section -->
        <div v-if="isAdmin" class="admin-divider mt-8 px-8 py-2">
           <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ locale === 'ar' ? 'التحكم بالإدارة' : 'Admin Control' }}</span>
        </div>
        <SidebarMenuItem v-if="isAdmin" to="/leads" :label="locale === 'ar' ? 'العملاء المحتملين' : 'Leads'">
           <template #icon>
             <MessageSquareIcon class="w-5 h-5" />
           </template>
        </SidebarMenuItem>
      </div>
    </nav>
    
    <!-- Pro Banner -->
    <div class="px-4 mt-auto mb-4">
      <div class="pro-card">
         <div class="pro-icon bg-white/20 p-2 rounded-lg">
            <BoltIcon class="w-4 h-4 text-white" />
         </div>
         <div class="mt-2 text-white">
            <p class="text-xs font-bold">{{ locale === 'ar' ? 'اشترك في النسخة الاحترافية' : 'Go Pro' }}</p>
            <p class="text-[10px] opacity-70">{{ locale === 'ar' ? 'افتح مميزات الشركات المتطدة' : 'Unlock corporate features' }}</p>
         </div>
      </div>
    </div>
  </aside>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import SidebarMenuItem from './SidebarMenuItem.vue';
import api from '../services/api'; // Or however the api client is structured
import { useRouter } from 'vue-router';
import * as Icons from 'lucide-vue-next';
import { ChevronDownIcon, MessageSquareIcon } from 'lucide-vue-next';
import { BoltIcon } from '@heroicons/vue/24/solid';

const { locale } = useI18n();
const router = useRouter();
const isAdmin = ref(true); // Can be tied to user state

const menuItems = ref([]);
const openGroups = ref({});

const getIcon = (iconName) => {
  // Ensure the icon name is properly capitalized and exists in the Icons object
  const normalizedIconName = iconName && iconName[0]?.toUpperCase() + iconName.slice(1);
  
  // Return the specified icon or a default one if it doesn't exist
  return normalizedIconName && Icons[normalizedIconName] ? Icons[normalizedIconName] : Icons.Circle;
};

const toggleGroup = (key) => {
  openGroups.value[key] = !openGroups.value[key];
};

onMounted(async () => {
  const currentRoute = router.currentRoute.value;
  const isAuthenticated = localStorage.getItem('auth_fandaqah') || localStorage.getItem('sanctum_token');

  if (currentRoute.name === 'login' || currentRoute.path.startsWith('/login') || !isAuthenticated) {
    return;
  }

  try {
    const { data } = await api.get('/sidebar');
    menuItems.value = data.data;
    
    // Auto-expand active groups
    data.data.forEach(item => {
      if (item.is_active && item.children && item.children.length > 0) {
        openGroups.value[item.key] = true;
      }
    });
  } catch (err) {
    if (err.response?.status !== 401) {
        console.error('Failed to load sidebar menu', err);
    } else {
      // If unauthorized, redirect to login
      localStorage.removeItem('sanctum_token');
      localStorage.removeItem('auth_fandaqah');
      router.push('/login');
    }
  }
});
</script>

<style scoped>
.sidebar-wrapper {
  width: 280px;
  background-color: white;
  height: 100vh;
  position: sticky;
  top: 0;
  display: flex;
  flex-direction: column;
  border-inline-end: 1px solid #f1f5f9;
  z-index: 50;
  transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.sidebar-brand {
  padding: 32px 24px;
  display: flex;
  align-items: center;
  gap: 12px;
}

.logo-box {
  width: 40px;
  height: 40px;
  background: #fef2f2;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.brand-text {
  font-size: 20px;
  font-weight: 800;
  color: #2a273c;
  letter-spacing: -0.5px;
}

.nav-container {
  flex: 1;
  overflow-y: auto;
}

.group-toggle {
  width: calc(100% - 24px);
  margin: 0 12px;
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 12px 16px;
  border-radius: 16px;
  color: #64748b;
  font-weight: 500;
  font-size: 14px;
  transition: all 0.3s;
}

.group-toggle:hover {
  background: #f8fafc;
  color: #e95a54;
}

.active-group {
  color: #e95a54;
  background: #fef2f2;
  font-weight: 700;
}

.submenu {
  position: relative;
  margin-inline-start: 28px;
}

.dotted-line {
  position: absolute;
  inset-inline-start: 17px;
  top: 0;
  bottom: 12px;
  width: 1px;
  border-inline-start: 2px dotted #e2e8f0;
}

.pro-card {
  background: linear-gradient(135deg, #2a273c 0%, #1f1d2e 100%);
  padding: 20px;
  border-radius: 20px;
  box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.2);
}

/* RTL Specifics */
[dir="rtl"] .group-toggle span { text-align: start; }
</style>