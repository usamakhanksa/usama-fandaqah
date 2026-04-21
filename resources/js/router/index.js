import { createRouter, createWebHistory } from 'vue-router';
import { flattenSidebarRoutes, pagePathFromRoute } from '../config/sidebarConfig';
import { useAuthStore } from '../stores/auth';
import LoginPage from '../pages/LoginPage.vue';

const pageModules = import.meta.glob('../Pages/**/*.vue');

const resolvePage = (path) => {
  const pagePath = `../Pages/${pagePathFromRoute(path)}.vue`;
  return pageModules[pagePath];
};

const appRoutes = flattenSidebarRoutes().map((node) => ({
  path: node.path,
  name: node.route,
  component: resolvePage(node.path),
  meta: {
    permission: node.permission,
    requiresAuth: true,
  },
}));

const routes = [
  { path: '/login', name: 'login', component: LoginPage },
  ...appRoutes,
  { path: '/', redirect: '/dashboard' },
  { path: '/:pathMatch(.*)*', redirect: '/dashboard' },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach((to) => {
  const authStore = useAuthStore();

  if (to.name !== 'login' && !authStore.isAuthenticated) {
    return { name: 'login' };
  }

  if (to.name === 'login' && authStore.isAuthenticated) {
    return { name: 'dashboard' };
  }

  if (to.meta.permission && !authStore.can(to.meta.permission)) {
    return { name: 'dashboard' };
  }

  return true;
});

export default router;
