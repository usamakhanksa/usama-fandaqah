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
        <!-- Main items -->
        <SidebarMenuItem v-for="item in items" :key="item.path" :to="item.path" :label="$t(`nav.${item.key}`)">
          <template #icon>
            <component :is="item.icon" class="w-5 h-5" />
          </template>
        </SidebarMenuItem>

        <!-- Collapsible: Financial Management -->
        <div class="nav-group">
          <button 
            @click="financeOpen = !financeOpen" 
            class="group-toggle"
            :class="{ 'expanded': financeOpen }"
          >
            <CreditCardIcon class="w-5 h-5 flex-shrink-0" />
            <span class="flex-1 text-start">{{ $t('nav.financial') }}</span>
            <ChevronDownIcon class="w-4 h-4 transition-transform duration-300" :class="{ 'rotate-180': financeOpen }" />
          </button>
          
          <div v-if="financeOpen" class="submenu animate-in slide-in-from-top-2 duration-300">
            <div class="dotted-line"></div>
            <SidebarMenuItem v-for="item in financial" :key="item.path" :to="item.path" :label="$t(`nav.${item.key}`)">
              <template #icon>
                <div class="dot-icon"></div>
              </template>
            </SidebarMenuItem>
          </div>
        </div>

        <!-- Collapsible: POS -->
        <div class="nav-group">
          <button 
            @click="posOpen = !posOpen" 
            class="group-toggle"
            :class="{ 'expanded': posOpen }"
          >
            <ShoppingBagIcon class="w-5 h-5 flex-shrink-0" />
            <span class="flex-1 text-start">{{ $t('nav.pos') }}</span>
            <ChevronDownIcon class="w-4 h-4 transition-transform duration-300" :class="{ 'rotate-180': posOpen }" />
          </button>
          
          <div v-if="posOpen" class="submenu">
            <div class="dotted-line"></div>
            <SidebarMenuItem v-for="item in pos" :key="item.path" :to="item.path" :label="$t(`nav.${item.key}`)">
              <template #icon>
                <div class="dot-icon"></div>
              </template>
            </SidebarMenuItem>
          </div>
        </div>
        
        <!-- Admin Section -->
        <div v-if="isAdmin" class="admin-divider mt-8 px-8 py-2">
           <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ locale === 'ar' ? 'التحكم بالإدارة' : 'Admin Control' }}</span>
        </div>
        <SidebarMenuItem v-if="isAdmin" to="/leads" :label="$t('nav.leads')">
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
            <ZapIcon class="w-4 h-4 text-white" />
         </div>
         <div class="mt-2 text-white">
            <p class="text-xs font-bold">{{ locale === 'ar' ? 'اشترك في النسخة الاحترافية' : 'Go Pro' }}</p>
            <p class="text-[10px] opacity-70">{{ locale === 'ar' ? 'افتح مميزات الشركات المتطورة' : 'Unlock corporate features' }}</p>
         </div>
      </div>
    </div>
  </aside>
</template>

<script setup>
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import SidebarMenuItem from './SidebarMenuItem.vue';
import { 
  BarChart2Icon, HomeIcon, HotelIcon, UsersIcon, BuildingIcon, 
  CalendarIcon, BriefcaseIcon, PuzzleIcon, SettingsIcon, GlobeIcon,
  CreditCardIcon, ChevronDownIcon, ShoppingBagIcon, ZapIcon, MessageSquareIcon
} from 'lucide-vue-next';

const { locale } = useI18n();
const posOpen = ref(false);
const financeOpen = ref(false);
const isAdmin = ref(true);

const items = [
  { key: 'dashboard', path: '/dashboard', icon: BarChart2Icon },
  { key: 'rooms', path: '/rooms', icon: HotelIcon },
  { key: 'guests', path: '/guests', icon: UsersIcon },
  { key: 'units', path: '/units', icon: BuildingIcon },
  { key: 'schedule', path: '/reservations/schedule', icon: CalendarIcon },
  { key: 'management', path: '/reservations/management', icon: BriefcaseIcon },
  { key: 'services', path: '/services', icon: PuzzleIcon },
  { key: 'user_groups', path: '/user-groups', icon: UsersIcon },
  { key: 'settings', path: '/settings', icon: SettingsIcon },
  { key: 'reports', path: '/reports', icon: GlobeIcon },
];

const financial = [
  { key: 'receipts', path: '/financial/receipts' },
  { key: 'expenses', path: '/financial/expenses' },
  { key: 'bills', path: '/financial/bills' },
];

const pos = [
  { key: 'make_order', path: '/pos/store' },
  { key: 'services', path: '/pos/services' },
  { key: 'transactions', path: '/pos/transactions' },
  { key: 'products', path: '/pos/products' },
];
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
