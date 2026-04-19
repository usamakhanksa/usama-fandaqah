<template>
  <aside class="w-64 bg-white h-screen sticky top-0 p-4 hidden md:flex flex-col border-r">
    <div class="font-bold text-xl mb-4 flex items-center gap-2"><img src="/assets/avatars/admin.svg" class="w-7 h-7">fandaqah</div>
    <nav class="space-y-1 flex-1 overflow-y-auto pr-1">
      <SidebarMenuItem v-for="item in items" :key="item.path" :to="item.path" :icon="item.icon" :label="item.name" />
      <button @click="financeOpen=!financeOpen" class="w-full text-left px-3 py-2 rounded-xl text-slate-500 hover:bg-slate-100 flex items-center gap-2"><span>💳</span>Financial Management</button>
      <div v-if="financeOpen" class="pl-5 space-y-1">
        <SidebarMenuItem v-for="item in financial" :key="item.path" :to="item.path" icon="•" :label="item.name"/>
      </div>
      <button @click="open=!open" class="w-full text-left px-3 py-2 rounded-xl text-slate-500 hover:bg-slate-100 flex items-center gap-2"><span>🧾</span>POS</button>
      <div v-if="open" class="pl-5 space-y-1"><SidebarMenuItem v-for="item in pos" :key="item.path" :to="item.path" icon="•" :label="item.name"/></div>
    </nav>
    <img src="/assets/banners/banner3.svg" class="w-full opacity-70" alt="building"/>
  </aside>
</template>
<script setup>
import { ref } from 'vue';
import SidebarMenuItem from './SidebarMenuItem.vue';
const open = ref(true);
const financeOpen = ref(true);
const items = [
  { name: 'Dashboard', path: '/dashboard', icon: '🏠' },
  { name: 'Rooms', path: '/rooms', icon: '🏨' },
  { name: 'Guest', path: '/guests', icon: '👥' },
  { name: 'Unit Housing', path: '/units', icon: '🏢' },
  { name: 'Reservations Schedule', path: '/reservations/schedule', icon: '📅' },
  { name: 'Reservations Management', path: '/reservations/management', icon: '🛎️' },
  { name: 'Services Management', path: '/services', icon: '🧰' },
  { name: 'User Grouping', path: '/user-groups', icon: '🧩' },
];
const financial = [
  { name: 'Receipts Management', path: '/financial/receipts' },
  { name: 'Expense Management', path: '/financial/expenses' },
  { name: 'Bills Management', path: '/financial/bills' },
  { name: 'Fund Movement Report', path: '/financial/fund-movement' },
  { name: 'Credit Notes Management', path: '/financial/credit-notes' },
];
const pos = [
  { name: 'Make Order', path: '/pos/store' },
  { name: 'Services', path: '/pos/services' },
  { name: 'Transactions', path: '/pos/transactions' },
  { name: 'Products', path: '/pos/products' },
];
</script>
