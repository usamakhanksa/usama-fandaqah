import { createRouter, createWebHistory } from 'vue-router';
import DashboardPage from '../pages/DashboardPage.vue';
import RoomsPage from '../pages/RoomsPage.vue';
import SimplePage from '../pages/SimplePage.vue';

const routes = [
  { path: '/dashboard', component: DashboardPage },
  { path: '/rooms', component: RoomsPage },
  ...['guests','units','reservations/schedule','reservations','financial','services','user-groups','pos','pos/orders','pos/services','pos/transactions','pos/products','profile','settings'].map(p=>({path:`/${p}`,component:SimplePage,props:{title:p}})),
  { path: '/:pathMatch(.*)*', redirect: '/dashboard' },
];

export default createRouter({ history: createWebHistory(), routes });
