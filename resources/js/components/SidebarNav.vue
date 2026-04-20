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
        <SidebarMenuItem v-for="item in items" :key="item.path" :to="item.path" :label="item.name">
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
            <span class="flex-1 text-left">Financial</span>
            <ChevronDownIcon class="w-4 h-4 transition-transform duration-300" :class="{ 'rotate-180': financeOpen }" />
          </button>
          
          <div v-if="financeOpen" class="submenu animate-in slide-in-from-top-2 duration-300">
            <div class="dotted-line"></div>
            <SidebarMenuItem v-for="item in financial" :key="item.path" :to="item.path" :label="item.name">
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
            <span class="flex-1 text-left">POS</span>
            <ChevronDownIcon class="w-4 h-4 transition-transform duration-300" :class="{ 'rotate-180': posOpen }" />
          </button>
          
          <div v-if="posOpen" class="submenu">
            <div class="dotted-line"></div>
            <SidebarMenuItem v-for="item in pos" :key="item.path" :to="item.path" :label="item.name">
              <template #icon>
                <div class="dot-icon"></div>
              </template>
            </SidebarMenuItem>
          </div>
        </div>
        
        <!-- Admin Section (Only Show if Admin) -->
        <div v-if="isAdmin" class="admin-divider mt-8 px-8 py-2">
           <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Admin Control</span>
        </div>
        <SidebarMenuItem v-if="isAdmin" to="/leads" label="Leads Management">
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
         <div class="mt-2">
            <p class="text-xs font-bold text-white">Go Pro</p>
            <p class="text-[10px] text-white/70">Unlock corporate features</p>
         </div>
      </div>
    </div>
  </aside>
</template>

<script setup>
import { ref } from 'vue';
import SidebarMenuItem from './SidebarMenuItem.vue';
import { 
  BarChart2Icon, 
  HomeIcon, 
  HotelIcon, 
  UsersIcon, 
  BuildingIcon, 
  CalendarIcon, 
  BriefcaseIcon, 
  PuzzleIcon, 
  SettingsIcon, 
  GlobeIcon,
  CreditCardIcon,
  ChevronDownIcon,
  ShoppingBagIcon,
  ZapIcon,
  MessageSquareIcon
} from 'lucide-vue-next';

const posOpen = ref(true);
const financeOpen = ref(true);
const isAdmin = ref(true); // Temporary until auth logic is added

const items = [
  { name: 'Dashboard', path: '/dashboard', icon: BarChart2Icon },
  { name: 'Rooms', path: '/rooms', icon: HotelIcon },
  { name: 'Guest', path: '/guests', icon: UsersIcon },
  { name: 'Unit Housing', path: '/units', icon: BuildingIcon },
  { name: 'Reservations Schedule', path: '/reservations/schedule', icon: CalendarIcon },
  { name: 'Reservations Management', path: '/reservations/management', icon: BriefcaseIcon },
  { name: 'Services Management', path: '/services', icon: PuzzleIcon },
  { name: 'User Grouping', path: '/user-groups', icon: UsersIcon },
  { name: 'Settings', path: '/settings', icon: SettingsIcon },
  { name: 'Reports', path: '/reports', icon: GlobeIcon },
];

const financial = [
  { name: 'Receipts', path: '/financial/receipts' },
  { name: 'Expenses', path: '/financial/expenses' },
  { name: 'Bills', path: '/financial/bills' },
];

const pos = [
  { name: 'Make Order', path: '/pos/store' },
  { name: 'Services', path: '/pos/services' },
  { name: 'Transactions', path: '/pos/transactions' },
  { name: 'Products', path: '/pos/products' },
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
  border-right: 1px solid #f1f5f9;
  z-index: 50;
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

.brand-img {
  width: 24px;
  height: 24px;
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
  padding-bottom: 24px;
}

.nav-scroll::-webkit-scrollbar {
  width: 4px;
}

.nav-scroll::-webkit-scrollbar-thumb {
  background: #f1f5f9;
  border-radius: 10px;
}

.nav-group {
  margin: 4px 0;
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
  color: #2a273c;
}

.submenu {
  position: relative;
  margin-left: 28px;
}

.dotted-line {
  position: absolute;
  left: 17px;
  top: 0;
  bottom: 12px;
  width: 1px;
  border-left: 2px dotted #e2e8f0;
  z-index: 0;
}

.dot-icon {
  width: 8px;
  height: 8px;
  background: #cbd5e1;
  border-radius: 50%;
  transition: all 0.3s;
}

.active .dot-icon {
  background: white;
  box-shadow: 0 0 10px rgba(255, 255, 255, 0.8);
}

.pro-card {
  background: linear-gradient(135deg, #2a273c 0%, #1f1d2e 100%);
  padding: 20px;
  border-radius: 20px;
  box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.2);
}
</style>
