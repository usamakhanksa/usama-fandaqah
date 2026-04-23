import { createRouter, createWebHistory } from 'vue-router';
import { flattenSidebarRoutes, pagePathFromRoute } from '../config/sidebarConfig';
import { useAuthStore } from '../stores/auth';
import LoginPage from '../pages/LoginPage.vue';

const pageModules = import.meta.glob('../pages/**/*.vue', { eager: true });

const resolvePage = (path) => {
  const pageName = pagePathFromRoute(path);
  const potentialPaths = [
    `../pages/${pageName}.vue`,
    `../pages/${pageName}Page.vue`,
    `../pages/${pageName}/Index.vue`,
  ];
  
  for (const p of potentialPaths) {
    if (pageModules[p]) return pageModules[p].default || pageModules[p];
  }
  
  console.warn(`[Router] Could not resolve component for path: ${path} (tried ${potentialPaths.join(', ')})`);
  return () => import('../pages/SimplePage.vue'); // Fallback
};

const appRoutes = [
    {
        path: '/dashboard',
        name: 'dashboard',
        component: () => import('@/pages/Dashboard.vue'),
        meta: { requiresAuth: true }
    },
    {
        path: '/dashboard-notices',
        name: 'dashboard-notices.index',
        component: () => import('@/pages/DashboardNotices/Index.vue'),
        meta: { requiresAuth: true }
    },
    {
        path: '/dashboard-notices/create',
        name: 'dashboard-notices.create',
        component: () => import('@/pages/DashboardNotices/Create.vue'),
        meta: { requiresAuth: true }
    },
    {
        path: '/dashboard-notices/:id/edit',
        name: 'dashboard-notices.edit',
        component: () => import('@/pages/DashboardNotices/Edit.vue'),
        meta: { requiresAuth: true }
    },
    ...flattenSidebarRoutes().map((node) => ({
      path: node.path,
      name: node.route,
      component: resolvePage(node.path),
      meta: {
        permission: node.permission,
        requiresAuth: true,
      },
    }))
];

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
    if (to.name === 'dashboard') {
        console.error('CRITICAL: User has no permission for Dashboard. Logging out to prevent loop.');
        authStore.logout();
        return { name: 'login' };
    }
    return { name: 'dashboard' };
  }

  return true;
});

export default router;
